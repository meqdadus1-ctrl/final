<?php

namespace App\Http\Controllers;

use App\Models\Chat;
use App\Models\Employee;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Storage;

/**
 * لوحة التحكم — إدارة محادثات الموظفين
 * Route: /admin/chat
 */
class AdminChatController extends Controller
{
    /**
     * قائمة الموظفين مع عدد رسائلهم الجديدة
     */
    public function index()
    {
        $employees = Employee::active()
            ->withCount(['chats as unread_count' => function ($q) {
                $q->where('sender_type', 'employee')->where('is_read', false);
            }])
            ->get(['id', 'name', 'job_title', 'photo', 'department_id'])
            ->each(function ($emp) {
                $emp->last_message = Chat::where('employee_id', $emp->id)
                    ->latest()
                    ->first();
            })
            ->sortByDesc('unread_count');

        $totalUnread = $employees->sum('unread_count');

        return view('chat.index', compact('employees', 'totalUnread'));
    }

    /**
     * محادثة موظف معين
     */
    public function show(Employee $employee)
    {
        $messages = Chat::where('employee_id', $employee->id)
            ->orderBy('created_at', 'asc')
            ->get();

        // تحديد رسائل الموظف كمقروءة
        Chat::where('employee_id', $employee->id)
            ->where('sender_type', 'employee')
            ->where('is_read', false)
            ->update(['is_read' => true]);

        return view('chat.show', compact('employee', 'messages'));
    }

    /**
     * إرسال رسالة من الإدارة للموظف
     */
    public function send(Request $request, Employee $employee)
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

            if (in_array($mime, ['image/jpeg', 'image/png', 'image/gif', 'image/webp'])
                || in_array($ext, ['jpg', 'jpeg', 'png', 'gif', 'webp'])) {
                $attachmentType = 'image';
                $folder         = 'chat/images';
            } else {
                $attachmentType = 'document';
                $folder         = 'chat/documents';
            }

            $attachmentPath = $file->store($folder, 'public');
            $attachmentName = $file->getClientOriginalName();
        }

        Chat::create([
            'employee_id'     => $employee->id,
            'sender_type'     => 'admin',
            'message'         => $request->message,
            'attachment_path' => $attachmentPath,
            'attachment_type' => $attachmentType,
            'attachment_name' => $attachmentName,
            'is_read'         => false,
        ]);

        // إرسال FCM للموظف إذا عنده token
        if ($employee->fcm_token) {
            try {
                app(\App\Services\FcmService::class)->sendToEmployee(
                    $employee->fcm_token,
                    'رسالة جديدة من الإدارة 💬',
                    $request->message ?? 'تم إرسال مرفق جديد'
                );
            } catch (\Exception $e) {
                // لا نوقف العملية إذا فشل الإشعار
            }
        }

        if (request()->expectsJson()) {
            return response()->json(['ok' => true]);
        }
        return back()->with('success', 'تم إرسال الرسالة');
    }

    /**
     * حذف رسالة
     */
    public function destroy(Chat $chat)
    {
        if ($chat->attachment_path) {
            Storage::disk('public')->delete($chat->attachment_path);
        }
        $chat->delete();

        return response()->json(['ok' => true]);
    }

    /**
     * Polling — رسائل جديدة بعد آخر ID معروف
     */
    public function poll(Request $request, Employee $employee)
    {
        $afterId = (int) $request->query('after', 0);

        $messages = Chat::where('employee_id', $employee->id)
            ->where('id', '>', $afterId)
            ->orderBy('id')
            ->get()
            ->map(fn($m) => [
                'id'              => $m->id,
                'sender_type'     => $m->sender_type,
                'message'         => $m->message,
                'attachment_type' => $m->attachment_type,
                'attachment_url'  => $m->attachment_path
                    ? Storage::url($m->attachment_path) : null,
                'attachment_name' => $m->attachment_name,
                'is_read'         => $m->is_read,
                'time'            => $m->created_at->format('h:i A'),
                'ago'             => $m->created_at->diffForHumans(),
            ]);

        // تحديد رسائل الموظف كمقروءة
        Chat::where('employee_id', $employee->id)
            ->where('sender_type', 'employee')
            ->where('is_read', false)
            ->update(['is_read' => true]);

        return response()->json(['messages' => $messages]);
    }

    /**
     * Polling قائمة المحادثات — عدد الرسائل غير المقروءة لكل موظف
     */
    public function pollList()
    {
        $unread = Chat::where('sender_type', 'employee')
            ->where('is_read', false)
            ->selectRaw('employee_id, count(*) as cnt')
            ->groupBy('employee_id')
            ->pluck('cnt', 'employee_id');

        return response()->json([
            'total' => $unread->sum(),
            'byEmp' => $unread,
        ]);
    }
}
