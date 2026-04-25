<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Models\Employee;
use Illuminate\Http\Request;

class ProfileController extends Controller
{
    /**
     * GET /api/v1/profile
     */
    public function show(Request $request)
    {
        $employee = Employee::where('user_id', $request->user()->id)
            ->with(['department', 'bank', 'activeLoan'])
            ->firstOrFail();

        return response()->json([
            'success'  => true,
            'employee' => [
                'id'           => $employee->id,
                'name'         => $employee->name,
                'job_title'    => $employee->job_title,
                'department'   => $employee->department?->name,
                'photo_url'    => $employee->photo_url,
                'national_id'  => $employee->national_id,
                'hire_date'    => $employee->hire_date?->format('Y-m-d'),
                'phone'        => $employee->phone,
                'balance'      => $employee->ledger_balance,

                // بيانات البنك
                'bank' => [
                    'bank_name'    => $employee->bank?->name ?? null,
                    'bank_id'      => $employee->bank_id,
                    'account_name' => $employee->account_name,
                    'bank_account' => $employee->bank_account,
                    'is_locked'    => (bool) $employee->bank_info_locked,
                ],

                // السلفة النشطة
                'active_loan' => $employee->activeLoan ? [
                    'total_amount'      => $employee->activeLoan->total_amount,
                    'remaining_amount'  => $employee->activeLoan->remaining_amount,
                    'installment_amount'=> $employee->activeLoan->installment_amount,
                    'installments_paid' => $employee->activeLoan->installments_paid,
                    'installments_total'=> $employee->activeLoan->installments_total,
                ] : null,
            ],
        ]);
    }

    /**
     * PUT /api/v1/profile/bank
     * الموظف يعدّل بيانات البنك — فقط إذا لم تكن مقفولة
     */
    public function updateBank(Request $request)
    {
        $employee = Employee::where('user_id', $request->user()->id)->firstOrFail();

        if ($employee->bank_info_locked) {
            return response()->json([
                'success' => false,
                'message' => 'بيانات البنك مُعتمدة ومقفولة. تواصل مع الإدارة لأي تعديل.',
            ], 403);
        }

        $request->validate([
            'bank_type'    => 'required|in:bank_of_palestine,pal_pay,jawwal_pay',
            'account_name' => 'required|string|max:255',
            'bank_account' => 'required|string|max:100',
        ]);

        // حفظ طلب التعديل — يذهب للمراجعة أولاً
        $employee->update([
            'pending_bank_type'    => $request->bank_type,
            'pending_account_name' => $request->account_name,
            'pending_bank_account' => $request->bank_account,
            'bank_info_pending'    => true,
        ]);

        return response()->json([
            'success' => true,
            'message' => 'تم إرسال طلب تعديل بيانات البنك. سيتم مراجعته من قِبل الإدارة.',
        ]);
    }
}
