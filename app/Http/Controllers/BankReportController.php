<?php

namespace App\Http\Controllers;

use App\Models\SalaryPayment;
use App\Models\Bank;
use Illuminate\Http\Request;

class BankReportController extends Controller
{
    public function index(Request $request)
    {
        $year = $request->get('year', now()->year);
        $week = $request->get('week', null);

        $banks = Bank::all();

        // جلب جميع المدفوعات في query واحدة بدل N+1
        $paymentsQuery = SalaryPayment::with('employee.bank');

        if ($week) {
            $paymentsQuery->where('fiscal_period', $year . '-W' . str_pad($week, 2, '0', STR_PAD_LEFT));
        } else {
            $paymentsQuery->whereYear('payment_date', $year);
        }

        $allPayments = $paymentsQuery->get();

        // تقسيم المدفوعات حسب طريقة الدفع الحقيقية
        $cashPayments     = $allPayments->where('payment_method', 'cash');
        $bankPayments     = $allPayments->where('payment_method', 'bank');
        $deferredPayments = $allPayments->where('payment_method', 'deferred');

        $bankData = [];

        // 1. صندوق الكاش — جميع المدفوعات النقدية
        $bankData[] = [
            'type'     => 'cash',
            'label'    => 'صندوق الكاش',
            'icon'     => 'fas fa-money-bill',
            'total'    => $cashPayments->sum('net_salary'),
            'count'    => $cashPayments->count(),
            'payments' => $cashPayments->values(),
        ];

        // 2. البنوك — مجمعة حسب bank_id الموظف
        foreach ($banks as $bank) {
            $payments = $bankPayments->filter(function ($p) use ($bank) {
                return $p->employee && $p->employee->bank_id === $bank->id;
            })->values();

            $bankData[] = [
                'type'     => 'bank',
                'label'    => $bank->bank_name,
                'icon'     => 'fas fa-university',
                'total'    => $payments->sum('net_salary'),
                'count'    => $payments->count(),
                'payments' => $payments,
            ];
        }

        // 3. المرحّلة — لم تُدفع بعد
        $bankData[] = [
            'type'     => 'deferred',
            'label'    => 'مرحّلة (لم تُصرف)',
            'icon'     => 'fas fa-clock',
            'total'    => $deferredPayments->sum('net_salary'),
            'count'    => $deferredPayments->count(),
            'payments' => $deferredPayments->values(),
        ];

        $weeks = [];
        for ($w = 1; $w <= 52; $w++) {
            $weeks[$w] = 'أسبوع ' . $w;
        }

        $grandTotal = collect($bankData)->sum('total');

        return view('banks.report', compact('bankData', 'year', 'week', 'weeks', 'grandTotal'));
    }
}