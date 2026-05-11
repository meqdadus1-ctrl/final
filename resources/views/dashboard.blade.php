<x-app-layout>
<x-slot name="title">لوحة التحكم</x-slot>
<div dir="rtl">

{{-- ===== Stats Row ===== --}}
<div class="row g-3 mb-4">
    <div class="col-md-2 col-sm-4">
        <a href="{{ route('employees.index') }}" class="text-decoration-none">
            <div class="card text-white h-100" style="background:#1e3a5f">
                <div class="card-body d-flex justify-content-between align-items-center py-3">
                    <div><div class="fs-3 fw-bold">{{ $activeCount }}</div><div class="small">موظفون نشطون</div></div>
                    <i class="fas fa-users fa-2x opacity-75"></i>
                </div>
            </div>
        </a>
    </div>
    <div class="col-md-2 col-sm-4">
        <a href="{{ route('departments.index') }}" class="text-decoration-none">
            <div class="card text-white h-100" style="background:#2d8a4e">
                <div class="card-body d-flex justify-content-between align-items-center py-3">
                    <div><div class="fs-3 fw-bold">{{ $deptCount }}</div><div class="small">الأقسام</div></div>
                    <i class="fas fa-sitemap fa-2x opacity-75"></i>
                </div>
            </div>
        </a>
    </div>
    <div class="col-md-2 col-sm-4">
        <a href="{{ route('loans.index') }}" class="text-decoration-none">
            <div class="card text-white h-100" style="background:#7b1fa2">
                <div class="card-body d-flex justify-content-between align-items-center py-3">
                    <div><div class="fs-3 fw-bold">{{ $activeLoanCount }}</div><div class="small">سلف نشطة</div></div>
                    <i class="fas fa-hand-holding-usd fa-2x opacity-75"></i>
                </div>
            </div>
        </a>
    </div>
    <div class="col-md-2 col-sm-4">
        <a href="{{ route('leaves.index') }}" class="text-decoration-none">
            <div class="card text-white h-100" style="background:#c0392b">
                <div class="card-body d-flex justify-content-between align-items-center py-3">
                    <div><div class="fs-3 fw-bold">{{ $pendingLeaves }}</div><div class="small">إجازات معلّقة</div></div>
                    <i class="fas fa-calendar-times fa-2x opacity-75"></i>
                </div>
            </div>
        </a>
    </div>
    {{-- راتب هذا الأسبوع --}}
    <div class="col-md-2 col-sm-4">
        <a href="{{ route('salary.index') }}" class="text-decoration-none">
            <div class="card text-white h-100" style="background:#0277bd">
                <div class="card-body d-flex justify-content-between align-items-center py-3">
                    <div>
                        <div class="fs-5 fw-bold">{{ number_format($weekPayroll) }}</div>
                        <div class="small">رواتب الأسبوع ₪</div>
                    </div>
                    <i class="fas fa-money-bill-wave fa-2x opacity-75"></i>
                </div>
            </div>
        </a>
    </div>
    {{-- إجمالي الأرصدة --}}
    <div class="col-md-2 col-sm-4">
        <div class="card text-white h-100" style="background:{{ $totalBalance >= 0 ? '#1b5e20' : '#b71c1c' }}">
            <div class="card-body d-flex justify-content-between align-items-center py-3">
                <div>
                    <div class="fs-6 fw-bold">{{ number_format($totalBalance) }}</div>
                    <div class="small">إجمالي الأرصدة ₪</div>
                </div>
                <i class="fas fa-balance-scale fa-2x opacity-75"></i>
            </div>
        </div>
    </div>
</div>

{{-- ===== Charts Row ===== --}}
<div class="row g-4 mb-4">

    {{-- مخطط الرواتب الأسبوعية --}}
    <div class="col-md-8">
        <div class="card shadow-sm h-100">
            <div class="card-header py-2 fw-semibold">
                <i class="fas fa-chart-bar me-2 text-primary"></i>الرواتب الأسبوعية — آخر 10 أسابيع
            </div>
            <div class="card-body">
                <canvas id="payrollChart" height="100"></canvas>
            </div>
        </div>
    </div>

    {{-- مخطط الحضور --}}
    <div class="col-md-4">
        <div class="card shadow-sm h-100">
            <div class="card-header py-2 fw-semibold">
                <i class="fas fa-chart-pie me-2 text-success"></i>الحضور هذا الأسبوع
            </div>
            <div class="card-body d-flex flex-column align-items-center justify-content-center">
                <canvas id="attendanceChart" style="max-height:180px"></canvas>
                <div class="mt-3 d-flex gap-3 small">
                    <span><span class="badge bg-success">{{ $presentCount }}</span> حاضر</span>
                    <span><span class="badge bg-danger">{{ $absentCount }}</span> غائب</span>
                    <span><span class="badge bg-info text-dark">{{ $leaveCount }}</span> إجازة</span>
                </div>
            </div>
        </div>
    </div>
</div>

{{-- ===== Actions + Week + Balances ===== --}}
<div class="row g-4 mb-4">

    <div class="col-md-4">
        <div class="card shadow-sm h-100">
            <div class="card-header py-2 fw-semibold">
                <i class="fas fa-bolt me-2 text-warning"></i>إجراءات سريعة
            </div>
            <div class="card-body">
                <div class="d-grid gap-2">
                    <a href="{{ route('salary.create') }}" class="btn btn-primary btn-sm">
                        <i class="fas fa-calculator me-2"></i>احتساب راتب
                    </a>
                    <a href="{{ route('attendance.create') }}" class="btn btn-outline-primary btn-sm">
                        <i class="fas fa-fingerprint me-2"></i>تسجيل حضور
                    </a>
                    <a href="{{ route('attendance.pull.page') }}" class="btn btn-outline-secondary btn-sm">
                        <i class="fas fa-sync me-2"></i>مزامنة البصمة
                    </a>
                    <a href="{{ route('loans.create') }}" class="btn btn-outline-secondary btn-sm">
                        <i class="fas fa-hand-holding-usd me-2"></i>سلفة جديدة
                    </a>
                    <a href="{{ route('audit.index') }}" class="btn btn-outline-dark btn-sm">
                        <i class="fas fa-history me-2"></i>سجل التدقيق
                    </a>
                    <a href="{{ route('backup.index') }}" class="btn btn-outline-danger btn-sm">
                        <i class="fas fa-database me-2"></i>النسخ الاحتياطية
                    </a>
                </div>
            </div>
        </div>
    </div>

    <div class="col-md-4">
        <div class="card shadow-sm h-100">
            <div class="card-header py-2 fw-semibold">
                <i class="fas fa-calendar-week me-2 text-primary"></i>الأسبوع الحالي — {{ $currentFiscal }}
            </div>
            <div class="card-body">
                <div class="d-flex justify-content-between small mb-2">
                    <span class="text-muted">من</span>
                    <span class="fw-semibold">{{ \Carbon\Carbon::parse($weekStart)->format('d/m/Y') }}</span>
                </div>
                <div class="d-flex justify-content-between small mb-3">
                    <span class="text-muted">إلى</span>
                    <span class="fw-semibold">{{ \Carbon\Carbon::parse($weekEnd)->format('d/m/Y') }}</span>
                </div>
                @php
                    $weekPaymentsCount = \App\Models\SalaryPayment::where('fiscal_period', $currentFiscal)->count();
                    $pendingEmps = $activeCount - $weekPaymentsCount;
                @endphp
                <div class="d-flex justify-content-between small mb-1">
                    <span class="text-muted">رواتب محتسبة</span>
                    <span class="badge bg-success">{{ $weekPaymentsCount }}</span>
                </div>
                <div class="d-flex justify-content-between small mb-1">
                    <span class="text-muted">بدون راتب</span>
                    <span class="badge {{ $pendingEmps > 0 ? 'bg-warning text-dark' : 'bg-secondary' }}">{{ max(0,$pendingEmps) }}</span>
                </div>
                <div class="d-flex justify-content-between small mb-1">
                    <span class="text-muted">إجمالي صافي</span>
                    <span class="fw-bold text-success">{{ number_format($weekPayroll) }} ₪</span>
                </div>
                <hr class="my-2">
                <div class="d-flex justify-content-between small">
                    <span class="text-muted">تعديلات معلّقة</span>
                    <span class="badge {{ $pendingAdjCount > 0 ? 'bg-warning text-dark' : 'bg-secondary' }}">{{ $pendingAdjCount }}</span>
                </div>
            </div>
        </div>
    </div>

    <div class="col-md-4">
        <div class="card shadow-sm h-100">
            <div class="card-header py-2 fw-semibold d-flex justify-content-between">
                <span><i class="fas fa-wallet me-2 text-success"></i>أرصدة الموظفين</span>
            </div>
            <div class="card-body p-0">
                <div class="list-group list-group-flush" style="max-height:240px;overflow-y:auto">
                    @foreach($activeEmps->take(10) as $emp)
                    @php $bal = $emp->ledger_balance; @endphp
                    <a href="{{ route('ledger.show', $emp) }}"
                       class="list-group-item list-group-item-action d-flex justify-content-between align-items-center small py-2">
                        <span>{{ $emp->name }}</span>
                        <span class="fw-bold {{ $bal >= 0 ? 'text-success' : 'text-danger' }}">
                            {{ number_format($bal, 2) }} ₪
                        </span>
                    </a>
                    @endforeach
                </div>
            </div>
        </div>
    </div>
</div>

{{-- ===== آخر الرواتب + سجل التدقيق ===== --}}
<div class="row g-4 mb-4">
    <div class="col-md-8">
        <div class="card shadow-sm">
            <div class="card-header py-2 fw-semibold d-flex justify-content-between">
                <span><i class="fas fa-money-bill-wave me-2"></i>آخر الرواتب</span>
                <a href="{{ route('salary.index') }}" class="btn btn-sm btn-outline-primary py-0">عرض الكل</a>
            </div>
            <div class="card-body p-0">
                <div class="table-responsive">
                    <table class="table table-hover align-middle mb-0 small">
                        <thead class="table-dark">
                            <tr>
                                <th class="px-3">الموظف</th>
                                <th>الكود</th>
                                <th class="text-end text-success">الصافي</th>
                                <th class="text-end">الرصيد بعد</th>
                                <th class="text-center">الدفع</th>
                                <th></th>
                            </tr>
                        </thead>
                        <tbody>
                            @forelse($lastPayments as $p)
                            <tr>
                                <td class="px-3">
                                    <div class="fw-semibold">{{ $p->employee?->name ?? '—' }}</div>
                                    <small class="text-muted">{{ $p->employee?->department?->name ?? '—' }}</small>
                                </td>
                                <td><span class="badge bg-secondary">{{ $p->fiscal_period }}</span></td>
                                <td class="text-end fw-bold text-success">{{ number_format($p->net_salary) }} ₪</td>
                                <td class="text-end">
                                    <span class="{{ ($p->balance_after ?? 0) >= 0 ? 'text-success' : 'text-danger' }} fw-semibold">
                                        {{ number_format($p->balance_after ?? 0) }} ₪
                                    </span>
                                </td>
                                <td class="text-center">
                                    <span class="badge {{ $p->payment_method === 'bank' ? 'bg-primary' : ($p->payment_method === 'deferred' ? 'bg-info text-dark' : 'bg-warning text-dark') }}">
                                        {{ $p->payment_method === 'bank' ? 'بنك' : ($p->payment_method === 'deferred' ? 'ترحيل' : 'كاش') }}
                                    </span>
                                </td>
                                <td><a href="{{ route('salary.show', $p) }}" class="btn btn-sm btn-outline-primary py-0"><i class="fas fa-eye"></i></a></td>
                            </tr>
                            @empty
                            <tr><td colspan="6" class="text-center py-4 text-muted">لا توجد رواتب بعد</td></tr>
                            @endforelse
                        </tbody>
                    </table>
                </div>
            </div>
        </div>
    </div>

    {{-- آخر سجلات التدقيق --}}
    <div class="col-md-4">
        <div class="card shadow-sm h-100">
            <div class="card-header py-2 fw-semibold d-flex justify-content-between">
                <span><i class="fas fa-history me-2 text-secondary"></i>آخر التعديلات</span>
                <a href="{{ route('audit.index') }}" class="btn btn-sm btn-outline-secondary py-0">الكل</a>
            </div>
            <div class="card-body p-0">
                <div class="list-group list-group-flush" style="max-height:280px;overflow-y:auto">
                    @forelse($recentAudit as $log)
                    <div class="list-group-item py-2 small">
                        <div class="d-flex justify-content-between">
                            <span class="badge bg-{{ $log->action_color }}">{{ $log->action_label }}</span>
                            <span class="text-muted">{{ $log->created_at->diffForHumans() }}</span>
                        </div>
                        <div class="mt-1">
                            <span class="fw-semibold">{{ $log->user?->name ?? 'النظام' }}</span>
                            <span class="text-muted"> — {{ $log->model_name }} #{{ $log->model_id }}</span>
                        </div>
                    </div>
                    @empty
                    <div class="list-group-item text-muted text-center py-4">لا توجد سجلات بعد</div>
                    @endforelse
                </div>
            </div>
        </div>
    </div>
</div>

</div>

{{-- Chart.js --}}
<script src="https://cdn.jsdelivr.net/npm/chart.js@4.4.0/dist/chart.umd.min.js"></script>
<script>
// مخطط الرواتب
const payrollCtx = document.getElementById('payrollChart');
new Chart(payrollCtx, {
    type: 'bar',
    data: {
        labels: {!! json_encode($payrollChart->keys()->values()) !!},
        datasets: [{
            label: 'صافي الرواتب ₪',
            data: {!! json_encode($payrollChart->values()->map(fn($v) => (int)$v)) !!},
            backgroundColor: 'rgba(30, 58, 95, 0.8)',
            borderColor: 'rgba(30, 58, 95, 1)',
            borderWidth: 1,
            borderRadius: 4,
        }]
    },
    options: {
        responsive: true,
        plugins: { legend: { display: false } },
        scales: {
            y: { beginAtZero: true, ticks: { callback: v => v.toLocaleString() + ' ₪' } }
        }
    }
});

// مخطط الحضور
const attCtx = document.getElementById('attendanceChart');
new Chart(attCtx, {
    type: 'doughnut',
    data: {
        labels: ['حاضر', 'غائب', 'إجازة'],
        datasets: [{
            data: [{{ $presentCount }}, {{ $absentCount }}, {{ $leaveCount }}],
            backgroundColor: ['#198754','#dc3545','#0dcaf0'],
            borderWidth: 2,
        }]
    },
    options: {
        responsive: true,
        plugins: { legend: { display: false } },
        cutout: '65%',
    }
});
</script>
</x-app-layout>
