<x-portal-layout>
<x-slot name="title">الرئيسية</x-slot>

{{-- Employee Hero Card --}}
<div class="card mb-4" style="background: linear-gradient(135deg, #1e3a5f 0%, #2d5a8e 100%); color:#fff; overflow:hidden; position:relative;">
    <div class="card-body p-4">
        <div class="row align-items-center">
            <div class="col-auto">
                <img src="{{ $employee->photo_url }}"
                     class="rounded-circle border border-3 border-white"
                     style="width:75px; height:75px; object-fit:cover;">
            </div>
            <div class="col">
                <h4 class="fw-bold mb-1">{{ $employee->name }}</h4>
                <div style="opacity:.8; font-size:14px;">
                    {{ $employee->job_title ?? '—' }}
                    @if($employee->department)
                        <span class="mx-2">·</span> {{ $employee->department->name }}
                    @endif
                </div>
                <div class="mt-2" style="font-size:12px; opacity:.65;">
                    <i class="fas fa-id-badge me-1"></i> {{ $employee->employee_number ?? '—' }}
                    @if($employee->hire_date)
                        <span class="mx-2">·</span>
                        <i class="fas fa-calendar-check me-1"></i> منذ {{ $employee->hire_date->format('Y') }}
                    @endif
                </div>
            </div>
            <div class="col-auto text-center d-none d-md-block">
                <div style="font-size:11px; opacity:.7; margin-bottom:4px;">رصيدي الحالي</div>
                <div class="fw-bold" style="font-size:28px; color: {{ $balance >= 0 ? '#4ade80' : '#f87171' }};">
                    {{ number_format(abs($balance), 2) }} ₪
                </div>
                <div style="font-size:11px; opacity:.6;">{{ $balance >= 0 ? 'مستحق لك' : 'مستحق عليك' }}</div>
            </div>
        </div>
    </div>
</div>

{{-- Stats Row --}}
<div class="row g-3 mb-4">
    <div class="col-6 col-md-3">
        <div class="stat-card">
            <div class="stat-icon" style="background:#e8f5e9; color:#2e7d32;">
                <i class="fas fa-check-circle"></i>
            </div>
            <div>
                <div class="stat-value text-success">{{ $summary['total_credits'] ? number_format($summary['total_credits'], 0) : '0' }}</div>
                <div class="stat-label">إجمالي له ₪</div>
            </div>
        </div>
    </div>
    <div class="col-6 col-md-3">
        <div class="stat-card">
            <div class="stat-icon" style="background:#fce4ec; color:#c62828;">
                <i class="fas fa-arrow-down"></i>
            </div>
            <div>
                <div class="stat-value text-danger">{{ $summary['total_debits'] ? number_format($summary['total_debits'], 0) : '0' }}</div>
                <div class="stat-label">إجمالي عليه ₪</div>
            </div>
        </div>
    </div>
    <div class="col-6 col-md-3">
        <div class="stat-card">
            <div class="stat-icon" style="background:#e3f2fd; color:#1565c0;">
                <i class="fas fa-money-bill-wave"></i>
            </div>
            <div>
                <div class="stat-value text-primary">{{ $summary['net_paid'] ? number_format($summary['net_paid'], 0) : '0' }}</div>
                <div class="stat-label">رواتب صُرفت ₪</div>
            </div>
        </div>
    </div>
    <div class="col-6 col-md-3">
        <div class="stat-card">
            <div class="stat-icon" style="background:#fff8e1; color:#e65100;">
                <i class="fas fa-minus-circle"></i>
            </div>
            <div>
                <div class="stat-value text-warning">{{ $summary['total_deductions'] ? number_format($summary['total_deductions'], 0) : '0' }}</div>
                <div class="stat-label">خصومات ₪</div>
            </div>
        </div>
    </div>
</div>

<div class="row g-3">

    {{-- Account Statement --}}
    <div class="col-lg-8">
        <div class="card">
            <div class="card-header d-flex justify-content-between align-items-center">
                <span><i class="fas fa-book text-primary me-2"></i>كشف الحساب</span>
                <div class="d-flex gap-2 align-items-center">
                    <span class="badge bg-secondary">{{ $entries->count() }} حركة</span>
                    {{-- Date filter inline --}}
                    <form method="GET" class="d-flex gap-1">
                        <input type="date" name="from" class="form-control form-control-sm" value="{{ $from }}" style="width:130px;">
                        <input type="date" name="to"   class="form-control form-control-sm" value="{{ $to }}"   style="width:130px;">
                        <button type="submit" class="btn btn-primary btn-sm"><i class="fas fa-search"></i></button>
                        <a href="{{ route('portal.index') }}" class="btn btn-secondary btn-sm"><i class="fas fa-times"></i></a>
                    </form>
                </div>
            </div>

            {{-- Quick Range Buttons --}}
            <div class="px-3 pt-2 pb-1 d-flex gap-1 flex-wrap" id="rangeBar">
                @foreach(['month' => 'شهر', '3months' => '3 أشهر', '6months' => '6 أشهر', 'year' => 'سنة'] as $key => $label)
                <button type="button" class="btn btn-outline-secondary btn-sm py-0 px-2" style="font-size:12px;"
                        onclick="setRange('{{ $key }}')">{{ $label }}</button>
                @endforeach
            </div>

            <div class="card-body p-0">
                @if($entries->isEmpty())
                    <div class="text-center py-5 text-muted">
                        <i class="fas fa-inbox fa-2x mb-2 d-block opacity-25"></i>
                        لا توجد حركات في هذه الفترة
                    </div>
                @else
                <div class="table-responsive">
                    <table class="table table-hover align-middle mb-0 small">
                        <thead class="table-dark">
                            <tr>
                                <th class="px-3">التاريخ</th>
                                <th>البيان</th>
                                <th class="text-end">دائن ↑</th>
                                <th class="text-end">مدين ↓</th>
                                <th class="text-end px-3">الرصيد</th>
                            </tr>
                        </thead>
                        <tbody>
                            @php
                            $typeLabels = [
                                'salary'            => ['label' => 'راتب',        'color' => 'bg-primary'],
                                'overtime'          => ['label' => 'أوفرتايم',    'color' => 'bg-info text-dark'],
                                'bonus'             => ['label' => 'مكافأة',       'color' => 'bg-success'],
                                'adjustment'        => ['label' => 'تسوية',        'color' => 'bg-secondary'],
                                'deduction_late'    => ['label' => 'خصم تأخير',   'color' => 'bg-warning text-dark'],
                                'deduction_absence' => ['label' => 'خصم غياب',    'color' => 'bg-warning text-dark'],
                                'deduction_manual'  => ['label' => 'خصم',          'color' => 'bg-danger'],
                                'loan_installment'  => ['label' => 'قسط سلفة',    'color' => 'bg-danger'],
                                'loan_disbursement' => ['label' => 'صرف سلفة',    'color' => 'bg-danger'],
                                'payment'           => ['label' => 'صرف نقدي',     'color' => 'bg-dark'],
                                'opening_balance'   => ['label' => 'رصيد افتتاحي','color' => 'bg-secondary'],
                            ];
                            @endphp
                            @foreach($entries as $entry)
                            @php $t = $typeLabels[$entry->entry_type] ?? ['label' => $entry->entry_type, 'color' => 'bg-secondary']; @endphp
                            <tr>
                                <td class="px-3 text-muted small">{{ $entry->entry_date?->format('d/m/Y') }}</td>
                                <td>
                                    <span class="badge {{ $t['color'] }} me-1">{{ $t['label'] }}</span>
                                    <span class="text-muted">{{ $entry->description }}</span>
                                </td>
                                <td class="text-end text-success fw-semibold">
                                    {{ $entry->credit > 0 ? number_format($entry->credit, 2).' ₪' : '' }}
                                </td>
                                <td class="text-end text-danger fw-semibold">
                                    {{ $entry->debit > 0 ? number_format($entry->debit, 2).' ₪' : '' }}
                                </td>
                                <td class="text-end px-3 fw-bold {{ $entry->balance_after >= 0 ? 'text-success' : 'text-danger' }}">
                                    {{ number_format($entry->balance_after, 2) }} ₪
                                </td>
                            </tr>
                            @endforeach
                        </tbody>
                        <tfoot style="background:#f8fafc;">
                            <tr>
                                <td colspan="2" class="px-3 fw-bold small">المجموع</td>
                                <td class="text-end text-success fw-bold">{{ number_format($entries->sum('credit'), 2) }} ₪</td>
                                <td class="text-end text-danger fw-bold">{{ number_format($entries->sum('debit'), 2) }} ₪</td>
                                <td class="text-end px-3 fw-bold {{ $balance >= 0 ? 'text-success' : 'text-danger' }}">
                                    {{ number_format($balance, 2) }} ₪
                                </td>
                            </tr>
                        </tfoot>
                    </table>
                </div>
                @endif
            </div>
        </div>
    </div>

    {{-- Sidebar --}}
    <div class="col-lg-4">

        {{-- Active Loan --}}
        @if($employee->activeLoan)
        <div class="card mb-3 border-0" style="border-right: 4px solid #f59e0b !important;">
            <div class="card-header py-2" style="font-size:13px;">
                <i class="fas fa-hand-holding-usd text-warning me-2"></i> السلفة النشطة
            </div>
            <div class="card-body">
                @php
                    $loan = $employee->activeLoan;
                    $pct  = $loan->total_amount > 0 ? min(100, round($loan->amount_paid / $loan->total_amount * 100)) : 0;
                @endphp
                <div class="d-flex justify-content-between small mb-1">
                    <span class="text-muted">الإجمالي</span>
                    <span class="fw-semibold">{{ number_format($loan->total_amount, 2) }} ₪</span>
                </div>
                <div class="d-flex justify-content-between small mb-1">
                    <span class="text-muted">تم سداده</span>
                    <span class="text-success fw-semibold">{{ number_format($loan->amount_paid, 2) }} ₪</span>
                </div>
                <div class="d-flex justify-content-between small mb-3">
                    <span class="text-muted">المتبقي</span>
                    <span class="text-danger fw-bold">{{ number_format($loan->total_amount - $loan->amount_paid, 2) }} ₪</span>
                </div>
                <div class="progress" style="height:8px;">
                    <div class="progress-bar bg-success" style="width:{{ $pct }}%"></div>
                </div>
                <div class="text-center text-muted small mt-1">{{ $pct }}% مسدّد</div>
            </div>
        </div>
        @endif

        {{-- Quick Links --}}
        <div class="card">
            <div class="card-header py-2" style="font-size:13px;">
                <i class="fas fa-bolt me-2 text-warning"></i> وصول سريع
            </div>
            <div class="list-group list-group-flush">
                <a href="{{ route('portal.attendance') }}" class="list-group-item list-group-item-action d-flex align-items-center gap-3 py-3">
                    <div style="width:36px;height:36px;background:#e8f5e9;border-radius:8px;display:flex;align-items:center;justify-content:center;">
                        <i class="fas fa-clock text-success"></i>
                    </div>
                    <div>
                        <div class="fw-semibold small">سجل الحضور</div>
                        <div class="text-muted" style="font-size:11px;">عرض حضور وغياب الشهر</div>
                    </div>
                    <i class="fas fa-chevron-left ms-auto text-muted small"></i>
                </a>
                <a href="{{ route('portal.payslips') }}" class="list-group-item list-group-item-action d-flex align-items-center gap-3 py-3">
                    <div style="width:36px;height:36px;background:#e3f2fd;border-radius:8px;display:flex;align-items:center;justify-content:center;">
                        <i class="fas fa-money-bill-wave text-primary"></i>
                    </div>
                    <div>
                        <div class="fw-semibold small">كشوف الرواتب</div>
                        <div class="text-muted" style="font-size:11px;">عرض ومشاركة الراتب الشهري</div>
                    </div>
                    <i class="fas fa-chevron-left ms-auto text-muted small"></i>
                </a>
                <a href="{{ route('portal.leaves') }}" class="list-group-item list-group-item-action d-flex align-items-center gap-3 py-3">
                    <div style="width:36px;height:36px;background:#fff8e1;border-radius:8px;display:flex;align-items:center;justify-content:center;">
                        <i class="fas fa-umbrella-beach text-warning"></i>
                    </div>
                    <div>
                        <div class="fw-semibold small">طلب إجازة</div>
                        <div class="text-muted" style="font-size:11px;">تقديم ومتابعة طلبات الإجازة</div>
                    </div>
                    <i class="fas fa-chevron-left ms-auto text-muted small"></i>
                </a>
                <a href="{{ route('portal.loans') }}" class="list-group-item list-group-item-action d-flex align-items-center gap-3 py-3">
                    <div style="width:36px;height:36px;background:#fce4ec;border-radius:8px;display:flex;align-items:center;justify-content:center;">
                        <i class="fas fa-hand-holding-usd text-danger"></i>
                    </div>
                    <div>
                        <div class="fw-semibold small">طلب سلفة</div>
                        <div class="text-muted" style="font-size:11px;">تقديم ومتابعة طلبات السلف</div>
                    </div>
                    <i class="fas fa-chevron-left ms-auto text-muted small"></i>
                </a>
                <a href="{{ route('portal.tasks') }}" class="list-group-item list-group-item-action d-flex align-items-center gap-3 py-3">
                    <div style="width:36px;height:36px;background:#ede9fe;border-radius:8px;display:flex;align-items:center;justify-content:center;">
                        <i class="fas fa-tasks" style="color:#7c3aed;"></i>
                    </div>
                    <div>
                        <div class="fw-semibold small">مهامي</div>
                        <div class="text-muted" style="font-size:11px;">عرض المهام المسندة إليك</div>
                    </div>
                    <i class="fas fa-chevron-left ms-auto text-muted small"></i>
                </a>
            </div>
        </div>

    </div>
</div>

<script>
function setRange(range) {
    const today = new Date();
    const fmt   = d => d.toISOString().split('T')[0];
    let from    = new Date(today);
    if      (range === 'month')   from.setMonth(today.getMonth() - 1);
    else if (range === '3months') from.setMonth(today.getMonth() - 3);
    else if (range === '6months') from.setMonth(today.getMonth() - 6);
    else if (range === 'year')    from.setFullYear(today.getFullYear() - 1);
    document.querySelector('[name="from"]').value = fmt(from);
    document.querySelector('[name="to"]').value   = fmt(today);
}
</script>
</x-portal-layout>
