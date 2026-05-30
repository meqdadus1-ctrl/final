<?php

namespace App\Http\Controllers;

use App\Models\AuditLog;
use App\Models\Bank;
use App\Models\Department;
use App\Models\Employee;
use App\Models\EmployeeLedger;
use App\Models\LeaveRequest;
use App\Models\Loan;
use App\Models\SalaryAdjustment;
use App\Models\SalaryPayment;
use Carbon\Carbon;
use Illuminate\Support\Facades\DB;

class DashboardController extends Controller
{
    public function index()
    {
        /* ---- إحصاءات عامة ---- */
        $activeCount       = Employee::where('status', 'active')->count();
        $deptCount         = Department::count();
        $activeLoanCount   = Loan::where('status', 'active')->count();
        $pendingLeaves     = LeaveRequest::where('status', 'pending')->count();
        $pendingAdjCount   = SalaryAdjustment::where('status', 'pending')->count();
        $bankCount         = Bank::count();

        /* ---- الأسبوع الحالي ---- */
        $today     = Carbon::now();
        $dayOfWeek = $today->dayOfWeek;
        $daysBack  = ($dayOfWeek >= Carbon::THURSDAY) ? ($dayOfWeek - Carbon::THURSDAY) : ($dayOfWeek + 3);
        $weekStart = $today->copy()->subDays($daysBack)->toDateString();
        $weekEnd   = $today->copy()->subDays($daysBack)->addDays(6)->toDateString();
        $currentFiscal = Carbon::parse($weekStart)->format('Y-\WW');

        /* ---- رواتب هذا الأسبوع ---- */
        $weekPayroll = SalaryPayment::where('fiscal_period', $currentFiscal)->sum('net_salary');

        /* ---- إجمالي أرصدة الموظفين ---- */
        $totalBalance = (float) EmployeeLedger::whereIn(
            'id',
            EmployeeLedger::selectRaw('MAX(id)')
                ->whereHas('employee', fn($q) => $q->where('status', 'active'))
                ->groupBy('employee_id')
        )->sum('balance_after');

        /* ---- مخطط الرواتب: آخر 10 أسابيع ---- */
        $payrollChart = SalaryPayment::selectRaw('fiscal_period, SUM(net_salary) as total')
            ->groupBy('fiscal_period')
            ->orderByDesc('fiscal_period')
            ->limit(10)
            ->pluck('total', 'fiscal_period')
            ->reverse();

        /* ---- مخطط الحضور: هذا الأسبوع ---- */
        $attendanceStats = DB::table('attendance')
            ->selectRaw("status, COUNT(*) as cnt")
            ->whereBetween('date', [$weekStart, $weekEnd])
            ->groupBy('status')
            ->pluck('cnt', 'status');

        $presentCount = ($attendanceStats['present'] ?? 0) + ($attendanceStats['late'] ?? 0);
        $absentCount  = $attendanceStats['absent'] ?? 0;
        $leaveCount   = $attendanceStats['leave']  ?? 0;

        /* ---- أرصدة الموظفين ---- */
        $activeEmps   = Employee::active()->with('ledger')->get();

        /* ---- آخر الرواتب ---- */
        $lastPayments = SalaryPayment::with('employee')->latest()->limit(6)->get();

        /* ---- آخر موظفين ---- */
        $latestEmployees = Employee::latest()->limit(5)->get();

        /* ---- آخر سجلات التدقيق ---- */
        $recentAudit = AuditLog::with('user')->latest()->limit(8)->get();

        return view('dashboard', compact(
            'activeCount', 'deptCount', 'activeLoanCount', 'pendingLeaves',
            'pendingAdjCount', 'bankCount', 'weekStart', 'weekEnd', 'currentFiscal',
            'weekPayroll', 'totalBalance',
            'payrollChart', 'presentCount', 'absentCount', 'leaveCount',
            'activeEmps', 'lastPayments', 'latestEmployees', 'recentAudit'
        ));
    }
}
