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
                            <div class="col-md-4">
                                <label class="form-label fw-semibold">رقم الموظف</label>
                                <input type="text" name="employee_number" class="form-control" value="{{ old('employee_number', $employee->employee_number) }}">
                            </div>
                            <div class="col-md-8">
                                <label class="form-label fw-semibold">المسمى الوظيفي</label>
                                <input type="text" name="job_title" class="form-control" value="{{ old('job_title', $employee->job_title) }}">
                            </div>
                            <div class="col-md-6">
                                <label class="form-label fw-semibold">القسم</label>
                                <select name="department_id" class="form-select">
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
                                <label class="form-label fw-semibold">الراتب الأساسي</label>
                                <input type="number" name="salary" step="0.01" min="0" class="form-control" value="{{ old('salary', $employee->salary) }}">
                            </div>
                            <div class="col-md-4">
                                <label class="form-label fw-semibold">نوع الراتب</label>
                                <input type="text" name="salary_type" class="form-control" value="{{ old('salary_type', $employee->salary_type) }}" placeholder="شهري / بالساعة">
                            </div>
                            <div class="col-md-4">
                                <label class="form-label fw-semibold">الحالة</label>
                                <select name="status" class="form-select">
                                    <option value="active"   {{ old('status',$employee->status)=='active'   ?'selected':'' }}>نشط</option>
                                    <option value="inactive" {{ old('status',$employee->status)=='inactive' ?'selected':'' }}>غير نشط</option>
                                </select>
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
</x-app-layout>
