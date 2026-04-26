<!DOCTYPE html>
<html lang="ar" dir="rtl">
<head>
<meta charset="UTF-8">
<title>تقرير الموظفين</title>
<style>
* { box-sizing: border-box; margin: 0; padding: 0; }
body { font-family: 'Dejavu Sans', Arial, sans-serif; font-size: 11px; color: #1a1a1a; direction: rtl; background: #fff; }

.employee-page { padding: 14mm 12mm; page-break-after: always; }
.employee-page:last-child { page-break-after: avoid; }

/* رأس الموظف */
.emp-header { background: #1e3a5f; color: #fff; padding: 10px 14px; border-radius: 6px; margin-bottom: 12px; }
.emp-header h3 { font-size: 14px; margin-bottom: 3px; }
.emp-header .meta { font-size: 9px; opacity: 0.8; }
.emp-header .balance-box { text-align: left; }
.emp-header .balance-box .lbl { font-size: 9px; opacity: 0.7; }
.emp-header .balance-box .val { font-size: 16px; font-weight: 700; }

/* قسم التقرير */
.section { margin-bottom: 12px; border: 1px solid #ddd; border-radius: 5px; overflow: hidden; }
.section-header { padding: 5px 10px; color: #fff; font-size: 11px; font-weight: 700; }
.section-body { padding: 8px; }

/* الملخص */
.stats-row { display: table; width: 100%; border-collapse: collapse; }
.stat-cell { display: table-cell; text-align: center; padding: 4px 6px; border-left: 1px solid #eee; }
.stat-cell:last-child { border-left: none; }
.stat-val { font-size: 14px; font-weight: 700; }
.stat-lbl { font-size: 8px; color: #666; }

/* جداول */
table { width: 100%; border-collapse: collapse; font-size: 10px; margin-top: 6px; }
th { background: #f1f3f5; padding: 4px 6px; font-weight: 700; border: 1px solid #ddd; font-size: 9px; }
td { padding: 3px 6px; border: 1px solid #eee; }
.tfoot-row td { background: #f8f9fa; font-weight: 700; border-top: 2px solid #ddd; }
tr:nth-child(even) td { background: #fafafa; }

/* badges */
.badge { padding: 1px 6px; border-radius: 10px; font-size: 8px; font-weight: 700; display: inline-block; }
.badge-present  { background: #d1fae5; color: #065f46; }
.badge-absent   { background: #fee2e2; color: #991b1b; }
.badge-leave    { background: #fef3c7; color: #92400e; }
.badge-pending  { background: #fef3c7; color: #92400e; }
.badge-approved { background: #d1fae5; color: #065f46; }
.badge-rejected { background: #fee2e2; color: #991b1b; }
.badge-active   { background: #dbeafe; color: #1e40af; }
.badge-completed{ background: #d1fae5; color: #065f46; }
.badge-green    { background: #d1fae5; color: #065f46; }
.badge-red      { background: #fee2e2; color: #991b1b; }

/* توقيع */
.signature-row { margin-top: 20px; padding-top: 8px; border-top: 1px solid #ddd; }
.sig-box { display: inline-block; width: 30%; text-align: center; font-size: 9px; color: #888; }
.sig-line { border-top: 1px dashed #bbb; margin-top: 28px; padding-top: 4px; }

/* colors */
.text-success { color: #065f46; }
.text-danger  { color: #991b1b; }
.text-primary { color: #1e40af; }
.text-warning { color: #92400e; }
.text-info    { color: #0e7490; }
.text-muted   { color: #666; }
.text-center  { text-align: center; }
.text-end     { text-align: left; }
.fw-bold      { font-weight: 700; }
</style>
</head>
<body>

@foreach($data as $empData)
@php $employee = $empData['employee']; @endphp

<div class="employee-page">

    {{-- رأس --}}
    <div class="emp-header">
        <table style="border:none; background:transparent;">
            <tr>
                <td style="border:none; background:transparent; width:70%">
                    <div style="font-size:14px; font-weight:700; color:#fff">{{ $employee->name }}</div>
                    <div class="meta" style="color:#c9d4e0; margin-top:3px">
                        {{ $employee->department->name ?? '—' }} &nbsp;•&nbsp;
                        {{ $employee->job_title ?? '—' }} &nbsp;•&nbsp;
                        {{ $employee->salary_type === 'hourly' ? 'راتب بالساعة' : 'راتب ثابت' }}
                    </div>
                    <div class="meta" style="color:#c9d4e0; margin-top:2px">
                        التقرير من {{ \Carbon\Carbon::parse($from)->format('d/m/Y') }}
                        إلى {{ \Carbon\Carbon::parse($to)->format('d/m/Y') }}
                    </div>
                </td>
                <td style="border:none; background:transparent; text-align:left; vertical-align:top">
                    <div style="font-size:8px; color:#c9d4e0">الرصيد الحالي</div>
                    <div style="font-size:16px; font-weight:700; color:{{ $empData['ledger_balance'] >= 0 ? '#6ee7b7' : '#fca5a5' }}">
                        {{ number_format($empData['ledger_balance'],2) }} ₪
                    </div>
                    <div style="font-size:8px; color:#c9d4e0">{{ now()->format('d/m/Y') }}</div>
                </td>
            </tr>
        </table>
    </div>

    {{-- ① الحضور --}}
    @if(isset($empData['attendance']))
    @php $att = $empData['attendance']; @endphp
    <div class="section">
        <div class="section-header" style="background:#06d6a0">① الحضور والانصراف</div>
        <div class="section-body">
            <div class="stats-row">
                <div class="stat-cell"><div class="stat-val text-success">{{ $att['present_days'] }}</div><div class="stat-lbl">أيام حضور</div></div>
                <div class="stat-cell"><div class="stat-val text-danger">{{ $att['absent_days'] }}</div><div class="stat-lbl">أيام غياب</div></div>
                <div class="stat-cell"><div class="stat-val text-warning">{{ $att['leave_days'] }}</div><div class="stat-lbl">أيام إجازة</div></div>
                <div class="stat-cell"><div class="stat-val text-primary">{{ $att['total_hours'] }}</div><div class="stat-lbl">إجمالي الساعات</div></div>
                <div class="stat-cell"><div class="stat-val text-info">{{ $att['overtime_hours'] }}</div><div class="stat-lbl">أوفرتايم (س)</div></div>
                <div class="stat-cell"><div class="stat-val text-muted">{{ $att['late_count'] }}</div><div class="stat-lbl">مرات التأخر</div></div>
            </div>
            @if($att['records']->isNotEmpty())
            <table>
                <thead><tr>
                    <th>التاريخ</th><th>اليوم</th><th>الحالة</th>
                    <th>دخول</th><th>خروج</th>
                    <th class="text-center">ساعات</th><th class="text-center">أوفرتايم</th>
                </tr></thead>
                <tbody>
                @foreach($att['records'] as $a)
                @php
                    $stClass = match($a->status) { 'present'=>'badge-present','absent'=>'badge-absent','leave','on_leave'=>'badge-leave', default=>'badge-pending' };
                    $stLabel = match($a->status) { 'present'=>'حاضر','absent'=>'غائب','leave','on_leave'=>'إجازة', default=>$a->status };
                @endphp
                <tr>
                    <td>{{ $a->date?->format('d/m/Y') }}</td>
                    <td class="text-muted">{{ $a->date?->locale('ar')->dayName }}</td>
                    <td><span class="badge {{ $stClass }}">{{ $stLabel }}</span></td>
                    <td>{{ $a->check_in ? \Carbon\Carbon::parse($a->check_in)->format('H:i') : '—' }}</td>
                    <td>{{ $a->check_out ? \Carbon\Carbon::parse($a->check_out)->format('H:i') : '—' }}</td>
                    <td class="text-center fw-bold">{{ $a->work_hours ? number_format($a->work_hours,1) : '—' }}</td>
                    <td class="text-center text-info">{{ $a->overtime_hours > 0 ? number_format($a->overtime_hours,1) : '—' }}</td>
                </tr>
                @endforeach
                </tbody>
                <tfoot><tr class="tfoot-row">
                    <td colspan="5" class="fw-bold text-end">المجموع</td>
                    <td class="text-center fw-bold">{{ $att['total_hours'] }} س</td>
                    <td class="text-center fw-bold text-info">{{ $att['overtime_hours'] }} س</td>
                </tr></tfoot>
            </table>
            @endif
        </div>
    </div>
    @endif

    {{-- ② الراتب --}}
    @if(isset($empData['salary']))
    @php $sal = $empData['salary']; @endphp
    <div class="section">
        <div class="section-header" style="background:#4361ee">② كشف الراتب</div>
        <div class="section-body">
            <div class="stats-row">
                <div class="stat-cell"><div class="stat-val text-primary">{{ number_format($sal['total_gross'],2) }}</div><div class="stat-lbl">الإجمالي ₪</div></div>
                <div class="stat-cell"><div class="stat-val text-success">{{ number_format($sal['total_net'],2) }}</div><div class="stat-lbl">الصافي ₪</div></div>
                <div class="stat-cell"><div class="stat-val text-danger">{{ number_format($sal['total_deductions'],2) }}</div><div class="stat-lbl">الخصومات ₪</div></div>
                <div class="stat-cell"><div class="stat-val text-warning">{{ number_format($sal['total_late_ded'],2) }}</div><div class="stat-lbl">خصم تأخير ₪</div></div>
                <div class="stat-cell"><div class="stat-val text-info">{{ $sal['total_hours'] }}</div><div class="stat-lbl">إجمالي الساعات</div></div>
            </div>
            @if($sal['payments']->isNotEmpty())
            <table>
                <thead><tr>
                    <th>الفترة</th><th class="text-center">ساعات</th><th class="text-center">أوفرتايم</th>
                    <th class="text-end">الإجمالي</th><th class="text-end">خصم تأخير</th>
                    <th class="text-end">خصم سلفة</th><th class="text-end">إجمالي الخصم</th>
                    <th class="text-end fw-bold">الصافي</th>
                </tr></thead>
                <tbody>
                @foreach($sal['payments'] as $pay)
                <tr>
                    <td>{{ $pay->week_start?->format('d/m') }} — {{ $pay->week_end?->format('d/m/Y') }}</td>
                    <td class="text-center">{{ number_format($pay->hours_worked,1) }}</td>
                    <td class="text-center text-info">{{ $pay->overtime_hours > 0 ? number_format($pay->overtime_hours,1) : '—' }}</td>
                    <td class="text-end">{{ number_format($pay->gross_salary,2) }}</td>
                    <td class="text-end text-danger">{{ $pay->late_deduction > 0 ? number_format($pay->late_deduction,2) : '—' }}</td>
                    <td class="text-end text-danger">{{ $pay->loan_deduction_amount > 0 ? number_format($pay->loan_deduction_amount,2) : '—' }}</td>
                    <td class="text-end text-danger">{{ number_format($pay->total_deductions,2) }}</td>
                    <td class="text-end fw-bold text-success">{{ number_format($pay->net_salary,2) }}</td>
                </tr>
                @endforeach
                </tbody>
                <tfoot><tr class="tfoot-row">
                    <td colspan="3" class="fw-bold text-end">المجموع</td>
                    <td class="text-end fw-bold">{{ number_format($sal['total_gross'],2) }} ₪</td>
                    <td class="text-end fw-bold text-danger">{{ number_format($sal['total_late_ded'],2) }} ₪</td>
                    <td class="text-end fw-bold text-danger">{{ number_format($sal['total_loan_ded'],2) }} ₪</td>
                    <td class="text-end fw-bold text-danger">{{ number_format($sal['total_deductions'],2) }} ₪</td>
                    <td class="text-end fw-bold text-success">{{ number_format($sal['total_net'],2) }} ₪</td>
                </tr></tfoot>
            </table>
            @endif
        </div>
    </div>
    @endif

    {{-- ③ السلف --}}
    @if(isset($empData['loans']))
    @php $loans = $empData['loans']; @endphp
    <div class="section">
        <div class="section-header" style="background:#f72585">③ السلف</div>
        <div class="section-body">
            <div class="stats-row">
                <div class="stat-cell"><div class="stat-val text-danger">{{ number_format($loans['total_borrowed'],2) }}</div><div class="stat-lbl">إجمالي ₪</div></div>
                <div class="stat-cell"><div class="stat-val text-success">{{ number_format($loans['total_paid'],2) }}</div><div class="stat-lbl">المدفوع ₪</div></div>
                <div class="stat-cell"><div class="stat-val text-warning">{{ number_format($loans['total_remaining'],2) }}</div><div class="stat-lbl">المتبقي ₪</div></div>
                <div class="stat-cell"><div class="stat-val text-primary">{{ $loans['active_count'] }}</div><div class="stat-lbl">سلف نشطة</div></div>
            </div>
            @if($loans['records']->isNotEmpty())
            <table>
                <thead><tr>
                    <th>تاريخ البدء</th><th>الوصف</th><th class="text-center">الحالة</th>
                    <th class="text-end">الكلي ₪</th><th class="text-center">الأقساط</th>
                    <th class="text-end">المدفوع ₪</th><th class="text-end">المتبقي ₪</th>
                </tr></thead>
                <tbody>
                @foreach($loans['records'] as $loan)
                @php $rem = max(0,$loan->total_amount-$loan->amount_paid); @endphp
                <tr>
                    <td>{{ $loan->start_date?->format('d/m/Y') }}</td>
                    <td>{{ $loan->description ?? '—' }}</td>
                    <td class="text-center"><span class="badge badge-{{ $loan->status }}">{{ $loan->status_label }}</span></td>
                    <td class="text-end fw-bold">{{ number_format($loan->total_amount,2) }}</td>
                    <td class="text-center">{{ $loan->installments_paid }}/{{ $loan->installments_total }}</td>
                    <td class="text-end text-success fw-bold">{{ number_format($loan->amount_paid,2) }}</td>
                    <td class="text-end {{ $rem>0?'text-danger fw-bold':'text-muted' }}">
                        {{ $rem>0 ? number_format($rem,2) : 'مكتمل' }}
                    </td>
                </tr>
                @endforeach
                </tbody>
                <tfoot><tr class="tfoot-row">
                    <td colspan="3" class="text-end fw-bold">المجموع</td>
                    <td class="text-end fw-bold">{{ number_format($loans['total_borrowed'],2) }} ₪</td>
                    <td></td>
                    <td class="text-end fw-bold text-success">{{ number_format($loans['total_paid'],2) }} ₪</td>
                    <td class="text-end fw-bold text-danger">{{ number_format($loans['total_remaining'],2) }} ₪</td>
                </tr></tfoot>
            </table>
            @endif
        </div>
    </div>
    @endif

    {{-- ④ الإجازات --}}
    @if(isset($empData['leaves']))
    @php $leaves = $empData['leaves']; @endphp
    <div class="section">
        <div class="section-header" style="background:#fb8500">④ الإجازات</div>
        <div class="section-body">
            <div class="stats-row">
                <div class="stat-cell"><div class="stat-val text-success">{{ $leaves['approved'] }}</div><div class="stat-lbl">موافق عليها</div></div>
                <div class="stat-cell"><div class="stat-val text-warning">{{ $leaves['pending'] }}</div><div class="stat-lbl">قيد المراجعة</div></div>
                <div class="stat-cell"><div class="stat-val text-danger">{{ $leaves['rejected'] }}</div><div class="stat-lbl">مرفوضة</div></div>
                <div class="stat-cell"><div class="stat-val text-primary">{{ $leaves['total_days'] }}</div><div class="stat-lbl">أيام معتمدة</div></div>
            </div>
            @if($leaves['records']->isNotEmpty())
            <table>
                <thead><tr>
                    <th>نوع الإجازة</th><th>من</th><th>إلى</th>
                    <th class="text-center">الأيام</th><th class="text-center">الحالة</th><th>السبب</th>
                </tr></thead>
                <tbody>
                @foreach($leaves['records'] as $leave)
                <tr>
                    <td>{{ $leave->leaveType->name ?? '—' }}</td>
                    <td>{{ $leave->start_date?->format('d/m/Y') }}</td>
                    <td>{{ $leave->end_date?->format('d/m/Y') }}</td>
                    <td class="text-center fw-bold">{{ $leave->total_days }}</td>
                    <td class="text-center">
                        <span class="badge badge-{{ $leave->status }}">
                            {{ match($leave->status){ 'approved'=>'موافق','pending'=>'قيد المراجعة','rejected'=>'مرفوض',default=>$leave->status } }}
                        </span>
                    </td>
                    <td class="text-muted">{{ $leave->reason ?? '—' }}</td>
                </tr>
                @endforeach
                </tbody>
            </table>
            @endif

            @if($leaves['balances']->isNotEmpty())
            <div style="margin-top:6px">
                <div style="font-size:9px; font-weight:700; color:#666; margin-bottom:4px">
                    رصيد الإجازات {{ \Carbon\Carbon::parse($from)->year }}
                </div>
                <table style="width:auto">
                    <thead><tr>
                        <th>نوع الإجازة</th>
                        <th class="text-center">مستحق</th>
                        <th class="text-center">مستخدم</th>
                        <th class="text-center">متبقي</th>
                    </tr></thead>
                    <tbody>
                    @foreach($leaves['balances'] as $bal)
                    <tr>
                        <td>{{ $bal->leaveType->name ?? '—' }}</td>
                        <td class="text-center">{{ $bal->entitled_days }}</td>
                        <td class="text-center text-danger">{{ $bal->used_days }}</td>
                        <td class="text-center text-success fw-bold">{{ $bal->remaining_days }}</td>
                    </tr>
                    @endforeach
                    </tbody>
                </table>
            </div>
            @endif
        </div>
    </div>
    @endif

    {{-- توقيع --}}
    <div class="signature-row">
        <div class="sig-box"><div class="sig-line">توقيع الموظف</div></div>
        <div class="sig-box"><div class="sig-line">توقيع المدير</div></div>
        <div class="sig-box"><div class="sig-line">الختم الرسمي</div></div>
    </div>

</div>
@endforeach

</body>
</html>
