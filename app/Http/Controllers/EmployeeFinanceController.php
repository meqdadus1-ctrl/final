<?php

namespace App\Http\Controllers;

use App\Models\Employee;
use App\Models\EmployeeLedger;
use App\Services\LedgerService;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;

class EmployeeFinanceController extends Controller
{
    public function __construct(private LedgerService $ledger) {}

    /**
     * تعديل الرصيد الافتتاحي للموظف (أمانة/مديونية مباشرة)
     * موجب = له على الشركة | سالب = عليه للشركة
     * POST /employees/{employee}/opening-balance
     */
    public function updateOpeningBalance(Request $request, Employee $employee)
    {
        $data = $request->validate([
            'opening_balance' => 'required|numeric|between:-999999.99,999999.99',
            'notes'           => 'nullable|string|max:500',
        ]);

        $newBalance = (float) $data['opening_balance'];

        // 1. تحديث العمود في جدول الموظفين
        $employee->update(['opening_balance' => $newBalance]);

        // 2. تسجيل (أو تصفير) القيد في الـ Ledger
        $this->ledger->recordOpeningBalance(
            $employee,
            $newBalance,
            now()->toDateString(),
            $data['notes'] ?? 'رصيد افتتاحي محدّث'
        );

        // 3. إعادة حساب كل الأرصدة اللاحقة عبر الخدمة المركزية
        $this->ledger->recalculateBalances($employee->id);

        $sign  = $newBalance >= 0 ? 'له' : 'عليه';
        $value = number_format(abs($newBalance), 2);

        return back()->with('success', "تم تحديث الرصيد الافتتاحي: {$sign} {$value} ₪ وإعادة حساب كشف الحساب.");
    }

    /**
     * تصفير الرصيد الافتتاحي بالكامل
     * POST /employees/{employee}/reset-opening-balance
     */
    public function resetOpeningBalance(Employee $employee)
    {
        // 1. صفّر العمود
        $employee->update(['opening_balance' => 0]);

        // 2. احذف قيد opening_balance من الـ Ledger
        EmployeeLedger::where('employee_id', $employee->id)
            ->where('entry_type', 'opening_balance')
            ->delete();

        // 3. أعد حساب الأرصدة اللاحقة عبر الخدمة المركزية
        $this->ledger->recalculateBalances($employee->id);

        return back()->with('success', 'تم تصفير الرصيد الافتتاحي وإعادة حساب كشف الحساب.');
    }
}
