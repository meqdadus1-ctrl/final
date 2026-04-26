<?php

namespace App\Http\Controllers;

use App\Models\Attendance;
use App\Models\Department;
use App\Models\Employee;
use App\Models\EmployeeLedger;
use App\Models\LeaveBalance;
use App\Models\LeaveRequest;
use App\Models\Loan;
use App\Models\SalaryAdjustment;
use App\Models\SalaryPayment;
use Carbon\Carbon;
use Illuminate\Http\Request;

class ReportController extends Controller
{
    /* =====================================================
     *  صفحة الفلاتر
     * ===================================================== */
    public function index()
    {
        $employees   = Employee::with('department')->active()->orderBy('name')->get(['id','name','department_id']);
        $departments = Department::orderBy('name')->get(['id','name']);
        return view('reports.index', compact('employees', 'departments'));
    }

    /* =====================================================
     *  عرض التقرير HTML (طباعة أو معاينة)
     * ===================================================== */
    public function generate(Request $request)
    {
        $request->validate([
            'employees'   => 'required|array|min:1',
            'employees.*' => 'integer|exists:employees,id',
            'from'        => 'required|date',
            'to'          => 'required|date|after_or_equal:from',
        ]);

        $from     = $request->from;
        $to       = $request->to;
        $sections = $request->sections ?? ['attendance','salary','loans','leaves'];

        $data = $this->buildReportData($request->employees, $from, $to, $sections);

        return view('reports.generate', compact('data','from','to','sections'));
    }

    /* =====================================================
     *  تصدير PDF عبر Dompdf
     * ===================================================== */
    public function pdf(Request $request)
    {
        $request->validate([
            'employees'   => 'required|array|min:1',
            'employees.*' => 'integer|exists:employees,id',
            'from'        => 'required|date',
            'to'          => 'required|date|after_or_equal:from',
        ]);

        $from     = $request->from;
        $to       = $request->to;
        $sections = $request->sections ?? ['attendance','salary','loans','leaves'];

        $data = $this->buildReportData($request->employees, $from, $to, $sections);

        $html = view('reports.pdf', compact('data','from','to','sections'))->render();

        $dompdf = new \Dompdf\Dompdf(['isRemoteEnabled' => true, 'isHtml5ParserEnabled' => true]);
        $dompdf->loadHtml($html, 'UTF-8');
        $dompdf->setPaper('A4', 'portrait');
        $dompdf->render();

        $filename = 'تقرير_' . $from . '_' . $to . '.pdf';
        return response($dompdf->output(), 200, [
            'Content-Type'        => 'application/pdf',
            'Content-Disposition' => 'inline; filename="' . $filename . '"',
        ]);
    }

    /* =====================================================
     *  بناء بيانات التقرير لكل الموظفين
     * ===================================================== */
    private function buildReportData(array $ids, string $from, string $to, array $sections): array
    {
        $employees = Employee::with('department')
            ->whereIn('id', $ids)
            ->orderBy('name')
            ->get();

        $data = [];

        foreach ($employees as $employee) {
            $emp = ['employee' => $employee];

            /* ① الحضور والانصراف */
            if (in_array('attendance', $sections)) {
                $att = Attendance::where('employee_id', $employee->id)
                    ->whereBetween('date', [$from, $to])
                    ->orderBy('date')
                    ->get();

                $emp['attendance'] = [
                    'records'        => $att,
                    'present_days'   => $att->where('status','present')->count(),
                    'absent_days'    => $att->where('status','absent')->count(),
                    'leave_days'     => $att->whereIn('status',['leave','on_leave'])->count(),
                    'total_hours'    => round($att->sum('work_hours'), 2),
                    'overtime_hours' => round($att->sum('overtime_hours'), 2),
                    'late_count'     => $att->filter(function ($a) use ($employee) {
                        if ($a->status !== 'present' || !$a->check_in) return false;
                        $shift = $employee->shift_start ?? '08:00:00';
                        return Carbon::parse($a->check_in)->format('H:i:s') > $shift;
                    })->count(),
                ];
            }

            /* ② كشف الراتب */
            if (in_array('salary', $sections)) {
                $payments = SalaryPayment::where('employee_id', $employee->id)
                    ->where(function ($q) use ($from, $to) {
                        $q->whereBetween('week_start', [$from, $to])
                          ->orWhereBetween('week_end', [$from, $to])
                          ->orWhere(fn($q2) => $q2->where('week_start','<=',$from)->where('week_end','>=',$to));
                    })
                    ->orderBy('week_start')
                    ->get();

                $adjustments = SalaryAdjustment::where('employee_id', $employee->id)
                    ->whereBetween('adjustment_date', [$from, $to])
                    ->where('status','approved')
                    ->orderBy('adjustment_date')
                    ->get();

                $emp['salary'] = [
                    'payments'         => $payments,
                    'adjustments'      => $adjustments,
                    'total_gross'      => round($payments->sum('gross_salary'), 2),
                    'total_net'        => round($payments->sum('net_salary'), 2),
                    'total_hours'      => round($payments->sum('hours_worked'), 2),
                    'total_overtime'   => round($payments->sum('overtime_hours'), 2),
                    'total_late_ded'   => round($payments->sum('late_deduction'), 2),
                    'total_loan_ded'   => round($payments->sum('loan_deduction_amount'), 2),
                    'total_deductions' => round($payments->sum('total_deductions'), 2),
                    'bonus_total'      => round($adjustments->where('sign', 1)->sum('amount'), 2),
                    'deduct_manual'    => round($adjustments->where('sign',-1)->sum('amount'), 2),
                ];
            }

            /* ③ السلف */
            if (in_array('loans', $sections)) {
                $loans = Loan::where('employee_id', $employee->id)
                    ->where(function ($q) use ($from, $to) {
                        $q->whereBetween('start_date', [$from, $to])
                          ->orWhereIn('status', ['active','pending']);
                    })
                    ->orderByRaw("FIELD(status,'active','pending','completed','cancelled') ASC")
                    ->orderBy('start_date')
                    ->get();

                $emp['loans'] = [
                    'records'         => $loans,
                    'total_borrowed'  => round($loans->sum('total_amount'), 2),
                    'total_paid'      => round($loans->sum('amount_paid'), 2),
                    'total_remaining' => round($loans->sum(fn($l) => max(0, $l->total_amount - $l->amount_paid)), 2),
                    'active_count'    => $loans->where('status','active')->count(),
                ];
            }

            /* ④ الإجازات */
            if (in_array('leaves', $sections)) {
                $leaves = LeaveRequest::with('leaveType')
                    ->where('employee_id', $employee->id)
                    ->where(function ($q) use ($from, $to) {
                        $q->whereBetween('start_date', [$from, $to])
                          ->orWhereBetween('end_date', [$from, $to]);
                    })
                    ->orderBy('start_date')
                    ->get();

                $balances = LeaveBalance::with('leaveType')
                    ->where('employee_id', $employee->id)
                    ->where('year', Carbon::parse($from)->year)
                    ->get();

                $emp['leaves'] = [
                    'records'     => $leaves,
                    'balances'    => $balances,
                    'total_days'  => $leaves->where('status','approved')->sum('total_days'),
                    'approved'    => $leaves->where('status','approved')->count(),
                    'pending'     => $leaves->where('status','pending')->count(),
                    'rejected'    => $leaves->where('status','rejected')->count(),
                ];
            }

            /* رصيد كشف الحساب الحالي */
            $emp['ledger_balance'] = (float)(EmployeeLedger::where('employee_id', $employee->id)
                ->orderBy('id','desc')
                ->value('balance_after') ?? 0);

            $data[] = $emp;
        }

        return $data;
    }
}
