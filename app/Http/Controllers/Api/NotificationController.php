<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Models\Employee;
use App\Models\MobileNotification;
use Illuminate\Http\Request;

class NotificationController extends Controller
{
    /**
     * GET /api/v1/notifications
     */
    public function index(Request $request)
    {
        $employee = Employee::where('user_id', $request->user()->id)->firstOrFail();

        $notifications = MobileNotification::where('employee_id', $employee->id)
            ->where('target', 'employee')
            ->orderByDesc('created_at')
            ->paginate(20);

        return response()->json([
            'success'      => true,
            'unread_count' => MobileNotification::where('employee_id', $employee->id)
                ->where('target', 'employee')
                ->where('is_read', false)
                ->count(),
            'data' => $notifications->map(fn($n) => [
                'id'         => $n->id,
                'type'       => $n->type,
                'title'      => $n->title,
                'body'       => $n->body,
                'data'       => $n->data,
                'is_read'    => $n->is_read,
                'created_at' => $n->created_at->format('Y-m-d H:i'),
            ]),
        ]);
    }

    /**
     * POST /api/v1/notifications/read
     * تعليم كل الإشعارات كمقروءة
     */
    public function markAllRead(Request $request)
    {
        $employee = Employee::where('user_id', $request->user()->id)->firstOrFail();

        MobileNotification::where('employee_id', $employee->id)
            ->where('target', 'employee')
            ->where('is_read', false)
            ->update(['is_read' => true, 'read_at' => now()]);

        return response()->json(['success' => true]);
    }

    /**
     * POST /api/v1/fcm-token
     * حفظ FCM token للجهاز
     */
    public function saveFcmToken(Request $request)
    {
        $request->validate(['fcm_token' => 'required|string']);

        $employee = Employee::where('user_id', $request->user()->id)->firstOrFail();
        $employee->update(['fcm_token' => $request->fcm_token]);

        return response()->json(['success' => true]);
    }
}
