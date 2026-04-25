<?php

namespace App\Http\Controllers;

use App\Models\Employee;
use App\Models\EmployeeLedger;
use App\Models\SalaryPayment;
use App\Models\LeaveRequest;
use App\Models\LeaveType;
use App\Models\Loan;
use App\Models\Attendance;
use App\Services\LedgerService;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Carbon\Carbon;

class EmployeePortalController extends Controller
{
    public function __construct(private LedgerService $ledger) {}

    /* =====================================================
     *  Helper: جلب الموظف الحالي
     * ===================================================== */
    private function getEmployee()
    {
        return Employee::where('user_id', Auth::id())
            ->with(['department', 'activeLoan'])
            ->firstOrFail();
    }

    /* =====================================================
     *  DASHBOARD / كشف الحساب – /portal
     * ===================================================== */
    public function index(Request $request)
    {
        $employee = $this->getEmployee();

        // فلتر الفترة
        $from = $request->get('from', now()->subMonths(3)->toDateString());
        $to   = $request->get('to',   now()->toDateString());

        // قيود الـ Ledger
        $entries = EmployeeLedger::where('employee_id', $employee->id)
            ->whereBetween('entry_date', [$from, $to])
            ->orderBy('entry_date')
            ->orderBy('id')
            ->get();

        // الرصيد الحالي والملخص
        $balance = $this->ledger->getCurrentBalance($employee->id);
        $summary = $this->ledger->getSummary($employee->id);

        return view('portal.index', compact(
            'employee', 'entries', 'balance', 'summary', 'from', 'to'
        ));
    }

    /* =====================================================
     *  PAYSLIPS – /portal/payslips
     * ===================================================== */
    public function payslips()
    {
        $employee = $this->getEmployee();

        $payslips = SalaryPayment::where('employee_id', $employee->id)
            ->orderByDesc('payment_date')
            ->paginate(12);

        return view('portal.payslips', compact('employee', 'payslips'));
    }

    /* =====================================================
     *  LEAVES – /portal/leaves
     * ===================================================== */
    public function leaves()
    {
        $employee = $this->getEmployee();

        $leaves = LeaveRequest::with('leaveType')
            ->where('employee_id', $employee->id)
            ->orderByDesc('created_at')
            ->paginate(10);

        $leaveTypes = LeaveType::orderBy('name')->get();

        return view('portal.leaves', compact('employee', 'leaves', 'leaveTypes'));
    }

    /* =====================================================
     *  STORE LEAVE REQUEST – POST /portal/leaves
     * ===================================================== */
    public function storeLeave(Request $request)
    {
        $employee = $this->getEmployee();

        $request->validate([
            'leave_type_id' => 'required|exists:leave_types,id',
            'start_date'    => 'required|date|after_or_equal:today',
            'end_date'      => 'required|date|after_or_equal:start_date',
            'reason'        => 'nullable|string|max:500',
        ]);

        $start = Carbon::parse($request->start_date);
        $end   = Carbon::parse($request->end_date);
        $days  = $start->diffInDays($end) + 1;

        LeaveRequest::create([
            'employee_id'   => $employee->id,
            'leave_type_id' => $request->leave_type_id,
            'start_date'    => $request->start_date,
            'end_date'      => $request->end_date,
            'total_days'    => $days,
            'reason'        => $request->reason,
            'status'        => 'pending',
        ]);

        return back()->with('success', 'تم تقديم طلب الإجازة بنجاح، سيتم مراجعته قريباً ✅');
    }

    /* =====================================================
     *  LOANS – /portal/loans
     * ===================================================== */
    public function loans()
    {
        $employee = $this->getEmployee();

        $loans = Loan::where('employee_id', $employee->id)
            ->orderByDesc('created_at')
            ->paginate(10);

        return view('portal.loans', compact('employee', 'loans'));
    }

    /* =====================================================
     *  STORE LOAN REQUEST – POST /portal/loans
     * ===================================================== */
    public function storeLoan(Request $request)
    {
        $employee = $this->getEmployee();

        $request->validate([
            'total_amount'       => 'required|numeric|min:100',
            'installments_total' => 'required|integer|min:1|max:24',
            'description'        => 'nullable|string|max:500',
        ]);

        // تأكد مافي سلفة نشطة
        $activeLoan = Loan::where('employee_id', $employee->id)
            ->whereIn('status', ['pending', 'active'])
            ->exists();

        if ($activeLoan) {
            return back()->with('error', 'لديك طلب سلفة نشط بالفعل، يرجى الانتظار حتى يتم البت فيه.');
        }

        $installmentAmount = round($request->total_amount / $request->installments_total, 2);

        Loan::create([
            'employee_id'        => $employee->id,
            'total_amount'       => $request->total_amount,
            'installment_amount' => $installmentAmount,
            'installments_total' => $request->installments_total,
            'amount_paid'        => 0,
            'installments_paid'  => 0,
            'start_date'         => now()->toDateString(),
            'description'        => $request->description,
            'status'             => 'pending',
        ]);

        return back()->with('success', 'تم تقديم طلب السلفة بنجاح، سيتم مراجعته قريباً ✅');
    }

    /* =====================================================
     *  ATTENDANCE – /portal/attendance
     * ===================================================== */
    public function attendance(Request $request)
    {
        $employee = $this->getEmployee();

        $month = (int) ($request->month ?? now()->month);
        $year  = (int) ($request->year  ?? now()->year);

        $records = Attendance::where('employee_id', $employee->id)
            ->whereYear('date', $year)
            ->whereMonth('date', $month)
            ->orderBy('date')
            ->get();

        $stats = [
            'present' => $records->where('status', 'present')->count(),
            'absent'  => $records->where('status', 'absent')->count(),
            'late'    => $records->where('status', 'late')->count(),
            'leave'   => $records->where('status', 'leave')->count(),
        ];

        return view('portal.attendance', compact('employee', 'records', 'stats', 'month', 'year'));
    }
}
