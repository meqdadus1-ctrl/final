<?php

namespace App\Http\Controllers;

use App\Models\Employee;
use App\Models\EmployeeLedger;
use App\Services\LedgerService;
use Illuminate\Http\Request;
use Carbon\Carbon;

class LedgerController extends Controller
{
    public function __construct(private LedgerService $ledger) {}

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

        // All employees for quick nav
        $employees = Employee::active()->orderBy('name')->get(['id','name']);

        return view('ledger.show', compact(
            'employee', 'entries', 'summary', 'balance',
            'employees', 'from', 'to'
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

        $balance = $this->ledger->getCurrentBalance($employee->id);

        if ((float)$request->amount > $balance) {
            return back()->with('error',
                'المبلغ المطلوب (' . number_format($request->amount, 2) . ' ₪) '
                . 'أكبر من الرصيد المتاح (' . number_format($balance, 2) . ' ₪).'
            );
        }

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
     *  DESTROY ENTRY — حذف قيد محاسبي
     * ===================================================== */
    public function destroyEntry(EmployeeLedger $entry)
    {
        $employeeId = $entry->employee_id;
        $entryId    = $entry->id;

        $entry->delete();

        // إعادة حساب الأرصدة اللاحقة
        $this->ledger->recalculateBalances($employeeId);

        return back()->with('success', 'تم حذف القيد #' . $entryId . ' وإعادة حساب الأرصدة.');
    }
}
