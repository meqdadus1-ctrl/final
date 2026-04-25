<!DOCTYPE html>
<html lang="ar" dir="rtl">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>استمارة راتب — {{ $salary->employee?->name }}</title>
    <style>
        * { margin: 0; padding: 0; box-sizing: border-box; }

        html, body {
            direction: rtl;
            font-family: 'Courier New', Courier, monospace;
            font-size: 12px;
            background: #fff;
            color: #000;
        }

       body {
    width: 70mm;   /* صغّر العرض */
    margin: 0;
    padding: 2mm;
}

        .center  { text-align: center; }
        .bold    { font-weight: bold; }
        .large   { font-size: 15px; }
        .small   { font-size: 10px; }
        .gray    { color: #0b0909; }

        .divider  { border-top: 1px dashed #000; margin: 5px 0; }
        .divider2 { border-top: 2px solid #000;  margin: 5px 0; }

        /* صف بيانات: label يسار، value يمين */
        .row {
            display: table;
            width: 100%;
            padding: 2px 0;
            font-size: 11px;
        }
        .row .lbl {
            display: table-cell;
            color: #000000;
            width: 70px;
        }
        .row .val {
            display: table-cell;
            font-weight: bold;
            text-align: right;
        }

        /* عنوان قسم */
        .section-title {
            font-weight: bold;
            font-size: 11px;
            background: #000;
            color: #fff;
            padding: 2px 5px;
            margin: 4px 0 2px;
        }

        /* صف بند (بيان + مبلغ) */
        .item-row {
            display: table;
            width: 100%;
            padding: 3px 4px;
            font-size: 11px;
            border-bottom: 1px dotted #ccc;
        }
        .item-row .item-name {
            display: table-cell;
            text-align: right;
        }
        .item-row .item-amt {
            display: table-cell;
            text-align: left;
            font-weight: bold;
            white-space: nowrap;
            width: 1px;
            padding-right: 4px;
        }
        .item-row .item-note {
            font-size: 9px;
            color: #000000;
        }

        /* صف الإجمالي */
        .total-row {
            display: table;
            width: 100%;
            padding: 3px 4px;
            font-weight: bold;
            font-size: 12px;
            border-top: 1px solid #000;
            background: #f0f0f0;
        }
        .total-row .item-name {
            display: table-cell;
            text-align: right;
        }
        .total-row .item-amt {
            display: table-cell;
            text-align: left;
            width: 1px;
            padding-right: 4px;
        }

        /* صافي الراتب */
        .net-box {
            border: 2px solid #000;
            padding: 6px 4px;
            text-align: center;
            margin: 5px 0;
            border-radius: 3px;
        }
        .net-box .net-lbl { font-size: 11px; }
        .net-box .net-amt { font-size: 22px; font-weight: bold; margin-top: 2px; }
        .net-box .net-method { font-size: 10px; margin-top: 3px; color: #333; }

        /* التوقيعات */
        .sig-wrap {
            display: table;
            width: 100%;
            margin-top: 8px;
        }
        .sig-cell {
            display: table-cell;
            text-align: center;
            font-size: 10px;
            padding: 0 3px;
        }
        .sig-line {
            border-top: 1px solid #000;
            margin: 18px 2px 3px;
        }

        .no-print { display: block; }

 @media print {
    body {
        width: 70mm;
        margin: 0;
        padding: 2mm;
    }

    @page {
        margin: 0;
        size: 80mm auto;
    }
}
    </style>
</head>
<body>

    <div class="no-print" style="text-align:center; margin-bottom:8px;">
        <button onclick="window.print()"
            style="padding:5px 16px; font-size:12px; cursor:pointer;
                   background:#1e3a5f; color:#fff; border:none; border-radius:4px;">
            🖨️ طباعة
        </button>
        <button onclick="window.close()"
            style="padding:5px 12px; font-size:12px; cursor:pointer;
                   background:#888; color:#fff; border:none; border-radius:4px; margin-right:4px;">
            إغلاق
        </button>
    </div>

    {{-- العنوان --}}
    <div class="center bold large">استمارة راتب موظف</div>
    <div class="divider2"></div>

    {{-- بيانات الموظف --}}
    <div class="row"><span class="lbl">الاسم:</span><span class="val">{{ $salary->employee?->name }}</span></div>
    @if($salary->employee?->job_title)
    <div class="row"><span class="lbl">الوظيفة:</span><span class="val">{{ $salary->employee->job_title }}</span></div>
    @endif
    @if($salary->employee?->department?->name)
    <div class="row"><span class="lbl">القسم:</span><span class="val">{{ $salary->employee->department->name }}</span></div>
    @endif
    @if($salary->employee?->national_id)
    <div class="row"><span class="lbl">رقم الهوية:</span><span class="val">{{ $salary->employee->national_id }}</span></div>
    @endif
    <div class="row"><span class="lbl">الفترة:</span><span class="val">{{ $salary->week_start?->format('d/m/Y') }} — {{ $salary->week_end?->format('d/m/Y') }}</span></div>
    <div class="row"><span class="lbl">الكود:</span><span class="val">{{ $salary->fiscal_period }}</span></div>
    <div class="row"><span class="lbl">تاريخ الإصدار:</span><span class="val">{{ now()->format('d/m/Y') }}</span></div>

    <div class="divider"></div>

    @php
        $salaryA = round($salary->hours_worked * ($salary->hourly_rate ?? 0), 2);
        $salaryB = round($salary->overtime_hours * ($salary->hourly_rate ?? 0) * ($salary->employee?->overtime_rate ?? 1.5), 2);
    @endphp

    {{-- ===== المستحقات ===== --}}
    <div class="section-title">▼ المستحقات</div>

    <div class="item-row">
        <span class="item-name">
            راتب ساعات العمل
            <div class="item-note">({{ $salary->hours_worked }} س × {{ number_format($salary->hourly_rate ?? 0, 2) }} ₪)</div>
        </span>
        <span class="item-amt">{{ number_format($salaryA, 2) }} ₪</span>
    </div>

    @if($salaryB > 0)
    <div class="item-row">
        <span class="item-name">
            أجر الأوفرتايم
            <div class="item-note">({{ $salary->overtime_hours }} س)</div>
        </span>
        <span class="item-amt">{{ number_format($salaryB, 2) }} ₪</span>
    </div>
    @endif

    @if($salary->manual_additions > 0)
    <div class="item-row">
        <span class="item-name">مكافآت / إضافات يدوية</span>
        <span class="item-amt">{{ number_format($salary->manual_additions, 2) }} ₪</span>
    </div>
    @endif

    <div class="total-row">
        <span class="item-name">إجمالي المستحقات</span>
        <span class="item-amt">{{ number_format($salary->gross_salary, 2) }} ₪</span>
    </div>

    {{-- ===== الخصومات ===== --}}
    <div class="section-title">▼ الخصومات</div>

    @if($salary->late_deduction > 0)
    <div class="item-row">
        <span class="item-name">
            خصم التأخير
            @if($salary->late_minutes > 0)<div class="item-note">({{ $salary->late_minutes }} دقيقة)</div>@endif
        </span>
        <span class="item-amt">{{ number_format($salary->late_deduction, 2) }} ₪</span>
    </div>
    @endif

    @if($salary->absence_deduction > 0)
    <div class="item-row">
        <span class="item-name">خصم الغياب</span>
        <span class="item-amt">{{ number_format($salary->absence_deduction, 2) }} ₪</span>
    </div>
    @endif

    @if($salary->manual_deductions > 0)
    <div class="item-row">
        <span class="item-name">خصومات يدوية</span>
        <span class="item-amt">{{ number_format($salary->manual_deductions, 2) }} ₪</span>
    </div>
    @endif

    @if($salary->loan_deduction_amount > 0)
    <div class="item-row">
        <span class="item-name">قسط السلفة</span>
        <span class="item-amt">{{ number_format($salary->loan_deduction_amount, 2) }} ₪</span>
    </div>
    @endif

    @php $totalDed = $salary->total_deductions + $salary->loan_deduction_amount; @endphp
    @if($totalDed > 0)
    <div class="total-row">
        <span class="item-name">إجمالي الخصومات</span>
        <span class="item-amt">{{ number_format($totalDed, 2) }} ₪</span>
    </div>
    @endif

    <div class="divider"></div>

    {{-- صافي الراتب --}}
    <div class="net-box">
        <div class="net-lbl">صافي الراتب</div>
        <div class="net-amt">{{ number_format($salary->net_salary, 2) }} ₪</div>
        <div class="net-method">
            طريقة الدفع:
            @if($salary->payment_method === 'bank') تحويل بنكي 🏦
            @elseif($salary->payment_method === 'cash') نقدي 💵
            @endif
        </div>
    </div>

    {{-- الرصيد --}}
    @if($salary->balance_after !== null)
    <div class="row"><span class="lbl small">رصيد قبل:</span><span class="val small">{{ number_format($salary->balance_before ?? 0, 2) }} ₪</span></div>
    <div class="row"><span class="lbl small">رصيد بعد:</span><span class="val small bold">{{ number_format($salary->balance_after, 2) }} ₪</span></div>
    <div class="divider"></div>
    @endif

    {{-- ملاحظات --}}
    @if($salary->notes)
    <div style="font-size:10px; margin: 3px 0;">
        <span class="bold">ملاحظات: </span>{{ $salary->notes }}
    </div>
    <div class="divider"></div>
    @endif

    {{-- التوقيعات --}}
    <div class="sig-wrap">
        <div class="sig-cell"><div class="sig-line"></div>توقيع المستلم</div>
        <div class="sig-cell"><div class="sig-line"></div>توقيع المحاسب</div>
        <div class="sig-cell"><div class="sig-line"></div>توقيع الإدارة</div>
    </div>

    <div class="divider" style="margin-top:8px;"></div>
    <div class="center small gray" style="margin-top:3px;">
        {{ config('app.name') }} — {{ now()->format('d/m/Y H:i') }}
    </div>

    <script>
        document.documentElement.setAttribute('dir', 'rtl');
        document.body.setAttribute('dir', 'rtl');
    </script>
</body>
</html>
