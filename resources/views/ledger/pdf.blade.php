<!DOCTYPE html>
<html lang="ar" dir="rtl">
<head>
    <meta charset="UTF-8">
    <title>كشف حساب — {{ $employee->name }}</title>
    <style>
        * { box-sizing: border-box; margin: 0; padding: 0; }
        body { font-family: 'Arial', sans-serif; font-size: 12px; color: #333; direction: rtl; }
        .header { text-align: center; padding: 20px; border-bottom: 2px solid #333; margin-bottom: 16px; }
        .header h2 { font-size: 18px; margin-bottom: 4px; }
        .header p { color: #666; font-size: 11px; }
        .info-row { display: flex; justify-content: space-between; padding: 8px 0; border-bottom: 1px solid #eee; }
        .info-row .label { color: #777; }
        .section { margin-bottom: 20px; }
        .section-title { font-weight: bold; background: #f5f5f5; padding: 6px 10px; border-right: 4px solid #333; margin-bottom: 8px; }
        table { width: 100%; border-collapse: collapse; font-size: 11px; }
        th { background: #333; color: #fff; padding: 6px 8px; text-align: right; }
        td { padding: 5px 8px; border-bottom: 1px solid #eee; }
        .text-end { text-align: left; }
        .text-center { text-align: center; }
        .credit { color: #2e7d32; font-weight: bold; }
        .debit  { color: #c62828; font-weight: bold; }
        .balance-pos { color: #2e7d32; font-weight: bold; }
        .balance-neg { color: #c62828; font-weight: bold; }
        tfoot td { background: #eee; font-weight: bold; }
        .summary-grid { display: flex; gap: 12px; margin-bottom: 16px; }
        .summary-card { flex: 1; border: 1px solid #ddd; border-radius: 4px; padding: 8px; text-align: center; }
        .summary-card .val { font-size: 14px; font-weight: bold; margin-top: 4px; }
        .footer { margin-top: 20px; text-align: center; font-size: 10px; color: #999; border-top: 1px solid #ddd; padding-top: 8px; }
        @media print {
            @page { margin: 1cm; }
            button { display: none; }
        }
    </style>
</head>
<body>

<div class="header">
    <h2>كشف الحساب</h2>
    <p>{{ $employee->name }} — {{ $employee->department?->name ?? '—' }}</p>
    <p>الفترة: {{ \Carbon\Carbon::parse($from)->format('d/m/Y') }} — {{ \Carbon\Carbon::parse($to)->format('d/m/Y') }}</p>
</div>

{{-- ملخص --}}
<div class="section">
    <div class="summary-grid">
        <div class="summary-card">
            <div style="color:#777;font-size:10px">إجمالي الدائن</div>
            <div class="val credit">{{ number_format($summary['total_credits'] ?? 0, 2) }} ₪</div>
        </div>
        <div class="summary-card">
            <div style="color:#777;font-size:10px">إجمالي المدين</div>
            <div class="val debit">{{ number_format($summary['total_debits'] ?? 0, 2) }} ₪</div>
        </div>
        <div class="summary-card">
            <div style="color:#777;font-size:10px">صافي الرواتب</div>
            <div class="val" style="color:#1565c0">{{ number_format($summary['net_paid'] ?? 0, 2) }} ₪</div>
        </div>
        <div class="summary-card">
            <div style="color:#777;font-size:10px">الرصيد الحالي</div>
            <div class="val {{ $balance >= 0 ? 'balance-pos' : 'balance-neg' }}">{{ number_format($balance, 2) }} ₪</div>
        </div>
    </div>
</div>

{{-- قيود الكشف --}}
<div class="section">
    @php
        $typeLabels = [
            'salary'            => 'راتب ساعات',
            'overtime'          => 'أوفرتايم',
            'bonus'             => 'مكافأة',
            'expense'           => 'مصروف',
            'adjustment'        => 'تعديل',
            'deduction_late'    => 'خصم تأخير',
            'deduction_absence' => 'خصم غياب',
            'deduction_manual'  => 'خصم يدوي',
            'loan_installment'  => 'قسط سلفة',
            'loan_disbursement' => 'صرف سلفة',
            'withdrawal'        => 'مسحوبات',
            'payment'           => 'دفع صافي',
            'opening_balance'   => 'رصيد افتتاحي',
        ];
    @endphp
    <table>
        <thead>
            <tr>
                <th>التاريخ</th>
                <th>البيان</th>
                <th>الكود</th>
                <th class="text-end">دائن</th>
                <th class="text-end">مدين</th>
                <th class="text-end">الرصيد</th>
            </tr>
        </thead>
        <tbody>
            @foreach($entries as $entry)
            <tr>
                <td>{{ $entry->entry_date?->format('d/m/Y') }}</td>
                <td>{{ $typeLabels[$entry->entry_type] ?? $entry->entry_type }} — {{ $entry->description }}</td>
                <td class="text-center">{{ $entry->fiscal_period ?? '—' }}</td>
                <td class="text-end credit">{{ $entry->credit > 0 ? number_format($entry->credit, 2) . ' ₪' : '' }}</td>
                <td class="text-end debit">{{ $entry->debit > 0 ? number_format($entry->debit, 2) . ' ₪' : '' }}</td>
                <td class="text-end {{ $entry->balance_after >= 0 ? 'balance-pos' : 'balance-neg' }}">
                    {{ number_format($entry->balance_after, 2) }} ₪
                </td>
            </tr>
            @endforeach
        </tbody>
        <tfoot>
            <tr>
                <td colspan="3">الإجمالي</td>
                <td class="text-end credit">{{ number_format($entries->sum('credit'), 2) }} ₪</td>
                <td class="text-end debit">{{ number_format($entries->sum('debit'), 2) }} ₪</td>
                <td class="text-end {{ $balance >= 0 ? 'balance-pos' : 'balance-neg' }}">{{ number_format($balance, 2) }} ₪</td>
            </tr>
        </tfoot>
    </table>
</div>

<div class="footer">
    تم الطباعة: {{ now()->format('d/m/Y H:i') }} — نظام إدارة الموارد البشرية
</div>

<script>window.print();</script>
</body>
</html>
