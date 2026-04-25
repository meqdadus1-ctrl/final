{{-- resources/views/employees/profile.blade.php --}}
<x-app-layout>
    <x-slot name="title">{{ 'ملف الموظف - ' . $employee->name }}</x-slot>
<div class="container-fluid py-4" dir="rtl">

    @if(session('success'))
        <div class="alert alert-success alert-dismissible fade show">
            {{ session('success') }}<button type="button" class="btn-close" data-bs-dismiss="alert"></button>
        </div>
    @endif
    @if(session('error'))
        <div class="alert alert-danger alert-dismissible fade show">
            {{ session('error') }}<button type="button" class="btn-close" data-bs-dismiss="alert"></button>
        </div>
    @endif

    {{-- ===== HERO HEADER ===== --}}
    <div class="card shadow-sm mb-4 border-0" style="background: linear-gradient(135deg, #1e3a5f 0%, #2c5282 100%);">
        <div class="card-body p-4 text-white">
            <div class="row align-items-center">
                <div class="col-auto">
                    <div class="position-relative">
                        <img src="{{ $employee->photo_url }}"
                             alt="{{ $employee->name }}"
                             class="rounded-circle border border-3 border-white"
                             style="width:90px;height:90px;object-fit:cover;">
                        <span class="position-absolute bottom-0 end-0 badge rounded-pill bg-{{ $employee->status === 'active' ? 'success' : 'danger' }}" style="font-size:9px;">
                            {{ $employee->status === 'active' ? 'نشط' : 'غير نشط' }}
                        </span>
                    </div>
                </div>
                <div class="col">
                    <h4 class="mb-1 fw-bold">{{ $employee->name }}</h4>
                    <div class="opacity-75 mb-2">
                        {{ $employee->job_title ?? '—' }}
                        @if($employee->department) · {{ $employee->department->name }} @endif
                    </div>
                    <div class="d-flex flex-wrap gap-2">
                        @if($employee->employee_number)
                        <span class="badge bg-white bg-opacity-25">🔢 {{ $employee->employee_number }}</span>
                        @endif
                        @if($employee->hire_date)
                        <span class="badge bg-white bg-opacity-25">📅 {{ $employee->years_of_service }}</span>
                        @endif
                        @if($employee->age)
                        <span class="badge bg-white bg-opacity-25">🎂 {{ $employee->age }} سنة</span>
                        @endif
                        @php $cs = $employee->contract_status; @endphp
                        <span class="badge bg-{{ $cs['color'] }}">📋 {{ $cs['label'] }}</span>
                    </div>
                </div>
                <div class="col-auto d-flex flex-column gap-2">
                    <a href="{{ route('employees.edit', $employee) }}" class="btn btn-light btn-sm">✏️ تعديل</a>
                    <a href="{{ route('payslips.index', ['employee_id' => $employee->id]) }}" class="btn btn-outline-light btn-sm">💰 الرواتب</a>
                </div>
            </div>
        </div>
    </div>

    {{-- ===== TABS ===== --}}
    <ul class="nav nav-tabs mb-4" id="profileTabs">
        <li class="nav-item"><a class="nav-link active" data-bs-toggle="tab" href="#personal">👤 المعلومات الشخصية</a></li>
        <li class="nav-item"><a class="nav-link" data-bs-toggle="tab" href="#work">💼 المعلومات الوظيفية</a></li>
        <li class="nav-item"><a class="nav-link" data-bs-toggle="tab" href="#documents">📎 المستندات <span class="badge bg-secondary">{{ $employee->documents->count() }}</span></a></li>
        <li class="nav-item"><a class="nav-link" data-bs-toggle="tab" href="#history">📈 السجل الوظيفي <span class="badge bg-secondary">{{ $employee->promotions->count() }}</span></a></li>
        <li class="nav-item"><a class="nav-link" data-bs-toggle="tab" href="#payslips">💰 الرواتب</a></li>
    </ul>

    <div class="tab-content">

        {{-- ===== TAB 1: PERSONAL ===== --}}
        <div class="tab-pane fade show active" id="personal">
            <div class="row g-4">

                {{-- Basic Info --}}
                <div class="col-md-6">
                    <div class="card shadow-sm h-100">
                        <div class="card-header fw-semibold">📋 المعلومات الأساسية</div>
                        <div class="card-body">
                            @include('employees.partials.info-row', ['label'=>'الاسم الكامل',    'value'=>$employee->name])
                            @include('employees.partials.info-row', ['label'=>'رقم الهوية',      'value'=>$employee->national_id])
                            @include('employees.partials.info-row', ['label'=>'تاريخ الميلاد',   'value'=>$employee->birth_date?->format('Y/m/d') . ($employee->age ? " ({$employee->age} سنة)" : '')])
                            @include('employees.partials.info-row', ['label'=>'الجنس',           'value'=>$employee->gender_label])
                            @include('employees.partials.info-row', ['label'=>'الحالة الاجتماعية','value'=>$employee->marital_status_label])
                            @include('employees.partials.info-row', ['label'=>'الجنسية',         'value'=>$employee->nationality])
                            @include('employees.partials.info-row', ['label'=>'الديانة',         'value'=>$employee->religion])
                        </div>
                    </div>
                </div>

                {{-- Contact --}}
                <div class="col-md-6">
                    <div class="card shadow-sm h-100">
                        <div class="card-header fw-semibold">📞 معلومات التواصل</div>
                        <div class="card-body">
                            @include('employees.partials.info-row', ['label'=>'البريد الإلكتروني','value'=>$employee->email])
                            @include('employees.partials.info-row', ['label'=>'البريد الشخصي',   'value'=>$employee->personal_email])
                            @include('employees.partials.info-row', ['label'=>'الهاتف',           'value'=>$employee->phone])
                            @include('employees.partials.info-row', ['label'=>'هاتف بديل',        'value'=>$employee->phone2])
                            @include('employees.partials.info-row', ['label'=>'العنوان',          'value'=>$employee->address])
                            @include('employees.partials.info-row', ['label'=>'المدينة',          'value'=>$employee->city])
                        </div>
                    </div>
                </div>

                {{-- Emergency --}}
                <div class="col-md-6">
                    <div class="card shadow-sm">
                        <div class="card-header fw-semibold">🚨 جهة الطوارئ</div>
                        <div class="card-body">
                            @include('employees.partials.info-row', ['label'=>'الاسم',    'value'=>$employee->emergency_contact_name])
                            @include('employees.partials.info-row', ['label'=>'الهاتف',   'value'=>$employee->emergency_contact_phone])
                            @include('employees.partials.info-row', ['label'=>'صلة القرابة','value'=>$employee->emergency_contact_relation])
                        </div>
                    </div>
                </div>

                {{-- Education --}}
                <div class="col-md-6">
                    <div class="card shadow-sm">
                        <div class="card-header fw-semibold">🎓 التعليم</div>
                        <div class="card-body">
                            @include('employees.partials.info-row', ['label'=>'المؤهل',    'value'=>$employee->education_level])
                            @include('employees.partials.info-row', ['label'=>'التخصص',   'value'=>$employee->education_major])
                            @include('employees.partials.info-row', ['label'=>'الجامعة',  'value'=>$employee->university])
                            @include('employees.partials.info-row', ['label'=>'سنة التخرج','value'=>$employee->graduation_year])
                        </div>
                    </div>
                </div>

            </div>
        </div>

        {{-- ===== TAB 2: WORK ===== --}}
        <div class="tab-pane fade" id="work">
            <div class="row g-4">

                <div class="col-md-6">
                    <div class="card shadow-sm h-100">
                        <div class="card-header fw-semibold">💼 البيانات الوظيفية</div>
                        <div class="card-body">
                            @include('employees.partials.info-row', ['label'=>'رقم الموظف',   'value'=>$employee->employee_number])
                            @include('employees.partials.info-row', ['label'=>'المسمى الوظيفي','value'=>$employee->job_title])
                            @include('employees.partials.info-row', ['label'=>'القسم',         'value'=>$employee->department?->name])
                            @include('employees.partials.info-row', ['label'=>'المدير المباشر', 'value'=>$employee->manager?->name])
                            @include('employees.partials.info-row', ['label'=>'موقع العمل',    'value'=>$employee->work_location])
                            @include('employees.partials.info-row', ['label'=>'تاريخ التعيين', 'value'=>$employee->hire_date?->format('Y/m/d')])
                            @include('employees.partials.info-row', ['label'=>'سنوات الخدمة',  'value'=>$employee->years_of_service])
                        </div>
                    </div>
                </div>

                <div class="col-md-6">
                    <div class="card shadow-sm h-100">
                        <div class="card-header fw-semibold">📋 العقد والراتب</div>
                        <div class="card-body">
                            @include('employees.partials.info-row', ['label'=>'نوع العقد',      'value'=>$employee->contract_type_label])
                            @include('employees.partials.info-row', ['label'=>'بداية العقد',    'value'=>$employee->contract_start?->format('Y/m/d')])
                            @include('employees.partials.info-row', ['label'=>'نهاية العقد',    'value'=>$employee->contract_end?->format('Y/m/d') ?? 'دائم'])
                            @php $cs = $employee->contract_status; @endphp
                            <div class="row py-2 border-bottom">
                                <div class="col-5 text-muted small">حالة العقد</div>
                                <div class="col-7"><span class="badge bg-{{ $cs['color'] }}">{{ $cs['label'] }}</span></div>
                            </div>
                            @include('employees.partials.info-row', ['label'=>'الراتب الأساسي', 'value'=>$employee->salary ? number_format($employee->salary, 2) . ' ₪' : null])
                            @include('employees.partials.info-row', ['label'=>'نوع الراتب',     'value'=>$employee->salary_type])
                            @include('employees.partials.info-row', ['label'=>'البريد الوظيفي', 'value'=>$employee->work_email])
                            @include('employees.partials.info-row', ['label'=>'هاتف العمل',     'value'=>$employee->work_phone])
                        </div>
                    </div>
                </div>

                @if($employee->notes)
                <div class="col-12">
                    <div class="card shadow-sm">
                        <div class="card-header fw-semibold">📝 ملاحظات</div>
                        <div class="card-body">{{ $employee->notes }}</div>
                    </div>
                </div>
                @endif

            </div>
        </div>

        {{-- ===== TAB 3: DOCUMENTS ===== --}}
        <div class="tab-pane fade" id="documents">
            <div class="row g-4">

                {{-- Upload Form --}}
                <div class="col-md-4">
                    <div class="card shadow-sm">
                        <div class="card-header fw-semibold bg-primary text-white">📤 رفع مستند جديد</div>
                        <div class="card-body">
                            <form action="{{ route('employees.documents.upload', $employee) }}" method="POST" enctype="multipart/form-data">
                                @csrf
                                <div class="mb-3">
                                    <label class="form-label fw-semibold">عنوان المستند <span class="text-danger">*</span></label>
                                    <input type="text" name="title" class="form-control" required placeholder="مثال: جواز سفر 2025">
                                </div>
                                <div class="mb-3">
                                    <label class="form-label fw-semibold">نوع المستند</label>
                                    <select name="type" class="form-select">
                                        <option value="id_card">🪪 بطاقة هوية</option>
                                        <option value="passport">📘 جواز سفر</option>
                                        <option value="contract">📝 عقد عمل</option>
                                        <option value="certificate">🎓 شهادة</option>
                                        <option value="cv">📄 سيرة ذاتية</option>
                                        <option value="medical">🏥 فحص طبي</option>
                                        <option value="other">📎 أخرى</option>
                                    </select>
                                </div>
                                <div class="mb-3">
                                    <label class="form-label fw-semibold">الملف <span class="text-danger">*</span></label>
                                    <input type="file" name="file" class="form-control" required accept=".pdf,.jpg,.jpeg,.png,.doc,.docx">
                                    <div class="form-text">PDF, صور, Word — حتى 5MB</div>
                                </div>
                                <div class="mb-3">
                                    <label class="form-label fw-semibold">تاريخ انتهاء الصلاحية</label>
                                    <input type="date" name="expiry_date" class="form-control">
                                </div>
                                <div class="mb-3">
                                    <label class="form-label fw-semibold">ملاحظات</label>
                                    <textarea name="notes" class="form-control" rows="2"></textarea>
                                </div>
                                <button type="submit" class="btn btn-primary w-100">رفع المستند</button>
                            </form>
                        </div>
                    </div>
                </div>

                {{-- Documents List --}}
                <div class="col-md-8">
                    @forelse($employee->documents as $doc)
                    <div class="card shadow-sm mb-3">
                        <div class="card-body">
                            <div class="d-flex justify-content-between align-items-start">
                                <div class="d-flex gap-3 align-items-start">
                                    <div style="font-size:2rem;">{{ $doc->type_icon }}</div>
                                    <div>
                                        <div class="fw-semibold">{{ $doc->title }}</div>
                                        <small class="text-muted">{{ $doc->type_label }} · {{ $doc->file_name }} · {{ $doc->file_size }}</small>
                                        @if($doc->expiry_date)
                                        <div class="mt-1">
                                            @php $es = $doc->expiry_status; @endphp
                                            <small>تنتهي: {{ $doc->expiry_date->format('Y/m/d') }}</small>
                                            @if($es['label'])
                                            <span class="badge bg-{{ $es['color'] }} ms-1">{{ $es['label'] }}</span>
                                            @endif
                                        </div>
                                        @endif
                                        @if($doc->notes)
                                        <div class="mt-1 text-muted small">{{ $doc->notes }}</div>
                                        @endif
                                        <div class="mt-1">
                                            <small class="text-muted">رُفع: {{ $doc->created_at->diffForHumans() }}</small>
                                            @if($doc->uploader) <small class="text-muted">· {{ $doc->uploader->name }}</small> @endif
                                        </div>
                                    </div>
                                </div>
                                <div class="d-flex gap-2">
                                    <a href="{{ $doc->download_url }}" class="btn btn-sm btn-outline-primary" target="_blank">⬇️ تحميل</a>
                                    <form action="{{ route('employees.documents.delete', $doc) }}" method="POST"
                                          onsubmit="return confirm('حذف هذا المستند؟')">
                                        @csrf @method('DELETE')
                                        <button class="btn btn-sm btn-outline-danger">🗑️</button>
                                    </form>
                                </div>
                            </div>
                        </div>
                    </div>
                    @empty
                    <div class="card shadow-sm">
                        <div class="card-body text-center py-5 text-muted">
                            <div style="font-size:3rem;">📂</div>
                            <div>لا توجد مستندات مرفوعة بعد</div>
                        </div>
                    </div>
                    @endforelse
                </div>

            </div>
        </div>

        {{-- ===== TAB 4: HISTORY ===== --}}
        <div class="tab-pane fade" id="history">
            <div class="row g-4">

                {{-- Add Promotion Form --}}
                <div class="col-md-4">
                    <div class="card shadow-sm">
                        <div class="card-header fw-semibold bg-success text-white">➕ إضافة حركة وظيفية</div>
                        <div class="card-body">
                            <form action="{{ route('employees.promotions.store', $employee) }}" method="POST">
                                @csrf
                                <div class="mb-3">
                                    <label class="form-label fw-semibold">نوع الحركة <span class="text-danger">*</span></label>
                                    <select name="type" class="form-select" id="promo-type" onchange="togglePromoFields(this.value)">
                                        <option value="promotion">⬆️ ترقية</option>
                                        <option value="transfer">🔄 نقل</option>
                                        <option value="demotion">⬇️ تخفيض رتبة</option>
                                        <option value="title_change">📝 تغيير المسمى</option>
                                        <option value="salary_change">💰 تعديل الراتب</option>
                                    </select>
                                </div>
                                <div class="mb-3" id="title-fields">
                                    <label class="form-label">المسمى الحالي</label>
                                    <input type="text" name="from_title" class="form-control form-control-sm mb-2"
                                        value="{{ $employee->job_title }}" placeholder="المسمى الحالي">
                                    <label class="form-label">المسمى الجديد</label>
                                    <input type="text" name="to_title" class="form-control form-control-sm" placeholder="المسمى الجديد">
                                </div>
                                <div class="mb-3" id="dept-fields">
                                    <label class="form-label">القسم الحالي ← الجديد</label>
                                    <select name="from_department_id" class="form-select form-select-sm mb-2">
                                        <option value="">—</option>
                                        @foreach(\App\Models\Department::orderBy('name')->get() as $dept)
                                        <option value="{{ $dept->id }}" {{ $employee->department_id == $dept->id ? 'selected':'' }}>{{ $dept->name }}</option>
                                        @endforeach
                                    </select>
                                    <select name="to_department_id" class="form-select form-select-sm">
                                        <option value="">—</option>
                                        @foreach(\App\Models\Department::orderBy('name')->get() as $dept)
                                        <option value="{{ $dept->id }}">{{ $dept->name }}</option>
                                        @endforeach
                                    </select>
                                </div>
                                <div class="mb-3" id="salary-fields">
                                    <label class="form-label">الراتب الحالي ← الجديد</label>
                                    <div class="d-flex gap-2">
                                        <input type="number" name="from_salary" class="form-control form-control-sm"
                                            value="{{ $employee->salary }}" placeholder="الحالي">
                                        <input type="number" name="to_salary" class="form-control form-control-sm" placeholder="الجديد">
                                    </div>
                                </div>
                                <div class="mb-3">
                                    <label class="form-label fw-semibold">تاريخ التنفيذ <span class="text-danger">*</span></label>
                                    <input type="date" name="effective_date" class="form-control" value="{{ date('Y-m-d') }}" required>
                                </div>
                                <div class="mb-3">
                                    <label class="form-label">السبب / الملاحظات</label>
                                    <textarea name="reason" class="form-control" rows="2" placeholder="سبب الحركة..."></textarea>
                                </div>
                                <button type="submit" class="btn btn-success w-100">حفظ</button>
                            </form>
                        </div>
                    </div>
                </div>

                {{-- Timeline --}}
                <div class="col-md-8">
                    @forelse($employee->promotions as $p)
                    <div class="card shadow-sm mb-3 border-start border-4 border-{{ $p->type_color }}">
                        <div class="card-body">
                            <div class="d-flex justify-content-between align-items-start">
                                <div>
                                    <div class="d-flex align-items-center gap-2 mb-1">
                                        <span class="badge bg-{{ $p->type_color }}">{{ $p->type_label }}</span>
                                        <span class="text-muted small">{{ $p->effective_date->format('Y/m/d') }}</span>
                                    </div>
                                    @if($p->from_title || $p->to_title)
                                    <div class="mb-1">
                                        <span class="text-muted">{{ $p->from_title ?? '—' }}</span>
                                        <span class="mx-2">←</span>
                                        <strong>{{ $p->to_title ?? '—' }}</strong>
                                    </div>
                                    @endif
                                    @if($p->fromDepartment || $p->toDepartment)
                                    <div class="mb-1 small text-muted">
                                        {{ $p->fromDepartment?->name ?? '—' }} ← {{ $p->toDepartment?->name ?? '—' }}
                                    </div>
                                    @endif
                                    @if($p->salary_diff !== null)
                                    <div class="mb-1 small">
                                        {{ number_format($p->from_salary, 2) }} ← {{ number_format($p->to_salary, 2) }}
                                        <span class="badge bg-{{ $p->salary_diff >= 0 ? 'success':'danger' }} ms-1">
                                            {{ $p->salary_diff >= 0 ? '+':'' }}{{ number_format($p->salary_diff, 2) }}
                                        </span>
                                    </div>
                                    @endif
                                    @if($p->reason)
                                    <div class="text-muted small mt-1">{{ $p->reason }}</div>
                                    @endif
                                    @if($p->approver)
                                    <small class="text-muted">بواسطة: {{ $p->approver->name }}</small>
                                    @endif
                                </div>
                                <form action="{{ route('employees.promotions.delete', $p) }}" method="POST"
                                      onsubmit="return confirm('حذف هذا السجل؟')">
                                    @csrf @method('DELETE')
                                    <button class="btn btn-sm btn-outline-danger">🗑️</button>
                                </form>
                            </div>
                        </div>
                    </div>
                    @empty
                    <div class="card shadow-sm">
                        <div class="card-body text-center py-5 text-muted">
                            <div style="font-size:3rem;">📈</div>
                            <div>لا يوجد سجل وظيفي بعد</div>
                        </div>
                    </div>
                    @endforelse
                </div>

            </div>
        </div>

        {{-- ===== TAB 5: PAYSLIPS ===== --}}
        <div class="tab-pane fade" id="payslips">
            <div class="table-responsive">
                <table class="table table-hover align-middle">
                    <thead class="table-light">
                        <tr>
                            <th>الشهر / السنة</th>
                            <th>الراتب الأساسي</th>
                            <th>الإجمالي</th>
                            <th>الخصومات</th>
                            <th>الصافي</th>
                            <th>الحالة</th>
                            <th></th>
                        </tr>
                    </thead>
                    <tbody>
                        @forelse($employee->payslips as $ps)
                        <tr>
                            <td>{{ $ps->month_name }} {{ $ps->year }}</td>
                            <td>{{ number_format($ps->basic_salary, 2) }}</td>
                            <td class="text-success">{{ number_format($ps->total_allowances, 2) }}</td>
                            <td class="text-danger">{{ number_format($ps->total_deductions, 2) }}</td>
                            <td class="fw-bold">{{ number_format($ps->net_salary, 2) }}</td>
                            <td><span class="badge bg-{{ $ps->status_color }}">{{ $ps->status_label }}</span></td>
                            <td>
                                <a href="{{ route('payslips.show', $ps) }}" class="btn btn-sm btn-outline-primary">عرض</a>
                                <a href="{{ route('payslips.pdf', $ps) }}" class="btn btn-sm btn-outline-danger">PDF</a>
                            </td>
                        </tr>
                        @empty
                        <tr><td colspan="7" class="text-center py-4 text-muted">لا توجد كشوف رواتب</td></tr>
                        @endforelse
                    </tbody>
                </table>
            </div>
            <div class="mt-2">
                <a href="{{ route('payslips.index', ['employee_id' => $employee->id]) }}" class="btn btn-outline-primary btn-sm">
                    عرض كل الرواتب ←
                </a>
            </div>
        </div>

    </div>{{-- end tab-content --}}
</div>

<script>
function togglePromoFields(type) {
    const showTitle  = ['promotion','demotion','title_change'].includes(type);
    const showDept   = ['transfer','promotion'].includes(type);
    const showSalary = ['salary_change','promotion','demotion'].includes(type);
    document.getElementById('title-fields').style.display  = showTitle  ? '' : 'none';
    document.getElementById('dept-fields').style.display   = showDept   ? '' : 'none';
    document.getElementById('salary-fields').style.display = showSalary ? '' : 'none';
}
togglePromoFields('promotion');
</script>
</x-app-layout>
