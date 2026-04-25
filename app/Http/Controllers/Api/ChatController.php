<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Models\Chat;
use App\Models\Employee;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Storage;
use Illuminate\Validation\Rule;

class ChatController extends Controller
{
    /**
     * GET /api/v1/chat
     * جلب المحادثة — مرتبة من الأقدم للأحدث
     * ?after=ID  → يجلب فقط الرسائل بعد هذا الـ ID (للـ polling)
     */
    public function index(Request $request)
    {
        $employee = Employee::where('user_id', $request->user()->id)->firstOrFail();

        $query = Chat::where('employee_id', $employee->id)
            ->orderBy('id', 'asc'); // الأقدم أولاً دائماً

        // polling mode: جلب الرسائل الجديدة فقط
        if ($request->filled('after')) {
            $query->where('id', '>', (int) $request->input('after'));
        } else {
            // تحميل أول مرة: آخر 100 رسالة (الأحدث ثم نعكس)
            $query = Chat::where('employee_id', $employee->id)
                ->orderBy('id', 'desc')
                ->limit(100);
            $chats = $query->get()->reverse()->values();

            // تحديد رسائل الإدارة كمقروءة
            Chat::where('employee_id', $employee->id)
                ->where('sender_type', 'admin')
                ->where('is_read', false)
                ->update(['is_read' => true]);

            return response()->json([
                'success' => true,
                'data'    => $chats->map(fn($c) => $this->formatMessage($c, $employee)),
            ]);
        }

        $chats = $query->get();

        // تحديد رسائل الإدارة كمقروءة
        if ($chats->where('sender_type', 'admin')->where('is_read', false)->count()) {
            Chat::where('employee_id', $employee->id)
                ->where('sender_type', 'admin')
                ->where('is_read', false)
                ->update(['is_read' => true]);
        }

        return response()->json([
            'success' => true,
            'data'    => $chats->map(fn($c) => $this->formatMessage($c, $employee)),
        ]);
    }

    private function formatMessage($chat, $employee): array
    {
        return [
            'id'              => $chat->id,
            'sender_type'     => $chat->sender_type,
            'sender_name'     => $chat->sender_type === 'admin' ? 'الإدارة' : $employee->name,
            'message'         => $chat->message,
            'attachment_url'  => $chat->attachment_path
                ? \Storage::url($chat->attachment_path) : null,
            'attachment_type' => $chat->attachment_type,
            'attachment_name' => $chat->attachment_name,
            'is_read'         => (bool) $chat->is_read,
            'created_at'      => $chat->created_at->toIso8601String(),
            'time_ago'        => $chat->created_at->format('h:i A'),
            'ago'             => $chat->created_at->diffForHumans(),
        ];
    }

    /**
     * POST /api/v1/chat
     * إرسال رسالة (نص أو ملف multipart)
     */
    public function store(Request $request)
    {
        $request->validate([
            'message'    => 'nullable|string|max:5000',
            'attachment' => 'nullable|file|mimes:jpg,jpeg,png,pdf,doc,docx|max:20480',
        ]);

        // التحقق من أن هناك رسالة أو ملف
        if (!$request->message && !$request->hasFile('attachment')) {
            return response()->json([
                'success' => false,
                'message' => 'يجب إدخال نص أو إرفاق ملف.',
            ], 422);
        }

        $employee = Employee::where('user_id', $request->user()->id)->firstOrFail();

        $attachmentPath = null;
        $attachmentType = null;
        $attachmentName = null;

        // معالجة الملف
        if ($request->hasFile('attachment')) {
            $file = $request->file('attachment');
            $attachmentName = $file->getClientOriginalName();
            $extension = strtolower($file->getClientOriginalExtension());

            // تحديد نوع الملف
            if (in_array($extension, ['jpg', 'jpeg', 'png'])) {
                $attachmentType = 'image';
            } elseif (in_array($extension, ['pdf', 'doc', 'docx'])) {
                $attachmentType = 'document';
            } else {
                return response()->json([
                    'success' => false,
                    'message' => 'نوع الملف غير مدعوم.',
                ], 422);
            }

            // حفظ الملف
            $filename = 'chat_' . $employee->id . '_' . time() . '_' . uniqid() . '.' . $extension;
            $attachmentPath = Storage::disk('public')->putFileAs(
                'chat',
                $file,
                $filename
            );

            if (!$attachmentPath) {
                return response()->json([
                    'success' => false,
                    'message' => 'فشل حفظ الملف. حاول مرة أخرى.',
                ], 500);
            }
        }

        // إنشاء الرسالة
        $chat = Chat::create([
            'employee_id'      => $employee->id,
            'sender_type'      => 'employee',
            'message'          => $request->message ?? null,
            'attachment_path'  => $attachmentPath,
            'attachment_type'  => $attachmentType,
            'attachment_name'  => $attachmentName,
            'is_read'          => false,
        ]);

        return response()->json([
            'success' => true,
            'message' => 'تم إرسال الرسالة بنجاح.',
            'data'    => $this->formatMessage($chat, $employee),
        ], 201);
    }

    /**
     * POST /api/v1/chat/read
     * تحديث كل الرسائل كمقروءة
     */
    public function markRead(Request $request)
    {
        $employee = Employee::where('user_id', $request->user()->id)->firstOrFail();

        $updatedCount = Chat::where('employee_id', $employee->id)
            ->where('is_read', false)
            ->update(['is_read' => true]);

        return response()->json([
            'success' => true,
            'message' => 'تم تحديث حالة الرسائل.',
            'updated_count' => $updatedCount,
        ]);
    }

    /**
     * GET /api/v1/chat/new
     * جلب الرسائل الجديدة (للـ polling)
     */
    public function getNew(Request $request)
    {
        $request->validate([
            'since' => 'nullable|date_format:Y-m-d H:i:s',
        ]);

        $employee = Employee::where('user_id', $request->user()->id)->firstOrFail();

        $query = Chat::where('employee_id', $employee->id)
            ->with('employee');

        if ($request->since) {
            $query->where('created_at', '>', $request->since);
        }

        $chats = $query->orderBy('created_at', 'asc')->get();

        return response()->json([
            'success' => true,
            'data'    => $chats->map(function ($chat) {
                return [
                    'id'              => $chat->id,
                    'sender_type'     => $chat->sender_type,
                    'sender_name'     => $chat->sender_type === 'admin' ? 'الإدارة' : $chat->employee->name,
                    'sender_photo'    => $chat->employee->photo_url,
                    'message'         => $chat->message,
                    'attachment_url'  => $chat->attachment_url,
                    'attachment_type' => $chat->attachment_type,
                    'attachment_name' => $chat->attachment_name,
                    'is_read'         => $chat->is_read,
                    'created_at'      => $chat->created_at->toIso8601String(),
                ];
            }),
        ]);
    }

    /**
     * GET /api/v1/chat/unread-count
     * عدد الرسائل غير المقروءة
     */
    public function getUnreadCount(Request $request)
    {
        $employee = Employee::where('user_id', $request->user()->id)->firstOrFail();

        $unreadCount = Chat::where('employee_id', $employee->id)
            ->where('is_read', false)
            ->count();

        return response()->json([
            'success' => true,
            'unread_count' => $unreadCount,
        ]);
    }
}
