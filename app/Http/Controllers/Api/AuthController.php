<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Models\Employee;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Hash;
use App\Models\User;

class AuthController extends Controller
{
    /**
     * POST /api/v1/login
     */
    public function login(Request $request)
    {
        $request->validate([
            'email'     => 'required|email',
            'password'  => 'required|string',
            'fcm_token' => 'nullable|string',
        ]);

        $user = User::where('email', $request->email)->first();

        if (!$user || !Hash::check($request->password, $user->password)) {
            return response()->json([
                'success' => false,
                'message' => 'بيانات الدخول غير صحيحة.',
            ], 401);
        }

        // تأكد إن المستخدم مرتبط بموظف
        $employee = Employee::where('user_id', $user->id)
            ->with(['department', 'bank'])
            ->first();

        if (!$employee) {
            return response()->json([
                'success' => false,
                'message' => 'هذا الحساب غير مرتبط بأي موظف.',
            ], 403);
        }

        if ($employee->status !== 'active') {
            return response()->json([
                'success' => false,
                'message' => 'حسابك غير نشط. تواصل مع الإدارة.',
            ], 403);
        }

        // حذف الـ tokens القديمة وإنشاء token جديد
        $user->tokens()->delete();
        $token = $user->createToken('mobile-app')->plainTextToken;

        // حفظ FCM token إذا موجود
        if ($request->fcm_token) {
            $employee->update(['fcm_token' => $request->fcm_token]);
        }

        return response()->json([
            'success' => true,
            'token'   => $token,
            'employee' => [
                'id'         => $employee->id,
                'name'       => $employee->name,
                'job_title'  => $employee->job_title,
                'department' => $employee->department?->name,
                'photo_url'  => $employee->photo_url,
                'email'      => $user->email,
            ],
        ]);
    }

    /**
     * POST /api/v1/logout
     */
    public function logout(Request $request)
    {
        $request->user()->currentAccessToken()->delete();

        return response()->json([
            'success' => true,
            'message' => 'تم تسجيل الخروج بنجاح.',
        ]);
    }
}
