{{-- resources/views/employees/edit.blade.php --}}
<x-app-layout>
    <x-slot name="title">تعديل بيانات الموظف</x-slot>
<div class="container-fluid py-4" dir="rtl">

    <div class="d-flex align-items-center gap-3 mb-4">
        <a href="{{ route('employees.profile', $employee) }}" class="btn btn-outline-secondary btn-sm">← رجوع</a>
        <div>
            <h4 class="mb-0 fw-bold">تعديل: {{ $employee->name }}</h4>
        </div>
    </div>

    @if($errors->any())
        <div class="alert alert-danger">
            <ul class="mb-0">@foreach($errors->all() as $e)<li>{{ $e }}</li>@endforeach</ul>
        </div>
    @endif

    <form action="{{ route('employees.update', $employee) }}" method="POST" enctype="multipart/form-data">
        @csrf @method('PUT')

        {{-- Tabs --}}
        <ul class="nav nav-tabs mb-4">
            <li class="nav-item"><a class="nav-link active" data-bs-toggle="tab" href="#ep-personal">👤 شخصية</a></li>
            <li class="nav-item"><a class="nav-link" data-bs-toggle="tab" href="#ep-contact">📞 تواصل</a></li>
            <li class="nav-item"><a class="nav-link" data-bs-toggle="tab" href="#ep-work">💼 وظيفية</a></li>
            <li class="nav-item"><a class="nav-link" data-bs-toggle="tab" href="#ep-edu">🎓 تعليم</a></li>
        </ul>

        <div class="tab-content mb-4">

            {{-- Personal --}}
            <div class="tab-pane fade show active" id="ep-personal">
                <div class="card shadow-sm">
                    <div class="card-body">
                        <div class="row g-3">
                            {{-- Photo --}}
                            <div class="col-12 d-flex align-items-center gap-3 mb-2">
                                <img src="{{ $employee->photo_url }}" class="rounded-circle" style="width:70px;height:70px;object-fit:cover;">
                                <div>
                                    <label class="form-label fw-semibold mb-1">تغيير الصورة</label>
                                    <input type="file" name="photo" class="form-control form-control-sm" accept="image/*">
                                    <div class="form-text">JPG, PNG, WebP — حتى 2MB</div>
                                </div>
                            </div>
                            <div class="col-md-6">
                                <label class="form-label fw-semibold">الاسم الكامل <span class="text-danger">*</span></label>
                                <input type="text" name="name" class="form-control" value="{{ old('name', $employee->name) }}" required>
                            </div>
                            <div class="col-md-6">
                                <label class="form-label fw-semibold">رقم الهوية</label>
                                <input type="text" name="national_id" class="form-control" value="{{ old('national_id', $employee->national_id) }}">
                            </div>
                            <div class="col-md-4">
                                <label class="form-label fw-semibold">تاريخ الميلاد</label>
                                <input type="date" name="birth_date" class="form-control" value="{{ old('birth_date', $employee->birth_date?->format('Y-m-d')) }}">
                            </div>
                            <div class="col-md-4">
                                <label class="form-label fw-semibold">الجنس</label>
                                <select name="gender" class="form-select">
                                    <option value="">—</option>
                                    <option value="male"   {{ old('gender',$employee->gender)=='male'   ?'selected':'' }}>ذكر</option>
                                    <option value="female" {{ old('gender',$employee->gender)=='female' ?'selected':'' }}>أنثى</option>
                                </select>
                            </div>
                            <div class="col-md-4">
                                <label class="form-label fw-semibold">الحالة الاجتماعية</label>
                                <select name="marital_status" class="form-select">
                                    <option value="">—</option>
                                    <option value="single"   {{ old('marital_status',$employee->marital_status)=='single'   ?'selected':'' }}>أعزب</option>
                                    <option value="married"  {{ old('marital_status',$employee->marital_status)=='married'  ?'selected':'' }}>متزوج</option>
                                    <option value="divorced" {{ old('marital_status',$employee->marital_status)=='divorced' ?'selected':'' }}>مطلق</option>
                                    <option value="widowed"  {{ old('marital_status',$employee->marital_status)=='widowed'  ?'selected':'' }}>أرمل</option>
                                </select>
                            </div>
                            <div class="col-md-6">
                                <label class="form-label fw-semibold">الجنسية</label>
                                <input type="text" name="nationality" class="form-control" value="{{ old('nationality', $employee->nationality) }}">
                            </div>
                            <div class="col-md-6">
                                <label class="form-label fw-semibold">الديانة</label>
                                <input type="text" name="religion" class="form-control" value="{{ old('religion', $employee->religion) }}">
                            </div>
                        </div>
                    </div>
                </div>
            </div>

            {{-- Contact --}}
            <div class="tab-pane fade" id="ep-contact">
                <div class="card shadow-sm">
                    <div class="card-body">
                        <div class="row g-3">
                            <div class="col-md-6">
                                <label class="form-label fw-semibold">البريد الإلكتروني</label>
                                <input type="email" name="email" class="form-control" value="{{ old('email', $employee->email) }}">
                            </div>
                            <div class="col-md-6">
                                <label class="form-label fw-semibold">البريد الشخصي</label>
                                <input type="email" name="personal_email" class="form-control" value="{{ old('personal_email', $employee->personal_email) }}">
                            </div>
                            <div class="col-md-6">
                                <label class="form-label fw-semibold">رقم الجوال (للتطبيق)</label>
                                <input type="text" name="mobile" class="form-control"
                                    value="{{ old('mobile', $employee->mobile) }}"
                                    placeholder="05xxxxxxxx">
                                <div class="form-text text-primary">
                                    <i class="fas fa-mobile-alt me-1"></i> يُستخدم لتسجيل الدخول في التطبيق وإرسال الإشعارات
                                </div>
                            </div>
                            <div class="col-md-6">
                                <label class="form-label fw-semibold">رقم الهاتف</label>
                                <input type="text" name="phone" class="form-control" value="{{ old('phone', $employee->phone) }}">
                            </div>
                            <div class="col-md-6">
                                <label class="form-label fw-semibold">هاتف بديل</label>
                                <input type="text" name="phone2" class="form-control" value="{{ old('phone2', $employee->phone2) }}">
                            </div>
                            <div class="col-md-8">
                                <label class="form-label fw-semibold">العنوان</label>
                                <input type="text" name="address" class="form-control" value="{{ old('address', $employee->address) }}">
                            </div>
                            <div class="col-md-4">
                                <label class="form-label fw-semibold">المدينة</label>
                                <input type="text" name="city" class="form-control" value="{{ old('city', $employee->city) }}">
                            </div>
                            <div class="col-12"><hr><h6 class="fw-semibold">🚨 جهة الطوارئ</h6></div>
                            <div class="col-md-4">
                                <label class="form-label fw-semibold">الاسم</label>
                                <input type="text" name="emergency_contact_name" class="form-control" value="{{ old('emergency_contact_name', $employee->emergency_contact_name) }}">
                            </div>
                            <div class="col-md-4">
                                <label class="form-label fw-semibold">الهاتف</label>
                                <input type="text" name="emergency_contact_phone" class="form-control" value="{{ old('emergency_contact_phone', $employee->emergency_contact_phone) }}">
                            </div>
                            <div class="col-md-4">
                                <label class="form-label fw-semibold">صلة القرابة</label>
                                <input type="text" name="emergency_contact_relation" class="form-control" value="{{ old('emergency_contact_relation', $employee->emergency_contact_relation) }}">
                            </div>
                        </div>
                    </div>
                </div>
            </div>

            {{-- Work --}}
            <div class="tab-pane fade" id="ep-work">
                <div class="card shadow-sm">
                    <div class="card-body">
                        <div class="row g-3">
                            <div class="col-md-3">
                                <label class="form-label fw-semibold">الرقم الوظيفي</label>
                                <input type="text" name="employee_number" class="form-control @error('employee_number') is-invalid @enderror"
                                       value="{{ old('employee_number', $employee->employee_number) }}" placeholder="EMP-001">
                                @error('employee_number') <div class="invalid-feedback">{{ $message }}</div> @enderror
                            </div>
                            <div class="col-md-3">
                                <label class="form-label fw-semibold">
                                    <i class="fas fa-fingerprint me-1"></i> رقم البصمة <span class="text-danger">*</span>
                                </label>
                                <input type="number" name="fingerprint_id" min="1"
                                       class="form-control @error('fingerprint_id') is-invalid @enderror"
                                       value="{{ old('fingerprint_id', $employee->fingerprint_id) }}"
                                       placeholder="مثال: 875" required>
                                @error('fingerprint_id') <div class="invalid-feedback">{{ $message }}</div> @enderror
                                <small class="text-muted">الرقم المخزّن على جهاز البصمة</small>
                            </div>
                            <div class="col-md-6">
                                <label class="form-label fw-semibold">المسمى الوظيفي</label>
                                <input type="text" name="job_title" class="form-control" value="{{ old('job_title', $employee->job_title) }}">
                            </div>
                            <div class="col-md-6">
                                <label class="form-label fw-semibold">القسم <span class="text-danger">*</span></label>
                                <select name="department_id" class="form-select" required>
                                    <option value="">—</option>
                                    @foreach($departments as $dept)
                                    <option value="{{ $dept->id }}" {{ old('department_id',$employee->department_id)==$dept->id?'selected':'' }}>{{ $dept->name }}</option>
                                    @endforeach
                                </select>
                            </div>
                            <div class="col-md-6">
                                <label class="form-label fw-semibold">المدير المباشر</label>
                                <select name="manager_id" class="form-select">
                                    <option value="">—</option>
                                    @foreach($managers as $mgr)
                                    <option value="{{ $mgr->id }}" {{ old('manager_id',$employee->manager_id)==$mgr->id?'selected':'' }}>{{ $mgr->name }}</option>
                                    @endforeach
                                </select>
                            </div>
                            <div class="col-md-6">
                                <label class="form-label fw-semibold">تاريخ التعيين</label>
                                <input type="date" name="hire_date" class="form-control" value="{{ old('hire_date', $employee->hire_date?->format('Y-m-d')) }}">
                            </div>
                            <div class="col-md-6">
                                <label class="form-label fw-semibold">موقع العمل</label>
                                <input type="text" name="work_location" class="form-control" value="{{ old('work_location', $employee->work_location) }}">
                            </div>
                            <div class="col-md-4">
                                <label class="form-label fw-semibold">نوع العقد</label>
                                <select name="contract_type" class="form-select">
                                    <option value="permanent" {{ old('contract_type',$employee->contract_type)=='permanent'?'selected':'' }}>دائم</option>
                                    <option value="temporary" {{ old('contract_type',$employee->contract_type)=='temporary'?'selected':'' }}>مؤقت</option>
                                    <option value="part_time" {{ old('contract_type',$employee->contract_type)=='part_time'?'selected':'' }}>دوام جزئي</option>
                                    <option value="freelance" {{ old('contract_type',$employee->contract_type)=='freelance'?'selected':'' }}>مستقل</option>
                                </select>
                            </div>
                            <div class="col-md-4">
                                <label class="form-label fw-semibold">بداية العقد</label>
                                <input type="date" name="contract_start" class="form-control" value="{{ old('contract_start', $employee->contract_start?->format('Y-m-d')) }}">
                            </div>
                            <div class="col-md-4">
                                <label class="form-label fw-semibold">نهاية العقد</label>
                                <input type="date" name="contract_end" class="form-control" value="{{ old('contract_end', $employee->contract_end?->format('Y-m-d')) }}">
                                <div class="form-text">اتركه فارغاً إذا كان دائماً</div>
                            </div>
                            <div class="col-md-4">
                                <label class="form-label fw-semibold">نوع الراتب <span class="text-danger">*</span></label>
                                <select name="salary_type" class="form-select @error('salary_type') is-invalid @enderror" id="salaryTypeEdit" required>
                                    <option value="fixed"  {{ old('salary_type', $employee->salary_type) == 'fixed'  ? 'selected' : '' }}>ثابت</option>
                                    <option value="hourly" {{ old('salary_type', $employee->salary_type) == 'hourly' ? 'selected' : '' }}>بالساعة</option>
                                </select>
                                @error('salary_type') <div class="invalid-feedback">{{ $message }}</div> @enderror
                            </div>
                            <div class="col-md-4" id="baseSalaryFieldEdit">
                                <label class="form-label fw-semibold">الراتب الأساسي</label>
                                <input type="number" step="0.01" name="base_salary" min="0" class="form-control @error('base_salary') is-invalid @enderror" value="{{ old('base_salary', $employee->base_salary) }}">
                                @error('base_salary') <div class="invalid-feedback">{{ $message }}</div> @enderror
                            </div>
                            <div class="col-md-4" id="hourlyRateFieldEdit" style="display:none;">
                                <label class="form-label fw-semibold">أجر الساعة <span class="text-danger" id="hourlyRateRequiredEdit" style="display:none;">*</span></label>
                                <input type="number" step="0.01" name="hourly_rate" min="0" class="form-control @error('hourly_rate') is-invalid @enderror" value="{{ old('hourly_rate', $employee->hourly_rate) }}" id="hourlyRateInputEdit">
                                @error('hourly_rate') <div class="invalid-feedback">{{ $message }}</div> @enderror
                            </div>
                            <div class="col-md-4">
                                <label class="form-label fw-semibold">الحالة</label>
                                <select name="status" class="form-select">
                                    <option value="active"   {{ old('status',$employee->status)=='active'   ?'selected':'' }}>نشط</option>
                                    <option value="inactive" {{ old('status',$employee->status)=='inactive' ?'selected':'' }}>غير نشط</option>
                                </select>
                            </div>
                            <div class="col-12"><hr><h6 class="fw-semibold">⏰ وقت الدوام والأوفرتايم</h6></div>
                            <div class="col-md-3">
                                <label class="form-label fw-semibold">بداية الدوام</label>
                                <input type="time" name="shift_start" class="form-control"
                                    value="{{ old('shift_start', $employee->shift_start ? \Carbon\Carbon::parse($employee->shift_start)->format('H:i') : '') }}">
                                <div class="form-text">مثال: 08:00</div>
                            </div>
                            <div class="col-md-3">
                                <label class="form-label fw-semibold">نهاية الدوام</label>
                                <input type="time" name="shift_end" class="form-control"
                                    value="{{ old('shift_end', $employee->shift_end ? \Carbon\Carbon::parse($employee->shift_end)->format('H:i') : '') }}">
                                <div class="form-text">مثال: 17:00</div>
                            </div>
                            <div class="col-md-3">
                                <label class="form-label fw-semibold">معامل الأوفرتايم</label>
                                <select name="overtime_rate" class="form-select">
                                    <option value="1"   {{ old('overtime_rate', $employee->overtime_rate) == '1'   ? 'selected' : '' }}>× 1.0 — عادي (افتراضي)</option>
                                    <option value="1.25"{{ old('overtime_rate', $employee->overtime_rate) == '1.25'? 'selected' : '' }}>× 1.25</option>
                                    <option value="1.5" {{ old('overtime_rate', $employee->overtime_rate) == '1.5' ? 'selected' : '' }}>× 1.5</option>
                                    <option value="2"   {{ old('overtime_rate', $employee->overtime_rate) == '2'   ? 'selected' : '' }}>× 2.0 (مضاعف)</option>
                                </select>
                                <div class="form-text">مضاعف أجر ساعة الأوفرتايم</div>
                            </div>
                            <div class="col-md-3 d-flex align-items-center pt-3">
                                <div id="shift_summary" class="alert alert-info py-2 px-3 mb-0 small w-100" style="display:none!important">
                                    <i class="fas fa-clock me-1"></i>
                                    <span id="shift_hours_text">—</span>
                                </div>
                            </div>
                            <div class="col-12">
                                <label class="form-label fw-semibold">ملاحظات</label>
                                <textarea name="notes" class="form-control" rows="3">{{ old('notes', $employee->notes) }}</textarea>
                            </div>
                        </div>
                    </div>
                </div>
            </div>

            {{-- Education --}}
            <div class="tab-pane fade" id="ep-edu">
                <div class="card shadow-sm">
                    <div class="card-body">
                        <div class="row g-3">
                            <div class="col-md-6">
                                <label class="form-label fw-semibold">المؤهل العلمي</label>
                                <input type="text" name="education_level" class="form-control" value="{{ old('education_level', $employee->education_level) }}" placeholder="بكالوريوس، ماجستير...">
                            </div>
                            <div class="col-md-6">
                                <label class="form-label fw-semibold">التخصص</label>
                                <input type="text" name="education_major" class="form-control" value="{{ old('education_major', $employee->education_major) }}">
                            </div>
                            <div class="col-md-8">
                                <label class="form-label fw-semibold">الجامعة / المؤسسة</label>
                                <input type="text" name="university" class="form-control" value="{{ old('university', $employee->university) }}">
                            </div>
                            <div class="col-md-4">
                                <label class="form-label fw-semibold">سنة التخرج</label>
                                <input type="number" name="graduation_year" class="form-control" min="1970" max="{{ date('Y')+1 }}" value="{{ old('graduation_year', $employee->graduation_year) }}">
                            </div>
                        </div>
                    </div>
                </div>
            </div>

        </div>

        <div class="d-flex justify-content-end gap-2">
            <a href="{{ route('employees.profile', $employee) }}" class="btn btn-secondary">إلغاء</a>
            <button type="submit" class="btn btn-primary px-5">💾 حفظ التغييرات</button>
        </div>

    </form>
</div>

<script>
    const salaryTypeEdit           = document.getElementById('salaryTypeEdit');
    const baseSalaryFieldEdit      = document.getElementById('baseSalaryFieldEdit');
    const hourlyRateFieldEdit      = document.getElementById('hourlyRateFieldEdit');
    const hourlyRateInputEdit      = document.getElementById('hourlyRateInputEdit');
    const hourlyRateRequiredEdit   = document.getElementById('hourlyRateRequiredEdit');

    function toggleSalaryEdit() {
        if (salaryTypeEdit.value === 'hourly') {
            baseSalaryFieldEdit.style.display = 'none';
            hourlyRateFieldEdit.style.display = 'block';
            hourlyRateRequiredEdit.style.display = 'inline';
            hourlyRateInputEdit.setAttribute('required', 'required');
        } else {
            baseSalaryFieldEdit.style.display = 'block';
            hourlyRateFieldEdit.style.display = 'none';
            hourlyRateRequiredEdit.style.display = 'none';
            hourlyRateInputEdit.removeAttribute('required');
        }
    }

    salaryTypeEdit.addEventListener('change', toggleSalaryEdit);
    toggleSalaryEdit();

    // حساب ساعات الدوام تلقائياً
    const shiftStart = document.querySelector('input[name="shift_start"]');
    const shiftEnd   = document.querySelector('input[name="shift_end"]');
    const shiftSummary = document.getElementById('shift_summary');
    const shiftHoursText = document.getElementById('shift_hours_text');

    function calcShiftHours() {
        if (shiftStart.value && shiftEnd.value) {
            const [sh, sm] = shiftStart.value.split(':').map(Number);
            const [eh, em] = shiftEnd.value.split(':').map(Number);
            const totalMins = (eh * 60 + em) - (sh * 60 + sm);
            if (totalMins > 0) {
                const h = Math.floor(totalMins / 60);
                const m = totalMins % 60;
                shiftHoursText.textContent = `مدة الوردية: ${h} ساعة${m > 0 ? ' و' + m + ' دقيقة' : ''}`;
                shiftSummary.style.setProperty('display', 'block', 'important');
            } else {
                shiftSummary.style.setProperty('display', 'none', 'important');
            }
        } else {
            shiftSummary.style.setProperty('display', 'none', 'important');
        }
    }

    shiftStart.addEventListener('change', calcShiftHours);
    shiftEnd.addEventListener('change', calcShiftHours);
    calcShiftHours();
</script>
</x-app-layout>
