<?php

namespace App\Http\Controllers;

use App\Models\SalaryPayment;
use App\Models\SalaryAdjustment;
use App\Models\Employee;
use App\Models\Loan;
use App\Models\Attendance;
use App\Services\LedgerService;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Schema;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Facades\Validator;
use Carbon\Carbon;

class SalaryController extends Controller
{
    public function __construct(private LedgerService $ledger) {}

    /* =====================================================
     *  INDEX — قائمة الرواتب
     * ===================================================== */
    public function index(Request $request)
    {
        $query = SalaryPayment::with('employee.department')->latest();

        if ($request->filled('employee_id')) {
            $query->where('employee_id', $request->employee_id);
        }
        if ($request->filled('fiscal_period')) {
            $query->where('fiscal_period', $request->fiscal_period);
        }

        /* ---- بحث نصي شامل ---- */
        if ($request->filled('search')) {
            $term = $request->search;
            $query->where(function ($q) use ($term) {
                $q->where('fiscal_period', 'like', "%{$term}%")
                  ->orWhereHas('employee', function ($eq) use ($term) {
                      $eq->where('name', 'like', "%{$term}%")
                         ->orWhere('employee_number', 'like', "%{$term}%")
                         ->orWhere('fingerprint_id', 'like', "%{$term}%");
                  });
            });
        }

        $payments  = $query->paginate(20)->withQueryString();
        $employees = Employee::active()->orderBy('name')->get(['id','name']);

        return view('salary.index', compact('payments', 'employees'));
    }

    /* =====================================================
     *  CREATE — نموذج احتساب الراتب
     * ===================================================== */
    public function create()
    {
        $employees = Employee::active()->orderBy('name')->get();
        [$weekStart, $weekEnd] = $this->currentWeekBounds(0);
        return view('salary.create', compact('employees', 'weekStart', 'weekEnd'));
    }

    /* =====================================================
     *  CALCULATE — معاينة الراتب
     * ===================================================== */
    public function calculate(Request $request)
    {
        $request->validate([
            'employee_id'       => 'required|exists:employees,id',
            'week_start'        => 'required|date',
            'week_end'          => 'required|date|after_or_equal:week_start',
            'late_factor'       => 'required|numeric|min:0',
            'late_grace'        => 'required|integer|min:0',
            'salary_multiplier' => 'required|numeric|min:0.01|max:10',
        ]);

        $employee  = Employee::with('activeLoan')->findOrFail($request->employee_id);
        $weekStart = Carbon::parse($request->week_start);
        $weekEnd   = Carbon::parse($request->week_end);

        /* ---- أجر الساعة ---- */
        [$hourlyRate, $shiftHoursPerDay] = $this->resolveHourlyRate($employee);

        /* ---- سجلات الحضور (بدون الجمعة) ---- */
        $attendances = Attendance::where('employee_id', $employee->id)
            ->whereBetween('date', [$weekStart->toDateString(), $weekEnd->toDateString()])
            ->whereRaw('DAYOFWEEK(date) != 6')
            ->orderBy('date')
            ->get();

        /* ---- حساب الساعات ---- */
        $hoursWorked   = (float) $attendances->sum('work_hours');
        $overtimeHours = (float) $attendances->sum('overtime_hours');

        /* ---- حساب التأخير ---- */
        $lateMinutes = 0;
        foreach ($attendances as $att) {
            if ($att->check_in && $att->status !== 'leave' && $employee->shift_start) {
                $scheduledIn = Carbon::parse($att->date->toDateString() . ' ' . $employee->shift_start);
                $actualIn    = Carbon::parse($att->date->toDateString() . ' ' . $att->check_in);
                if ($actualIn->gt($scheduledIn)) {
                    $late = $scheduledIn->diffInMinutes($actualIn);
                    if ($late > $request->late_grace) {
                        $lateMinutes += ($late - $request->late_grace);
                    }
                }
            }
        }

        /* ---- معامل الراتب ---- */
        $salaryMultiplier = (float) $request->salary_multiplier;

        /* ---- حساب المبالغ ---- */
        $overtimeRate  = (float) ($employee->overtime_rate ?? 1.5); // معامل الأوفرتايم من بيانات الموظف
        $salaryA       = round($hoursWorked * $hourlyRate * $salaryMultiplier, 2);
        $salaryB       = round($overtimeHours * $hourlyRate * $overtimeRate * $salaryMultiplier, 2);
        $lateDeduction = round(($lateMinutes / 60) * $hourlyRate * $request->late_factor, 2);

        /* ---- السلفة النشطة ---- */
        $activeLoan          = $employee->activeLoan;
        $suggestedLoanDeduct = ($activeLoan && !$activeLoan->is_paused)
            ? (float) $activeLoan->installment_amount : 0;

        /* ---- التعديلات المعلّقة ---- */
        $pendingAdjustments = SalaryAdjustment::where('employee_id', $employee->id)
            ->where('status', 'pending')
            ->orderBy('adjustment_date')
            ->get();

        /* ---- الرصيد الحالي ---- */
        $currentBalance = $this->ledger->getCurrentBalance($employee->id);

        $data = [
            'employee'            => $employee,
            'weekStart'           => $weekStart->toDateString(),
            'weekEnd'             => $weekEnd->toDateString(),
            'fiscalPeriod'        => $weekStart->format('Y-\WW'),
            'hoursWorked'         => $hoursWorked,
            'overtimeHours'       => $overtimeHours,
            'overtimeRate'        => $overtimeRate,
            'lateMinutes'         => $lateMinutes,
            'lateGrace'           => $request->late_grace,
            'lateFactor'          => $request->late_factor,
            'lateDeduction'       => $lateDeduction,
            'salaryA'             => $salaryA,
            'salaryB'             => $salaryB,
            'hourlyRate'          => $hourlyRate,
            'salaryMultiplier'    => $salaryMultiplier,
            'activeLoan'          => $activeLoan,
            'suggestedLoanDeduct' => $suggestedLoanDeduct,
            'pendingAdjustments'  => $pendingAdjustments,
            'currentBalance'      => $currentBalance,
            'attendances'         => $attendances,
        ];

        return view('salary.review', compact('data'));
    }

    /* =====================================================
     *  STORE — حفظ الراتب + توليد قيود الـ Ledger
     * ===================================================== */
    public function store(Request $request)
    {
        $validator = Validator::make($request->all(), [
            'employee_id'          => 'required|exists:employees,id',
            'week_start'           => 'required|date',
            'week_end'             => 'required|date',
            'hours_worked'         => 'required|numeric|min:0',
            'overtime_hours'       => 'nullable|numeric|min:0',
            'late_minutes'         => 'nullable|numeric|min:0',
            'late_deduction'       => 'nullable|numeric|min:0',
            'late_factor'          => 'nullable|numeric|min:0',
            'absence_deduction'    => 'nullable|numeric|min:0',
            'manual_deductions'    => 'nullable|numeric|min:0',
            'loan_deduction_amount'=> 'nullable|numeric|min:0',
            'hourly_rate'          => 'required|numeric|min:0',
            'salary_multiplier'    => 'required|numeric|min:0.01|max:10',
            'overtime_rate'        => 'nullable|numeric|min:0',
            'payment_method'       => 'required|in:bank,cash,deferred',
            'adjustment_ids'       => 'nullable|array',
            'adjustment_ids.*'     => 'exists:salary_adjustments,id',
            'new_adj_type'         => 'nullable|in:bonus,expense,deduction,other',
            'new_adj_amount'       => 'nullable|numeric|min:0.01',
            'new_adj_reason'       => 'nullable|string|max:255',
            'balance_before'       => 'nullable|numeric',
        ]);

        if ($validator->fails()) {
            Log::warning('Salary store validation failed', [
                'errors' => $validator->errors()->toArray(),
                'input'  => $request->except(['_token']),
            ]);
            return redirect()->route('salary.create')
                ->withInput()
                ->withErrors($validator)
                ->with('error', 'خطأ في البيانات المُرسَلة: ' . $validator->errors()->first());
        }

        $employee = Employee::findOrFail($request->employee_id);

        /* ---- إضافة تعديل يدوي جديد إذا أُدخل ---- */
        $newAdjId = null;
        if ($request->filled('new_adj_type') && $request->filled('new_adj_amount') && $request->filled('new_adj_reason')) {
            $adjSign = match($request->new_adj_type) {
                'bonus', 'expense' => 1,
                'deduction'        => -1,
                'other'            => (int) ($request->new_adj_sign ?? -1),
            };
            $newAdj = SalaryAdjustment::create([
                'employee_id'      => $employee->id,
                'type'             => $request->new_adj_type,
                'sign'             => $adjSign,
                'amount'           => $request->new_adj_amount,
                'adjustment_date'  => $request->week_end,
                'reason'           => $request->new_adj_reason,
                'status'           => 'pending',
                'created_by'       => auth()->id(),
            ]);
            $newAdjId = $newAdj->id;
        }

        /* ---- جمع الـ adjustment IDs ---- */
        $adjIds = array_filter(array_merge(
            $request->adjustment_ids ?? [],
            $newAdjId ? [$newAdjId] : []
        ));

        /* ---- حساب الإضافات والخصومات من الـ adjustments ---- */
        $manualAdditions  = 0;
        $manualDeductions = 0;
        if (!empty($adjIds)) {
            $adjs = SalaryAdjustment::whereIn('id', $adjIds)
                ->where('employee_id', $employee->id)
                ->where('status', 'pending')
                ->get();
            foreach ($adjs as $adj) {
                if ($adj->is_addition) {
                    $manualAdditions += (float) $adj->amount;
                } else {
                    $manualDeductions += (float) $adj->amount;
                }
            }
        }

        /* ---- حساب الراتب ---- */
        $salaryMultiplier = (float) $request->salary_multiplier;
        $overtimeRate     = (float) ($request->overtime_rate ?? $employee->overtime_rate ?? 1.5);
        $salaryA          = round((float)$request->hours_worked * (float)$request->hourly_rate * $salaryMultiplier, 2);
        $salaryB          = round((float)($request->overtime_hours ?? 0) * (float)$request->hourly_rate * $overtimeRate * $salaryMultiplier, 2);
        $grossSalary      = $salaryA + $salaryB + $manualAdditions;

        // خصومات الراتب: تأخير + غياب + يدوية + قسط السلفة
        $salaryDeductions = (float)($request->late_deduction ?? 0)
                          + (float)($request->absence_deduction ?? 0)
                          + (float)($request->manual_deductions ?? 0)
                          + $manualDeductions
                          + (float)($request->loan_deduction_amount ?? 0);

        $totalDeductions = $salaryDeductions;

        $netSalary = max(0, $grossSalary - $salaryDeductions);

        /* ---- حفظ الـ SalaryPayment ---- */
        $paymentData = [
            'employee_id'           => $employee->id,
            'week_start'            => $request->week_start,
            'week_end'              => $request->week_end,
            'hours_worked'          => (float)$request->hours_worked,
            'overtime_hours'        => (float)($request->overtime_hours ?? 0),
            'hourly_rate'           => (float)$request->hourly_rate,
            'salary_multiplier'     => $salaryMultiplier,
            'late_minutes'          => (int)($request->late_minutes ?? 0),
            'late_deduction'        => (float)($request->late_deduction ?? 0),
            'late_factor'           => (float)($request->late_factor ?? 1),
            'absence_deduction'     => (float)($request->absence_deduction ?? 0),
            'manual_additions'      => $manualAdditions,
            'manual_deductions'     => (float)($request->manual_deductions ?? 0) + $manualDeductions,
            'loan_deduction_amount' => (float)($request->loan_deduction_amount ?? 0),
            'gross_salary'          => $grossSalary,
            'total_allowances'      => $manualAdditions,
            'total_deductions'      => $salaryDeductions,
            'loan_deduction'        => (float)($request->loan_deduction_amount ?? 0),
            'net_salary'            => $netSalary,
            'payment_date'          => now(),
            'fiscal_period'         => Carbon::parse($request->week_start)->format('Y-\WW'),
            'payment_method'        => $request->payment_method,
            'notes'                 => $request->notes,
            'created_by'            => auth()->id(),
        ];

        // أضف الحقول الاختيارية فقط إذا كان العمود موجوداً في الجدول
        if (Schema::hasColumn('salary_payments', 'overtime_rate')) {
            $paymentData['overtime_rate'] = $overtimeRate;
        }
        if (Schema::hasColumn('salary_payments', 'salary_from_hours')) {
            $paymentData['salary_from_hours'] = $salaryA;
        }
        if (Schema::hasColumn('salary_payments', 'salary_from_overtime')) {
            $paymentData['salary_from_overtime'] = $salaryB;
        }
        if (Schema::hasColumn('salary_payments', 'adjustments_total')) {
            $paymentData['adjustments_total'] = $manualAdditions - $manualDeductions;
        }
        if (Schema::hasColumn('salary_payments', 'status')) {
            $paymentData['status'] = 'confirmed';
        }
        if (Schema::hasColumn('salary_payments', 'balance_before')) {
            // استخدم الرصيد المُدخَل يدوياً إذا أُرسل، وإلا اسحب من الـ Ledger
            $paymentData['balance_before'] = $request->filled('balance_before')
                ? (float) $request->balance_before
                : $this->ledger->getCurrentBalance($employee->id);
        }

        try {
            $payment = SalaryPayment::create($paymentData);
        } catch (\Throwable $e) {
            Log::error('Salary store — create failed', [
                'message'     => $e->getMessage(),
                'paymentData' => $paymentData,
                'trace'       => $e->getTraceAsString(),
            ]);
            return redirect()->route('salary.create')
                ->withInput()
                ->with('error', 'خطأ في حفظ الراتب: ' . $e->getMessage());
        }

        /* ---- توليد قيود الـ Ledger تلقائياً ---- */
        try {
            $this->ledger->recordSalaryPayment($payment, array_values($adjIds));
        } catch (\Throwable $e) {
            Log::error('Salary store — ledger failed', [
                'message'    => $e->getMessage(),
                'payment_id' => $payment->id,
                'trace'      => $e->getTraceAsString(),
            ]);
            $payment->delete();
            return redirect()->route('salary.create')
                ->withInput()
                ->with('error', 'خطأ في تسجيل القيود المحاسبية: ' . $e->getMessage());
        }

        /* ---- تحديث قسط السلفة ---- */
        if ((float)$request->loan_deduction_amount > 0) {
            $loan = Loan::where('employee_id', $employee->id)
                ->where('status', 'active')
                ->where('is_paused', false)
                ->first();
            if ($loan) {
                $loan->amount_paid        += (float)$request->loan_deduction_amount;
                $loan->installments_paid  += 1;
                $loan->last_payment_date   = now();
                if ($loan->installments_paid >= $loan->installments_total) {
                    $loan->status = 'completed';
                }
                $loan->save();
            }
        }

        // إرسال SMS — اختياري فقط إذا فعّله المستخدم
        if ($request->send_sms && $employee->mobile && $request->sms_message) {
            try {
                app(\App\Services\SmsService::class)->send(
                    $employee->mobile,
                    $request->sms_message
                );
            } catch (\Throwable $e) {
                // SMS فشل لكن الراتب محفوظ — نكمل
            }
        }

        return redirect()->route('salary.index')
            ->with('success', "✅ تم حفظ راتب {$employee->name} بنجاح.");
    }

    /* =====================================================
     *  SHOW — عرض راتب + قيوده
     * ===================================================== */
    public function show(SalaryPayment $salary)
    {
        $salary->load('employee.department');

        // قيود الـ Ledger المرتبطة بهذه الدفعة
        $ledgerEntries = \App\Models\EmployeeLedger::where('reference_type', 'SalaryPayment')
            ->where('reference_id', $salary->id)
            ->orderBy('id')
            ->get();

        return view('salary.show', compact('salary', 'ledgerEntries'));
    }

    /* =====================================================
     *  EDIT — تعديل راتب
     * ===================================================== */
    public function edit(SalaryPayment $salary)
    {
        $salary->load('employee.department');
        return view('salary.edit', compact('salary'));
    }

    /* =====================================================
     *  UPDATE — حفظ التعديل
     * ===================================================== */
    public function update(Request $request, SalaryPayment $salary)
    {
        $request->validate([
            'notes'            => 'nullable|string|max:1000',
            'payment_method'   => 'required|in:cash,bank,deferred',
            'manual_additions' => 'nullable|numeric|min:0',
            'manual_deductions'=> 'nullable|numeric|min:0',
        ]);

        $newAdditions  = (float) ($request->manual_additions  ?? $salary->manual_additions);
        $newDeductions = (float) ($request->manual_deductions ?? $salary->manual_deductions);
        $oldAdditions  = (float) $salary->manual_additions;
        $oldDeductions = (float) $salary->manual_deductions;

        $needsRecalc   = ($newAdditions !== $oldAdditions || $newDeductions !== $oldDeductions);
        $oldMethod     = $salary->payment_method;

        if ($needsRecalc) {
            // إعادة حساب المبالغ
            $baseSalary    = (float) ($salary->salary_from_hours ?? round($salary->hours_worked * $salary->hourly_rate * ($salary->salary_multiplier ?? 1), 2));
            $overtimeSalary = (float) ($salary->salary_from_overtime ?? round(($salary->overtime_hours ?? 0) * $salary->hourly_rate * ($salary->overtime_rate ?? 1.5) * ($salary->salary_multiplier ?? 1), 2));

            $grossSalary     = $baseSalary + $overtimeSalary + $newAdditions;
            $totalDeductions = (float) $salary->late_deduction
                             + (float) $salary->absence_deduction
                             + $newDeductions
                             + (float) $salary->loan_deduction_amount;
            $netSalary       = max(0, $grossSalary - $totalDeductions);

            $salary->update([
                'notes'            => $request->notes,
                'payment_method'   => $request->payment_method,
                'manual_additions' => $newAdditions,
                'manual_deductions'=> $newDeductions,
                'gross_salary'     => $grossSalary,
                'total_allowances' => $newAdditions,
                'total_deductions' => $totalDeductions,
                'net_salary'       => $netSalary,
            ]);

            // حذف القيود القديمة + إعادة بنائها
            $this->ledger->deleteSalaryEntries($salary);
            $this->ledger->recordSalaryPayment($salary);
        } else {
            $salary->update([
                'notes'          => $request->notes,
                'payment_method' => $request->payment_method,
            ]);

            // إذا تغيرت طريقة الدفع فقط → أعد بناء القيود لتحديث قيد الدفع
            if ($request->payment_method !== $oldMethod) {
                $this->ledger->deleteSalaryEntries($salary);
                $this->ledger->recordSalaryPayment($salary);
            }
        }

        return redirect()->route('salary.show', $salary)
            ->with('success', '✅ تم تحديث الراتب بنجاح');
    }

    /* =====================================================
     *  DESTROY — حذف راتب
     * ===================================================== */
    public function destroy(SalaryPayment $salary)
    {
        $employeeId   = $salary->employee_id;
        $employeeName = $salary->employee?->name;

        // حذف القيود المحاسبية + إعادة الحساب عبر الخدمة المركزية
        $this->ledger->deleteSalaryEntries($salary);

        // حذف الراتب
        $salary->delete();

        return redirect()->route('salary.index')
            ->with('success', "تم حذف راتب {$employeeName} وتحديث كشف الحساب");
    }

    /* =====================================================
     *  THERMAL PRINT — طباعة حرارية
     * ===================================================== */
    public function thermal(SalaryPayment $salary)
    {
        $salary->load('employee.department');

        $ledgerEntries = \App\Models\EmployeeLedger::where('reference_type', 'SalaryPayment')
            ->where('reference_id', $salary->id)
            ->orderBy('id')
            ->get();

        return view('salary.thermal', compact('salary', 'ledgerEntries'));
    }

    /* =====================================================
     *  ADJUSTMENTS — إدارة التعديلات اليدوية
     * ===================================================== */

    /** قائمة التعديلات لموظف معين */
    public function adjustments(Request $request)
    {
        $query = SalaryAdjustment::with('employee')->latest();

        if ($request->filled('employee_id')) {
            $query->where('employee_id', $request->employee_id);
        }
        if ($request->filled('status')) {
            $query->where('status', $request->status);
        }

        $adjustments = $query->paginate(20)->withQueryString();
        $employees   = Employee::active()->orderBy('name')->get(['id','name']);

        return view('salary.adjustments', compact('adjustments', 'employees'));
    }

    /** حفظ تعديل يدوي جديد */
    public function storeAdjustment(Request $request)
    {
        $rules = [
            'employee_id'     => 'required|exists:employees,id',
            'type'            => 'required|in:bonus,expense,deduction,other',
            'amount'          => 'required|numeric|min:0.01',
            'adjustment_date' => 'required|date',
            'reason'          => 'required|string|max:255',
            'notes'           => 'nullable|string',
        ];

        // sign مطلوب فقط لنوع other
        if ($request->type === 'other') {
            $rules['sign'] = 'required|in:1,-1';
        }

        $request->validate($rules);

        // تحديد الإشارة: bonus/expense = +1، deduction = -1، other = حسب الاختيار
        $sign = match($request->type) {
            'bonus', 'expense' => 1,
            'deduction'        => -1,
            'other'            => (int) $request->sign,
        };

        SalaryAdjustment::create([
            'employee_id'     => $request->employee_id,
            'type'            => $request->type,
            'sign'            => $sign,
            'amount'          => $request->amount,
            'adjustment_date' => $request->adjustment_date,
            'reason'          => $request->reason,
            'notes'           => $request->notes,
            'status'          => 'pending',
            'created_by'      => auth()->id(),
        ]);

        return back()->with('success', 'تم إضافة التعديل بنجاح، سيُطبَّق في الراتب القادم.');
    }

    /** إلغاء تعديل */
    public function cancelAdjustment(SalaryAdjustment $adjustment)
    {
        if ($adjustment->status !== 'pending') {
            return back()->with('error', 'لا يمكن إلغاء تعديل مُطبَّق.');
        }
        $adjustment->update(['status' => 'cancelled']);
        return back()->with('success', 'تم إلغاء التعديل.');
    }

    /* =====================================================
     *  HELPERS
     * ===================================================== */

    /**
     * حساب أجر الساعة وساعات الوردية اليومية
     * @return [float $hourlyRate, float $shiftHoursPerDay]
     */
    private function resolveHourlyRate(Employee $employee): array
    {
        if ($employee->salary_type === 'hourly') {
            return [(float)$employee->hourly_rate, 8.0];
        }

        // راتب ثابت → نحوله لأجر ساعة
        // الأسبوع = 6 أيام عمل (خميس إلى أربعاء بدون جمعة)
        $shiftHours = 8.0;
        if ($employee->shift_start && $employee->shift_end) {
            $in  = Carbon::parse($employee->shift_start);
            $out = Carbon::parse($employee->shift_end);
            $shiftHours = max(1, $in->diffInMinutes($out) / 60);
        }

        $weeklyHours = $shiftHours * 6;
        $weeklyRate  = ($employee->base_salary ?? $employee->salary ?? 0) / 4;
        $hourlyRate  = $weeklyHours > 0 ? round($weeklyRate / $weeklyHours, 4) : 0;

        return [$hourlyRate, $shiftHours];
    }

    /**
     * حساب بداية ونهاية الأسبوع الحالي (الخميس → الأربعاء)
     */
    private function currentWeekBounds(int $offsetWeeks = 0): array
    {
        $today     = Carbon::now();
        $dayOfWeek = $today->dayOfWeek;

        $daysBack = ($dayOfWeek >= Carbon::THURSDAY)
            ? ($dayOfWeek - Carbon::THURSDAY)
            : ($dayOfWeek + 3);

        $weekStart = $today->copy()->subDays($daysBack)->addWeeks($offsetWeeks)->startOfDay();
        $weekEnd   = $weekStart->copy()->addDays(6)->endOfDay();

        return [$weekStart->toDateString(), $weekEnd->toDateString()];
    }
}
