<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Models\Chat;
use App\Models\Employee;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Storage;

class AdminChatController extends Controller
{
    /**
     * GET /api/v1/admin/chats
     * جلب كل المحادثات (للإدارة) مع pagination
     */
    public function index(Request $request)
    {
        $request->validate([
            'employee_id' => 'nullable|integer|exists:employees,id',
            'page'        => 'nullable|integer|min:1',
            'per_page'    => 'nullable|integer|min:5|max:100',
            'search'      => 'nullable|string|max:255',
        ]);

        $perPage = $request->input('per_page', 20);
        $query = Chat::with('employee');

        // تصفية حسب الموظف
        if ($request->employee_id) {
            $query->where('employee_id', $request->employee_id);
        }

        // البحث في الرسائل والأسماء
        if ($request->search) {
            $search = $request->search;
            $query->whereHas('employee', function ($q) use ($search) {
                $q->where('name', 'like', "%{$search}%");
            })->orWhere('message', 'like', "%{$search}%");
        }

        $chats = $query->latest()->paginate($perPage);

        return response()->json([
            'success' => true,
            'data'    => $chats->map(function ($chat) {
                return [
                    'id'              => $chat->id,
                    'employee_id'     => $chat->employee_id,
                    'employee_name'   => $chat->employee->name,
                    'sender_type'     => $chat->sender_type,
                    'message'         => $chat->message,
                    'attachment_url'  => $chat->attachment_url,
                    'attachment_type' => $chat->attachment_type,
                    'attachment_name' => $chat->attachment_name,
                    'is_read'         => $chat->is_read,
                    'created_at'      => $chat->created_at->toIso8601String(),
                ];
            }),
            'pagination' => [
                'current_page' => $chats->currentPage(),
                'total'        => $chats->total(),
                'per_page'     => $chats->perPage(),
                'last_page'    => $chats->lastPage(),
            ],
        ]);
    }

    /**
     * GET /api/v1/admin/chats/{employeeId}
     * جلب محادثة موظف معين
     */
    public function show(Request $request, $employeeId)
    {
        $request->validate([
            'page'     => 'nullable|integer|min:1',
            'per_page' => 'nullable|integer|min:5|max:100',
        ]);

        $employee = Employee::findOrFail($employeeId);
        $perPage = $request->input('per_page', 30);

        $chats = Chat::where('employee_id', $employeeId)
            ->with('employee')
            ->latest()
            ->paginate($perPage);

        return response()->json([
            'success' => true,
            'employee' => [
                'id'         => $employee->id,
                'name'       => $employee->name,
                'email'      => $employee->user?->email,
                'photo_url'  => $employee->photo_url,
                'job_title'  => $employee->job_title,
            ],
            'data' => $chats->map(function ($chat) {
                return [
                    'id'              => $chat->id,
                    'sender_type'     => $chat->sender_type,
                    'sender_name'     => $chat->sender_type === 'admin' ? 'الإدارة' : $chat->employee->name,
                    'message'         => $chat->message,
                    'attachment_url'  => $chat->attachment_url,
                    'attachment_type' => $chat->attachment_type,
                    'attachment_name' => $chat->attachment_name,
                    'is_read'         => $chat->is_read,
                    'created_at'      => $chat->created_at->toIso8601String(),
                ];
            }),
            'pagination' => [
                'current_page' => $chats->currentPage(),
                'total'        => $chats->total(),
                'per_page'     => $chats->perPage(),
                'last_page'    => $chats->lastPage(),
            ],
        ]);
    }

    /**
     * POST /api/v1/admin/chats/{employeeId}/send
     * الإدارة ترسل رسالة لموظف
     */
    public function send(Request $request, $employeeId)
    {
        $request->validate([
            'message'    => 'nullable|string|max:5000',
            'attachment' => 'nullable|file|mimes:jpg,jpeg,png,pdf,doc,docx|max:20480',
        ]);

        if (!$request->message && !$request->hasFile('attachment')) {
            return response()->json([
                'success' => false,
                'message' => 'يجب إدخال نص أو إرفاق ملف.',
            ], 422);
        }

        $employee = Employee::findOrFail($employeeId);

        $attachmentPath = null;
        $attachmentType = null;
        $attachmentName = null;

        // معالجة الملف
        if ($request->hasFile('attachment')) {
            $file = $request->file('attachment');
            $attachmentName = $file->getClientOriginalName();
            $extension = strtolower($file->getClientOriginalExtension());

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

            $filename = 'chat_admin_' . $employee->id . '_' . time() . '_' . uniqid() . '.' . $extension;
            $attachmentPath = Storage::disk('public')->putFileAs(
                'chat',
                $file,
                $filename
            );

            if (!$attachmentPath) {
                return response()->json([
                    'success' => false,
                    'message' => 'فشل حفظ الملف.',
                ], 500);
            }
        }

        $chat = Chat::create([
            'employee_id'      => $employee->id,
            'sender_type'      => 'admin',
            'message'          => $request->message ?? null,
            'attachment_path'  => $attachmentPath,
            'attachment_type'  => $attachmentType,
            'attachment_name'  => $attachmentName,
            'is_read'          => false,
        ]);

        return response()->json([
            'success' => true,
            'message' => 'تم إرسال الرسالة بنجاح.',
            'data'    => [
                'id'              => $chat->id,
                'employee_id'     => $employee->id,
                'sender_type'     => $chat->sender_type,
                'sender_name'     => 'الإدارة',
                'message'         => $chat->message,
                'attachment_url'  => $chat->attachment_url,
                'attachment_type' => $chat->attachment_type,
                'attachment_name' => $chat->attachment_name,
                'created_at'      => $chat->created_at->toIso8601String(),
            ],
        ], 201);
    }

    /**
     * POST /api/v1/admin/chats/{chatId}/delete
     * حذف رسالة
     */
    public function delete(Request $request, $chatId)
    {
        $chat = Chat::findOrFail($chatId);

        // حذف الملف إذا كان موجود
        if ($chat->attachment_path) {
            Storage::disk('public')->delete($chat->attachment_path);
        }

        $chat->delete();

        return response()->json([
            'success' => true,
            'message' => 'تم حذف الرسالة بنجاح.',
        ]);
    }

    /**
     * POST /api/v1/admin/chats/employee/{employeeId}/clear
     * حذف كل محادثات الموظف
     */
    public function clearEmployee(Request $request, $employeeId)
    {
        $request->validate([
            'confirm' => 'required|boolean|accepted',
        ]);

        $chats = Chat::where('employee_id', $employeeId)->get();

        foreach ($chats as $chat) {
            if ($chat->attachment_path) {
                Storage::disk('public')->delete($chat->attachment_path);
            }
            $chat->delete();
        }

        return response()->json([
            'success' => true,
            'message' => 'تم حذف كل المحادثات بنجاح.',
            'deleted_count' => count($chats),
        ]);
    }

    /**
     * GET /api/v1/admin/chats/stats
     * إحصائيات المحادثات
     */
    public function stats(Request $request)
    {
        $totalChats = Chat::count();
        $unreadChats = Chat::where('is_read', false)->count();
        $employeeChats = Chat::where('sender_type', 'employee')->count();
        $adminChats = Chat::where('sender_type', 'admin')->count();
        $chatImages = Chat::where('attachment_type', 'image')->count();
        $chatDocuments = Chat::where('attachment_type', 'document')->count();

        // عدد الموظفين اللذين لديهم محادثات
        $employeesWithChats = Chat::distinct('employee_id')->count('employee_id');

        // أنشط الموظفين
        $topEmployees = Chat::select('employee_id')
            ->groupBy('employee_id')
            ->orderByRaw('COUNT(*) DESC')
            ->limit(10)
            ->with('employee')
            ->get()
            ->map(function ($record) {
                return [
                    'employee_id' => $record->employee_id,
                    'employee_name' => $record->employee->name,
                    'message_count' => Chat::where('employee_id', $record->employee_id)->count(),
                ];
            });

        return response()->json([
            'success' => true,
            'stats'   => [
                'total_chats'        => $totalChats,
                'unread_chats'       => $unreadChats,
                'employee_messages'  => $employeeChats,
                'admin_messages'     => $adminChats,
                'image_attachments'  => $chatImages,
                'document_attachments' => $chatDocuments,
                'employees_with_chats' => $employeesWithChats,
                'top_10_employees'   => $topEmployees,
            ],
        ]);
    }
}
