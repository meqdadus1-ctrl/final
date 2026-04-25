<?php

namespace App\Http\Controllers;

use App\Models\Loan;
use App\Models\Employee;
use Illuminate\Http\Request;

class LoanController extends Controller
{
    public function index()
    {
        $loans = Loan::with('employee')->latest()->paginate(20);
        return view('loans.index', compact('loans'));
    }

    public function create()
    {
        $employees = Employee::active()->orderBy('name')->get();
        return view('loans.create', compact('employees'));
    }

    public function store(Request $request)
    {
        $request->validate([
            'employee_id'        => 'required|exists:employees,id',
            'total_amount'       => 'required|numeric|min:1',
            'installment_amount' => 'required|numeric|min:1',
            'installments_total' => 'required|integer|min:1|max:104',
            'installment_type'   => 'nullable|in:weekly,monthly',
            'start_date'         => 'required|date',
            'description'        => 'nullable|string',
        ]);

        // التحقق من أن مجموع الأقساط لا يتجاوز المبلغ الكلي
        $totalByInstallments = $request->installment_amount * $request->installments_total;
        if ($totalByInstallments < $request->total_amount) {
            return back()->withErrors([
                'installment_amount' => 'مجموع الأقساط (' . $totalByInstallments . ') أقل من المبلغ الكلي (' . $request->total_amount . '). يرجى رفع قيمة القسط أو عدد الأقساط.',
            ])->withInput();
        }

        Loan::create([
            'employee_id'        => $request->employee_id,
            'total_amount'       => $request->total_amount,
            'installment_amount' => $request->installment_amount,
            'installment_type'   => $request->installment_type ?? 'weekly',
            'installments_total' => $request->installments_total,
            'amount_paid'        => 0,
            'installments_paid'  => 0,
            'start_date'         => $request->start_date,
            'status'             => 'active',
            'description'        => $request->description,
        ]);

        return redirect()->route('loans.index')->with('success', 'تم إضافة السلفة بنجاح');
    }

    public function show(Loan $loan)
    {
        $loan->load('employee');
        return view('loans.show', compact('loan'));
    }

    public function edit(Loan $loan)
    {
        $employees = Employee::active()->orderBy('name')->get();
        return view('loans.edit', compact('loan', 'employees'));
    }

    public function update(Request $request, Loan $loan)
    {
        $request->validate([
            'is_paused' => 'boolean',
            'status'    => 'in:active,completed,cancelled',
        ]);

        $loan->update($request->only('is_paused', 'status', 'description'));

        return redirect()->route('loans.index')->with('success', 'تم تحديث السلفة');
    }

    public function destroy(Loan $loan)
    {
        $loan->delete();
        return redirect()->route('loans.index')->with('success', 'تم حذف السلفة');
    }

    public function payInstallment(Loan $loan)
    {
        if ($loan->status !== 'active' || $loan->is_paused) {
            return back()->with('error', 'لا يمكن دفع قسط لهذه السلفة');
        }

        $loan->amount_paid        += $loan->installment_amount;
        $loan->installments_paid  += 1;
        $loan->last_payment_date   = now();

        if ($loan->installments_paid >= $loan->installments_total) {
            $loan->status = 'completed';
        }

        $loan->save();

        return back()->with('success', 'تم دفع القسط بنجاح');
    }
}