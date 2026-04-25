<x-portal-layout>
<x-slot name="title">كشف حسابي</x-slot>
<div class="container-fluid py-4" dir="rtl">

    {{-- Header الموظف --}}
    <div class="card border-0 shadow-sm mb-4"
         style="background: linear-gradient(135deg, #1e3a5f 0%, #2c5282 100%);">
        <div class="card-body p-4 text-white">
            <div class="row align-items-center">
                <div class="col-auto">
                    <img src="{{ $employee->photo_url }}"
                         class="rounded-circle border border-3 border-white"
                         style="width:70px;height:70px;object-fit:cover;">
                </div>
                <div class="col">
                    <h5 class="fw-bold mb-1">{{ $employee->name }}</h5>
                    <div class="opacity-75 small">
                        {{ $employee->job_title ?? '—' }}
                        @if($employee->department) · {{ $employee->department->name }} @endif
                    </div>
                </div>
                <div class="col-auto text-center">
                    <div class="small opacity-75 mb-1">رصيدي الحالي</div>
                    <div class="fw-bold fs-3 {{ $balance >= 0 ? 'text-warning' : 'text-danger' }}">
                        {{ number_format($balance, 2) }} ₪
                    </div>
                </div>
            </div>
        </div>
    </div>

    <div class="row g-4">

        {{-- COL-RIGHT: الفلتر + الملخص --}}
        <div class="col-lg-3">

            {{-- بطاقات الملخص --}}
            <div class="row g-2 mb-3">
                <div class="col-6">
                    <div class="card border-0 text-center py-3 shadow-sm"
                         style="background:#e8f5e9">
                        <div class="small text-muted">إجمالي له</div>
                        <div class="fw-bold text-success">{{ number_format($summary['total_credits'] ?? 0, 2) }} ₪</div>
                    </div>
                </div>
                <div class="col-6">
                    <div class="card border-0 text-center py-3 shadow-sm"
                         style="background:#fce4ec">
                        <div class="small text-muted">إجمالي عليه</div>
                        <div class="fw-bold text-danger">{{ number_format($summary['total_debits'] ?? 0, 2) }} ₪</div>
                    </div>
                </div>
                <div class="col-6">
                    <div class="card border-0 text-center py-3 shadow-sm"
                         style="background:#e3f2fd">
                        <div class="small text-muted">رواتب صُرفت</div>
                        <div class="fw-bold text-primary">{{ number_format($summary['net_paid'] ?? 0, 2) }} ₪</div>
                    </div>
                </div>
                <div class="col-6">
                    <div class="card border-0 text-center py-3 shadow-sm"
                         style="background:#fff8e1">
                        <div class="small text-muted">خصومات</div>
                        <div class="fw-bold text-warning">{{ number_format($summary['total_deductions'] ?? 0, 2) }} ₪</div>
                    </div>
                </div>
            </div>

            {{-- السلفة النشطة --}}
            @if($employee->activeLoan)
            <div class="card shadow-sm mb-3 border-warning">
                <div class="card-header py-2 bg-warning text-dark small fw-semibold">
                    💳 السلفة النشطة
                </div>
                <div class="card-body py-3">
                    <div class="d-flex justify-content-between small mb-1">
                        <span class="text-muted">إجمالي السلفة</span>
                        <span class="fw-semibold">{{ number_format($employee->activeLoan->total_amount, 2) }} ₪</span>
                    </div>
                    <div class="d-flex justify-content-between small mb-1">
                        <span class="text-muted">تم سداده</span>
                        <span class="fw-semibold text-success">{{ number_format($employee->activeLoan->amount_paid, 2) }} ₪</span>
                    </div>
                    <div class="d-flex justify-content-between small mb-2">
                        <span class="text-muted">المتبقي</span>
                        <span class="fw-bold text-danger">{{ number_format($employee->activeLoan->remaining_amount, 2) }} ₪</span>
                    </div>
                    @php
                        $pct = $employee->activeLoan->total_amount > 0
                            ? min(100, round($employee->activeLoan->amount_paid / $employee->activeLoan->total_amount * 100))
                            : 0;
                    @endphp
                    <div class="progress" style="height:6px">
                        <div class="progress-bar bg-success" style="width:{{ $pct }}%"></div>
                    </div>
                    <div class="small text-muted text-center mt-1">{{ $pct }}% مسدّد</div>
                </div>
            </div>
            @endif

            {{-- فلتر الفترة --}}
            <div class="card shadow-sm">
                <div class="card-header py-2 small fw-semibold">
                    <i class="fas fa-filter me-1"></i>فلتر الفترة
                </div>
                <div class="card-body">
                    <form method="GET">
                        <div class="mb-2">
                            <label class="form-label small mb-1">من</label>
                            <input type="date" name="from" class="form-control form-control-sm" value="{{ $from }}">
                        </div>
                        <div class="mb-3">
                            <label class="form-label small mb-1">إلى</label>
                            <input type="date" name="to" class="form-control form-control-sm" value="{{ $to }}">
                        </div>
                        <div class="d-flex flex-wrap gap-1 mb-3">
                            <button type="button" class="btn btn-outline-secondary btn-sm py-0 px-2 small"
                                onclick="setRange('month')">شهر</button>
                            <button type="button" class="btn btn-outline-secondary btn-sm py-0 px-2 small"
                                onclick="setRange('3months')">3 أشهر</button>
                            <button type="button" class="btn btn-outline-secondary btn-sm py-0 px-2 small"
                                onclick="setRange('6months')">6 أشهر</button>
                            <button type="button" class="btn btn-outline-secondary btn-sm py-0 px-2 small"
                                onclick="setRange('year')">سنة</button>
                        </div>
                        <button type="submit" class="btn btn-primary btn-sm w-100">
                            <i class="fas fa-search me-1"></i>عرض
                        </button>
                    </form>
                </div>
            </div>

        </div>

        {{-- COL-LEFT: جدول الحركات --}}
        <div class="col-lg-9">
            <div class="card shadow-sm">
                <div class="card-header d-flex justify-content-between align-items-center py-2">
                    <span class="fw-semibold">
                        <i class="fas fa-book me-2 text-primary"></i>
                        كشف حسابي —
                        {{ \Carbon\Carbon::parse($from)->format('d/m/Y') }}
                        إلى
                        {{ \Carbon\Carbon::parse($to)->format('d/m/Y') }}
                    </span>
                    <span class="badge bg-secondary">{{ $entries->count() }} حركة</span>
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
                                    <th class="text-end">دائن (له) ↑</th>
                                    <th class="text-end">مدين (عليه) ↓</th>
                                    <th class="text-end px-3">الرصيد</th>
                                </tr>
                            </thead>
                            <tbody>
                                @php
                                    $typeLabels = [
                                        'salary'            => ['label' => 'راتب',          'color' => 'bg-primary'],
                                        'overtime'          => ['label' => 'أوفرتايم',       'color' => 'bg-info text-dark'],
                                        'bonus'             => ['label' => 'مكافأة',          'color' => 'bg-success'],
                                        'adjustment'        => ['label' => 'تسوية',           'color' => 'bg-secondary'],
                                        'deduction_late'    => ['label' => 'خصم تأخير',      'color' => 'bg-warning text-dark'],
                                        'deduction_absence' => ['label' => 'خصم غياب',       'color' => 'bg-warning text-dark'],
                                        'deduction_manual'  => ['label' => 'خصم',             'color' => 'bg-danger'],
                                        'loan_installment'  => ['label' => 'قسط سلفة',       'color' => 'bg-danger'],
                                        'loan_disbursement' => ['label' => 'صرف سلفة',       'color' => 'bg-danger'],
                                        'payment'           => ['label' => 'صرف نقدي',        'color' => 'bg-dark'],
                                        'opening_balance'   => ['label' => 'رصيد افتتاحي',   'color' => 'bg-secondary'],
                                    ];
                                @endphp
                                @foreach($entries as $entry)
                                @php $t = $typeLabels[$entry->entry_type] ?? ['label' => $entry->entry_type, 'color' => 'bg-secondary']; @endphp
                                <tr class="{{ $entry->credit > 0 ? 'table-success bg-opacity-10' : 'table-danger bg-opacity-10' }}">
                                    <td class="px-3 text-muted">
                                        {{ $entry->entry_date?->format('d/m/Y') }}
                                    </td>
                                    <td>
                                        <span class="badge {{ $t['color'] }} me-1">{{ $t['label'] }}</span>
                                        <span class="text-muted">{{ $entry->description }}</span>
                                    </td>
                                    <td class="text-end text-success fw-semibold">
                                        {{ $entry->credit > 0 ? number_format($entry->credit, 2) . ' ₪' : '' }}
                                    </td>
                                    <td class="text-end text-danger fw-semibold">
                                        {{ $entry->debit > 0 ? number_format($entry->debit, 2) . ' ₪' : '' }}
                                    </td>
                                    <td class="text-end px-3 fw-bold {{ $entry->balance_after >= 0 ? 'text-success' : 'text-danger' }}">
                                        {{ number_format($entry->balance_after, 2) }} ₪
                                    </td>
                                </tr>
                                @endforeach
                            </tbody>
                            <tfoot class="table-light">
                                <tr>
                                    <td colspan="2" class="px-3 fw-bold">المجموع</td>
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
