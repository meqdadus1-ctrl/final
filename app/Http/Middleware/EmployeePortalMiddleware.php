<?php

namespace App\Http\Middleware;

use App\Models\Employee;
use Closure;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;

class EmployeePortalMiddleware
{
    public function handle(Request $request, Closure $next)
    {
        // يجب أن يكون مسجل الدخول
        if (!Auth::check()) {
            return redirect()->route('login');
        }

        $user = Auth::user();

        // أي مستخدم مرتبط بموظف → مسموح
        $employee = Employee::where('user_id', $user->id)->first();
        if ($employee) {
            return $next($request);
        }

        // أو مستخدم لديه صلاحيات إدارية → مسموح للاختبار
        try {
            if ($user->hasRole(['admin', 'manager', 'hr'])) {
                return $next($request);
            }
        } catch (\Exception $e) {
            // في حال مشكلة بالـ roles
        }

        abort(403, 'ليس لديك صلاحية الدخول لهذه الصفحة. تواصل مع المسؤول.');
    }
}
