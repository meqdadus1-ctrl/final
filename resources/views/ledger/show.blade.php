<x-app-layout>
<x-slot name="title">كشف حساب — {{ $employee->name }}</x-slot>
<div class="container-fluid py-4" dir="rtl">

    {{-- Header --}}
    <div class="d-flex justify-content-between align-items-center mb-4">
        <div>
            <h4 class="mb-0 fw-bold">📒 كشف الحساب</h4>
            <small class="text-muted">سجل المعاملات المالية — مثل كشف البنك</small>
        </div>
        <div class="d-flex gap-2">
            <a href="{{ route('ledger.pdf', $employee) . '?' . http_build_query(['from' => $from, 'to' => $to]) }}"
               class="btn btn-outline-danger btn-sm" target="_blank">
                <i class="fas fa-file-pdf me-1"></i> PDF
            </a>
            <button type="button" class="btn btn-outline-success btn-sm" data-bs-toggle="modal" data-bs-target="#importModal">
                <i class="fas fa-file-import me-1"></i> استيراد Excel
            </button>
            <a href="{{ route('employees.profile', $employee) }}" class="btn btn-outline-secondary btn-sm">
                <i class="fas fa-user me-1"></i> ملف الموظف
            </a>
        </div>
    </div>

    @if(session('success'))
        <div class="alert alert-success alert-dismissible fade show">{{ session('success') }}<button type="button" class="btn-close" data-bs-dismiss="alert"></button></div>
    @endif

    <div class="row g-4">

        {{-- ===== COL-LEFT: Employee + Balance + Filters + Opening Balance ===== --}}
        <div class="col-lg-3">

            {{-- بيانات الموظف --}}
            <div class="card shadow-sm mb-3">
                <div class="card-header bg-dark text-white py-2">
                    <i class="fas fa-user me-2"></i>الموظف
                </div>
                <div class="card-body">
                    <h6 class="fw-bold mb-1">{{ $employee->name }}</h6>
                    <p class="text-muted small mb-2">{{ $employee->department?->name ?? '—' }}</p>
                    <hr class="my-2">
                    <div class="d-flex justify-content-between small mb-1">
                        <span class="text-muted">نوع الراتب</span>
                        <span>{{ $employee->salary_type === 'hourly' ? 'بالساعة' : 'ثابت' }}</span>
                    </div>
                    <div class="d-flex justify-content-between small mb-1">
                        <span class="text-muted">تاريخ التوظيف</span>
                        <span>{{ $employee->hire_date?->format('d/m/Y') ?? '—' }}</span>
                    </div>
                </div>
            </div>

            {{-- الرصيد الحالي --}}
            <div class="card shadow-sm mb-3 border-0"
                style="background: {{ $balance >= 0 ? 'linear-gradient(135deg,#e8f5e9,#f9fbe7)' : 'linear-gradient(135deg,#fce4ec,#fce4ec)' }}">
                <div class="card-body text-center py-3">
                    <div class="small text-muted mb-1">الرصيد الحالي</div>
                    <div class="fw-bold fs-2 {{ $balance >= 0 ? 'text-success' : 'text-danger' }}">
                        {{ number_format($balance, 2) }} ₪
                    </div>
                    <div class="small text-muted">{{ now()->format('d/m/Y') }}</div>
                </div>
            </div>

            {{-- بطاقات الملخص --}}
            <div class="row g-2 mb-3">
                <div class="col-6">
                    <div class="card text-center border-0 bg-success bg-opacity-10 py-2">
                        <div class="small text-muted">إجمالي الدائن</div>
                        <div class="fw-bold text-success small">{{ number_format($summary['total_credits'] ?? 0, 2) }} ₪</div>
                    </div>
                </div>
                <div class="col-6">
                    <div class="card text-center border-0 bg-danger bg-opacity-10 py-2">
                        <div class="small text-muted">إجمالي المدين</div>
                        <div class="fw-bold text-danger small">{{ number_format($summary['total_debits'] ?? 0, 2) }} ₪</div>
                    </div>
                </div>
                <div class="col-6">
                    <div class="card text-center border-0 bg-primary bg-opacity-10 py-2">
                        <div class="small text-muted">رواتب صافية</div>
                        <div class="fw-bold text-primary small">{{ number_format($summary['net_paid'] ?? 0, 2) }} ₪</div>
                    </div>
                </div>
                <div class="col-6">
                    <div class="card text-center border-0 bg-warning bg-opacity-10 py-2">
                        <div class="small text-muted">خصومات</div>
                        <div class="fw-bold text-warning small">{{ number_format($summary['total_deductions'] ?? 0, 2) }} ₪</div>
                    </div>
                </div>
            </div>

            {{-- فلتر الفترة --}}
            <div class="card shadow-sm mb-3">
                <div class="card-header py-2 small fw-semibold">
                    <i class="fas fa-filter me-1"></i>فلتر الفترة
                </div>
                <div class="card-body">
                    <form method="GET">
                        <div class="mb-2">
                            <label class="form-label small mb-1">من</label>
                            <input type="date" name="from" class="form-control form-control-sm" value="{{ $from }}">
                        </div>
                        <div class="mb-3">
                            <label class="form-label small mb-1">إلى</label>
                            <input type="date" name="to" class="form-control form-control-sm" value="{{ $to }}">
                        </div>
                        <div class="d-flex gap-1 flex-wrap mb-2">
                            <button type="button" class="btn btn-outline-secondary btn-xs btn-sm py-0 px-2 small"
                                onclick="setRange('week')">أسبوع</button>
                            <button type="button" class="btn btn-outline-secondary btn-xs btn-sm py-0 px-2 small"
                                onclick="setRange('month')">شهر</button>
                            <button type="button" class="btn btn-outline-secondary btn-xs btn-sm py-0 px-2 small"
                                onclick="setRange('3months')">3 أشهر</button>
                            <button type="button" class="btn btn-outline-secondary btn-xs btn-sm py-0 px-2 small"
                                onclick="setRange('year')">سنة</button>
                        </div>
                        <button type="submit" class="btn btn-primary btn-sm w-100">تطبيق</button>
                    </form>
                </div>
            </div>

            {{-- الانتقال السريع لموظف آخر --}}
            <div class="card shadow-sm mb-3">
                <div class="card-header py-2 small fw-semibold">
                    <i class="fas fa-exchange-alt me-1"></i>موظف آخر
                </div>
                <div class="card-body py-2">
                    <select class="form-select form-select-sm" onchange="window.location='/ledger/'+this.value">
                        <option value="">— اختر —</option>
                        @foreach($employees as $emp)
                            <option value="{{ $emp->id }}" {{ $emp->id === $employee->id ? 'selected' : '' }}>
                                {{ $emp->name }}
                            </option>
                        @endforeach
                    </select>
                </div>
            </div>

            {{-- تسجيل قبض --}}
            <div class="card shadow-sm mb-3 border-success">
                <div class="card-header py-2 bg-success text-white small fw-semibold">
                    <i class="fas fa-hand-holding-usd me-1"></i>تسجيل قبض للموظف
                </div>
                <div class="card-body">
                    @if(session('error'))
                        <div class="alert alert-danger py-2 small mb-2">{{ session('error') }}</div>
                    @endif
                    <form method="POST" action="{{ route('ledger.payment', $employee) }}">
                        @csrf
                        <div class="mb-2">
                            <label class="small fw-semibold mb-1">المبلغ (₪) <span class="text-danger">*</span></label>
                            <input type="number" name="amount" class="form-control form-control-sm"
                                step="0.01" min="0.01"
                                placeholder="الرصيد المتاح: {{ number_format($balance, 2) }}">
                        </div>
                        <div class="mb-2">
                            <label class="small fw-semibold mb-1">تاريخ القبض</label>
                            <input type="date" name="payment_date" class="form-control form-control-sm"
                                value="{{ now()->toDateString() }}">
                        </div>
                        <div class="mb-2">
                            <label class="small fw-semibold mb-1">طريقة الدفع</label>
                            <div class="d-flex gap-3">
                                <div class="form-check form-check-inline">
                                    <input class="form-check-input" type="radio" name="payment_method" value="cash" id="pm_cash" checked>
                                    <label class="form-check-label small" for="pm_cash">💵 كاش</label>
                                </div>
                                <div class="form-check form-check-inline">
                                    <input class="form-check-input" type="radio" name="payment_method" value="bank" id="pm_bank">
                                    <label class="form-check-label small" for="pm_bank">🏦 بنك</label>
                                </div>
                            </div>
                        </div>
                        <div class="mb-3">
                            <label class="small fw-semibold mb-1">ملاحظة</label>
                            <input type="text" name="notes" class="form-control form-control-sm"
                                placeholder="اختياري — مثال: راتب أسبوع W17">
                        </div>
                        <button type="submit" class="btn btn-success btn-sm w-100"
                            {{ $balance <= 0 ? 'disabled' : '' }}>
                            <i class="fas fa-check me-1"></i>تسجيل القبض
                        </button>
                        @if($balance <= 0)
                        <div class="small text-muted text-center mt-1">الرصيد صفر — لا يمكن الصرف</div>
                        @endif
                    </form>
                </div>
            </div>

            {{-- إضافة قيد محاسبي يدوي --}}
            <div class="card shadow-sm mb-3 border-primary">
                <div class="card-header py-2 bg-primary text-white small fw-semibold">
                    <a class="text-decoration-none text-white d-flex justify-content-between align-items-center"
                       data-bs-toggle="collapse" href="#addEntrySection">
                        <span><i class="fas fa-plus-circle me-1"></i>إضافة قيد محاسبي</span>
                        <i class="fas fa-chevron-down fa-xs opacity-75"></i>
                    </a>
                </div>
                <div class="collapse" id="addEntrySection">
                    <form method="POST" action="{{ route('ledger.entry.store', $employee) }}">
                        @csrf
                        <div class="list-group list-group-flush">

                            {{-- التاريخ --}}
                            <div class="list-group-item px-3 py-2">
                                <label class="small fw-semibold text-muted mb-1 d-block">
                                    <i class="fas fa-calendar-alt fa-xs me-1 text-primary"></i>التاريخ <span class="text-danger">*</span>
                                </label>
                                <input type="date" name="entry_date" class="form-control form-control-sm"
                                    value="{{ old('entry_date', now()->toDateString()) }}" required>
                            </div>

                            {{-- نوع القيد — مدخل حر --}}
                            <div class="list-group-item px-3 py-2">
                                <label class="small fw-semibold text-muted mb-1 d-block">
                                    <i class="fas fa-tag fa-xs me-1 text-primary"></i>نوع القيد <span class="text-danger">*</span>
                                </label>
                                <input type="text" name="entry_type" class="form-control form-control-sm"
                                    value="{{ old('entry_type') }}"
                                    placeholder="مثال: راتب، مكافأة، خصم تأخير..." required>
                            </div>

                            {{-- دائن / مدين --}}
                            <div class="list-group-item px-3 py-2 bg-light">
                                <label class="small fw-semibold text-muted mb-2 d-block">
                                    <i class="fas fa-balance-scale fa-xs me-1 text-primary"></i>الجانب <span class="text-danger">*</span>
                                </label>
                                <div class="d-flex gap-2">
                                    <div class="flex-fill">
                                        <input type="radio" class="btn-check" name="side" value="credit"
                                               id="add_side_credit"
                                               {{ old('side', 'credit') === 'credit' ? 'checked' : '' }} required>
                                        <label class="btn btn-outline-success btn-sm w-100 fw-semibold" for="add_side_credit">
                                            <i class="fas fa-arrow-up me-1"></i>دائن (له)
                                        </label>
                                    </div>
                                    <div class="flex-fill">
                                        <input type="radio" class="btn-check" name="side" value="debit"
                                               id="add_side_debit"
                                               {{ old('side') === 'debit' ? 'checked' : '' }}>
                                        <label class="btn btn-outline-danger btn-sm w-100 fw-semibold" for="add_side_debit">
                                            <i class="fas fa-arrow-down me-1"></i>مدين (عليه)
                                        </label>
                                    </div>
                                </div>
                            </div>

                            {{-- المبلغ --}}
                            <div class="list-group-item px-3 py-2">
                                <label class="small fw-semibold text-muted mb-1 d-block">
                                    <i class="fas fa-coins fa-xs me-1 text-primary"></i>المبلغ (₪) <span class="text-danger">*</span>
                                </label>
                                <input type="number" name="amount" class="form-control form-control-sm"
                                    step="0.01" min="0.01"
                                    value="{{ old('amount') }}" placeholder="0.00" required>
                            </div>

                            {{-- البيان --}}
                            <div class="list-group-item px-3 py-2">
                                <label class="small fw-semibold text-muted mb-1 d-block">
                                    <i class="fas fa-align-right fa-xs me-1 text-primary"></i>البيان / الوصف <span class="text-danger">*</span>
                                </label>
                                <input type="text" name="description" class="form-control form-control-sm"
                                    value="{{ old('description') }}" placeholder="مثال: راتب أسبوع W17" required>
                            </div>

                            {{-- حقول إضافية (accordion داخلي) --}}
                            <div class="list-group-item px-3 py-2">
                                <a class="small text-muted text-decoration-none d-flex justify-content-between align-items-center"
                                   data-bs-toggle="collapse" href="#addOptionalFields">
                                    <span><i class="fas fa-sliders-h fa-xs me-1"></i>حقول إضافية <small class="opacity-50">(اختياري)</small></span>
                                    <i class="fas fa-chevron-down fa-xs opacity-50"></i>
                                </a>
                                <div class="collapse mt-2" id="addOptionalFields">
                                    <div class="mb-2">
                                        <label class="small text-muted mb-1">كود الأسبوع</label>
                                        <input type="text" name="fiscal_period" class="form-control form-control-sm"
                                            value="{{ old('fiscal_period') }}" placeholder="مثال: 2026-W17">
                                    </div>
                                    <div class="row g-2">
                                        <div class="col-6">
                                            <label class="small text-muted mb-1">من تاريخ</label>
                                            <input type="date" name="period_start" class="form-control form-control-sm"
                                                value="{{ old('period_start') }}">
                                        </div>
                                        <div class="col-6">
                                            <label class="small text-muted mb-1">إلى تاريخ</label>
                                            <input type="date" name="period_end" class="form-control form-control-sm"
                                                value="{{ old('period_end') }}">
                                        </div>
                                    </div>
                                </div>
                            </div>

                            {{-- زر الحفظ --}}
                            <div class="list-group-item px-3 py-2">
                                <button type="submit" class="btn btn-primary btn-sm w-100">
                                    <i class="fas fa-plus me-1"></i>إضافة القيد
                                </button>
                            </div>

                        </div>
                    </form>
                </div>
            </div>

            {{-- تسجيل / تصفير رصيد افتتاحي --}}
            <div class="card shadow-sm">
                <div class="card-header py-2 small fw-semibold d-flex justify-content-between align-items-center">
                    <a class="text-decoration-none text-dark" data-bs-toggle="collapse" href="#openingBalSection">
                        <i class="fas fa-wallet me-1"></i>رصيد افتتاحي
                        @if($employee->opening_balance != 0)
                            <span class="badge {{ $employee->opening_balance > 0 ? 'bg-success' : 'bg-danger' }} ms-1">
                                {{ number_format($employee->opening_balance, 2) }} ₪
                            </span>
                        @endif
                    </a>
                    {{-- زر تصفير سريع --}}
                    @if($employee->opening_balance != 0)
                    <form method="POST" action="{{ route('employees.opening-balance.reset', $employee) }}"
                          onsubmit="return confirm('هل تريد تصفير الرصيد الافتتاحي؟ سيؤثر على كشف الحساب.')">
                        @csrf
                        <button type="submit" class="btn btn-danger btn-sm py-0 px-2"
                                title="تصفير الرصيد الافتتاحي">
                            <i class="fas fa-times-circle me-1"></i>تصفير
                        </button>
                    </form>
                    @endif
                </div>
                <div class="collapse" id="openingBalSection">
                    <div class="card-body">

                        {{-- الرصيد الحالي --}}
                        <div class="alert {{ $employee->opening_balance == 0 ? 'alert-light' : ($employee->opening_balance > 0 ? 'alert-success' : 'alert-danger') }} py-2 small mb-3">
                            <strong>الرصيد الافتتاحي الحالي:</strong>
                            {{ $employee->opening_balance == 0 ? 'صفر' : number_format($employee->opening_balance, 2) . ' ₪' }}
                            @if($employee->opening_balance < 0)
                                <span class="text-danger">(عليه للشركة)</span>
                            @elseif($employee->opening_balance > 0)
                                <span class="text-success">(له على الشركة)</span>
                            @endif
                        </div>

                        {{-- نموذج التعديل --}}
                        <form method="POST" action="{{ route('employees.opening-balance.update', $employee) }}">
                            @csrf
                            <div class="mb-2">
                                <label class="small mb-1">المبلغ الجديد (₪)</label>
                                <input type="number" name="opening_balance" class="form-control form-control-sm"
                                    step="0.01" value="{{ $employee->opening_balance }}"
                                    placeholder="موجب = له، سالب = عليه">
                                <div class="form-text text-muted" style="font-size:0.7rem">
                                    موجب = الشركة مدينة له | سالب = الموظف مدين للشركة
                                </div>
                            </div>
                            <div class="mb-3">
                                <label class="small mb-1">الملاحظة</label>
                                <input type="text" name="notes" class="form-control form-control-sm"
                                    value="تعديل رصيد افتتاحي">
                            </div>
                            <div class="d-flex gap-2">
                                <button type="submit" class="btn btn-outline-primary btn-sm flex-fill">
                                    <i class="fas fa-save me-1"></i>حفظ
                                </button>
                                @if($employee->opening_balance != 0)
                                <button type="button" class="btn btn-outline-danger btn-sm"
                                    onclick="document.getElementById('openingBalSection').previousElementSibling.querySelector('form').submit()"
                                    title="تصفير">
                                    <i class="fas fa-times"></i>
                                </button>
                                @endif
                            </div>
                        </form>
                    </div>
                </div>
            </div>

        </div>

        {{-- ===== COL-RIGHT: كشف الحساب ===== --}}
        <div class="col-lg-9">
            <div class="card shadow-sm">
                <div class="card-header d-flex justify-content-between align-items-center py-2">
                    <span class="fw-semibold">
                        <i class="fas fa-list me-2"></i>
                        كشف الحساب — {{ \Carbon\Carbon::parse($from)->format('d/m/Y') }} إلى {{ \Carbon\Carbon::parse($to)->format('d/m/Y') }}
                    </span>
                    <span class="badge bg-secondary">{{ $entries->count() }} قيد</span>
                </div>
                <div class="card-body p-0">
                    @if($entries->isEmpty())
                        <div class="text-center py-5 text-muted">
                            <i class="fas fa-inbox fa-2x mb-2 d-block"></i>
                            لا توجد قيود في هذه الفترة
                            <div class="mt-2">
                                <button type="button" class="btn btn-primary btn-sm"
                                    onclick="document.getElementById('addEntrySection').classList.add('show')">
                                    <i class="fas fa-plus me-1"></i>إضافة أول قيد
                                </button>
                            </div>
                        </div>
                    @else
                    <div class="table-responsive">
                        <table class="table table-hover align-middle mb-0 small">
                            <thead class="table-dark">
                                <tr>
                                    <th class="px-2" style="min-width:40px">#</th>
                                    <th class="px-2" style="min-width:90px">التاريخ</th>
                                    <th>البيان</th>
                                    <th class="text-center" style="min-width:90px">الكود</th>
                                    <th class="text-end" style="min-width:100px">دائن (له) ↑</th>
                                    <th class="text-end" style="min-width:100px">مدين (عليه) ↓</th>
                                    <th class="text-end" style="min-width:110px">الرصيد</th>
                                    <th class="text-center px-2" style="min-width:80px">إجراءات</th>
                                </tr>
                            </thead>
                            <tbody>
                                @php
                                    $typeLabels = [
                                        'salary'            => ['label'=>'راتب ساعات',    'color'=>'bg-primary'],
                                        'overtime'          => ['label'=>'أوفرتايم',       'color'=>'bg-info text-dark'],
                                        'bonus'             => ['label'=>'مكافأة',          'color'=>'bg-success'],
                                        'expense'           => ['label'=>'مصروف',           'color'=>'bg-teal text-white'],
                                        'adjustment'        => ['label'=>'تعديل',           'color'=>'bg-secondary'],
                                        'deduction_late'    => ['label'=>'خصم تأخير',      'color'=>'bg-warning text-dark'],
                                        'deduction_absence' => ['label'=>'خصم غياب',       'color'=>'bg-warning text-dark'],
                                        'deduction_manual'  => ['label'=>'خصم يدوي',       'color'=>'bg-danger'],
                                        'loan_installment'  => ['label'=>'قسط سلفة',       'color'=>'bg-danger'],
                                        'loan_disbursement' => ['label'=>'صرف سلفة',       'color'=>'bg-danger'],
                                        'withdrawal'        => ['label'=>'مسحوبات',         'color'=>'bg-orange text-dark'],
                                        'payment'           => ['label'=>'دفع صافي',        'color'=>'bg-dark'],
                                        'opening_balance'   => ['label'=>'رصيد افتتاحي',   'color'=>'bg-secondary'],
                                    ];
                                @endphp
                                @foreach($entries as $entry)
                                @php $t = $typeLabels[$entry->entry_type] ?? ['label'=>$entry->entry_type,'color'=>'bg-secondary']; @endphp
                                <tr class="{{ $entry->credit > 0 ? 'table-success bg-opacity-25' : 'table-danger bg-opacity-10' }}">
                                    <td class="px-2 text-muted small">{{ $entry->id }}</td>
                                    <td class="px-2 text-muted">{{ $entry->entry_date?->format('d/m/Y') }}</td>
                                    <td>
                                        <span class="badge {{ $t['color'] }} me-1">{{ $t['label'] }}</span>
                                        {{ $entry->description }}
                                    </td>
                                    <td class="text-center text-muted">
                                        {{ $entry->fiscal_period ?? '—' }}
                                    </td>
                                    <td class="text-end text-success fw-semibold">
                                        {{ $entry->credit > 0 ? number_format($entry->credit, 2) . ' ₪' : '' }}
                                    </td>
                                    <td class="text-end text-danger fw-semibold">
                                        {{ $entry->debit > 0 ? number_format($entry->debit, 2) . ' ₪' : '' }}
                                    </td>
                                    <td class="text-end fw-bold {{ $entry->balance_after >= 0 ? 'text-success' : 'text-danger' }}">
                                        {{ number_format($entry->balance_after, 2) }} ₪
                                    </td>
                                    <td class="text-center px-2">
                                        <div class="d-flex gap-1 justify-content-center">
                                            <button type="button" class="btn btn-sm btn-outline-warning py-0 px-1"
                                                title="تعديل" data-bs-toggle="modal" data-bs-target="#editEntryModal{{ $entry->id }}">
                                                <i class="fas fa-edit" style="font-size:0.7rem"></i>
                                            </button>
                                            <form action="{{ route('ledger.entry.destroy', $entry) }}" method="POST" class="d-inline"
                                                onsubmit="return confirm('حذف القيد #{{ $entry->id }}؟ سيتم إعادة حساب الأرصدة.')">
                                                @csrf @method('DELETE')
                                                <button type="submit" class="btn btn-sm btn-outline-danger py-0 px-1" title="حذف">
                                                    <i class="fas fa-trash" style="font-size:0.7rem"></i>
                                                </button>
                                            </form>
                                        </div>
                                    </td>
                                </tr>
                                @endforeach
                            </tbody>
                            <tfoot class="table-dark">
                                <tr>
                                    <td colspan="4" class="px-2 fw-bold">المجموع</td>
                                    <td class="text-end text-success fw-bold">{{ number_format($entries->sum('credit'), 2) }} ₪</td>
                                    <td class="text-end text-danger fw-bold">{{ number_format($entries->sum('debit'), 2) }} ₪</td>
                                    <td class="text-end fw-bold {{ $balance >= 0 ? 'text-success' : 'text-danger' }}">
                                        {{ number_format($balance, 2) }} ₪
                                    </td>
                                    <td></td>
                                </tr>
                            </tfoot>
                        </table>
                    </div>
                    @endif
                </div>
            </div>
        </div>

    </div>
</div>

{{-- ===== Modals تعديل القيود ===== --}}
@foreach($entries as $entry)
<div class="modal fade" id="editEntryModal{{ $entry->id }}" tabindex="-1" aria-hidden="true">
    <div class="modal-dialog modal-dialog-centered" dir="rtl">
        <div class="modal-content">
            <div class="modal-header bg-warning text-dark">
                <h6 class="modal-title fw-bold">
                    <i class="fas fa-edit me-2"></i>تعديل القيد #{{ $entry->id }}
                </h6>
                <button type="button" class="btn-close" data-bs-dismiss="modal"></button>
            </div>
            <form action="{{ route('ledger.entry.update', $entry) }}" method="POST">
                @csrf @method('PUT')
                <div class="modal-body p-0">
                    <div class="list-group list-group-flush">

                        {{-- التاريخ + نوع القيد --}}
                        <div class="list-group-item px-3 py-2">
                            <div class="row g-2">
                                <div class="col-6">
                                    <label class="small fw-semibold text-muted mb-1 d-block">التاريخ <span class="text-danger">*</span></label>
                                    <input type="date" name="entry_date" class="form-control form-control-sm"
                                        value="{{ $entry->entry_date?->format('Y-m-d') }}" required>
                                </div>
                                <div class="col-6">
                                    <label class="small fw-semibold text-muted mb-1 d-block">نوع القيد <span class="text-danger">*</span></label>
                                    <input type="text" name="entry_type" class="form-control form-control-sm"
                                        value="{{ $entry->entry_type }}"
                                        placeholder="مثال: راتب، مكافأة..." required>
                                </div>
                            </div>
                        </div>

                        {{-- البيان --}}
                        <div class="list-group-item px-3 py-2">
                            <label class="small fw-semibold text-muted mb-1 d-block">البيان <span class="text-danger">*</span></label>
                            <input type="text" name="description" class="form-control form-control-sm"
                                value="{{ $entry->description }}" required>
                        </div>

                        {{-- دائن / مدين --}}
                        <div class="list-group-item px-3 py-2 bg-light">
                            <label class="small fw-semibold text-muted mb-2 d-block">الجانب <span class="text-danger">*</span></label>
                            <div class="d-flex gap-2 mb-0">
                                <div class="flex-fill">
                                    <input type="radio" class="btn-check" name="side" value="credit"
                                           id="edit_side_credit_{{ $entry->id }}"
                                           {{ $entry->credit > 0 ? 'checked' : '' }} required>
                                    <label class="btn btn-outline-success btn-sm w-100 fw-semibold" for="edit_side_credit_{{ $entry->id }}">
                                        <i class="fas fa-arrow-up me-1"></i>دائن (له)
                                    </label>
                                </div>
                                <div class="flex-fill">
                                    <input type="radio" class="btn-check" name="side" value="debit"
                                           id="edit_side_debit_{{ $entry->id }}"
                                           {{ $entry->debit > 0 ? 'checked' : '' }}>
                                    <label class="btn btn-outline-danger btn-sm w-100 fw-semibold" for="edit_side_debit_{{ $entry->id }}">
                                        <i class="fas fa-arrow-down me-1"></i>مدين (عليه)
                                    </label>
                                </div>
                            </div>
                        </div>

                        {{-- المبلغ --}}
                        <div class="list-group-item px-3 py-2">
                            <label class="small fw-semibold text-muted mb-1 d-block">المبلغ (₪) <span class="text-danger">*</span></label>
                            <input type="number" name="amount" class="form-control form-control-sm"
                                step="0.01" min="0.01"
                                value="{{ $entry->credit > 0 ? $entry->credit : $entry->debit }}" required>
                        </div>

                        {{-- حقول إضافية --}}
                        <div class="list-group-item px-3 py-2">
                            <div class="row g-2">
                                <div class="col-12">
                                    <label class="small text-muted mb-1">كود الأسبوع</label>
                                    <input type="text" name="fiscal_period" class="form-control form-control-sm"
                                        value="{{ $entry->fiscal_period }}" placeholder="2026-W17">
                                </div>
                                <div class="col-6">
                                    <label class="small text-muted mb-1">من تاريخ</label>
                                    <input type="date" name="period_start" class="form-control form-control-sm"
                                        value="{{ $entry->period_start?->format('Y-m-d') }}">
                                </div>
                                <div class="col-6">
                                    <label class="small text-muted mb-1">إلى تاريخ</label>
                                    <input type="date" name="period_end" class="form-control form-control-sm"
                                        value="{{ $entry->period_end?->format('Y-m-d') }}">
                                </div>
                            </div>
                        </div>

                    </div>
                </div>
                <div class="modal-footer">
                    <button type="button" class="btn btn-outline-secondary btn-sm" data-bs-dismiss="modal">إلغاء</button>
                    <button type="submit" class="btn btn-warning btn-sm">
                        <i class="fas fa-save me-1"></i>حفظ التعديل
                    </button>
                </div>
            </form>
        </div>
    </div>
</div>
@endforeach

{{-- ===== Modal استيراد Excel ===== --}}
<div class="modal fade" id="importModal" tabindex="-1" aria-labelledby="importModalLabel" aria-hidden="true">
    <div class="modal-dialog modal-dialog-centered" dir="rtl">
        <div class="modal-content">
            <div class="modal-header bg-success text-white">
                <h5 class="modal-title" id="importModalLabel">
                    <i class="fas fa-file-import me-2"></i>استيراد حركات من Excel
                </h5>
                <button type="button" class="btn-close btn-close-white" data-bs-dismiss="modal"></button>
            </div>
            <form action="{{ route('ledger.import.preview') }}" method="POST" enctype="multipart/form-data">
                @csrf
                <div class="modal-body">

                    <div class="mb-3">
                        <label class="form-label fw-semibold">الموظف <span class="text-danger">*</span></label>
                        <select name="employee_id" class="form-select" required>
                            <option value="">— اختر الموظف —</option>
                            @foreach($employees as $emp)
                                <option value="{{ $emp->id }}"
                                    {{ $emp->id === $employee->id ? 'selected' : '' }}>
                                    {{ $emp->name }}
                                </option>
                            @endforeach
                        </select>
                        <div class="form-text text-muted">الموظف الحالي محدد تلقائياً</div>
                    </div>

                    <div class="mb-3">
                        <label class="form-label fw-semibold">ملف Excel <span class="text-danger">*</span></label>
                        <input type="file" name="file" accept=".xlsx,.xls" class="form-control" required>
                        <div class="form-text text-muted">صيغ مدعومة: xlsx, xls — الحد الأقصى 5MB</div>
                    </div>

                    <div class="alert alert-light border small mb-0 py-2">
                        <i class="fas fa-info-circle text-primary me-1"></i>
                        سيتم عرض معاينة قبل الحفظ النهائي
                    </div>

                </div>
                <div class="modal-footer">
                    <button type="button" class="btn btn-outline-secondary" data-bs-dismiss="modal">إلغاء</button>
                    <button type="submit" class="btn btn-success px-4">
                        <i class="fas fa-search me-2"></i>معاينة الحركات
                    </button>
                </div>
            </form>
        </div>
    </div>
</div>

<script>
function setRange(range) {
    const today = new Date();
    const fmt = d => d.toISOString().split('T')[0];
    let from = new Date(today), to = new Date(today);

    if (range === 'week') {
        from.setDate(today.getDate() - 7);
    } else if (range === 'month') {
        from.setMonth(today.getMonth() - 1);
    } else if (range === '3months') {
        from.setMonth(today.getMonth() - 3);
    } else if (range === 'year') {
        from.setFullYear(today.getFullYear() - 1);
    }

    document.querySelector('[name="from"]').value = fmt(from);
    document.querySelector('[name="to"]').value = fmt(to);
}
</script>
</x-app-layout>
