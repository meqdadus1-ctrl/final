<x-app-layout>
    <x-slot name="title">تفاصيل الموظف</x-slot>

    <div class="row g-4">

        {{-- بطاقة الموظف --}}
        <div class="col-md-4">
            <div class="card text-center">
                <div class="card-body py-4">
                    <img src="{{ $employee->photo && $employee->photo !== 'default_user.png' ? Storage::url($employee->photo) : asset('images/default_user.png') }}"
                         class="rounded-circle mb-3" width="100" height="100" style="object-fit:cover;">
                    <h5 class="fw-bold mb-1">{{ $employee->name }}</h5>
                    <div class="text-muted mb-2">{{ $employee->department->name ?? '—' }}</div>
                    <span class="badge {{ $employee->status == 'active' ? 'bg-success' : 'bg-secondary' }} mb-3">
                        {{ $employee->status == 'active' ? 'نشط' : 'غير نشط' }}
                    </span>
                    <div class="d-flex gap-2 justify-content-center mt-2">
                        <a href="{{ route('employees.edit', $employee->id) }}" class="btn btn-primary btn-sm">
                            <i class="fas fa-edit me-1"></i> تعديل
                        </a>
                        <a href="{{ route('employees.index') }}" class="btn btn-secondary btn-sm">
                            <i class="fas fa-arrow-right me-1"></i> رجوع
                        </a>
                    </div>
                </div>
            </div>

            {{-- البيانات الأساسية --}}
            <div class="card mt-4">
                <div class="card-header"><i class="fas fa-info-circle me-2"></i>البيانات الأساسية</div>
                <div class="card-body">
                    <table class="table table-sm mb-0">
                        <tr>
                            <td class="text-muted">الموبايل</td>
                            <td class="fw-semibold">{{ $employee->mobile ?? '—' }}</td>
                        </tr>
                        <tr>
                            <td class="text-muted">رقم الهوية</td>
                            <td class="fw-semibold">{{ $employee->national_id ?? '—' }}</td>
                        </tr>
                        <tr>
                            <td class="text-muted">تاريخ التعيين</td>
                            <td class="fw-semibold">{{ $employee->hire_date ? $employee->hire_date->format('Y-m-d') : '—' }}</td>
                        </tr>
                        <tr>
                            <td class="text-muted">رقم البصمة</td>
                            <td class="fw-semibold">{{ $employee->fingerprint_id ?? '—' }}</td>
                        </tr>
                    </table>
                </div>
            </div>
        </div>

        {{-- التفاصيل --}}
        <div class="col-md-8">

            {{-- الراتب والدوام --}}
            <div class="card mb-4">
                <div class="card-header"><i class="fas fa-money-bill me-2"></i>الراتب والدوام</div>
                <div class="card-body">
                    <div class="row g-3">
                        <div class="col-6 col-md-3 text-center">
                            <div class="text-muted small">نوع الراتب</div>
                            <div class="fw-bold">{{ $employee->salary_type == 'fixed' ? 'ثابت' : 'بالساعة' }}</div>
                        </div>
                        <div class="col-6 col-md-3 text-center">
                            <div class="text-muted small">{{ $employee->salary_type == 'fixed' ? 'الراتب الأساسي' : 'أجر الساعة' }}</div>
                            <div class="fw-bold text-success">
                                {{ $employee->salary_type == 'fixed' ? number_format($employee->base_salary, 2) : number_format($employee->hourly_rate, 2) }}
                            </div>
                        </div>
                        <div class="col-6 col-md-3 text-center">
                            <div class="text-muted small">معدل الأوفرتايم</div>
                            <div class="fw-bold">x{{ $employee->overtime_rate }}</div>
                        </div>
                        <div class="col-6 col-md-3 text-center">
                            <div class="text-muted small">ساعات الدوام</div>
                            <div class="fw-bold">
                                {{ $employee->shift_start ? \Carbon\Carbon::parse($employee->shift_start)->format('H:i') : '—' }}
                                -
                                {{ $employee->shift_end ? \Carbon\Carbon::parse($employee->shift_end)->format('H:i') : '—' }}
                            </div>
                        </div>
                    </div>
                </div>
            </div>

            {{-- بيانات البنك --}}
            <div class="card mb-4">
                <div class="card-header"><i class="fas fa-university me-2"></i>بيانات البنك</div>
                <div class="card-body">
                    <div class="row g-3">
                        <div class="col-md-4 text-center">
                            <div class="text-muted small">البنك</div>
                            <div class="fw-bold">{{ $employee->bank->name ?? '—' }}</div>
                        </div>
                        <div class="col-md-4 text-center">
                            <div class="text-muted small">اسم صاحب الحساب</div>
                            <div class="fw-bold">{{ $employee->account_name ?? '—' }}</div>
                        </div>
                        <div class="col-md-4 text-center">
                            <div class="text-muted small">رقم الحساب</div>
                            <div class="fw-bold">{{ $employee->bank_account ?? '—' }}</div>
                        </div>
                    </div>
                </div>
            </div>

            {{-- آخر سجلات الحضور --}}
            <div class="card">
                <div class="card-header d-flex justify-content-between align-items-center">
                    <span><i class="fas fa-clock me-2"></i>آخر سجلات الحضور</span>
                    <a href="{{ route('attendance.index', ['employee_id' => $employee->id]) }}" class="btn btn-sm btn-outline-primary">
                        عرض الكل
                    </a>
                </div>
                <div class="card-body p-0">
                    <table class="table table-hover mb-0">
                        <thead>
                            <tr>
                                <th class="px-3">التاريخ</th>
                                <th>الدخول</th>
                                <th>الخروج</th>
                                <th>ساعات العمل</th>
                                <th>الحالة</th>
                            </tr>
                        </thead>
                        <tbody>
                            @forelse($employee->attendances as $rec)
                            <tr>
                                <td class="px-3">{{ $rec->date->format('Y-m-d') }}</td>
                                <td>{{ $rec->check_in  ?? '—' }}</td>
                                <td>{{ $rec->check_out ?? '—' }}</td>
                                <td>{{ $rec->work_hours ?? '0' }} س</td>
                                <td>
                                    @php
                                        $badges = [
                                            'present' => ['bg-success', 'حاضر'],
                                            'absent'  => ['bg-danger',  'غائب'],
                                            'late'    => ['bg-warning text-dark', 'متأخر'],
                                            'leave'   => ['bg-info text-dark',    'إجازة'],
                                            'holiday' => ['bg-secondary',         'عطلة'],
                                        ];
                                        $b = $badges[$rec->status] ?? ['bg-secondary', $rec->status];
                                    @endphp
                                    <span class="badge {{ $b[0] }}">{{ $b[1] }}</span>
                                </td>
                            </tr>
                            @empty
                            <tr>
                                <td colspan="5" class="text-center text-muted py-3">لا توجد سجلات حضور بعد</td>
                            </tr>
                            @endforelse
                        </tbody>
                    </table>
                </div>
            </div>

        </div>
    </div>
</x-app-layout>