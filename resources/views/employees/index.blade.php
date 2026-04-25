<x-app-layout>
    <x-slot name="title">الموظفون</x-slot>

    {{-- فلاتر البحث --}}
    <div class="card mb-4">
        <div class="card-body">
            <form method="GET" action="{{ route('employees.index') }}" class="row g-3 align-items-end">
                <div class="col-md-4">
                    <label class="form-label">بحث</label>
                    <input type="text" name="search" class="form-control"
                           placeholder="الاسم، الموبايل، الهوية، رقم البصمة، الرقم الوظيفي..."
                           value="{{ request('search') }}">
                </div>
                <div class="col-md-3">
                    <label class="form-label">القسم</label>
                    <select name="department_id" class="form-select">
                        <option value="">-- كل الأقسام --</option>
                        @foreach($departments as $dep)
                            <option value="{{ $dep->id }}" {{ request('department_id') == $dep->id ? 'selected' : '' }}>
                                {{ $dep->name }}
                            </option>
                        @endforeach
                    </select>
                </div>
                <div class="col-md-2">
                    <label class="form-label">الحالة</label>
                    <select name="status" class="form-select">
                        <option value="">-- الكل --</option>
                        <option value="active"   {{ request('status') == 'active'   ? 'selected' : '' }}>نشط</option>
                        <option value="inactive" {{ request('status') == 'inactive' ? 'selected' : '' }}>غير نشط</option>
                    </select>
                </div>
                <div class="col-md-3 d-flex gap-2">
                    <button type="submit" class="btn btn-primary flex-fill">
                        <i class="fas fa-search me-1"></i> بحث
                    </button>
                    <a href="{{ route('employees.index') }}" class="btn btn-secondary flex-fill">
                        <i class="fas fa-times me-1"></i> إلغاء
                    </a>
                </div>
            </form>
        </div>
    </div>

    {{-- جدول الموظفين --}}
    <div class="card">
        <div class="card-header d-flex justify-content-between align-items-center">
            <span><i class="fas fa-users me-2"></i> قائمة الموظفين ({{ $employees->total() }})</span>
            <a href="{{ route('employees.create') }}" class="btn btn-primary btn-sm">
                <i class="fas fa-plus me-1"></i> إضافة موظف
            </a>
        </div>
        <div class="card-body p-0">
            <table class="table table-hover mb-0">
                <thead>
                    <tr>
                        <th class="px-4">الموظف</th>
                        <th>رقم البصمة</th>
                        <th>الرقم الوظيفي</th>
                        <th>القسم</th>
                        <th>الموبايل</th>
                        <th>نوع الراتب</th>
                        <th>الراتب</th>
                        <th>الحالة</th>
                        <th>إجراءات</th>
                    </tr>
                </thead>
                <tbody>
                    @forelse($employees as $emp)
                    <tr>
                        <td class="px-4">
                            <div class="d-flex align-items-center gap-2">
                                <img src="{{ $emp->photo && $emp->photo !== 'default_user.png' ? Storage::url($emp->photo) : asset('images/default_user.png') }}"
                                     class="rounded-circle" width="38" height="38" style="object-fit:cover;">
                                <div>
                                    <a href="{{ route('employees.profile', $emp->id) }}"
                                       class="fw-semibold text-decoration-none text-dark">{{ $emp->name }}</a>
                                    <div class="text-muted small">{{ $emp->national_id ?? '' }}</div>
                                </div>
                            </div>
                        </td>
                        <td>
                            @if($emp->fingerprint_id)
                                <span class="badge bg-dark">
                                    <i class="fas fa-fingerprint me-1"></i>{{ $emp->fingerprint_id }}
                                </span>
                            @else
                                <span class="text-muted small">—</span>
                            @endif
                        </td>
                        <td>
                            {{ $emp->employee_number ?? '—' }}
                        </td>
                        <td>{{ $emp->department->name ?? '—' }}</td>
                        <td>{{ $emp->mobile ?? '—' }}</td>
                        <td>{{ $emp->salary_type == 'fixed' ? 'ثابت' : 'بالساعة' }}</td>
                        <td>
                            @if($emp->salary_type == 'fixed')
                                {{ number_format($emp->base_salary, 2) }}
                            @else
                                {{ number_format($emp->hourly_rate, 2) }}/س
                            @endif
                        </td>
                        <td>
                            <span class="badge {{ $emp->status == 'active' ? 'bg-success' : 'bg-secondary' }}">
                                {{ $emp->status == 'active' ? 'نشط' : 'غير نشط' }}
                            </span>
                        </td>
                        <td>
                            <a href="{{ route('employees.profile', $emp->id) }}" class="btn btn-sm btn-primary" title="الملف الكامل">
                                <i class="fas fa-id-card"></i>
                            </a>
                            <a href="{{ route('employees.show', $emp->id) }}" class="btn btn-sm btn-outline-info" title="عرض مختصر">
                                <i class="fas fa-eye"></i>
                            </a>
                            <a href="{{ route('employees.edit', $emp->id) }}" class="btn btn-sm btn-outline-primary" title="تعديل">
                                <i class="fas fa-edit"></i>
                            </a>
                            <form action="{{ route('employees.destroy', $emp->id) }}" method="POST" class="d-inline"
                                  onsubmit="return confirm('هل أنت متأكد من حذف الموظف؟')">
                                @csrf @method('DELETE')
                                <button class="btn btn-sm btn-outline-danger" title="حذف">
                                    <i class="fas fa-trash"></i>
                                </button>
                            </form>
                        </td>
                    </tr>
                    @empty
                    <tr>
                        <td colspan="9" class="text-center text-muted py-4">لا يوجد موظفون بعد</td>
                    </tr>
                    @endforelse
                </tbody>
            </table>
        </div>
        @if($employees->hasPages())
        <div class="card-footer">
            {{ $employees->links() }}
        </div>
        @endif
    </div>
</x-app-layout>