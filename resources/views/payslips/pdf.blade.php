{{-- resources/views/payslips/pdf.blade.php --}}
<!DOCTYPE html>
<html lang="ar" dir="rtl">
<head>
<meta charset="UTF-8">
<meta http-equiv="Content-Type" content="text/html; charset=utf-8"/>
<title>كشف راتب - {{ $payslip->employee->name }}</title>
<style>
    @font-face {
        font-family: 'DejaVu Sans';
        src: url('{{ storage_path("fonts/DejaVuSans.ttf") }}') format('truetype');
    }
    * { margin: 0; padding: 0; box-sizing: border-box; }
    body {
        font-family: 'DejaVu Sans', Arial, sans-serif;
        font-size: 11px;
        color: #1a1a2e;
        direction: rtl;
        background: #fff;
        padding: 20px;
    }

    /* Header */
    .header {
        background: linear-gradient(135deg, #1e3a5f 0%, #2c5282 100%);
        color: white;
        padding: 20px 24px;
        border-radius: 8px;
        margin-bottom: 20px;
        display: table;
        width: 100%;
    }
    .header-right { display: table-cell; vertical-align: middle; }
    .header-left  { display: table-cell; vertical-align: middle; text-align: left; }
    .company-name { font-size: 18px; font-weight: bold; margin-bottom: 4px; }
    .company-sub  { font-size: 10px; opacity: 0.8; }
    .slip-title   { font-size: 16px; font-weight: bold; margin-bottom: 4px; }
    .slip-period  { font-size: 11px; opacity: 0.9; }

    /* Status badge */
    .status-badge {
        display: inline-block;
        padding: 3px 10px;
        border-radius: 12px;
        font-size: 10px;
        font-weight: bold;
        margin-top: 4px;
    }
    .status-draft  { background: #e2e8f0; color: #4a5568; }
    .status-issued { background: #fef3c7; color: #92400e; }
    .status-paid   { background: #d1fae5; color: #065f46; }

    /* Employee info */
    .emp-box {
        background: #f8fafc;
        border: 1px solid #e2e8f0;
        border-radius: 6px;
        padding: 12px 16px;
        margin-bottom: 16px;
    }
    .emp-grid { display: table; width: 100%; }
    .emp-cell { display: table-cell; width: 25%; padding: 4px 8px; }
    .emp-label { font-size: 9px; color: #64748b; text-transform: uppercase; margin-bottom: 2px; }
    .emp-value { font-weight: bold; font-size: 11px; }

    /* Sections */
    .sections { display: table; width: 100%; margin-bottom: 16px; }
    .col-half { display: table-cell; width: 50%; vertical-align: top; padding: 0 6px; }
    .col-half:first-child { padding-right: 0; }
    .col-half:last-child  { padding-left: 0; }

    .section-title {
        font-size: 11px;
        font-weight: bold;
        padding: 6px 10px;
        border-radius: 4px 4px 0 0;
        margin-bottom: 0;
    }
    .section-title-green { background: #065f46; color: white; }
    .section-title-red   { background: #9b1c1c; color: white; }

    table.items {
        width: 100%;
        border-collapse: collapse;
        border: 1px solid #e2e8f0;
        border-top: none;
        border-radius: 0 0 4px 4px;
    }
    table.items td {
        padding: 6px 10px;
        border-bottom: 1px solid #f1f5f9;
        font-size: 10px;
    }
    table.items tr:last-child td { border-bottom: none; }
    table.items .amount { text-align: left; }
    .total-row td {
        font-weight: bold;
        padding: 8px 10px;
        font-size: 11px;
    }
    .total-green { background: #d1fae5; color: #065f46; }
    .total-red   { background: #fee2e2; color: #9b1c1c; }

    /* Net salary */
    .net-box {
        background: #1e3a5f;
        color: white;
        border-radius: 6px;
        padding: 14px 20px;
        margin-bottom: 16px;
        display: table;
        width: 100%;
    }
    .net-label { display: table-cell; font-size: 13px; font-weight: bold; vertical-align: middle; }
    .net-amount { display: table-cell; text-align: left; font-size: 22px; font-weight: bold; vertical-align: middle; }
    .net-period { font-size: 9px; opacity: 0.7; display: block; }

    /* Notes */
    .notes-box {
        background: #fffbeb;
        border: 1px solid #fcd34d;
        border-radius: 4px;
        padding: 10px 14px;
        font-size: 10px;
        margin-bottom: 16px;
    }

    /* Footer */
    .footer {
        border-top: 1px solid #e2e8f0;
        padding-top: 12px;
        display: table;
        width: 100%;
        color: #94a3b8;
        font-size: 9px;
    }
    .footer-left  { display: table-cell; }
    .footer-right { display: table-cell; text-align: left; }

    /* Signature */
    .sig-row { display: table; width: 100%; margin-top: 30px; }
    .sig-cell { display: table-cell; width: 33%; text-align: center; }
    .sig-line { border-top: 1px solid #94a3b8; margin: 20px auto 4px; width: 80%; }
    .sig-label { font-size: 9px; color: #64748b; }
</style>
</head>
<body>

{{-- Header --}}
<div class="header">
    <div class="header-right">
        <div class="company-name">🏢 اسم الشركة</div>
        <div class="company-sub">نظام الموارد البشرية</div>
    </div>
    <div class="header-left">
        <div class="slip-title">كشف الراتب الشهري</div>
        <div class="slip-period">{{ $payslip->month_name }} {{ $payslip->year }}</div>
        <div>
            <span class="status-badge status-{{ $payslip->status }}">{{ $payslip->status_label }}</span>
        </div>
    </div>
</div>

{{-- Employee Info --}}
<div class="emp-box">
    <div class="emp-grid">
        <div class="emp-cell">
            <div class="emp-label">اسم الموظف</div>
            <div class="emp-value">{{ $payslip->employee->name }}</div>
        </div>
        <div class="emp-cell">
            <div class="emp-label">القسم</div>
            <div class="emp-value">{{ $payslip->employee->department->name ?? '—' }}</div>
        </div>
        <div class="emp-cell">
            <div class="emp-label">نوع الراتب</div>
            <div class="emp-value">{{ $payslip->employee->salary_type ?? 'شهري' }}</div>
        </div>
        <div class="emp-cell">
            <div class="emp-label">الفترة</div>
            <div class="emp-value">{{ $payslip->month_name }} {{ $payslip->year }}</div>
        </div>
    </div>
</div>

{{-- Allowances + Deductions --}}
<div class="sections">

    {{-- Allowances --}}
    <div class="col-half">
        <div class="section-title section-title-green">الإيرادات والبدلات</div>
        <table class="items">
            <tr><td>الراتب الأساسي</td><td class="amount">{{ number_format($payslip->basic_salary, 2) }}</td></tr>
            @if($payslip->housing_allowance)
            <tr><td>بدل سكن</td><td class="amount">{{ number_format($payslip->housing_allowance, 2) }}</td></tr>
            @endif
            @if($payslip->transport_allowance)
            <tr><td>بدل مواصلات</td><td class="amount">{{ number_format($payslip->transport_allowance, 2) }}</td></tr>
            @endif
            @if($payslip->food_allowance)
            <tr><td>بدل طعام</td><td class="amount">{{ number_format($payslip->food_allowance, 2) }}</td></tr>
            @endif
            @if($payslip->other_allowances)
            <tr><td>بدلات أخرى</td><td class="amount">{{ number_format($payslip->other_allowances, 2) }}</td></tr>
            @endif
            @if($payslip->overtime_hours)
            <tr><td>أوفرتايم ({{ $payslip->overtime_hours }}h)</td><td class="amount">{{ number_format($payslip->overtime_hours * $payslip->overtime_rate, 2) }}</td></tr>
            @endif
            @if($payslip->bonus)
            <tr><td>مكافأة</td><td class="amount">{{ number_format($payslip->bonus, 2) }}</td></tr>
            @endif
            <tr class="total-row total-green">
                <td>الإجمالي</td>
                <td class="amount">{{ number_format($payslip->total_allowances, 2) }}</td>
            </tr>
        </table>
    </div>

    {{-- Deductions --}}
    <div class="col-half">
        <div class="section-title section-title-red">الخصومات</div>
        <table class="items">
            @if($payslip->deduction_absence)
            <tr><td>خصم غياب</td><td class="amount">{{ number_format($payslip->deduction_absence, 2) }}</td></tr>
            @endif
            @if($payslip->deduction_late)
            <tr><td>خصم تأخير</td><td class="amount">{{ number_format($payslip->deduction_late, 2) }}</td></tr>
            @endif
            @if($payslip->deduction_insurance)
            <tr><td>تأمين اجتماعي</td><td class="amount">{{ number_format($payslip->deduction_insurance, 2) }}</td></tr>
            @endif
            @if($payslip->deduction_tax)
            <tr><td>ضريبة</td><td class="amount">{{ number_format($payslip->deduction_tax, 2) }}</td></tr>
            @endif
            @if($payslip->deduction_loan)
            <tr><td>قسط سلفة</td><td class="amount">{{ number_format($payslip->deduction_loan, 2) }}</td></tr>
            @endif
            @if($payslip->other_deductions)
            <tr><td>خصومات أخرى</td><td class="amount">{{ number_format($payslip->other_deductions, 2) }}</td></tr>
            @endif
            @if($payslip->total_deductions == 0)
            <tr><td colspan="2" style="text-align:center; color: #94a3b8; padding: 12px;">لا توجد خصومات</td></tr>
            @endif
            <tr class="total-row total-red">
                <td>إجمالي الخصومات</td>
                <td class="amount">{{ number_format($payslip->total_deductions, 2) }}</td>
            </tr>
        </table>
    </div>
</div>

{{-- Net Salary --}}
<div class="net-box">
    <div class="net-label">
        الراتب الصافي
        <span class="net-period">{{ $payslip->month_name }} {{ $payslip->year }}</span>
    </div>
    <div class="net-amount">{{ number_format($payslip->net_salary, 2) }} ₪</div>
</div>

{{-- Notes --}}
@if($payslip->notes)
<div class="notes-box">
    <strong>ملاحظات:</strong> {{ $payslip->notes }}
</div>
@endif

{{-- Signatures --}}
<div class="sig-row">
    <div class="sig-cell">
        <div class="sig-line"></div>
        <div class="sig-label">توقيع الموظف</div>
    </div>
    <div class="sig-cell">
        <div class="sig-line"></div>
        <div class="sig-label">مدير القسم</div>
    </div>
    <div class="sig-cell">
        <div class="sig-line"></div>
        <div class="sig-label">مدير الموارد البشرية</div>
    </div>
</div>

{{-- Footer --}}
<div class="footer">
    <div class="footer-left">
        تم الإنشاء بتاريخ: {{ now()->format('Y/m/d H:i') }}
        @if($payslip->creator) — بواسطة: {{ $payslip->creator->name }} @endif
    </div>
    <div class="footer-right">نظام الموارد البشرية — وثيقة سرية</div>
</div>

</body>
</html>
