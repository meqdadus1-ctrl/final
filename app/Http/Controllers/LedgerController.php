<?php

namespace App\Http\Controllers;

use App\Models\Department;
use App\Models\Employee;
use App\Models\EmployeeLedger;
use App\Services\LedgerService;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Carbon\Carbon;

class LedgerController extends Controller
{
    public function __construct(private LedgerService $ledger) {}

    /* =====================================================
     *  INDEX — قائمة جميع الموظفين مع أرصدتهم
     * ===================================================== */
    public function index(Request $request)
    {
        $departments = Department::orderBy('name')->get(['id', 'name']);

        $query = Employee::with('department')
            ->when($request->filled('dept'),   fn($q) => $q->where('department_id', $request->dept))
            ->when($request->filled('search'), fn($q) => $q->where('name', 'like', '%'.$request->search.'%'))
            ->when($request->filled('status') && $request->status !== 'all',
                fn($q) => $q->where('status', $request->status),
                fn($q) => $q->whereIn('status', ['active', 'inactive'])
            )
            ->orderBy('name');

        $employees = $query->get(['id', 'name', 'department_id', 'status', 'job_title', 'employee_number']);

        // جلب آخر رصيد لكل موظف بـ subquery واحدة
        $latestBalances = EmployeeLedger::select('employee_id', DB::raw('MAX(id) as last_id'))
            ->whereIn('employee_id', $employees->pluck('id'))
            ->groupBy('employee_id')
            ->pluck('last_id', 'employee_id');

        $balances = EmployeeLedger::whereIn('id', $latestBalances->values())
            ->pluck('balance_after', 'employee_id');

        return view('ledger.index', compact('employees', 'departments', 'balances'));
    }

    /* =====================================================
     *  JOURNAL — جميع القيود المحاسبية بالترتيب
     * ===================================================== */
    public function journal(Request $request)
    {
        $departments = Department::orderBy('name')->get(['id', 'name']);
        $employees   = Employee::orderBy('name')->get(['id', 'name', 'department_id']);

        $entryTypes = [
            'salary'            => 'راتب',
            'overtime'          => 'أوفرتايم',
            'bonus'             => 'مكافأة',
            'deduction_late'    => 'خصم تأخير',
            'deduction_absence' => 'خصم غياب',
            'deduction_manual'  => 'خصم يدوي',
            'loan_installment'  => 'قسط سلفة',
            'loan_disbursement' => 'صرف سلفة',
            'withdrawal'        => 'سحب / مصروف',
            'payment'           => 'دفع للموظف',
            'adjustment'        => 'تسوية',
            'opening_balance'   => 'رصيد افتتاحي',
        ];

        $filters = fn($q) => $q
            ->when($request->filled('from'),       fn($q) => $q->where('entry_date', '>=', $request->from))
            ->when($request->filled('to'),         fn($q) => $q->where('entry_date', '<=', $request->to))
            ->when($request->filled('employee'),   fn($q) => $q->where('employee_id', $request->employee))
            ->when($request->filled('dept'),       fn($q) => $q->whereHas('employee', fn($e) => $e->where('department_id', $request->dept)))
            ->when($request->filled('entry_type'), fn($q) => $q->where('entry_type', $request->entry_type))
            ->when($request->filled('side') && $request->side === 'credit', fn($q) => $q->where('credit', '>', 0))
            ->when($request->filled('side') && $request->side === 'debit',  fn($q) => $q->where('debit', '>', 0));

        $totals = $filters(EmployeeLedger::query())
            ->selectRaw('SUM(credit) as total_credit, SUM(debit) as total_debit, COUNT(*) as total_count')
            ->first();

        $entries = $filters(EmployeeLedger::with(['employee.department', 'createdBy']))
            ->orderByDesc('entry_date')
            ->orderByDesc('id')
            ->paginate(50)
            ->withQueryString();

        return view('ledger.journal', compact('entries', 'departments', 'employees', 'entryTypes', 'totals'));
    }

    /* =====================================================
     *  SHOW — كشف حساب موظف (bank statement style)
     * ===================================================== */
    public function show(Employee $employee, Request $request)
    {
        $from = $request->filled('from') ? $request->from : null;
        $to   = $request->filled('to')   ? $request->to   : null;

        // Default: last 3 months
        if (!$from && !$to) {
            $from = now()->subMonths(3)->toDateString();
            $to   = now()->toDateString();
        }

        $entries = EmployeeLedger::where('employee_id', $employee->id)
            ->when($from, fn($q) => $q->where('entry_date', '>=', $from))
            ->when($to,   fn($q) => $q->where('entry_date', '<=', $to))
            ->orderBy('id')
            ->get();

        $summary  = $this->ledger->getSummary($employee->id, $from, $to);
        $balance  = $this->ledger->getCurrentBalance($employee->id);

        // Balance at the start of the displayed period (the row just before $from).
        // Shown as "رصيد أول المدة" so users understand where the running balance begins.
        $periodOpeningBalance = 0.0;
        if ($from) {
            $periodOpeningBalance = (float)(EmployeeLedger::where('employee_id', $employee->id)
                ->where('entry_date', '<', $from)
                ->orderByDesc('entry_date')->orderByDesc('id')
                ->value('balance_after') ?? 0.0);
        }

        // All employees for quick nav
        $employees = Employee::active()->orderBy('name')->get(['id','name']);

        return view('ledger.show', compact(
            'employee', 'entries', 'summary', 'balance',
            'employees', 'from', 'to', 'periodOpeningBalance'
        ));
    }

    /* =====================================================
     *  PDF — طباعة كشف الحساب
     * ===================================================== */
    public function pdf(Employee $employee, Request $request)
    {
        $from = $request->from ?? now()->subMonths(3)->toDateString();
        $to   = $request->to   ?? now()->toDateString();

        $entries = EmployeeLedger::where('employee_id', $employee->id)
            ->where('entry_date', '>=', $from)
            ->where('entry_date', '<=', $to)
            ->orderBy('id')
            ->get();

        $summary = $this->ledger->getSummary($employee->id, $from, $to);
        $balance = $this->ledger->getCurrentBalance($employee->id);

        return view('ledger.pdf', compact('employee', 'entries', 'summary', 'balance', 'from', 'to'));
    }

    /* =====================================================
     *  SET OPENING BALANCE
     * ===================================================== */
    public function setOpeningBalance(Employee $employee, Request $request)
    {
        $request->validate([
            'amount' => 'required|numeric',
            'date'   => 'required|date',
            'reason' => 'nullable|string|max:255',
        ]);

        $this->ledger->recordOpeningBalance(
            $employee,
            (float) $request->amount,
            $request->date,
            $request->reason ?? 'رصيد افتتاحي'
        );

        return back()->with('success', 'تم تسجيل الرصيد الافتتاحي.');
    }

    /* =====================================================
     *  RECORD PAYMENT — تسجيل قبض الموظف
     *  يُخفض الرصيد: الشركة دفعت للموظف
     * ===================================================== */
    public function recordPayment(Employee $employee, Request $request)
    {
        $request->validate([
            'amount'         => 'required|numeric|min:0.01',
            'payment_date'   => 'required|date',
            'payment_method' => 'required|in:cash,bank',
            'notes'          => 'nullable|string|max:255',
        ]);

        $desc = $request->notes
            ? $request->notes
            : 'قبض ' . ($request->payment_method === 'bank' ? 'تحويل بنكي' : 'نقدي');

        $this->ledger->recordPayment(
            $employee->id,
            (float) $request->amount,
            $request->payment_date,
            $desc,
            $request->payment_method
        );

        return back()->with('success',
            'تم تسجيل قبض ' . number_format($request->amount, 2) . ' ₪ بنجاح.'
        );
    }

    /* =====================================================
     *  STORE ENTRY — إضافة قيد محاسبي يدوي
     * ===================================================== */
    public function storeEntry(Employee $employee, Request $request)
    {
        $request->validate([
            'entry_date'    => 'required|date',
            'entry_type'    => 'required|string|max:100',
            'side'          => 'required|in:credit,debit',
            'amount'        => 'required|numeric|min:0.01',
            'description'   => 'required|string|max:500',
            'fiscal_period' => 'nullable|string|max:20',
            'period_start'  => 'nullable|date',
            'period_end'    => 'nullable|date',
        ]);

        $credit = $request->side === 'credit' ? (float)$request->amount : 0.0;
        $debit  = $request->side === 'debit'  ? (float)$request->amount : 0.0;

        $this->ledger->addEntry(
            $employee->id,
            $request->entry_type,
            $credit,
            $debit,
            $request->description,
            $request->entry_date,
            [
                'fiscal_period'  => $request->fiscal_period,
                'period_start'   => $request->period_start,
                'period_end'     => $request->period_end,
                'reference_type' => 'Manual',
                'reference_id'   => null,
                'created_by'     => auth()->id(),
            ]
        );

        return back()->with('success', 'تم إضافة القيد المحاسبي بنجاح.');
    }

    /* =====================================================
     *  UPDATE ENTRY — تعديل قيد محاسبي
     * ===================================================== */
    public function updateEntry(Request $request, EmployeeLedger $entry)
    {
        $request->validate([
            'entry_date'    => 'required|date',
            'entry_type'    => 'required|string|max:100',
            'side'          => 'required|in:credit,debit',
            'amount'        => 'required|numeric|min:0.01',
            'description'   => 'required|string|max:500',
            'fiscal_period' => 'nullable|string|max:20',
            'period_start'  => 'nullable|date',
            'period_end'    => 'nullable|date',
        ]);

        $credit = $request->side === 'credit' ? (float)$request->amount : 0.0;
        $debit  = $request->side === 'debit'  ? (float)$request->amount : 0.0;

        $entry->update([
            'entry_date'    => $request->entry_date,
            'entry_type'    => $request->entry_type,
            'description'   => $request->description,
            'credit'        => $credit,
            'debit'         => $debit,
            'fiscal_period' => $request->fiscal_period,
            'period_start'  => $request->period_start,
            'period_end'    => $request->period_end,
        ]);

        // إعادة حساب جميع الأرصدة للموظف
        $this->ledger->recalculateBalances($entry->employee_id);

        return back()->with('success', 'تم تعديل القيد #' . $entry->id . ' بنجاح.');
    }

    /* =====================================================
     *  DESTROY ENTRY — حذف قيد محاسبي (يدوي أو مستورد من Excel)
     * ===================================================== */
    public function destroyEntry(EmployeeLedger $entry)
    {
        $employeeId = $entry->employee_id;
        $entryId    = $entry->id;

        $entry->delete();

        $this->ledger->recalculateBalances($employeeId);

        return back()->with('success', 'تم حذف القيد #' . $entryId . ' وإعادة حساب الأرصدة.');
    }
}
