<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Models\Employee;
use App\Models\Loan;
use Illuminate\Http\Request;

class LoanController extends Controller
{
    /**
     * GET /api/v1/loans
     */
    public function index(Request $request)
    {
        $employee = Employee::where('user_id', $request->user()->id)->firstOrFail();

        $loans = Loan::where('employee_id', $employee->id)
            ->orderByDesc('created_at')
            ->get()
            ->map(fn($l) => $this->formatLoan($l));

        return response()->json([
            'success' => true,
            'data'    => $loans,
        ]);
    }

    /**
     * POST /api/v1/loans
     */
    public function store(Request $request)
    {
        $employee = Employee::where('user_id', $request->user()->id)->firstOrFail();

        // تأكد مافي سلفة نشطة أو معلّقة
        $existing = Loan::where('employee_id', $employee->id)
            ->whereIn('status', ['active', 'pending'])
            ->first();

        if ($existing) {
            return response()->json([
                'success' => false,
                'message' => 'لديك طلب سلفة نشط بالفعل. أكمل سداده أولاً.',
            ], 422);
        }

        $request->validate([
            'total_amount'       => 'required|numeric|min:100',
            'installments_total' => 'required|integer|min:1|max:52',
            'reason'             => 'required|string|max:500',
        ]);

        $installmentAmount = round($request->total_amount / $request->installments_total, 2);

        $loan = Loan::create([
            'employee_id'        => $employee->id,
            'total_amount'       => $request->total_amount,
            'installment_amount' => $installmentAmount,
            'installments_total' => $request->installments_total,
            'installments_paid'  => 0,
            'amount_paid'        => 0,
            'start_date'         => now()->toDateString(),
            'description'        => $request->reason,
            'status'             => 'pending',
        ]);

        // إشعار الإدارة
        \App\Models\MobileNotification::create([
            'employee_id' => $employee->id,
            'type'        => 'loan_request',
            'title'       => 'طلب سلفة جديد',
            'body'        => "الموظف {$employee->name} يطلب سلفة بمبلغ {$request->total_amount} ₪",
            'data'        => json_encode(['loan_id' => $loan->id]),
            'target'      => 'admin',
        ]);

        return response()->json([
            'success' => true,
            'message' => 'تم إرسال طلب السلفة بنجاح. سيتم الرد عليك قريباً.',
            'data'    => $this->formatLoan($loan),
        ], 201);
    }

    private function formatLoan(Loan $l): array
    {
        return [
            'id'                 => $l->id,
            'total_amount'       => (float) $l->total_amount,
            'remaining_amount'   => (float) $l->remaining_amount,
            'amount_paid'        => (float) $l->amount_paid,
            'installment_amount' => (float) $l->installment_amount,
            'installments_total' => (int) $l->installments_total,
            'installments_paid'  => (int) $l->installments_paid,
            'status'             => $l->status,
            'reason'             => $l->description,
            'start_date'         => $l->start_date?->format('Y-m-d'),
            'rejection_reason'   => $l->rejection_reason ?? null,
            'progress_percent'   => $l->installments_total > 0
                ? round($l->installments_paid / $l->installments_total * 100, 1)
                : 0,
        ];
    }
}
