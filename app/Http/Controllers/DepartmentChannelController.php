<?php

namespace App\Http\Controllers;

use App\Models\Department;
use App\Models\DepartmentMessage;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Storage;

class DepartmentChannelController extends Controller
{
    /* =====================================================
     *  INDEX — قائمة قنوات الأقسام
     * ===================================================== */
    public function index()
    {
        $userId = auth()->id();

        $departments = Department::withCount('employees')
            ->with(['employees' => fn($q) => $q->select('employees.id', 'department_id')])
            ->orderBy('name')
            ->get()
            ->map(function ($dept) use ($userId) {
                $lastMsg = DepartmentMessage::where('department_id', $dept->id)
                    ->latest()->first();

                $lastRead = DB::table('department_message_reads')
                    ->where('user_id', $userId)
                    ->where('department_id', $dept->id)
                    ->value('last_read_id') ?? 0;

                $unread = DepartmentMessage::where('department_id', $dept->id)
                    ->where('id', '>', $lastRead)
                    ->count();

                $dept->last_message = $lastMsg;
                $dept->unread_count = $unread;
                return $dept;
            })
            ->sortByDesc('unread_count');

        $totalUnread = $departments->sum('unread_count');

        return view('channels.index', compact('departments', 'totalUnread'));
    }

    /* =====================================================
     *  SHOW — رسائل قسم معين
     * ===================================================== */
    public function show(Department $department)
    {
        $messages = DepartmentMessage::where('department_id', $department->id)
            ->with('sender')
            ->orderBy('created_at', 'asc')
            ->get();

        // تحديث آخر رسالة مقروءة
        $lastId = $messages->max('id') ?? 0;
        if ($lastId > 0) {
            DB::table('department_message_reads')->upsert(
                [
                    'user_id'       => auth()->id(),
                    'department_id' => $department->id,
                    'last_read_id'  => $lastId,
                    'created_at'    => now(),
                    'updated_at'    => now(),
                ],
                ['user_id', 'department_id'],
                ['last_read_id', 'updated_at']
            );
        }

        $departments = Department::orderBy('name')->get(['id', 'name']);

        return view('channels.show', compact('department', 'messages', 'departments'));
    }

    /* =====================================================
     *  SEND — إرسال رسالة للقسم
     * ===================================================== */
    public function send(Request $request, Department $department)
    {
        $request->validate([
            'message'    => 'nullable|string|max:2000',
            'attachment' => 'nullable|file|max:10240',
        ]);

        if (!$request->filled('message') && !$request->hasFile('attachment')) {
            return back()->with('error', 'أدخل رسالة أو ارفق ملفاً');
        }

        $attachmentPath = null;
        $attachmentType = null;
        $attachmentName = null;

        if ($request->hasFile('attachment')) {
            $file = $request->file('attachment');
            $mime = $file->getMimeType();
            $ext  = strtolower($file->getClientOriginalExtension());

            if (in_array($mime, ['image/jpeg','image/png','image/gif','image/webp'])
                || in_array($ext, ['jpg','jpeg','png','gif','webp'])) {
                $attachmentType = 'image';
                $folder         = 'channels/images';
            } else {
                $attachmentType = 'document';
                $folder         = 'channels/documents';
            }

            $attachmentPath = $file->store($folder, 'public');
            $attachmentName = $file->getClientOriginalName();
        }

        $msg = DepartmentMessage::create([
            'department_id'   => $department->id,
            'sender_id'       => auth()->id(),
            'message'         => $request->message,
            'attachment_path' => $attachmentPath,
            'attachment_type' => $attachmentType,
            'attachment_name' => $attachmentName,
        ]);

        // تحديث last_read للمُرسِل نفسه فوراً
        DB::table('department_message_reads')->upsert(
            [
                'user_id'       => auth()->id(),
                'department_id' => $department->id,
                'last_read_id'  => $msg->id,
                'created_at'    => now(),
                'updated_at'    => now(),
            ],
            ['user_id', 'department_id'],
            ['last_read_id', 'updated_at']
        );

        // FCM لكل أعضاء القسم (ما عدا المُرسِل)
        $this->notifyDepartmentMembers($department, $request->message ?? 'مرفق جديد');

        if ($request->expectsJson()) {
            return response()->json([
                'ok'  => true,
                'msg' => $this->formatMessage($msg),
            ]);
        }

        return back()->with('success', 'تم إرسال الرسالة');
    }

    /* =====================================================
     *  DESTROY — حذف رسالة
     * ===================================================== */
    public function destroy(DepartmentMessage $message)
    {
        if ($message->attachment_path) {
            Storage::disk('public')->delete($message->attachment_path);
        }
        $message->delete();

        return response()->json(['ok' => true]);
    }

    /* =====================================================
     *  POLL — رسائل جديدة بعد آخر ID
     * ===================================================== */
    public function poll(Request $request, Department $department)
    {
        $afterId = (int) $request->query('after', 0);

        $messages = DepartmentMessage::where('department_id', $department->id)
            ->where('id', '>', $afterId)
            ->with('sender')
            ->orderBy('id')
            ->get()
            ->map(fn($m) => $this->formatMessage($m));

        // تحديث last_read
        if ($messages->isNotEmpty()) {
            $lastId = $messages->max('id');
            DB::table('department_message_reads')->upsert(
                [
                    'user_id'       => auth()->id(),
                    'department_id' => $department->id,
                    'last_read_id'  => $lastId,
                    'created_at'    => now(),
                    'updated_at'    => now(),
                ],
                ['user_id', 'department_id'],
                ['last_read_id', 'updated_at']
            );
        }

        return response()->json(['messages' => $messages]);
    }

    /* =====================================================
     *  POLL LIST — عدد غير المقروءة لكل قسم
     * ===================================================== */
    public function pollList()
    {
        $userId = auth()->id();

        $reads = DB::table('department_message_reads')
            ->where('user_id', $userId)
            ->pluck('last_read_id', 'department_id');

        $unread = DepartmentMessage::selectRaw('department_id, count(*) as cnt')
            ->where(function ($q) use ($reads) {
                foreach ($reads as $deptId => $lastId) {
                    $q->orWhere(function ($sq) use ($deptId, $lastId) {
                        $sq->where('department_id', $deptId)->where('id', '>', $lastId);
                    });
                }
                // أقسام لم يُقرأ منها شيء بعد
                $q->orWhereNotIn('department_id', $reads->keys()->toArray());
            })
            ->groupBy('department_id')
            ->pluck('cnt', 'department_id');

        return response()->json([
            'total'   => $unread->sum(),
            'byDept'  => $unread,
        ]);
    }

    /* =====================================================
     *  HELPERS
     * ===================================================== */
    private function formatMessage(DepartmentMessage $m): array
    {
        return [
            'id'              => $m->id,
            'sender_name'     => $m->sender?->name ?? 'غير معروف',
            'is_mine'         => $m->sender_id === auth()->id(),
            'message'         => $m->message,
            'attachment_type' => $m->attachment_type,
            'attachment_url'  => $m->attachment_path ? Storage::url($m->attachment_path) : null,
            'attachment_name' => $m->attachment_name,
            'time'            => $m->created_at->format('h:i A'),
            'ago'             => $m->created_at->diffForHumans(),
        ];
    }

    private function notifyDepartmentMembers(Department $department, string $text): void
    {
        $senderId = auth()->id();
        $senderName = auth()->user()->name ?? 'الإدارة';

        $tokens = $department->employees()
            ->whereNotNull('fcm_token')
            ->whereHas('user', fn($q) => $q->where('id', '!=', $senderId))
            ->pluck('fcm_token');

        if ($tokens->isEmpty()) return;

        try {
            $fcm = app(\App\Services\FcmService::class);
            foreach ($tokens as $token) {
                $fcm->sendToEmployee(
                    $token,
                    "📢 {$department->name} — رسالة جديدة",
                    "{$senderName}: {$text}"
                );
            }
        } catch (\Exception $e) {
            // لا نوقف العملية إذا فشل الإشعار
        }
    }
}
