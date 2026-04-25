<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Models\Employee;
use App\Models\SalaryPayment;
use Illuminate\Http\Request;

class SalaryController extends Controller
{
    /**
     * GET /api/v1/salary
     * قائمة رواتب الموظف
     */
    public function index(Request $request)
    {
        $employee = Employee::where('user_id', $request->user()->id)->firstOrFail();

        $salaries = SalaryPayment::where('employee_id', $employee->id)
            ->orderByDesc('payment_date')
            ->paginate(10);

        $data = $salaries->map(fn($s) => $this->formatSalary($s));

        return response()->json([
            'success' => true,
            'data'    => $data,
            'meta'    => [
                'current_page' => $salaries->currentPage(),
                'last_page'    => $salaries->lastPage(),
                'total'        => $salaries->total(),
            ],
        ]);
    }

    /**
     * GET /api/v1/salary/{id}
     * تفاصيل راتب واحد
     */
    public function show(Request $request, $id)
    {
        $employee = Employee::where('user_id', $request->user()->id)->firstOrFail();

        $salary = SalaryPayment::where('employee_id', $employee->id)
            ->findOrFail($id);

        return response()->json([
            'success' => true,
            'data'    => $this->formatSalary($salary, detailed: true),
        ]);
    }

    /**
     * POST /api/v1/salary/{id}/request-statement
     * طلب كشف حساب
     */
    public function requestStatement(Request $request, $id)
    {
        $employee = Employee::where('user_id', $request->user()->id)->firstOrFail();

        $salary = SalaryPayment::where('employee_id', $employee->id)->findOrFail($id);

        // تسجيل الطلب
        $salary->update(['statement_requested' => true]);

        // إشعار الإدارة (سيتم ربطه لاحقاً مع FCM)
        \App\Models\MobileNotification::create([
            'employee_id' => $employee->id,
            'type'        => 'statement_request',
            'title'       => 'طلب كشف حساب',
            'body'        => "الموظف {$employee->name} يطلب كشف حساب للفترة {$salary->fiscal_period}",
            'data'        => json_encode(['salary_id' => $salary->id]),
            'target'      => 'admin',
        ]);

        return response()->json([
            'success' => true,
            'message' => 'تم إرسال طلب كشف الحساب. ستصلك الإجابة قريباً.',
        ]);
    }

    // ===== Helper =====
    private function formatSalary(SalaryPayment $s, bool $detailed = false): array
    {
        $hourlyRate = $s->hourly_rate ?? 0;
        $salaryA    = round($s->hours_worked * $hourlyRate, 2);
        $salaryB    = round($s->overtime_hours * $hourlyRate * ($s->employee?->overtime_rate ?? 1.5), 2);

        $base = [
            'id'             => $s->id,
            'fiscal_period'  => $s->fiscal_period,
            'week_start'     => $s->week_start?->format('Y-m-d'),
            'week_end'       => $s->week_end?->format('Y-m-d'),
            'payment_date'   => $s->payment_date?->format('Y-m-d'),
            'payment_method' => $s->payment_method,
            'net_salary'     => (float) $s->net_salary,
            'gross_salary'   => (float) $s->gross_salary,
        ];

        if ($detailed) {
            $base['details'] = [
                'hours_worked'        => (float) $s->hours_worked,
                'overtime_hours'      => (float) $s->overtime_hours,
                'hourly_rate'         => (float) $hourlyRate,
                'salary_from_hours'   => $salaryA,
                'salary_from_overtime'=> $salaryB,
                'manual_additions'    => (float) $s->manual_additions,
                'late_deduction'      => (float) $s->late_deduction,
                'late_minutes'        => (int) $s->late_minutes,
                'absence_deduction'   => (float) $s->absence_deduction,
                'manual_deductions'   => (float) $s->manual_deductions,
                'loan_deduction'      => (float) $s->loan_deduction_amount,
                'total_deductions'    => (float) ($s->total_deductions + $s->loan_deduction_amount),
                'balance_before'      => (float) $s->balance_before,
                'balance_after'       => (float) $s->balance_after,
                'notes'               => $s->notes,
            ];
        }

        return $base;
    }
}
