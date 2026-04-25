<?php

namespace App\Services;

use App\Models\Employee;
use App\Models\EmployeeLedger;
use App\Models\SalaryPayment;
use App\Models\SalaryAdjustment;
use App\Models\Loan;
use Illuminate\Support\Facades\DB;

/**
 * LedgerService — قلب نظام الحسابات
 *
 * كل عملية مالية تمر من هنا وتُسجَّل كقيد في employee_ledger.
 * الـ balance_after يُحسب تلقائياً لكل قيد جديد.
 *
 * القاعدة:
 *   credit  = مبلغ لصالح الموظف  → يرفع الرصيد
 *   debit   = مبلغ على الموظف     → يخفض الرصيد
 *   balance_after = balance_before + credit - debit
 */
class LedgerService
{
    /* =====================================================
     *  إضافة قيد جديد
     * ===================================================== */
    public function addEntry(
        int    $employeeId,
        string $entryType,
        float  $credit,
        float  $debit,
        string $description,
        string $entryDate,
        array  $extra = []
    ): EmployeeLedger {
        return DB::transaction(function () use (
            $employeeId, $entryType, $credit, $debit,
            $description, $entryDate, $extra
        ) {
            // الرصيد الحالي قبل القيد
            $balanceBefore = $this->getCurrentBalance($employeeId);
            $balanceAfter  = round($balanceBefore + $credit - $debit, 2);

            return EmployeeLedger::create([
                'employee_id'    => $employeeId,
                'entry_date'     => $entryDate,
                'period_start'   => $extra['period_start']   ?? null,
                'period_end'     => $extra['period_end']     ?? null,
                'entry_type'     => $entryType,
                'credit'         => $credit,
                'debit'          => $debit,
                'balance_after'  => $balanceAfter,
                'description'    => $description,
                'reference_type' => $extra['reference_type'] ?? null,
                'reference_id'   => $extra['reference_id']   ?? null,
                'fiscal_period'  => $extra['fiscal_period']  ?? null,
                'created_by'     => $extra['created_by']     ?? auth()->id(),
            ]);
        });
    }

    /* =====================================================
     *  الرصيد الحالي للموظف
     * ===================================================== */
    public function getCurrentBalance(int $employeeId): float
    {
        $last = EmployeeLedger::where('employee_id', $employeeId)
            ->orderByDesc('entry_date')
            ->orderByDesc('id')
            ->first();

        if ($last) {
            return (float) $last->balance_after;
        }

        // لا يوجد قيود بعد → الرصيد صفر (الرصيد الافتتاحي يجب أن يُسجَّل
        // كقيد صريح عبر recordOpeningBalance وليس كـ fallback صامت)
        return 0.0;
    }

    /* =====================================================
     *  تسجيل راتب أسبوعي كامل (يُنشئ قيوداً متعددة)
     * =====================================================
     *
     * يُنشئ القيود التالية تلقائياً:
     *   1. salary        → credit  (ساعات × أجر)
     *   2. overtime      → credit  (إذا > 0)
     *   3. bonus/expense → credit/debit (من salary_adjustments)
     *   4. deduction_late     → debit
     *   5. deduction_absence  → debit
     *   6. deduction_manual   → debit
     *   7. loan_installment   → debit (إذا > 0)
     *   8. payment       → debit  (صافي الراتب المدفوع)
     */
    public function recordSalaryPayment(SalaryPayment $payment, array $adjustmentIds = []): void
    {
        DB::transaction(function () use ($payment, $adjustmentIds) {
            $emp        = $payment->employee;
            $periodOpts = [
                'period_start'   => $payment->week_start->toDateString(),
                'period_end'     => $payment->week_end->toDateString(),
                'fiscal_period'  => $payment->fiscal_period,
                'reference_type' => 'SalaryPayment',
                'reference_id'   => $payment->id,
                'created_by'     => $payment->created_by,
            ];

            $paymentDate = $payment->payment_date
                ? $payment->payment_date->toDateString()
                : $payment->week_end->toDateString();

            $salaryMultiplier = (float)($payment->salary_multiplier ?? 1);
            $overtimeRate     = (float)($payment->overtime_rate ?? $emp->overtime_rate ?? 1.5);

            // 1. راتب ساعات العمل (مع تطبيق معامل الراتب)
            if ($payment->gross_salary - ($payment->manual_additions ?? 0) > 0) {
                $baseSalary = round(
                    $payment->hours_worked * (float)($payment->hourly_rate ?? 0) * $salaryMultiplier,
                    2
                );
                if ($baseSalary > 0) {
                    $multiplierNote = $salaryMultiplier != 1 ? " × {$salaryMultiplier}" : '';
                    $this->addEntry(
                        $emp->id, 'salary', $baseSalary, 0,
                        "راتب أسبوعي — {$payment->hours_worked} ساعة × " . number_format($payment->hourly_rate ?? 0, 4) . " ₪{$multiplierNote}",
                        $paymentDate, $periodOpts
                    );
                }
            }

            // 2. الأوفرتايم (مع تطبيق معامل الراتب ومعامل الأوفرتايم المحفوظ)
            if ((float)$payment->overtime_hours > 0) {
                $overtimePay = round(
                    $payment->overtime_hours * (float)($payment->hourly_rate ?? 0) * $overtimeRate * $salaryMultiplier,
                    2
                );
                if ($overtimePay > 0) {
                    $this->addEntry(
                        $emp->id, 'overtime', $overtimePay, 0,
                        "أوفرتايم — {$payment->overtime_hours} ساعة × معامل {$overtimeRate}",
                        $paymentDate, $periodOpts
                    );
                }
            }

            // 3. التعديلات اليدوية (Adjustments)
            $adjDeductionTotal = 0;
            if (!empty($adjustmentIds)) {
                $adjustments = SalaryAdjustment::whereIn('id', $adjustmentIds)
                    ->where('employee_id', $emp->id)
                    ->where('status', 'pending')
                    ->get();

                foreach ($adjustments as $adj) {
                    $isAddition = $adj->is_addition;
                    if (!$isAddition) {
                        $adjDeductionTotal += (float)$adj->amount;
                    }
                    $this->addEntry(
                        $emp->id,
                        $isAddition ? 'bonus' : 'deduction_manual',
                        $isAddition ? (float)$adj->amount : 0,
                        $isAddition ? 0 : (float)$adj->amount,
                        "{$adj->type_label}: {$adj->reason}",
                        $paymentDate,
                        array_merge($periodOpts, [
                            'reference_type' => 'SalaryAdjustment',
                            'reference_id'   => $adj->id,
                        ])
                    );

                    $adj->update([
                        'status'             => 'applied',
                        'salary_payment_id'  => $payment->id,
                    ]);
                }
            }

            // 4. خصم التأخير
            if ((float)$payment->late_deduction > 0) {
                $this->addEntry(
                    $emp->id, 'deduction_late', 0, (float)$payment->late_deduction,
                    "خصم تأخير — {$payment->late_minutes} دقيقة",
                    $paymentDate, $periodOpts
                );
            }

            // 5. خصم الغياب
            if ((float)$payment->absence_deduction > 0) {
                $this->addEntry(
                    $emp->id, 'deduction_absence', 0, (float)$payment->absence_deduction,
                    "خصم غياب",
                    $paymentDate, $periodOpts
                );
            }

            // 6. الجزء اليدوي فقط — التعديلات سُجّلت أعلاه لتجنب التكرار
            $formManualDeductions = max(0, (float)$payment->manual_deductions - $adjDeductionTotal);
            if ($formManualDeductions > 0) {
                $this->addEntry(
                    $emp->id, 'deduction_manual', 0, $formManualDeductions,
                    "خصومات يدوية",
                    $paymentDate, $periodOpts
                );
            }

            // 7. قسط السلفة
            if ((float)$payment->loan_deduction_amount > 0) {
                $this->addEntry(
                    $emp->id, 'loan_installment', 0, (float)$payment->loan_deduction_amount,
                    "قسط سلفة",
                    $paymentDate, $periodOpts
                );
            }

            // 8. دفع فوري (كاش أو بنك) — إذا اختار "ترحيل" لا يُسجَّل قيد دفع
            if ($payment->payment_method !== 'deferred') {
                $methodLabel = $payment->payment_method === 'bank' ? 'تحويل بنكي' : 'نقدي';
                $this->addEntry(
                    $emp->id, 'payment', 0, (float)$payment->net_salary,
                    "دفع راتب {$payment->week_start->format('d/m')}–{$payment->week_end->format('d/m/Y')} ({$methodLabel})",
                    $paymentDate, $periodOpts
                );
            }
            // إذا deferred: الراتب الصافي يبقى في الرصيد حتى يُصرف لاحقاً

            // تحديث balance_after على الـ SalaryPayment
            $finalBalance = $this->getCurrentBalance($emp->id);
            $payment->update([
                'balance_after' => $finalBalance,
            ]);
        });
    }

    /* =====================================================
     *  حذف قيود راتب + إعادة حساب الأرصدة اللاحقة
     * ===================================================== */
    public function deleteSalaryEntries(SalaryPayment $payment): void
    {
        DB::transaction(function () use ($payment) {
            EmployeeLedger::where('reference_type', 'SalaryPayment')
                ->where('reference_id', $payment->id)
                ->delete();

            // إعادة حساب الأرصدة من البداية
            $this->recalculateBalances($payment->employee_id);
        });
    }

    /* =====================================================
     *  إعادة حساب balance_after لجميع قيود الموظف
     * ===================================================== */
    public function recalculateBalances(int $employeeId): void
    {
        $entries = EmployeeLedger::where('employee_id', $employeeId)
            ->orderBy('entry_date')
            ->orderBy('id')
            ->get();

        $balance = 0;
        $firstEntry = $entries->first();
        if ($firstEntry && $firstEntry->entry_type !== 'opening_balance') {
            $employee = Employee::find($employeeId);
            $balance = $employee ? (float) $employee->opening_balance : 0;
        }

        foreach ($entries as $entry) {
            $balance = round($balance + $entry->credit - $entry->debit, 2);
            $entry->update(['balance_after' => $balance]);
        }
    }

    /* =====================================================
     *  تسجيل قبض يدوي من الموظف (cash/bank)
     *  يُخفض الرصيد — الشركة دفعت للموظف
     * ===================================================== */
    public function recordPayment(
        int    $employeeId,
        float  $amount,
        string $date,
        string $description = 'قبض نقدي',
        string $method = 'cash',
        array  $extra = []
    ): EmployeeLedger {
        return $this->addEntry(
            $employeeId,
            'payment',
            0,
            $amount,
            $description . ' (' . ($method === 'bank' ? 'تحويل بنكي' : 'كاش') . ')',
            $date,
            $extra
        );
    }

    /* =====================================================
     *  تسجيل صرف سلفة جديدة
     * ===================================================== */
    public function recordLoanDisbursement(Loan $loan): void
    {
        $this->addEntry(
            $loan->employee_id,
            'loan_disbursement',
            (float) $loan->total_amount,
            0,
            "سلفة جديدة — {$loan->description}",
            $loan->start_date ? $loan->start_date->toDateString() : now()->toDateString(),
            [
                'reference_type' => 'Loan',
                'reference_id'   => $loan->id,
            ]
        );
    }

    /* =====================================================
     *  تسجيل رصيد افتتاحي
     * ===================================================== */
    public function recordOpeningBalance(Employee $employee, float $amount, string $date, string $reason = 'رصيد افتتاحي'): EmployeeLedger
    {
        // نحذف أي رصيد افتتاحي سابق
        EmployeeLedger::where('employee_id', $employee->id)
            ->where('entry_type', 'opening_balance')
            ->delete();

        $isCredit = $amount >= 0;

        return $this->addEntry(
            $employee->id,
            'opening_balance',
            $isCredit ? abs($amount) : 0,
            $isCredit ? 0 : abs($amount),
            $reason,
            $date,
            ['reference_type' => 'Employee', 'reference_id' => $employee->id]
        );
    }

    /* =====================================================
     *  كشف حساب الموظف لفترة معينة
     * ===================================================== */
    public function getStatement(int $employeeId, ?string $from = null, ?string $to = null)
    {
        $query = EmployeeLedger::where('employee_id', $employeeId)
            ->orderBy('entry_date')
            ->orderBy('id');

        if ($from) $query->where('entry_date', '>=', $from);
        if ($to)   $query->where('entry_date', '<=', $to);

        return $query->get();
    }

    /* =====================================================
     *  ملخص الموظف المالي
     * ===================================================== */
    public function getSummary(int $employeeId, ?string $from = null, ?string $to = null): array
    {
        $query = EmployeeLedger::where('employee_id', $employeeId);
        if ($from) $query->where('entry_date', '>=', $from);
        if ($to)   $query->where('entry_date', '<=', $to);

        $entries = $query->get();

        $totalCredits     = (float) $entries->sum('credit');
        $totalDebits      = (float) $entries->sum('debit');
        $totalDeductions  = (float) $entries->whereIn('entry_type', ['deduction_late','deduction_absence','deduction_manual'])->sum('debit');
        $netPaid          = (float) $entries->where('entry_type', 'payment')->sum('debit');

        return [
            // مفاتيح تستخدمها الـ views
            'total_credits'    => $totalCredits,
            'total_debits'     => $totalDebits,
            'net_paid'         => $netPaid,
            'total_deductions' => $totalDeductions,

            // مفاتيح إضافية للتقارير
            'net'              => $totalCredits - $totalDebits,
            'current_balance'  => $this->getCurrentBalance($employeeId),
            'salary_total'     => (float) $entries->where('entry_type', 'salary')->sum('credit'),
            'overtime_total'   => (float) $entries->where('entry_type', 'overtime')->sum('credit'),
            'bonus_total'      => (float) $entries->where('entry_type', 'bonus')->sum('credit'),
            'loan_total'       => (float) $entries->where('entry_type', 'loan_installment')->sum('debit'),
            'payments_total'   => $netPaid,
        ];
    }
}
