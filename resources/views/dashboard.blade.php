<x-app-layout>
<x-slot name="title">لوحة التحكم</x-slot>
<div dir="rtl">

@php
    $activeCount   = \App\Models\Employee::where('status','active')->count();
    $inactiveCount = \App\Models\Employee::where('status','inactive')->count();
    $deptCount     = \App\Models\Department::count();
    $bankCount     = \App\Models\Bank::count();
    $activeLoanCount = \App\Models\Loan::where('status','active')->count();
    $pendingLeavesCount = \App\Models\LeaveRequest::where('status','pending')->count();
    $pendingAdjCount = \App\Models\SalaryAdjustment::where('status','pending')->count();

    // آخر راتب تم احتسابه
    $lastPayment = \App\Models\SalaryPayment::latest()->first();

    // الأسبوع الحالي (خميس→أربعاء)
    $today = \Carbon\Carbon::now();
    $dayOfWeek = $today->dayOfWeek;
    $daysBack = ($dayOfWeek >= \Carbon\Carbon::THURSDAY) ? ($dayOfWeek - \Carbon\Carbon::THURSDAY) : ($dayOfWeek + 3);
    $weekStart = $today->copy()->subDays($daysBack)->toDateString();
    $weekEnd   = $today->copy()->subDays($daysBack)->addDays(6)->toDateString();
    $currentFiscal = \Carbon\Carbon::parse($weekStart)->format('Y-\WW');

    // أرصدة الموظفين النشطين
    $activeEmps = \App\Models\Employee::active()->with('ledger')->get();
@endphp

{{-- ===== Stats Row ===== --}}
<div class="row g-3 mb-4">
    <div class="col-md-2 col-sm-4">
        <a href="{{ route('employees.index') }}" class="text-decoration-none">
            <div class="card text-white h-100" style="background:#1e3a5f">
                <div class="card-body d-flex justify-content-between align-items-center py-3">
                    <div>
                        <div class="fs-3 fw-bold">{{ $activeCount }}</div>
                        <div class="small">موظفون نشطون</div>
                    </div>
                    <i class="fas fa-users fa-2x opacity-75"></i>
                </div>
            </div>
        </a>
    </div>
    <div class="col-md-2 col-sm-4">
        <a href="{{ route('departments.index') }}" class="text-decoration-none">
            <div class="card text-white h-100" style="background:#2d8a4e">
                <div class="card-body d-flex justify-content-between align-items-center py-3">
                    <div>
                        <div class="fs-3 fw-bold">{{ $deptCount }}</div>
                        <div class="small">الأقسام</div>
                    </div>
                    <i class="fas fa-sitemap fa-2x opacity-75"></i>
                </div>
            </div>
        </a>
    </div>
    <div class="col-md-2 col-sm-4">
        <a href="{{ route('loans.index') }}" class="text-decoration-none">
            <div class="card text-white h-100" style="background:#7b1fa2">
                <div class="card-body d-flex justify-content-between align-items-center py-3">
                    <div>
                        <div class="fs-3 fw-bold">{{ $activeLoanCount }}</div>
                        <div class="small">سلف نشطة</div>
                    </div>
                    <i class="fas fa-hand-holding-usd fa-2x opacity-75"></i>
                </div>
            </div>
        </a>
    </div>
    <div class="col-md-2 col-sm-4">
        <a href="{{ route('leaves.index') }}" class="text-decoration-none">
            <div class="card text-white h-100" style="background:#c0392b">
                <div class="card-body d-flex justify-content-between align-items-center py-3">
                    <div>
                        <div class="fs-3 fw-bold">{{ $pendingLeavesCount }}</div>
                        <div class="small">إجازات معلّقة</div>
                    </div>
                    <i class="fas fa-calendar-times fa-2x opacity-75"></i>
                </div>
            </div>
        </a>
    </div>
    <div class="col-md-2 col-sm-4">
        <a href="{{ route('salary.adjustments') }}" class="text-decoration-none">
            <div class="card text-white h-100" style="background:#e67e22">
                <div class="card-body d-flex justify-content-between align-items-center py-3">
                    <div>
                        <div class="fs-3 fw-bold">{{ $pendingAdjCount }}</div>
                        <div class="small">تعديلات معلّقة</div>
                    </div>
                    <i class="fas fa-tags fa-2x opacity-75"></i>
                </div>
            </div>
        </a>
    </div>
    <div class="col-md-2 col-sm-4">
        <a href="{{ route('banks.index') }}" class="text-decoration-none">
            <div class="card text-white h-100" style="background:#d35400">
                <div class="card-body d-flex justify-content-between align-items-center py-3">
                    <div>
                        <div class="fs-3 fw-bold">{{ $bankCount }}</div>
                        <div class="small">البنوك</div>
                    </div>
                    <i class="fas fa-university fa-2x opacity-75"></i>
                </div>
            </div>
        </a>
    </div>
</div>

{{-- ===== Quick Actions + Last Salary ===== --}}
<div class="row g-4 mb-4">

    {{-- إجراءات سريعة --}}
    <div class="col-md-4">
        <div class="card shadow-sm h-100">
            <div class="card-header py-2 fw-semibold">
                <i class="fas fa-bolt me-2 text-warning"></i>إجراءات سريعة
            </div>
            <div class="card-body">
                <div class="d-grid gap-2">
                    <a href="{{ route('salary.create') }}" class="btn btn-primary">
                        <i class="fas fa-calculator me-2"></i>احتساب راتب أسبوعي
                    </a>
                    <a href="{{ route('attendance.create') }}" class="btn btn-outline-primary">
                        <i class="fas fa-fingerprint me-2"></i>تسجيل حضور
                    </a>
                    <a href="{{ route('attendance.pull.page') }}" class="btn btn-outline-secondary">
                        <i class="fas fa-sync me-2"></i>مزامنة جهاز البصمة
                    </a>
                    <a href="{{ route('loans.create') }}" class="btn btn-outline-purple">
                        <i class="fas fa-hand-holding-usd me-2"></i>تسجيل سلفة جديدة
                    </a>
                    <a href="{{ route('salary.adjustments') }}" class="btn btn-outline-warning">
                        <i class="fas fa-plus me-2"></i>إضافة تعديل يدوي
                    </a>
                </div>
            </div>
        </div>
    </div>

    {{-- الأسبوع الحالي --}}
    <div class="col-md-4">
        <div class="card shadow-sm h-100">
            <div class="card-header py-2 fw-semibold">
                <i class="fas fa-calendar-week me-2 text-primary"></i>الأسبوع الحالي
            </div>
            <div class="card-body">
                <div class="text-center mb-3">
                    <span class="badge bg-primary fs-6 px-3 py-2">{{ $currentFiscal }}</span>
                </div>
                <div class="d-flex justify-content-between small mb-2">
                    <span class="text-muted">من</span>
                    <span class="fw-semibold">{{ \Carbon\Carbon::parse($weekStart)->format('d/m/Y') }} (خميس)</span>
                </div>
                <div class="d-flex justify-content-between small mb-3">
                    <span class="text-muted">إلى</span>
                    <span class="fw-semibold">{{ \Carbon\Carbon::parse($weekEnd)->format('d/m/Y') }} (أربعاء)</span>
                </div>
                @php
                    $weekPayments = \App\Models\SalaryPayment::where('fiscal_period', $currentFiscal)->count();
                    $pendingEmps  = $activeCount - $weekPayments;
                @endphp
                <div class="d-flex justify-content-between small mb-1">
                    <span class="text-muted">رواتب محتسبة هذا الأسبوع</span>
                    <span class="badge bg-success">{{ $weekPayments }}</span>
                </div>
                <div class="d-flex justify-content-between small">
                    <span class="text-muted">موظفون بدون راتب</span>
                    <span class="badge {{ $pendingEmps > 0 ? 'bg-warning text-dark' : 'bg-secondary' }}">{{ max(0,$pendingEmps) }}</span>
                </div>
                @if($lastPayment)
                <hr class="my-2">
                <div class="small text-muted text-center">
                    آخر راتب: <strong>{{ $lastPayment->employee?->name }}</strong>
                    — {{ number_format($lastPayment->net_salary, 2) }} ₪
                </div>
                @endif
            </div>
        </div>
    </div>

    {{-- أرصدة الموظفين --}}
    <div class="col-md-4">
        <div class="card shadow-sm h-100">
            <div class="card-header py-2 fw-semibold d-flex justify-content-between">
                <span><i class="fas fa-wallet me-2 text-success"></i>أرصدة الموظفين</span>
                <small class="text-muted small">الرصيد الحالي</small>
            </div>
            <div class="card-body p-0">
                <div class="list-group list-group-flush" style="max-height:220px;overflow-y:auto">
                    @foreach($activeEmps->take(8) as $emp)
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

{{-- ===== آخر الرواتب ===== --}}
<div class="card shadow-sm mb-4">
    <div class="card-header py-2 fw-semibold d-flex justify-content-between">
        <span><i class="fas fa-money-bill-wave me-2"></i>آخر الرواتب المحتسبة</span>
        <a href="{{ route('salary.index') }}" class="btn btn-sm btn-outline-primary py-0">عرض الكل</a>
    </div>
    <div class="card-body p-0">
        <div class="table-responsive">
            <table class="table table-hover align-middle mb-0 small">
                <thead class="table-dark">
                    <tr>
                        <th class="px-3">الموظف</th>
                        <th>الكود</th>
                        <th class="text-end">الإجمالي</th>
                        <th class="text-end text-success">الصافي</th>
                        <th class="text-end">الرصيد بعد</th>
                        <th class="text-center">الدفع</th>
                        <th></th>
                    </tr>
                </thead>
                <tbody>
                    @forelse(\App\Models\SalaryPayment::with('employee.department')->latest()->take(6)->get() as $p)
                    <tr>
                        <td class="px-3">
                            <div class="fw-semibold">{{ $p->employee?->name ?? '—' }}</div>
                            <small class="text-muted">{{ $p->employee?->department?->name ?? '—' }}</small>
                        </td>
                        <td><span class="badge bg-secondary">{{ $p->fiscal_period }}</span></td>
                        <td class="text-end">{{ number_format($p->gross_salary, 2) }}</td>
                        <td class="text-end fw-bold text-success">{{ number_format($p->net_salary, 2) }} ₪</td>
                        <td class="text-end">
                            <span class="{{ ($p->balance_after ?? 0) >= 0 ? 'text-success' : 'text-danger' }} fw-semibold">
                                {{ number_format($p->balance_after ?? 0, 2) }} ₪
                            </span>
                        </td>
                        <td class="text-center">
                            <span class="badge {{ $p->payment_method === 'bank' ? 'bg-primary' : 'bg-warning text-dark' }}">
                                {{ $p->payment_method === 'bank' ? '🏦 بنك' : '💵 كاش' }}
                            </span>
                        </td>
                        <td>
                            <a href="{{ route('salary.show', $p) }}" class="btn btn-sm btn-outline-primary py-0">
                                <i class="fas fa-eye"></i>
                            </a>
                        </td>
                    </tr>
                    @empty
                    <tr><td colspan="7" class="text-center py-4 text-muted">لا توجد رواتب بعد</td></tr>
                    @endforelse
                </tbody>
            </table>
        </div>
    </div>
</div>

{{-- ===== آخر الموظفين ===== --}}
<div class="card shadow-sm">
    <div class="card-header py-2 fw-semibold d-flex justify-content-between">
        <span><i class="fas fa-users me-2"></i>آخر الموظفين المضافين</span>
        <a href="{{ route('employees.index') }}" class="btn btn-sm btn-outline-secondary py-0">عرض الكل</a>
    </div>
    <div class="card-body p-0">
        <table class="table table-hover align-middle mb-0 small">
            <thead class="table-light">
                <tr>
                    <th class="px-3">الاسم</th>
                    <th>القسم</th>
                    <th>نوع الراتب</th>
                    <th class="text-center">الحالة</th>
                    <th>تاريخ التعيين</th>
                    <th></th>
                </tr>
            </thead>
            <tbody>
                @forelse(\App\Models\Employee::with('department')->latest()->take(5)->get() as $emp)
                <tr>
                    <td class="px-3 fw-semibold">{{ $emp->name }}</td>
                    <td class="text-muted">{{ $emp->department?->name ?? '—' }}</td>
                    <td>{{ $emp->salary_type === 'fixed' ? 'ثابت' : 'بالساعة' }}</td>
                    <td class="text-center">
                        <span class="badge {{ $emp->status === 'active' ? 'bg-success' : 'bg-secondary' }}">
                            {{ $emp->status === 'active' ? 'نشط' : 'غير نشط' }}
                        </span>
                    </td>
                    <td class="text-muted">{{ $emp->hire_date?->format('d/m/Y') ?? '—' }}</td>
                    <td>
                        <a href="{{ route('ledger.show', $emp) }}" class="btn btn-xs btn-sm btn-outline-info py-0 px-2 small" title="كشف الحساب">
                            <i class="fas fa-book"></i>
                        </a>
                    </td>
                </tr>
                @empty
                <tr><td colspan="6" class="text-center text-muted py-4">لا يوجد موظفون بعد</td></tr>
                @endforelse
            </tbody>
        </table>
    </div>
</div>

</div>
</x-app-layout>