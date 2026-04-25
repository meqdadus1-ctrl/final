<x-app-layout>
<x-slot name="title">قسم {{ $department->name }}</x-slot>
<div class="container-fluid py-4" dir="rtl">

    {{-- Header --}}
    <div class="d-flex justify-content-between align-items-center mb-4">
        <div>
            <h4 class="mb-0 fw-bold">🏢 {{ $department->name }}</h4>
            <small class="text-muted">{{ $department->description ?? 'بيانات القسم وموظفيه' }}</small>
        </div>
        <div class="d-flex gap-2">
            <a href="{{ route('departments.edit', $department->id) }}" class="btn btn-outline-primary btn-sm">
                <i class="fas fa-edit me-1"></i> تعديل
            </a>
            <a href="{{ route('departments.index') }}" class="btn btn-outline-secondary btn-sm">
                <i class="fas fa-arrow-right me-1"></i> رجوع
            </a>
        </div>
    </div>

    {{-- Stats --}}
    @php
        $activeEmps   = $department->employees->where('status','active');
        $inactiveEmps = $department->employees->where('status','inactive');
        $withLoans    = $department->employees->filter(fn($e) => $e->activeLoan !== null);
        $totalBalance = $department->employees->sum(fn($e) => $e->ledger_balance);
    @endphp
    <div class="row g-3 mb-4">
        <div class="col-md-3">
            <div class="card border-0 shadow-sm text-center py-3">
                <div class="fs-2 fw-bold text-primary">{{ $department->employees_count }}</div>
                <div class="text-muted small">إجمالي الموظفين</div>
            </div>
        </div>
        <div class="col-md-3">
            <div class="card border-0 shadow-sm text-center py-3">
                <div class="fs-2 fw-bold text-success">{{ $activeEmps->count() }}</div>
                <div class="text-muted small">نشطون</div>
            </div>
        </div>
        <div class="col-md-3">
            <div class="card border-0 shadow-sm text-center py-3">
                <div class="fs-2 fw-bold text-warning">{{ $withLoans->count() }}</div>
                <div class="text-muted small">لديهم سلف نشطة</div>
            </div>
        </div>
        <div class="col-md-3">
            <div class="card border-0 shadow-sm text-center py-3">
                <div class="fs-2 fw-bold {{ $totalBalance >= 0 ? 'text-success' : 'text-danger' }}">
                    {{ number_format(abs($totalBalance), 0) }} ₪
                </div>
                <div class="text-muted small">إجمالي الأرصدة</div>
            </div>
        </div>
    </div>

    {{-- جدول الموظفين --}}
    <div class="card shadow-sm">
        <div class="card-header d-flex justify-content-between align-items-center py-2">
            <span class="fw-semibold"><i class="fas fa-users me-2"></i>موظفو القسم</span>
            <a href="{{ route('employees.create') }}" class="btn btn-sm btn-primary">
                <i class="fas fa-plus me-1"></i> موظف جديد
            </a>
        </div>
        <div class="card-body p-0">
            <div class="table-responsive">
                <table class="table table-hover align-middle mb-0">
                    <thead class="table-dark">
                        <tr>
                            <th class="px-3">#</th>
                            <th>الموظف</th>
                            <th>المسمى الوظيفي</th>
                            <th class="text-center">نوع الراتب</th>
                            <th class="text-center">الحالة</th>
                            <th class="text-center">سلفة</th>
                            <th class="text-end px-3">الرصيد</th>
                            <th class="text-center">إجراءات</th>
                        </tr>
                    </thead>
                    <tbody>
                        @forelse($department->employees as $emp)
                        @php $bal = $emp->ledger_balance; @endphp
                        <tr>
                            <td class="px-3 text-muted small">{{ $loop->iteration }}</td>
                            <td>
                                <div class="fw-semibold">{{ $emp->name }}</div>
                                <small class="text-muted">{{ $emp->employee_number ?? $emp->phone ?? '—' }}</small>
                            </td>
                            <td class="small text-muted">{{ $emp->job_title ?? '—' }}</td>
                            <td class="text-center">
                                <span class="badge {{ $emp->salary_type === 'hourly' ? 'bg-info text-dark' : 'bg-secondary' }}">
                                    {{ $emp->salary_type === 'hourly' ? 'بالساعة' : 'ثابت' }}
                                </span>
                            </td>
                            <td class="text-center">
                                <span class="badge {{ $emp->status === 'active' ? 'bg-success' : 'bg-secondary' }}">
                                    {{ $emp->status === 'active' ? 'نشط' : 'غير نشط' }}
                                </span>
                            </td>
                            <td class="text-center">
                                @if($emp->activeLoan)
                                    <span class="badge bg-warning text-dark" title="المتبقي: {{ number_format($emp->activeLoan->remaining_amount, 2) }} ₪">
                                        💳 {{ number_format($emp->activeLoan->remaining_amount, 0) }} ₪
                                    </span>
                                @else
                                    <span class="text-muted small">—</span>
                                @endif
                            </td>
                            <td class="text-end px-3 fw-bold {{ $bal >= 0 ? 'text-success' : 'text-danger' }}">
                                {{ number_format($bal, 2) }} ₪
                            </td>
                            <td class="text-center">
                                <div class="d-flex gap-1 justify-content-center">
                                    <a href="{{ route('employees.profile', $emp->id) }}"
                                       class="btn btn-sm btn-outline-primary" title="البروفايل">
                                        <i class="fas fa-user"></i>
                                    </a>
                                    <a href="{{ route('ledger.show', $emp->id) }}"
                                       class="btn btn-sm btn-outline-info" title="كشف الحساب">
                                        <i class="fas fa-book"></i>
                                    </a>
                                    <a href="{{ route('salary.create') }}?employee_id={{ $emp->id }}"
                                       class="btn btn-sm btn-outline-success" title="احتساب راتب">
                                        <i class="fas fa-calculator"></i>
                                    </a>
                                </div>
                            </td>
                        </tr>
                        @empty
                        <tr>
                            <td colspan="8" class="text-center py-5 text-muted">
                                <i class="fas fa-users fa-2x mb-2 d-block opacity-25"></i>
                                لا يوجد موظفون في هذا القسم
                            </td>
                        </tr>
                        @endforelse
                    </tbody>
                </table>
            </div>
        </div>
    </div>

</div>
</x-app-layout>
