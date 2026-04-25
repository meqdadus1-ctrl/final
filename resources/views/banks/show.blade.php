<x-app-layout>
    <x-slot name="title">تفاصيل البنك</x-slot>

    <div class="card mb-4">
        <div class="card-header d-flex justify-content-between align-items-center">
            <span><i class="fas fa-university me-2"></i> {{ $bank->name }}</span>
            <div class="d-flex gap-2">
                <a href="{{ route('banks.edit', $bank->id) }}" class="btn btn-sm btn-outline-primary">
                    <i class="fas fa-edit me-1"></i> تعديل
                </a>
                <a href="{{ route('banks.index') }}" class="btn btn-sm btn-secondary">
                    <i class="fas fa-arrow-right me-1"></i> رجوع
                </a>
            </div>
        </div>
        <div class="card-body">
            <div class="row g-3">
                <div class="col-md-4">
                    <div class="text-muted small">الاسم</div>
                    <div class="fw-semibold">{{ $bank->name }}</div>
                </div>
                <div class="col-md-4">
                    <div class="text-muted small">اسم البنك الرسمي</div>
                    <div class="fw-semibold">{{ $bank->bank_name ?? '—' }}</div>
                </div>
                <div class="col-md-4">
                    <div class="text-muted small">الفرع</div>
                    <div class="fw-semibold">{{ $bank->branch ?? '—' }}</div>
                </div>
                <div class="col-md-4">
                    <div class="text-muted small">رمز السويفت</div>
                    <div class="fw-semibold"><code>{{ $bank->swift_code ?? '—' }}</code></div>
                </div>
                <div class="col-12">
                    <div class="text-muted small">ملاحظات</div>
                    <div>{{ $bank->notes ?? '—' }}</div>
                </div>
            </div>
        </div>
    </div>

    <div class="card">
        <div class="card-header">
            <i class="fas fa-users me-2"></i> الموظفون المرتبطون بالبنك ({{ $bank->employees->count() }})
        </div>
        <div class="card-body p-0">
            <table class="table table-hover mb-0">
                <thead>
                    <tr>
                        <th class="px-4">#</th>
                        <th>الاسم</th>
                        <th>الرقم الوظيفي</th>
                        <th>رقم الحساب</th>
                        <th>اسم صاحب الحساب</th>
                    </tr>
                </thead>
                <tbody>
                    @forelse($bank->employees as $emp)
                    <tr>
                        <td class="px-4">{{ $loop->iteration }}</td>
                        <td class="fw-semibold">
                            <a href="{{ route('employees.show', $emp->id) }}">{{ $emp->name }}</a>
                        </td>
                        <td>{{ $emp->employee_number ?? '—' }}</td>
                        <td><code>{{ $emp->bank_account ?? '—' }}</code></td>
                        <td>{{ $emp->account_name ?? '—' }}</td>
                    </tr>
                    @empty
                    <tr>
                        <td colspan="5" class="text-center text-muted py-4">لا يوجد موظفون مرتبطون بهذا البنك</td>
                    </tr>
                    @endforelse
                </tbody>
            </table>
        </div>
    </div>
</x-app-layout>
