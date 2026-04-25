<x-app-layout>
    <x-slot name="title">إضافة موظف جديد</x-slot>

    <div class="card">
        <div class="card-header">
            <i class="fas fa-user-plus me-2"></i> إضافة موظف جديد
        </div>
        <div class="card-body">
            <form action="{{ route('employees.store') }}" method="POST" enctype="multipart/form-data">
                @csrf

                {{-- القسم الأول: البيانات الأساسية --}}
                <h6 class="fw-bold text-primary mb-3 mt-2"><i class="fas fa-user me-2"></i>البيانات الأساسية</h6>
                <div class="row g-3">

                    <div class="col-md-6">
                        <label class="form-label fw-semibold">الاسم الكامل <span class="text-danger">*</span></label>
                        <input type="text" name="name" class="form-control @error('name') is-invalid @enderror"
                               value="{{ old('name') }}" required>
                        @error('name') <div class="invalid-feedback">{{ $message }}</div> @enderror
                    </div>

                    <div class="col-md-6">
                        <label class="form-label fw-semibold">رقم الهوية</label>
                        <input type="text" name="national_id" class="form-control @error('national_id') is-invalid @enderror"
                               value="{{ old('national_id') }}">
                        @error('national_id') <div class="invalid-feedback">{{ $message }}</div> @enderror
                    </div>

                    <div class="col-md-6">
                        <label class="form-label fw-semibold">الموبايل</label>
                        <input type="text" name="mobile" class="form-control @error('mobile') is-invalid @enderror"
                               value="{{ old('mobile') }}">
                        @error('mobile') <div class="invalid-feedback">{{ $message }}</div> @enderror
                    </div>

                    <div class="col-md-6">
                        <label class="form-label fw-semibold">القسم <span class="text-danger">*</span></label>
                        <select name="department_id" class="form-select @error('department_id') is-invalid @enderror" required>
                            <option value="">-- اختر القسم --</option>
                            @foreach($departments as $dep)
                                <option value="{{ $dep->id }}" {{ old('department_id') == $dep->id ? 'selected' : '' }}>
                                    {{ $dep->name }}
                                </option>
                            @endforeach
                        </select>
                        @error('department_id') <div class="invalid-feedback">{{ $message }}</div> @enderror
                    </div>

                    <div class="col-md-6">
                        <label class="form-label fw-semibold">تاريخ التعيين</label>
                        <input type="date" name="hire_date" class="form-control @error('hire_date') is-invalid @enderror"
                               value="{{ old('hire_date') }}">
                        @error('hire_date') <div class="invalid-feedback">{{ $message }}</div> @enderror
                    </div>

                    <div class="col-md-6">
                        <label class="form-label fw-semibold">الحالة <span class="text-danger">*</span></label>
                        <select name="status" class="form-select @error('status') is-invalid @enderror">
                            <option value="active"   {{ old('status') == 'active'   ? 'selected' : '' }}>نشط</option>
                            <option value="inactive" {{ old('status') == 'inactive' ? 'selected' : '' }}>غير نشط</option>
                        </select>
                        @error('status') <div class="invalid-feedback">{{ $message }}</div> @enderror
                    </div>

                    <div class="col-md-6">
                        <label class="form-label fw-semibold">
                            <i class="fas fa-fingerprint me-1"></i> رقم البصمة (Fingerprint ID) <span class="text-danger">*</span>
                        </label>
                        <input type="number" name="fingerprint_id" min="1"
                               class="form-control @error('fingerprint_id') is-invalid @enderror"
                               value="{{ old('fingerprint_id') }}" placeholder="مثال: 875" required>
                        @error('fingerprint_id') <div class="invalid-feedback">{{ $message }}</div> @enderror
                        <small class="text-muted">الرقم المخزّن على جهاز البصمة</small>
                    </div>

                    <div class="col-md-6">
                        <label class="form-label fw-semibold">الرقم الوظيفي</label>
                        <input type="text" name="employee_number"
                               class="form-control @error('employee_number') is-invalid @enderror"
                               value="{{ old('employee_number') }}" placeholder="EMP-001">
                        @error('employee_number') <div class="invalid-feedback">{{ $message }}</div> @enderror
                    </div>

                    <div class="col-md-12">
                        <label class="form-label fw-semibold">الصورة الشخصية</label>
                        <input type="file" name="photo" class="form-control @error('photo') is-invalid @enderror" accept="image/*">
                        @error('photo') <div class="invalid-feedback">{{ $message }}</div> @enderror
                    </div>

                </div>

                <hr class="my-4">

                {{-- القسم الثاني: الراتب والدوام --}}
                <h6 class="fw-bold text-primary mb-3"><i class="fas fa-money-bill me-2"></i>الراتب والدوام</h6>
                <div class="row g-3">

                    <div class="col-md-6">
                        <label class="form-label fw-semibold">نوع الراتب <span class="text-danger">*</span></label>
                        <select name="salary_type" class="form-select @error('salary_type') is-invalid @enderror" id="salaryType">
                            <option value="fixed"  {{ old('salary_type') == 'fixed'  ? 'selected' : '' }}>ثابت</option>
                            <option value="hourly" {{ old('salary_type') == 'hourly' ? 'selected' : '' }}>بالساعة</option>
                        </select>
                        @error('salary_type') <div class="invalid-feedback">{{ $message }}</div> @enderror
                    </div>

                    <div class="col-md-6" id="baseSalaryField">
                        <label class="form-label fw-semibold">الراتب الأساسي</label>
                        <input type="number" step="0.01" name="base_salary" class="form-control @error('base_salary') is-invalid @enderror"
                               value="{{ old('base_salary', 0) }}">
                        @error('base_salary') <div class="invalid-feedback">{{ $message }}</div> @enderror
                    </div>

                    <div class="col-md-6" id="hourlyRateField" style="display:none;">
                        <label class="form-label fw-semibold">أجر الساعة <span class="text-danger" id="hourlyRateRequired" style="display:none;">*</span></label>
                        <input type="number" step="0.01" name="hourly_rate" class="form-control @error('hourly_rate') is-invalid @enderror"
                               value="{{ old('hourly_rate', 0) }}" id="hourlyRateInput">
                        @error('hourly_rate') <div class="invalid-feedback">{{ $message }}</div> @enderror
                    </div>

                    <div class="col-md-6">
                        <label class="form-label fw-semibold">معدل الأوفرتايم (x)</label>
                        <input type="number" step="0.01" name="overtime_rate" class="form-control @error('overtime_rate') is-invalid @enderror"
                               value="{{ old('overtime_rate', 1.5) }}">
                        @error('overtime_rate') <div class="invalid-feedback">{{ $message }}</div> @enderror
                    </div>

                    <div class="col-md-6">
                        <label class="form-label fw-semibold">بداية الدوام</label>
                        <input type="time" name="shift_start" class="form-control @error('shift_start') is-invalid @enderror"
                               value="{{ old('shift_start', '08:00') }}">
                        @error('shift_start') <div class="invalid-feedback">{{ $message }}</div> @enderror
                    </div>

                    <div class="col-md-6">
                        <label class="form-label fw-semibold">نهاية الدوام</label>
                        <input type="time" name="shift_end" class="form-control @error('shift_end') is-invalid @enderror"
                               value="{{ old('shift_end', '16:00') }}">
                        @error('shift_end') <div class="invalid-feedback">{{ $message }}</div> @enderror
                    </div>

                </div>

                <hr class="my-4">

                {{-- القسم الثالث: بيانات البنك --}}
                <h6 class="fw-bold text-primary mb-3"><i class="fas fa-university me-2"></i>بيانات البنك</h6>
                <div class="row g-3">

                    <div class="col-md-6">
                        <label class="form-label fw-semibold">البنك</label>
                        <select name="bank_id" class="form-select @error('bank_id') is-invalid @enderror">
                            <option value="">-- اختر البنك --</option>
                            @foreach($banks as $bank)
                                <option value="{{ $bank->id }}" {{ old('bank_id') == $bank->id ? 'selected' : '' }}>
                                    {{ $bank->name }}
                                </option>
                            @endforeach
                        </select>
                        @error('bank_id') <div class="invalid-feedback">{{ $message }}</div> @enderror
                    </div>

                    <div class="col-md-6">
                        <label class="form-label fw-semibold">اسم صاحب الحساب</label>
                        <input type="text" name="account_name" class="form-control @error('account_name') is-invalid @enderror"
                               value="{{ old('account_name') }}">
                        @error('account_name') <div class="invalid-feedback">{{ $message }}</div> @enderror
                    </div>

                    <div class="col-md-6">
                        <label class="form-label fw-semibold">رقم الحساب</label>
                        <input type="text" name="bank_account" class="form-control @error('bank_account') is-invalid @enderror"
                               value="{{ old('bank_account') }}">
                        @error('bank_account') <div class="invalid-feedback">{{ $message }}</div> @enderror
                    </div>

                </div>

                {{-- أزرار --}}
                <div class="d-flex gap-2 mt-4">
                    <button type="submit" class="btn btn-primary">
                        <i class="fas fa-save me-1"></i> حفظ الموظف
                    </button>
                    <a href="{{ route('employees.index') }}" class="btn btn-secondary">
                        <i class="fas fa-arrow-right me-1"></i> رجوع
                    </a>
                </div>

            </form>
        </div>
    </div>

    <script>
        const salaryType           = document.getElementById('salaryType');
        const baseSalaryField      = document.getElementById('baseSalaryField');
        const hourlyRateField      = document.getElementById('hourlyRateField');
        const hourlyRateInput      = document.getElementById('hourlyRateInput');
        const hourlyRateRequired   = document.getElementById('hourlyRateRequired');

        function toggleSalary() {
            if (salaryType.value === 'hourly') {
                baseSalaryField.style.display = 'none';
                hourlyRateField.style.display = 'block';
                hourlyRateRequired.style.display = 'inline';
                hourlyRateInput.setAttribute('required', 'required');
            } else {
                baseSalaryField.style.display = 'block';
                hourlyRateField.style.display = 'none';
                hourlyRateRequired.style.display = 'none';
                hourlyRateInput.removeAttribute('required');
            }
        }

        salaryType.addEventListener('change', toggleSalary);
        toggleSalary();
    </script>
</x-app-layout>