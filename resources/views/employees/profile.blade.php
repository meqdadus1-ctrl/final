<x-app-layout>




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
                    @php $__bal = $employee->balance; @endphp
                    <div class="text-center py-2 px-3 rounded" style="background: rgba(255,255,255,0.15); min-width:160px;">
                        <div class="opacity-75 small">الرصيد (أمانة)</div>
                        <div class="fw-bold fs-5 text-{{ $__bal >= 0 ? 'warning' : 'danger' }}">
                            {{ $__bal >= 0 ? '+' : '−' }}{{ number_format(abs($__bal), 2) }} ₪
                        </div>
                        <small class="opacity-75">
                            {{ $__bal >= 0 ? 'له' : 'عليه' }}
                        </small>
                    </div>
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
        <li class="nav-item">
            <a class="nav-link" data-bs-toggle="tab" href="#finance">
                🏦 الصندوق المالي
            </a>
        </li>
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
                            @include('employees.partials.info-row', ['label'=>'الجوال (التطبيق)', 'value'=>$employee->mobile])
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
                            @include('employees.partials.info-row', ['label'=>'الرقم الوظيفي',  'value'=>$employee->employee_number])
                            @include('employees.partials.info-row', ['label'=>'🖐️ رقم البصمة', 'value'=>$employee->fingerprint_id])
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

                {{-- ===== بيانات البنك ===== --}}
                <div class="col-12">
                    <div class="card shadow-sm">
                        <div class="card-header fw-semibold d-flex justify-content-between align-items-center">
                            <span>🏦 بيانات البنك</span>
                            <button class="btn btn-sm btn-outline-primary" type="button"
                                data-bs-toggle="collapse" data-bs-target="#bankEditForm">
                                <i class="fas fa-edit me-1"></i> تعديل
                            </button>
                        </div>
                        <div class="card-body">
                            {{-- عرض البيانات الحالية --}}
                            <div id="bankDisplay">
                                <div class="row g-3">
                                    <div class="col-md-4">
                                        <div class="text-muted small mb-1">البنك</div>
                                        <div class="fw-semibold">
                                            {{ $employee->bank?->name ?? $employee->bank?->bank_name ?? '—' }}
                                        </div>
                                    </div>
                                    <div class="col-md-4">
                                        <div class="text-muted small mb-1">اسم صاحب الحساب</div>
                                        <div class="fw-semibold">{{ $employee->account_name ?? '—' }}</div>
                                    </div>
                                    <div class="col-md-4">
                                        <div class="text-muted small mb-1">رقم الحساب / IBAN</div>
                                        <div class="fw-semibold font-monospace">{{ $employee->bank_account ?? '—' }}</div>
                                    </div>
                                </div>
                            </div>

                            {{-- فورم التعديل (مطوي) --}}
                            <div class="collapse mt-3" id="bankEditForm">
                                <hr>
                                <form method="POST" action="{{ route('employees.profile.update', $employee) }}">
                                    @csrf
                                    @method('PUT')
                                    {{-- نرسل حقل hidden لتمييز أن هذا submit للبنك فقط --}}
                                    <input type="hidden" name="name" value="{{ $employee->name }}">
                                    <div class="row g-3">
                                        <div class="col-md-4">
                                            <label class="form-label fw-semibold small">البنك</label>
                                            <select name="bank_id" class="form-select form-select-sm">
                                                <option value="">— بدون بنك —</option>
                                                @foreach($banks as $bank)
                                                    <option value="{{ $bank->id }}"
                                                        {{ $employee->bank_id == $bank->id ? 'selected' : '' }}>
                                                        {{ $bank->name ?? $bank->bank_name }}
                                                    </option>
                                                @endforeach
                                            </select>
                                        </div>
                                        <div class="col-md-4">
                                            <label class="form-label fw-semibold small">اسم صاحب الحساب</label>
                                            <input type="text" name="account_name" class="form-control form-control-sm"
                                                value="{{ old('account_name', $employee->account_name) }}"
                                                placeholder="{{ $employee->name }}">
                                        </div>
                                        <div class="col-md-4">
                                            <label class="form-label fw-semibold small">رقم الحساب / IBAN</label>
                                            <input type="text" name="bank_account" class="form-control form-control-sm font-monospace"
                                                value="{{ old('bank_account', $employee->bank_account) }}"
                                                placeholder="IL00 0000 0000 0000">
                                        </div>
                                    </div>
                                    <div class="mt-3 d-flex gap-2">
                                        <button type="submit" class="btn btn-primary btn-sm">
                                            <i class="fas fa-save me-1"></i> حفظ بيانات البنك
                                        </button>
                                        <button type="button" class="btn btn-outline-secondary btn-sm"
                                            data-bs-toggle="collapse" data-bs-target="#bankEditForm">
                                            إلغاء
                                        </button>
                                    </div>
                                </form>
                            </div>
                        </div>
                    </div>
                </div>

            </div>

            {{-- ===== بطاقة حساب الدخول ===== --}}
            <div class="card shadow-sm mt-4">
                <div class="card-header py-2 d-flex justify-content-between align-items-center">
                    <span class="fw-semibold">🔐 حساب بوابة الموظف</span>
                    @if($employee->user_id)
                        <span class="badge bg-success">مُفعَّل</span>
                    @else
                        <span class="badge bg-secondary">غير مرتبط</span>
                    @endif
                </div>
                <div class="card-body">

                    @if($employee->user_id)
                        {{-- حساب موجود --}}
                        @php $linkedUser = \App\Models\User::find($employee->user_id); @endphp
                        <div class="d-flex justify-content-between align-items-center mb-3">
                            <div>
                                <div class="fw-semibold">{{ $linkedUser?->email ?? '—' }}</div>
                                <small class="text-muted">حساب الدخول للبوابة</small>
                            </div>
                            <a href="{{ route('portal.index') }}" target="_blank"
                               class="btn btn-sm btn-outline-primary">
                                <i class="fas fa-external-link-alt me-1"></i>فتح البوابة
                            </a>
                        </div>

                        {{-- تغيير كلمة السر --}}
                        <div class="collapse" id="resetPwForm">
                            <form action="{{ route('employees.user.reset-password', $employee) }}" method="POST" class="mt-2">
                                @csrf
                                <div class="row g-2">
                                    <div class="col-md-5">
                                        <input type="password" name="password" class="form-control form-control-sm"
                                            placeholder="كلمة السر الجديدة" required minlength="6">
                                    </div>
                                    <div class="col-md-5">
                                        <input type="password" name="password_confirmation" class="form-control form-control-sm"
                                            placeholder="تأكيد كلمة السر" required>
                                    </div>
                                    <div class="col-md-2">
                                        <button type="submit" class="btn btn-warning btn-sm w-100">حفظ</button>
                                    </div>
                                </div>
                            </form>
                        </div>

                        <div class="d-flex gap-2 mt-2">
                            <button class="btn btn-sm btn-outline-warning"
                                data-bs-toggle="collapse" data-bs-target="#resetPwForm">
                                <i class="fas fa-key me-1"></i>تغيير كلمة السر
                            </button>
                            <form action="{{ route('employees.user.unlink', $employee) }}" method="POST"
                                  onsubmit="return confirm('هل تريد فك ربط الحساب؟')">
                                @csrf @method('DELETE')
                                <button type="submit" class="btn btn-sm btn-outline-danger">
                                    <i class="fas fa-unlink me-1"></i>فك الربط
                                </button>
                            </form>
                        </div>

                    @else
                        {{-- إنشاء حساب جديد --}}
                        <p class="text-muted small mb-3">
                            الموظف ليس له حساب دخول بعد. أنشئ له حساباً ليتمكن من رؤية كشف حسابه.
                        </p>

                        <form action="{{ route('employees.user.create', $employee) }}" method="POST">
                            @csrf
                            <div class="row g-2 mb-2">
                                <div class="col-md-4">
                                    <input type="email" name="email" class="form-control form-control-sm"
                                        placeholder="البريد الإلكتروني"
                                        value="{{ old('email', $employee->email ?? $employee->work_email ?? '') }}"
                                        required>
                                </div>
                                <div class="col-md-3">
                                    <input type="password" name="password" class="form-control form-control-sm"
                                        placeholder="كلمة السر" required minlength="6">
                                </div>
                                <div class="col-md-3">
                                    <input type="password" name="password_confirmation" class="form-control form-control-sm"
                                        placeholder="تأكيد كلمة السر" required>
                                </div>
                                <div class="col-md-2">
                                    <button type="submit" class="btn btn-success btn-sm w-100">
                                        <i class="fas fa-user-plus me-1"></i>إنشاء
                                    </button>
                                </div>
                            </div>
                        </form>
                    @endif

                </div>
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

        {{-- ===== TAB 5: PAYSLIPS (Weekly Salary Payments) ===== --}}
        <div class="tab-pane fade" id="payslips">

            @php
                $payments = $employee->salaryPayments;
                $totalPaid   = $payments->sum('net_salary');
                $totalGross  = $payments->sum('gross_salary');
                $totalDeduct = $payments->sum('total_deductions');
            @endphp

            {{-- ملخص --}}
            <div class="row g-3 mb-3">
                <div class="col-md-4">
                    <div class="card border-0 shadow-sm" style="background:#e0f2fe;">
                        <div class="card-body text-center">
                            <div class="text-muted small">إجمالي المدفوع</div>
                            <div class="fw-bold fs-4 text-primary">{{ number_format($totalPaid, 2) }} ₪</div>
                        </div>
                    </div>
                </div>
                <div class="col-md-4">
                    <div class="card border-0 shadow-sm" style="background:#d1fae5;">
                        <div class="card-body text-center">
                            <div class="text-muted small">إجمالي الأجور (قبل الخصومات)</div>
                            <div class="fw-bold fs-4 text-success">{{ number_format($totalGross, 2) }} ₪</div>
                        </div>
                    </div>
                </div>
                <div class="col-md-4">
                    <div class="card border-0 shadow-sm" style="background:#fee2e2;">
                        <div class="card-body text-center">
                            <div class="text-muted small">إجمالي الخصومات</div>
                            <div class="fw-bold fs-4 text-danger">{{ number_format($totalDeduct, 2) }} ₪</div>
                        </div>
                    </div>
                </div>
            </div>

            <div class="card shadow-sm">
                <div class="card-header d-flex justify-content-between align-items-center">
                    <span class="fw-semibold">💵 كشوف الرواتب الأسبوعية ({{ $payments->count() }})</span>
                    <a href="{{ route('salary.create') }}" class="btn btn-primary btn-sm">
                        <i class="fas fa-plus me-1"></i> احتساب راتب جديد
                    </a>
                </div>
                <div class="table-responsive">
                    <table class="table table-hover align-middle mb-0">
                        <thead class="table-light">
                            <tr>
                                <th class="px-3">الفترة</th>
                                <th>ساعات</th>
                                <th>الإجمالي</th>
                                <th>البدلات</th>
                                <th>الخصومات</th>
                                <th>خصم السلفة</th>
                                <th>الصافي</th>
                                <th>تاريخ الدفع</th>
                                <th>الطريقة</th>
                                <th></th>
                            </tr>
                        </thead>
                        <tbody>
                            @forelse($payments as $sp)
                            <tr>
                                <td class="px-3">
                                    <div class="fw-semibold">{{ $sp->week_start?->format('Y-m-d') }}</div>
                                    <small class="text-muted">إلى {{ $sp->week_end?->format('Y-m-d') }}</small>
                                </td>
                                <td>
                                    {{ number_format($sp->hours_worked, 1) }}
                                    @if($sp->overtime_hours > 0)
                                    <small class="text-info d-block">+{{ number_format($sp->overtime_hours, 1) }} OT</small>
                                    @endif
                                </td>
                                <td class="text-success">{{ number_format($sp->gross_salary, 2) }}</td>
                                <td class="text-success">{{ number_format($sp->total_allowances ?? $sp->manual_additions ?? 0, 2) }}</td>
                                <td class="text-danger">{{ number_format($sp->total_deductions, 2) }}</td>
                                <td class="text-warning">{{ number_format($sp->loan_deduction_amount ?? 0, 2) }}</td>
                                <td class="fw-bold fs-6 text-primary">{{ number_format($sp->net_salary, 2) }} ₪</td>
                                <td>{{ $sp->payment_date?->format('Y-m-d') ?? '—' }}</td>
                                <td>
                                    <span class="badge bg-{{ $sp->payment_method === 'bank' ? 'info' : 'secondary' }}">
                                        {{ $sp->payment_method === 'bank' ? 'بنكي' : 'كاش' }}
                                    </span>
                                </td>
                                <td>
                                    <a href="{{ route('salary.show', $sp) }}" class="btn btn-sm btn-outline-primary" title="عرض">
                                        <i class="fas fa-eye"></i>
                                    </a>
                                </td>
                            </tr>
                            @empty
                            <tr>
                                <td colspan="10" class="text-center py-5 text-muted">
                                    <i class="fas fa-receipt fs-1 d-block mb-2 opacity-25"></i>
                                    لا توجد قبضات سابقة بعد
                                </td>
                            </tr>
                            @endforelse
                        </tbody>
                    </table>
                </div>
            </div>
            <div class="mt-2">
                <a href="{{ route('salary.index') }}?search={{ urlencode($employee->name) }}" class="btn btn-outline-primary btn-sm">
                    عرض كل الكشوف ←
                </a>
            </div>
        </div>

        {{-- ===== TAB 6: FINANCE (الصندوق المالي) ===== --}}
        <div class="tab-pane fade" id="finance">

            @php
                $balance = $employee->balance;
                $openingBalance = (float) $employee->opening_balance;
                $accrued = $employee->accruedWagesSinceLastPayment();
                $lastPayment = $employee->last_payment;
                $activeLoan  = $employee->activeLoan;
            @endphp

            {{-- ملخص مالي سريع --}}
            <div class="row g-3 mb-4">
                <div class="col-md-3">
                    <div class="card h-100 border-0 shadow-sm" style="background:{{ $balance >= 0 ? '#d1fae5' : '#fee2e2' }};">
                        <div class="card-body text-center">
                            <div class="text-muted small">الرصيد الحالي (أمانة)</div>
                            <div class="fw-bold fs-3 {{ $balance >= 0 ? 'text-success' : 'text-danger' }}">
                                {{ $balance >= 0 ? '+' : '−' }}{{ number_format(abs($balance), 2) }} ₪
                            </div>
                            <small class="{{ $balance >= 0 ? 'text-success' : 'text-danger' }}">
                                {{ $balance >= 0 ? 'للموظف على الشركة' : 'على الموظف للشركة' }}
                            </small>
                            @if($openingBalance != 0)
                            <div class="mt-1 pt-1 border-top">
                                <small class="text-muted">
                                    افتتاحي: {{ $openingBalance > 0 ? '+' : '−' }}{{ number_format(abs($openingBalance), 2) }}
                                </small>
                            </div>
                            @endif
                        </div>
                    </div>
                </div>
                <div class="col-md-3">
                    <div class="card h-100 border-0 shadow-sm" style="background:#e0f2fe;">
                        <div class="card-body text-center">
                            <div class="text-muted small">الأجر المتراكم</div>
                            <div class="fw-bold fs-3 text-primary">{{ number_format($accrued, 2) }} ₪</div>
                            <small class="text-muted">منذ آخر قبضة</small>
                        </div>
                    </div>
                </div>
                <div class="col-md-3">
                    <div class="card h-100 border-0 shadow-sm" style="background:#fef3c7;">
                        <div class="card-body text-center">
                            <div class="text-muted small">الرصيد الافتتاحي</div>
                            <div class="fw-bold fs-3 {{ $openingBalance >= 0 ? 'text-success' : 'text-danger' }}">{{ number_format(abs($openingBalance), 2) }} ₪</div>
                            <small class="text-muted">{{ $openingBalance >= 0 ? 'له' : 'عليه' }}</small>
                        </div>
                    </div>
                </div>
                <div class="col-md-3">
                    <div class="card h-100 border-0 shadow-sm" style="background:#ede9fe;">
                        <div class="card-body text-center">
                            <div class="text-muted small">آخر قبضة</div>
                            @if($lastPayment)
                                <div class="fw-bold fs-4 text-purple">{{ number_format($lastPayment->net_salary, 2) }} ₪</div>
                                <small class="text-muted">{{ $lastPayment->payment_date->format('Y-m-d') }}</small>
                            @else
                                <div class="fw-bold fs-4 text-muted">—</div>
                                <small class="text-muted">لا توجد قبضات</small>
                            @endif
                        </div>
                    </div>
                </div>
            </div>

            {{-- تعديل الرصيد الافتتاحي (يدوي) --}}
            <div class="card shadow-sm mb-4 border-primary">
                <div class="card-header bg-primary bg-opacity-10 fw-semibold">
                    <i class="fas fa-sliders-h me-2"></i>
                    تعديل الرصيد الافتتاحي (أمانة مباشرة)
                </div>
                <div class="card-body">
                    <form action="{{ route('employees.opening-balance.update', $employee) }}" method="POST" class="row g-3 align-items-end">
                        @csrf
                        <div class="col-md-4">
                            <label class="form-label fw-semibold">الرصيد الافتتاحي (₪)</label>
                            <div class="input-group">
                                <input type="number" name="opening_balance" step="0.01"
                                       class="form-control form-control-lg fw-bold text-center
                                       {{ $openingBalance > 0 ? 'text-success' : ($openingBalance < 0 ? 'text-danger' : '') }}"
                                       value="{{ old('opening_balance', $openingBalance) }}"
                                       placeholder="0.00">
                                <span class="input-group-text">₪</span>
                            </div>
                            <small class="text-muted d-block mt-1">
                                <span class="text-success">موجب (+)</span> = للموظف على الشركة |
                                <span class="text-danger">سالب (−)</span> = على الموظف للشركة
                            </small>
                        </div>
                        <div class="col-md-5">
                            <label class="form-label fw-semibold">ملاحظات (اختياري)</label>
                            <input type="text" name="notes" class="form-control"
                                   placeholder="مثال: رصيد مُرحَّل من السنة الماضية">
                        </div>
                        <div class="col-md-3">
                            <button type="submit" class="btn btn-primary w-100"
                                    onclick="return confirm('تأكيد تحديث الرصيد الافتتاحي؟');">
                                <i class="fas fa-save me-1"></i> حفظ الرصيد
                            </button>
                        </div>
                    </form>

                    @if($openingBalance != 0)
                    <div class="alert {{ $openingBalance > 0 ? 'alert-success' : 'alert-danger' }} mt-3 mb-0 py-2">
                        الرصيد الافتتاحي الحالي:
                        <strong>
                            {{ $openingBalance > 0 ? '+' : '−' }}{{ number_format(abs($openingBalance), 2) }} ₪
                        </strong>
                        — {{ $openingBalance > 0 ? 'الشركة مدينة للموظف' : 'الموظف مدين للشركة' }}
                    </div>
                    @endif

                    <small class="text-muted d-block mt-2">
                        💡 هذا الرصيد يُضاف إلى الأجر المتراكم لحساب الرصيد الكلي. لا يتأثر بالرواتب — يتغير فقط لما تعدّله يدوياً.
                    </small>
                </div>
            </div>

            {{-- السلفة النشطة --}}
            @if($activeLoan)
            <div class="card shadow-sm mb-4 border-warning">
                <div class="card-header bg-warning bg-opacity-25 fw-semibold">
                    💳 سلفة نشطة — أقساط {{ $activeLoan->installment_type_label }}
                </div>
                <div class="card-body">
                    <div class="row g-3">
                        <div class="col-md-3">
                            <div class="text-muted small">إجمالي السلفة</div>
                            <div class="fw-bold">{{ number_format($activeLoan->total_amount, 2) }} ₪</div>
                        </div>
                        <div class="col-md-3">
                            <div class="text-muted small">قيمة القسط الأسبوعي</div>
                            <div class="fw-bold text-primary">{{ number_format($activeLoan->installment_amount, 2) }} ₪</div>
                        </div>
                        <div class="col-md-3">
                            <div class="text-muted small">المدفوع</div>
                            <div class="fw-bold text-success">
                                {{ number_format($activeLoan->amount_paid, 2) }} ₪
                                <small>({{ $activeLoan->installments_paid }} / {{ $activeLoan->installments_total }})</small>
                            </div>
                        </div>
                        <div class="col-md-3">
                            <div class="text-muted small">المتبقي</div>
                            <div class="fw-bold text-danger">{{ number_format($activeLoan->remaining_amount, 2) }} ₪</div>
                        </div>
                    </div>
                    @php
                        $progress = $activeLoan->installments_total > 0
                            ? round(($activeLoan->installments_paid / $activeLoan->installments_total) * 100, 1)
                            : 0;
                    @endphp
                    <div class="progress mt-3" style="height:20px;">
                        <div class="progress-bar bg-success" style="width:{{ $progress }}%">
                            {{ $progress }}%
                        </div>
                    </div>
                    <div class="mt-3 d-flex gap-2">
                        <a href="{{ route('loans.show', $activeLoan->id) }}" class="btn btn-sm btn-outline-primary">
                            <i class="fas fa-eye me-1"></i> عرض التفاصيل
                        </a>
                        <a href="{{ route('loans.edit', $activeLoan->id) }}" class="btn btn-sm btn-outline-secondary">
                            <i class="fas fa-edit me-1"></i> تعديل
                        </a>
                    </div>
                </div>
            </div>
            @endif

            {{-- سجل القبضات الأسبوعية --}}
            <div class="card shadow-sm">
                <div class="card-header fw-semibold">
                    💵 سجل القبضات الأسبوعية
                </div>
                <div class="table-responsive">
                    <table class="table table-hover mb-0 align-middle">
                        <thead class="table-light">
                            <tr>
                                <th class="px-3">الفترة</th>
                                <th>الإجمالي</th>
                                <th>البدلات</th>
                                <th>الخصومات</th>
                                <th>السلفة</th>
                                <th>الصافي</th>
                                <th>تاريخ الدفع</th>
                            </tr>
                        </thead>
                        <tbody>
                            @forelse($employee->salaryPayments as $sp)
                            <tr>
                                <td class="px-3">
                                    {{ $sp->week_start?->format('Y-m-d') }} <br>
                                    <small class="text-muted">إلى {{ $sp->week_end?->format('Y-m-d') }}</small>
                                </td>
                                <td>{{ number_format($sp->gross_salary, 2) }}</td>
                                <td class="text-success">{{ number_format($sp->total_allowances, 2) }}</td>
                                <td class="text-danger">{{ number_format($sp->total_deductions, 2) }}</td>
                                <td class="text-warning">{{ number_format($sp->loan_deduction ?? 0, 2) }}</td>
                                <td class="fw-bold text-primary">{{ number_format($sp->net_salary, 2) }} ₪</td>
                                <td>{{ $sp->payment_date?->format('Y-m-d') ?? '—' }}</td>
                            </tr>
                            @empty
                            <tr><td colspan="7" class="text-center py-4 text-muted">لا توجد قبضات سابقة</td></tr>
                            @endforelse
                        </tbody>
                    </table>
                </div>
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