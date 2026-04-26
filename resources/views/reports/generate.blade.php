<x-app-layout>
<x-slot name="title">تقرير الموظفين</x-slot>

{{-- شريط أدوات الطباعة --}}
<div class="d-flex justify-content-between align-items-center mb-3 no-print" dir="rtl">
    <div>
        <h5 class="mb-0 fw-bold"><i class="fas fa-file-alt me-2 text-primary"></i>تقرير الموظفين</h5>
        <small class="text-muted">
            الفترة: {{ \Carbon\Carbon::parse($from)->format('d/m/Y') }} —
            {{ \Carbon\Carbon::parse($to)->format('d/m/Y') }} •
            {{ count($data) }} موظف
        </small>
    </div>
    <div class="d-flex gap-2">
        <button onclick="window.print()" class="btn btn-success btn-sm">
            <i class="fas fa-print me-1"></i>طباعة
        </button>
        <a href="{{ request()->fullUrlWithQuery(['_format'=>'pdf']) }}"
           href="{{ route('reports.pdf', request()->query()) }}" target="_blank"
           class="btn btn-danger btn-sm">
            <i class="fas fa-file-pdf me-1"></i>PDF
        </a>
        <a href="{{ route('reports.index') }}" class="btn btn-outline-secondary btn-sm">
            <i class="fas fa-arrow-right me-1"></i>العودة
        </a>
    </div>
</div>

<style>
@media print {
    .no-print, .sidebar, .topbar, .main-content > .topbar { display: none !important; }
    .main-content { margin: 0 !important; padding: 0 !important; }
    body { background: #fff !important; }
    .employee-page { page-break-after: always; padding: 10mm; }
    .employee-page:last-child { page-break-after: avoid; }
}
.employee-page { background: #fff; border-radius: 10px; margin-bottom: 24px; padding: 24px; box-shadow: 0 1px 8px rgba(0,0,0,0.07); }
.report-section { border-radius: 8px; overflow: hidden; margin-bottom: 16px; }
.report-section-header { padding: 8px 14px; font-weight: 700; font-size: 13px; color: #fff; display: flex; align-items: center; gap: 8px; }
.emp-header { background: linear-gradient(135deg, #1e3a5f, #2d5a8e); color: #fff; border-radius: 8px; padding: 16px 20px; margin-bottom: 16px; }
.stat-box { background: #f8f9fa; border-radius: 6px; padding: 8px 12px; text-align: center; }
.stat-box .val { font-size: 18px; font-weight: 700; }
.stat-box .lbl { font-size: 10px; color: #666; }
.table-report { font-size: 12px; margin-bottom: 0; }
.table-report th { background: #f1f3f5; font-weight: 600; font-size: 11px; padding: 6px 8px; }
.table-report td { padding: 5px 8px; vertical-align: middle; }
.summary-row { background: #f8f9fa; font-weight: 700; }
.badge-status-present  { background: #d1fae5; color: #065f46; }
.badge-status-absent   { background: #fee2e2; color: #991b1b; }
.badge-status-leave    { background: #fef3c7; color: #92400e; }
.badge-status-pending  { background: #fef3c7; color: #92400e; }
.badge-status-approved { background: #d1fae5; color: #065f46; }
.badge-status-rejected { background: #fee2e2; color: #991b1b; }
.badge-status-active   { background: #dbeafe; color: #1e40af; }
.badge-status-completed{ background: #d1fae5; color: #065f46; }
</style>

@forelse($data as $empData)
@php $employee = $empData['employee']; @endphp

<div class="employee-page" dir="rtl">

    {{-- رأس الموظف --}}
    <div class="emp-header d-flex justify-content-between align-items-center mb-3">
        <div>
            <h5 class="mb-1 fw-bold">{{ $employee->name }}</h5>
            <div class="small opacity-75">
                {{ $employee->department->name ?? '—' }} •
                {{ $employee->job_title ?? '—' }} •
                {{ $employee->salary_type === 'hourly' ? 'راتب بالساعة' : 'راتب ثابت' }}
            </div>
            <div class="small opacity-75 mt-1">
                <i class="fas fa-calendar me-1"></i>
                التقرير من {{ \Carbon\Carbon::parse($from)->format('d/m/Y') }}
                إلى {{ \Carbon\Carbon::parse($to)->format('d/m/Y') }}
            </div>
        </div>
        <div class="text-end">
            <div class="small opacity-75">الرصيد الحالي</div>
            <div class="fs-4 fw-bold {{ $empData['ledger_balance'] >= 0 ? 'text-success' : 'text-warning' }}">
                {{ number_format($empData['ledger_balance'], 2) }} ₪
            </div>
            <div class="small opacity-75">{{ now()->format('d/m/Y') }}</div>
        </div>
    </div>

    {{-- ①  الحضور والانصراف --}}
    @if(isset($empData['attendance']))
    @php $att = $empData['attendance']; @endphp
    <div class="report-section border">
        <div class="report-section-header" style="background:#06d6a0">
            <i class="fas fa-clock"></i> ① الحضور والانصراف
        </div>

        {{-- ملخص سريع --}}
        <div class="row g-2 p-2">
            <div class="col"><div class="stat-box"><div class="val text-success">{{ $att['present_days'] }}</div><div class="lbl">أيام حضور</div></div></div>
            <div class="col"><div class="stat-box"><div class="val text-danger">{{ $att['absent_days'] }}</div><div class="lbl">أيام غياب</div></div></div>
            <div class="col"><div class="stat-box"><div class="val text-warning">{{ $att['leave_days'] }}</div><div class="lbl">أيام إجازة</div></div></div>
            <div class="col"><div class="stat-box"><div class="val text-primary">{{ $att['total_hours'] }}</div><div class="lbl">إجمالي الساعات</div></div></div>
            <div class="col"><div class="stat-box"><div class="val text-info">{{ $att['overtime_hours'] }}</div><div class="lbl">أوفرتايم (س)</div></div></div>
            <div class="col"><div class="stat-box"><div class="val text-secondary">{{ $att['late_count'] }}</div><div class="lbl">مرات التأخر</div></div></div>
        </div>

        @if($att['records']->isNotEmpty())
        <div class="table-responsive">
            <table class="table table-hover table-bordered table-report">
                <thead>
                    <tr>
                        <th>التاريخ</th>
                        <th>اليوم</th>
                        <th>الحالة</th>
                        <th>دخول</th>
                        <th>خروج</th>
                        <th class="text-center">ساعات العمل</th>
                        <th class="text-center">أوفرتايم</th>
                        <th>ملاحظة</th>
                    </tr>
                </thead>
                <tbody>
                    @foreach($att['records'] as $a)
                    <tr>
                        <td>{{ $a->date?->format('d/m/Y') }}</td>
                        <td class="text-muted">{{ $a->date?->locale('ar')->dayName }}</td>
                        <td>
                            @php
                                $stClass = match($a->status) {
                                    'present'  => 'badge-status-present',
                                    'absent'   => 'badge-status-absent',
                                    'leave','on_leave' => 'badge-status-leave',
                                    default    => 'bg-light text-dark',
                                };
                                $stLabel = match($a->status) {
                                    'present'  => 'حاضر',
                                    'absent'   => 'غائب',
                                    'leave','on_leave' => 'إجازة',
                                    default    => $a->status,
                                };
                            @endphp
                            <span class="badge {{ $stClass }} rounded-pill px-2">{{ $stLabel }}</span>
                        </td>
                        <td>{{ $a->check_in ? \Carbon\Carbon::parse($a->check_in)->format('H:i') : '—' }}</td>
                        <td>{{ $a->check_out ? \Carbon\Carbon::parse($a->check_out)->format('H:i') : '—' }}</td>
                        <td class="text-center fw-semibold">{{ $a->work_hours ? number_format($a->work_hours,1) : '—' }}</td>
                        <td class="text-center {{ $a->overtime_hours > 0 ? 'text-info fw-semibold' : 'text-muted' }}">
                            {{ $a->overtime_hours > 0 ? number_format($a->overtime_hours,1) : '—' }}
                        </td>
                        <td class="text-muted small">{{ $a->leave_reason ?? '' }}</td>
                    </tr>
                    @endforeach
                </tbody>
                <tfoot>
                    <tr class="summary-row">
                        <td colspan="5" class="text-end fw-bold">المجموع</td>
                        <td class="text-center fw-bold">{{ $att['total_hours'] }} س</td>
                        <td class="text-center fw-bold text-info">{{ $att['overtime_hours'] }} س</td>
                        <td></td>
                    </tr>
                </tfoot>
            </table>
        </div>
        @else
        <div class="text-center text-muted py-3 small">لا توجد سجلات حضور في هذه الفترة</div>
        @endif
    </div>
    @endif

    {{-- ② كشف الراتب --}}
    @if(isset($empData['salary']))
    @php $sal = $empData['salary']; @endphp
    <div class="report-section border">
        <div class="report-section-header" style="background:#4361ee">
            <i class="fas fa-money-bill-wave"></i> ② كشف الراتب
        </div>

        <div class="row g-2 p-2">
            <div class="col"><div class="stat-box"><div class="val text-primary">{{ number_format($sal['total_gross'],2) }}</div><div class="lbl">إجمالي الراتب ₪</div></div></div>
            <div class="col"><div class="stat-box"><div class="val text-success">{{ number_format($sal['total_net'],2) }}</div><div class="lbl">الصافي ₪</div></div></div>
            <div class="col"><div class="stat-box"><div class="val text-danger">{{ number_format($sal['total_deductions'],2) }}</div><div class="lbl">إجمالي الخصومات ₪</div></div></div>
            <div class="col"><div class="stat-box"><div class="val text-warning">{{ number_format($sal['total_late_ded'],2) }}</div><div class="lbl">خصم تأخير ₪</div></div></div>
            <div class="col"><div class="stat-box"><div class="val text-info">{{ $sal['total_hours'] }}</div><div class="lbl">إجمالي الساعات</div></div></div>
        </div>

        @if($sal['payments']->isNotEmpty())
        <div class="table-responsive">
            <table class="table table-hover table-bordered table-report">
                <thead>
                    <tr>
                        <th>الفترة</th>
                        <th class="text-center">ساعات</th>
                        <th class="text-center">أوفرتايم</th>
                        <th class="text-end">الراتب الإجمالي</th>
                        <th class="text-end">خصم تأخير</th>
                        <th class="text-end">خصم سلفة</th>
                        <th class="text-end">إجمالي الخصم</th>
                        <th class="text-end fw-bold">الصافي</th>
                        <th>طريقة الدفع</th>
                    </tr>
                </thead>
                <tbody>
                    @foreach($sal['payments'] as $pay)
                    <tr>
                        <td class="small">
                            {{ $pay->week_start?->format('d/m') }} — {{ $pay->week_end?->format('d/m/Y') }}
                            @if($pay->fiscal_period)<br><span class="text-muted">{{ $pay->fiscal_period }}</span>@endif
                        </td>
                        <td class="text-center">{{ number_format($pay->hours_worked,1) }}</td>
                        <td class="text-center text-info">{{ $pay->overtime_hours > 0 ? number_format($pay->overtime_hours,1) : '—' }}</td>
                        <td class="text-end">{{ number_format($pay->gross_salary,2) }}</td>
                        <td class="text-end text-danger">{{ $pay->late_deduction > 0 ? number_format($pay->late_deduction,2) : '—' }}</td>
                        <td class="text-end text-danger">{{ $pay->loan_deduction_amount > 0 ? number_format($pay->loan_deduction_amount,2) : '—' }}</td>
                        <td class="text-end text-danger">{{ number_format($pay->total_deductions,2) }}</td>
                        <td class="text-end fw-bold text-success">{{ number_format($pay->net_salary,2) }}</td>
                        <td class="small text-muted">{{ $pay->payment_method === 'bank' ? 'بنك' : 'كاش' }}</td>
                    </tr>
                    @endforeach
                </tbody>
                <tfoot>
                    <tr class="summary-row">
                        <td colspan="3" class="fw-bold text-end">المجموع</td>
                        <td class="text-end fw-bold">{{ number_format($sal['total_gross'],2) }} ₪</td>
                        <td class="text-end fw-bold text-danger">{{ number_format($sal['total_late_ded'],2) }} ₪</td>
                        <td class="text-end fw-bold text-danger">{{ number_format($sal['total_loan_ded'],2) }} ₪</td>
                        <td class="text-end fw-bold text-danger">{{ number_format($sal['total_deductions'],2) }} ₪</td>
                        <td class="text-end fw-bold text-success">{{ number_format($sal['total_net'],2) }} ₪</td>
                        <td></td>
                    </tr>
                </tfoot>
            </table>
        </div>
        @else
        <div class="text-center text-muted py-3 small">لا توجد رواتب مسجلة في هذه الفترة</div>
        @endif

        {{-- التعديلات اليدوية --}}
        @if($sal['adjustments']->isNotEmpty())
        <div class="p-2 pt-0">
            <div class="small fw-semibold text-muted mb-1 px-1">التعديلات اليدوية (مكافآت / خصومات)</div>
            <table class="table table-sm table-bordered table-report">
                <thead>
                    <tr>
                        <th>التاريخ</th>
                        <th>النوع</th>
                        <th>السبب</th>
                        <th class="text-end">المبلغ</th>
                    </tr>
                </thead>
                <tbody>
                    @foreach($sal['adjustments'] as $adj)
                    <tr>
                        <td>{{ $adj->adjustment_date?->format('d/m/Y') }}</td>
                        <td>
                            <span class="badge {{ $adj->sign > 0 ? 'bg-success' : 'bg-danger' }}">
                                {{ $adj->sign > 0 ? 'إضافة' : 'خصم' }}
                            </span>
                        </td>
                        <td class="small">{{ $adj->reason ?? $adj->type }}</td>
                        <td class="text-end fw-semibold {{ $adj->sign > 0 ? 'text-success' : 'text-danger' }}">
                            {{ $adj->sign > 0 ? '+' : '-' }}{{ number_format($adj->amount,2) }} ₪
                        </td>
                    </tr>
                    @endforeach
                </tbody>
            </table>
        </div>
        @endif
    </div>
    @endif

    {{-- ③ السلف --}}
    @if(isset($empData['loans']))
    @php $loans = $empData['loans']; @endphp
    <div class="report-section border">
        <div class="report-section-header" style="background:#f72585">
            <i class="fas fa-hand-holding-usd"></i> ③ السلف
        </div>

        <div class="row g-2 p-2">
            <div class="col"><div class="stat-box"><div class="val text-danger">{{ number_format($loans['total_borrowed'],2) }}</div><div class="lbl">إجمالي السلف ₪</div></div></div>
            <div class="col"><div class="stat-box"><div class="val text-success">{{ number_format($loans['total_paid'],2) }}</div><div class="lbl">المدفوع ₪</div></div></div>
            <div class="col"><div class="stat-box"><div class="val text-warning">{{ number_format($loans['total_remaining'],2) }}</div><div class="lbl">المتبقي ₪</div></div></div>
            <div class="col"><div class="stat-box"><div class="val text-primary">{{ $loans['active_count'] }}</div><div class="lbl">سلف نشطة</div></div></div>
        </div>

        @if($loans['records']->isNotEmpty())
        <div class="table-responsive">
            <table class="table table-hover table-bordered table-report">
                <thead>
                    <tr>
                        <th>تاريخ البدء</th>
                        <th>الوصف</th>
                        <th class="text-center">الحالة</th>
                        <th class="text-end">المبلغ الكلي</th>
                        <th class="text-end">قسط شهري/أسبوعي</th>
                        <th class="text-center">الأقساط</th>
                        <th class="text-end">المدفوع</th>
                        <th class="text-end">المتبقي</th>
                    </tr>
                </thead>
                <tbody>
                    @foreach($loans['records'] as $loan)
                    @php $remaining = max(0, $loan->total_amount - $loan->amount_paid); @endphp
                    <tr>
                        <td>{{ $loan->start_date?->format('d/m/Y') }}</td>
                        <td class="small">{{ $loan->description ?? '—' }}</td>
                        <td class="text-center">
                            <span class="badge badge-status-{{ $loan->status }} rounded-pill px-2">
                                {{ $loan->status_label }}
                            </span>
                        </td>
                        <td class="text-end fw-semibold">{{ number_format($loan->total_amount,2) }} ₪</td>
                        <td class="text-end">{{ number_format($loan->installment_amount,2) }} ₪</td>
                        <td class="text-center">{{ $loan->installments_paid }}/{{ $loan->installments_total }}</td>
                        <td class="text-end text-success fw-semibold">{{ number_format($loan->amount_paid,2) }} ₪</td>
                        <td class="text-end {{ $remaining > 0 ? 'text-danger fw-bold' : 'text-muted' }}">
                            {{ $remaining > 0 ? number_format($remaining,2).' ₪' : 'مكتمل' }}
                        </td>
                    </tr>
                    @endforeach
                </tbody>
                <tfoot>
                    <tr class="summary-row">
                        <td colspan="3" class="fw-bold text-end">المجموع</td>
                        <td class="text-end fw-bold">{{ number_format($loans['total_borrowed'],2) }} ₪</td>
                        <td></td>
                        <td></td>
                        <td class="text-end fw-bold text-success">{{ number_format($loans['total_paid'],2) }} ₪</td>
                        <td class="text-end fw-bold text-danger">{{ number_format($loans['total_remaining'],2) }} ₪</td>
                    </tr>
                </tfoot>
            </table>
        </div>
        @else
        <div class="text-center text-muted py-3 small">لا توجد سلف مسجلة لهذا الموظف</div>
        @endif
    </div>
    @endif

    {{-- ④ الإجازات --}}
    @if(isset($empData['leaves']))
    @php $leaves = $empData['leaves']; @endphp
    <div class="report-section border">
        <div class="report-section-header" style="background:#fb8500">
            <i class="fas fa-umbrella-beach"></i> ④ الإجازات
        </div>

        <div class="row g-2 p-2">
            <div class="col"><div class="stat-box"><div class="val text-success">{{ $leaves['approved'] }}</div><div class="lbl">موافق عليها</div></div></div>
            <div class="col"><div class="stat-box"><div class="val text-warning">{{ $leaves['pending'] }}</div><div class="lbl">قيد المراجعة</div></div></div>
            <div class="col"><div class="stat-box"><div class="val text-danger">{{ $leaves['rejected'] }}</div><div class="lbl">مرفوضة</div></div></div>
            <div class="col"><div class="stat-box"><div class="val text-primary">{{ $leaves['total_days'] }}</div><div class="lbl">إجمالي الأيام المعتمدة</div></div></div>
        </div>

        {{-- طلبات الإجازة --}}
        @if($leaves['records']->isNotEmpty())
        <div class="table-responsive">
            <table class="table table-hover table-bordered table-report">
                <thead>
                    <tr>
                        <th>نوع الإجازة</th>
                        <th>من</th>
                        <th>إلى</th>
                        <th class="text-center">عدد الأيام</th>
                        <th class="text-center">الحالة</th>
                        <th>السبب</th>
                    </tr>
                </thead>
                <tbody>
                    @foreach($leaves['records'] as $leave)
                    <tr>
                        <td>{{ $leave->leaveType->name ?? '—' }}</td>
                        <td>{{ $leave->start_date?->format('d/m/Y') }}</td>
                        <td>{{ $leave->end_date?->format('d/m/Y') }}</td>
                        <td class="text-center fw-semibold">{{ $leave->total_days }}</td>
                        <td class="text-center">
                            <span class="badge badge-status-{{ $leave->status }} rounded-pill px-2">
                                {{ match($leave->status) { 'approved'=>'موافق','pending'=>'قيد المراجعة','rejected'=>'مرفوض', default=>$leave->status } }}
                            </span>
                        </td>
                        <td class="small text-muted">{{ $leave->reason ?? '—' }}</td>
                    </tr>
                    @endforeach
                </tbody>
            </table>
        </div>
        @else
        <div class="text-center text-muted py-3 small">لا توجد طلبات إجازة في هذه الفترة</div>
        @endif

        {{-- رصيد الإجازات --}}
        @if($leaves['balances']->isNotEmpty())
        <div class="p-2 pt-0">
            <div class="small fw-semibold text-muted mb-1 px-1">رصيد الإجازات {{ \Carbon\Carbon::parse($from)->year }}</div>
            <div class="row g-2">
                @foreach($leaves['balances'] as $bal)
                <div class="col-auto">
                    <div class="stat-box border px-3 py-2">
                        <div class="small fw-semibold">{{ $bal->leaveType->name ?? '—' }}</div>
                        <div class="d-flex gap-3 mt-1">
                            <span class="text-muted small">مستحق: <strong>{{ $bal->entitled_days }}</strong></span>
                            <span class="text-danger small">مستخدم: <strong>{{ $bal->used_days }}</strong></span>
                            <span class="text-success small">متبقي: <strong>{{ $bal->remaining_days }}</strong></span>
                        </div>
                    </div>
                </div>
                @endforeach
            </div>
        </div>
        @endif
    </div>
    @endif

    {{-- توقيع --}}
    <div class="row mt-4 pt-2 border-top">
        <div class="col-4 text-center small text-muted">
            <div style="border-top: 1px dashed #ccc; margin-top: 40px; padding-top: 4px;">توقيع الموظف</div>
        </div>
        <div class="col-4 text-center small text-muted">
            <div style="border-top: 1px dashed #ccc; margin-top: 40px; padding-top: 4px;">توقيع المدير</div>
        </div>
        <div class="col-4 text-center small text-muted">
            <div style="border-top: 1px dashed #ccc; margin-top: 40px; padding-top: 4px;">الختم الرسمي</div>
        </div>
    </div>

</div>{{-- end employee-page --}}

@empty
<div class="text-center py-5 text-muted">
    <i class="fas fa-inbox fa-3x mb-3 d-block opacity-25"></i>
    لم يتم العثور على بيانات
</div>
@endforelse
</x-app-layout>
