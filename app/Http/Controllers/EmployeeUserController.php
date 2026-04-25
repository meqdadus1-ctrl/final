<?php

namespace App\Http\Controllers;

use App\Models\Employee;
use App\Models\User;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Str;

class EmployeeUserController extends Controller
{
    /* =====================================================
     *  إنشاء حساب جديد للموظف
     * ===================================================== */
    public function createUser(Request $request, Employee $employee)
    {
        // إذا كان للموظف حساب مسبقاً
        if ($employee->user_id) {
            return back()->with('error', 'هذا الموظف مرتبط بحساب بالفعل.');
        }

        $request->validate([
            'email'    => 'required|email|unique:users,email',
            'password' => 'required|min:6|confirmed',
        ]);

        $user = User::create([
            'name'     => $employee->name,
            'email'    => $request->email,
            'password' => Hash::make($request->password),
        ]);

        // تعيين دور الموظف (إذا كان الدور موجوداً)
        try {
            if (\Spatie\Permission\Models\Role::where('name', 'employee')->exists()) {
                $user->assignRole('employee');
            }
        } catch (\Exception $e) {
            // تجاهل إذا فشل تعيين الدور
        }

        // ربط الموظف بالـ user
        $employee->update(['user_id' => $user->id]);

        return back()->with('success', "✅ تم إنشاء حساب الدخول لـ {$employee->name} بنجاح.");
    }

    /* =====================================================
     *  ربط الموظف بـ user موجود
     * ===================================================== */
    public function linkUser(Request $request, Employee $employee)
    {
        $request->validate([
            'user_id' => 'required|exists:users,id',
        ]);

        // تأكد مافي موظف آخر مربوط بنفس الـ user
        $alreadyLinked = Employee::where('user_id', $request->user_id)
            ->where('id', '!=', $employee->id)
            ->exists();

        if ($alreadyLinked) {
            return back()->with('error', 'هذا الحساب مرتبط بموظف آخر.');
        }

        $employee->update(['user_id' => $request->user_id]);

        return back()->with('success', 'تم ربط الحساب بالموظف بنجاح.');
    }

    /* =====================================================
     *  فك ربط الـ user عن الموظف
     * ===================================================== */
    public function unlinkUser(Employee $employee)
    {
        $employee->update(['user_id' => null]);
        return back()->with('success', 'تم فك ربط الحساب.');
    }

    /* =====================================================
     *  تغيير كلمة السر
     * ===================================================== */
    public function resetPassword(Request $request, Employee $employee)
    {
        if (!$employee->user_id) {
            return back()->with('error', 'الموظف ليس له حساب.');
        }

        $request->validate([
            'password' => 'required|min:6|confirmed',
        ]);

        User::where('id', $employee->user_id)->update([
            'password' => Hash::make($request->password),
        ]);

        return back()->with('success', 'تم تغيير كلمة السر بنجاح.');
    }
}
