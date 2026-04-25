<x-app-layout>
<x-slot name="title">مراجعة الراتب — {{ $data['employee']->name }}</x-slot>
<div class="container-fluid py-4" dir="rtl">

    {{-- Header --}}
    <div class="d-flex justify-content-between align-items-center mb-4">
        <div>
            <h4 class="mb-0 fw-bold">💰 مراجعة الراتب الأسبوعي</h4>
            <small class="text-muted">راجع الأرقام ثم اضغط تأكيد الحفظ</small>
        </div>
        <a href="{{ route('salary.create') }}" class="btn btn-outline-secondary btn-sm">
            <i class="fas fa-arrow-right me-1"></i> رجوع
        </a>
    </div>

    @if($errors->any())
        <div class="alert alert-danger alert-dismissible fade show">
            <ul class="mb-0">@foreach($errors->all() as $e)<li>{{ $e }}</li>@endforeach</ul>
            <button type="button" class="btn-close" data-bs-dismiss="alert"></button>
        </div>
    @endif

    <form action="{{ route('salary.store') }}" method="POST">
        @csrf
        {{-- Hidden fields --}}
        <input type="hidden" name="employee_id"       value="{{ $data['employee']->id }}">
        <input type="hidden" name="week_start"        value="{{ $data['weekStart'] }}">
        <input type="hidden" name="week_end"          value="{{ $data['weekEnd'] }}">
        <input type="hidden" name="hours_worked"        value="{{ $data['hoursWorked'] }}">
        <input type="hidden" id="overtime_hours_hidden" name="overtime_hours" value="{{ $data['overtimeHours'] }}">
        <input type="hidden" id="overtime_rate_hidden"  name="overtime_rate"  value="{{ $data['overtimeRate'] ?? $data['employee']->overtime_rate ?? 1.5 }}">
        <input type="hidden" name="late_minutes"        value="{{ $data['lateMinutes'] }}">
        <input type="hidden" name="late_factor"         value="{{ $data['lateFactor'] }}">
        <input type="hidden" name="hourly_rate"         value="{{ $data['hourlyRate'] }}">
        <input type="hidden" name="salary_multiplier"   value="{{ $data['salaryMultiplier'] }}">
        <input type="hidden" id="balance_before_hidden" name="balance_before" value="{{ $data['currentBalance'] }}">

        <div class="row g-4">

            {{-- ===== COL-LEFT: Employee Info + Balance + Attendance ===== --}}
            <div class="col-lg-4">

                {{-- بطاقة الموظف --}}
                <div class="card shadow-sm mb-3">
                    <div class="card-header bg-dark text-white py-2">
                        <i class="fas fa-user me-2"></i>بيانات الموظف
                    </div>
                    <div class="card-body">
                        <h5 class="fw-bold mb-1">{{ $data['employee']->name }}</h5>
                        <p class="text-muted small mb-2">{{ $data['employee']->department?->name ?? '—' }}</p>
                        <hr class="my-2">
                        <div class="d-flex justify-content-between small mb-1">
                            <span class="text-muted">الفترة</span>
                            <span class="fw-semibold">{{ \Carbon\Carbon::parse($data['weekStart'])->format('d/m') }} — {{ \Carbon\Carbon::parse($data['weekEnd'])->format('d/m/Y') }}</span>
                        </div>
                        <div class="d-flex justify-content-between small mb-1">
                            <span class="text-muted">الكود المالي</span>
                            <span class="badge bg-secondary">{{ $data['fiscalPeriod'] }}</span>
                        </div>
                        <div class="d-flex justify-content-between small mb-1">
                            <span class="text-muted">أجر الساعة</span>
                            <span class="fw-bold text-primary">{{ number_format($data['hourlyRate'], 2) }} ₪</span>
                        </div>
                        <div class="d-flex justify-content-between small">
                            <span class="text-muted">نوع الراتب</span>
                            <span>{{ $data['employee']->salary_type === 'hourly' ? 'بالساعة' : 'راتب ثابت' }}</span>
                        </div>
                    </div>
                </div>

                {{-- الرصيد الحالي (قابل للتعديل) --}}
                <div class="card shadow-sm mb-3 border-0"
                    style="background: linear-gradient(135deg,#e8f5e9,#f1f8e9)">
                    <div class="card-body py-3">
                        <div class="small text-muted mb-1 text-center">الرصيد الحالي في الصندوق</div>
                        <div class="input-group input-group-sm justify-content-center mb-1">
                            <input type="number" id="balance_input" step="0.01"
                                class="form-control form-control-sm text-center fw-bold fs-5"
                                style="max-width:160px; border-color:#4caf50"
                                value="{{ $data['currentBalance'] }}"
                                oninput="syncBalance(this.value)">
                            <span class="input-group-text fw-bold">₪</span>
                        </div>
                        <div class="small text-muted text-center">قبل احتساب هذا الراتب — يمكنك تعديله يدوياً</div>
                    </div>
                </div>

                {{-- سجل الحضور --}}
                @if($data['attendances']->count() > 0)
                <div class="card shadow-sm">
                    <div class="card-header py-2 small fw-semibold">
                        <i class="fas fa-clock me-1"></i>سجل الحضور ({{ $data['attendances']->count() }} يوم)
                    </div>
                    <div class="card-body p-0">
                        <table class="table table-sm mb-0 small">
                            <thead class="table-light">
                                <tr>
                                    <th class="px-2">التاريخ</th>
                                    <th class="text-center">دخول</th>
                                    <th class="text-center">خروج</th>
                                    <th class="text-center">ساعات</th>
                                </tr>
                            </thead>
                            <tbody>
                                @foreach($data['attendances'] as $att)
                                <tr>
                                    <td class="px-2">{{ $att->date->format('d/m') }} <span class="text-muted">({{ ['أح','إث','ث','أر','خ','ج','س'][$att->date->dayOfWeek] }})</span></td>
                                    <td class="text-center">{{ $att->check_in ? \Carbon\Carbon::parse($att->check_in)->format('H:i') : '—' }}</td>
                                    <td class="text-center">{{ $att->check_out ? \Carbon\Carbon::parse($att->check_out)->format('H:i') : '—' }}</td>
                                    <td class="text-center">{{ number_format($att->work_hours, 1) }}</td>
                                </tr>
                                @endforeach
                            </tbody>
                        </table>
                    </div>
                </div>
                @endif

            </div>

            {{-- ===== COL-RIGHT: Salary Calculation + Adjustments ===== --}}
            <div class="col-lg-8">

                {{-- حساب الراتب --}}
                <div class="card shadow-sm mb-3">
                    <div class="card-header bg-primary text-white py-2">
                        <i class="fas fa-calculator me-2"></i>تفاصيل الاحتساب
                    </div>
                    <div class="card-body p-0">
                        <table class="table mb-0">
                            <tbody>
                                {{-- معامل الراتب --}}
                                @if($data['salaryMultiplier'] != 1)
                                <tr class="table-warning">
                                    <td class="px-3 py-2" colspan="2">
                                        <i class="fas fa-exclamation-triangle text-warning me-1"></i>
                                        <strong>معامل الراتب مفعّل: × {{ $data['salaryMultiplier'] }}</strong>
                                        <small class="text-muted ms-2">— الراتب الأساسي والأوفرتايم مضروبان بهذا المعامل</small>
                                    </td>
                                </tr>
                                @endif

                                {{-- A: ساعات العمل --}}
                                <tr>
                                    <td class="px-3 py-2">
                                        <span class="badge bg-primary me-2">A</span>
                                        ساعات العمل
                                        <small class="text-muted">{{ $data['hoursWorked'] }} ساعة × {{ number_format($data['hourlyRate'], 2) }}@if($data['salaryMultiplier'] != 1) × {{ $data['salaryMultiplier'] }}@endif</small>
                                    </td>
                                    <td class="text-success fw-bold text-end px-3 py-2" style="min-width:130px">
                                        + {{ number_format($data['salaryA'], 2) }} ₪
                                    </td>
                                </tr>

                                {{-- B: الأوفرتايم --}}
                                <tr>
                                    <td class="px-3 py-2">
                                        <span class="badge bg-info me-2">B</span>
                                        الأوفرتايم
                                        <div class="d-flex gap-2 mt-1 align-items-center flex-wrap">
                                            <div>
                                                <label class="form-label small text-muted mb-0">ساعات</label>
                                                <input type="number" id="ot_hours_input" step="0.5" min="0"
                                                    class="form-control form-control-sm calc-input"
                                                    style="width:80px"
                                                    value="{{ $data['overtimeHours'] }}"
                                                    oninput="syncOT()">
                                            </div>
                                            <div>
                                                <label class="form-label small text-muted mb-0">معامل ×</label>
                                                <input type="number" id="ot_rate_input" step="0.1" min="1"
                                                    class="form-control form-control-sm calc-input"
                                                    style="width:75px"
                                                    value="{{ $data['overtimeRate'] ?? $data['employee']->overtime_rate ?? 1.5 }}"
                                                    oninput="syncOT()">
                                            </div>
                                            @if($data['salaryMultiplier'] != 1)
                                            <span class="small text-muted">× {{ $data['salaryMultiplier'] }}</span>
                                            @endif
                                        </div>
                                    </td>
                                    <td class="text-success fw-bold text-end px-3 py-2" id="salary_b_display">
                                        + {{ number_format($data['salaryB'], 2) }} ₪
                                    </td>
                                </tr>

                                {{-- D1: خصم التأخير --}}
                                <tr class="{{ $data['lateMinutes'] > 0 ? 'table-warning' : '' }}">
                                    <td class="px-3 py-2">
                                        <span class="badge bg-warning text-dark me-2">D1</span>
                                        خصم التأخير
                                        <small class="text-muted">{{ $data['lateMinutes'] }} د | سماح {{ $data['lateGrace'] }} د | معامل {{ $data['lateFactor'] }}×</small>
                                    </td>
                                    <td class="text-end px-3 py-2" style="min-width:160px">
                                        <div class="input-group input-group-sm justify-content-end">
                                            <span class="input-group-text text-danger fw-bold">−</span>
                                            <input type="number" name="late_deduction" id="late_deduction"
                                                class="form-control form-control-sm text-end calc-input"
                                                style="max-width:100px"
                                                value="{{ $data['lateDeduction'] }}" step="0.01" min="0">
                                        </div>
                                    </td>
                                </tr>

                                {{-- D2: خصم الغياب --}}
                                <tr>
                                    <td class="px-3 py-2">
                                        <span class="badge bg-warning text-dark me-2">D2</span>
                                        خصم الغياب بإذن
                                    </td>
                                    <td class="text-end px-3 py-2">
                                        <div class="input-group input-group-sm justify-content-end">
                                            <span class="input-group-text text-danger fw-bold">−</span>
                                            <input type="number" name="absence_deduction" id="absence_deduction"
                                                class="form-control form-control-sm text-end calc-input"
                                                style="max-width:100px"
                                                value="0" step="0.01" min="0">
                                        </div>
                                    </td>
                                </tr>

                                {{-- D3: خصومات يدوية --}}
                                <tr>
                                    <td class="px-3 py-2">
                                        <span class="badge bg-danger me-2">D3</span>
                                        خصومات يدوية أخرى
                                    </td>
                                    <td class="text-end px-3 py-2">
                                        <div class="input-group input-group-sm justify-content-end">
                                            <span class="input-group-text text-danger fw-bold">−</span>
                                            <input type="number" name="manual_deductions" id="manual_deductions"
                                                class="form-control form-control-sm text-end calc-input"
                                                style="max-width:100px"
                                                value="0" step="0.01" min="0">
                                        </div>
                                    </td>
                                </tr>

                                {{-- الصافي --}}
                                <tr class="table-success">
                                    <td class="px-3 py-3 fw-bold fs-5">الراتب الصافي</td>
                                    <td class="text-success fw-bold fs-4 text-end px-3 py-3" id="net_salary_display">— ₪</td>
                                </tr>

                                {{-- E: قسط السلفة (يُخصم من الرصيد لا من الراتب) --}}
                                <tr class="{{ $data['activeLoan'] ? 'table-warning' : 'text-muted' }}">
                                    <td class="px-3 py-2">
                                        <span class="badge bg-warning text-dark me-2">E</span>
                                        قسط السلفة
                                        <span class="badge bg-danger text-white border ms-1">
                                            <i class="fas fa-minus-circle"></i> يُخصم من الراتب
                                        </span>
                                        @if($data['activeLoan'])
                                            <small class="text-muted d-block mt-1">
                                                القسط: {{ number_format($data['suggestedLoanDeduct'], 2) }} ₪
                                                | المتبقي: {{ number_format($data['activeLoan']->remaining_amount ?? 0, 2) }} ₪
                                            </small>
                                        @else
                                            <small class="text-muted">لا توجد سلفة نشطة</small>
                                        @endif
                                    </td>
                                    <td class="text-end px-3 py-2">
                                        <div class="input-group input-group-sm justify-content-end">
                                            <span class="input-group-text text-warning fw-bold">−</span>
                                            <input type="number" name="loan_deduction_amount" id="loan_deduction"
                                                class="form-control form-control-sm text-end"
                                                style="max-width:100px"
                                                value="{{ $data['suggestedLoanDeduct'] }}" step="0.01" min="0"
                                                oninput="calcNet()">
                                        </div>
                                    </td>
                                </tr>

                                {{-- الرصيد المتوقع بعد العملية --}}
                                <tr style="background:#f8f9fa">
                                    <td class="px-3 py-2 text-muted small">
                                        <i class="fas fa-wallet me-1"></i>
                                        الرصيد المتوقع بعد الراتب وخصم السلفة
                                    </td>
                                    <td class="text-end px-3 py-2 fw-bold" id="expected_balance">— ₪</td>
                                </tr>
                            </tbody>
                        </table>
                    </div>
                </div>

                {{-- ===== التعديلات المعلّقة ===== --}}
                @if($data['pendingAdjustments']->count() > 0)
                <div class="card shadow-sm mb-3">
                    <div class="card-header bg-warning py-2">
                        <i class="fas fa-tags me-2"></i>
                        التعديلات المعلّقة ({{ $data['pendingAdjustments']->count() }})
                        <small class="ms-2">— اختر ما تريد تطبيقه</small>
                    </div>
                    <div class="card-body p-0">
                        <table class="table table-sm mb-0">
                            <thead class="table-light">
                                <tr>
                                    <th class="px-3" style="width:40px">
                                        <input type="checkbox" id="selectAllAdj" title="تحديد الكل">
                                    </th>
                                    <th>الموظف / النوع</th>
                                    <th>السبب</th>
                                    <th class="text-end px-3">المبلغ</th>
                                    <th>التاريخ</th>
                                </tr>
                            </thead>
                            <tbody>
                                @foreach($data['pendingAdjustments'] as $adj)
                                <tr>
                                    <td class="px-3">
                                        <input type="checkbox" name="adjustment_ids[]" value="{{ $adj->id }}" class="adj-check" checked>
                                    </td>
                                    <td>
                                        <span class="badge
                                            {{ $adj->type === 'bonus' ? 'bg-success' : ($adj->type === 'deduction' ? 'bg-danger' : 'bg-secondary') }}
                                            me-1">
                                            {{ ['bonus'=>'مكافأة','expense'=>'مصروف','deduction'=>'خصم','other'=>'أخرى'][$adj->type] ?? $adj->type }}
                                        </span>
                                    </td>
                                    <td class="small">{{ $adj->reason }}</td>
                                    <td class="text-end px-3 fw-semibold {{ $adj->type === 'bonus' ? 'text-success' : 'text-danger' }}">
                                        {{ $adj->type === 'bonus' ? '+' : '−' }} {{ number_format($adj->amount, 2) }} ₪
                                    </td>
                                    <td class="small text-muted">{{ $adj->adjustment_date?->format('d/m/Y') }}</td>
                                </tr>
                                @endforeach
                            </tbody>
                        </table>
                    </div>
                </div>
                @endif

                {{-- ===== إضافة تعديل جديد أثناء الراتب ===== --}}
                <div class="card shadow-sm mb-3">
                    <div class="card-header py-2">
                        <a class="text-decoration-none text-dark fw-semibold" data-bs-toggle="collapse" href="#newAdjSection">
                            <i class="fas fa-plus-circle text-success me-2"></i>إضافة تعديل جديد (اختياري)
                            <i class="fas fa-chevron-down small ms-1"></i>
                        </a>
                    </div>
                    <div class="collapse" id="newAdjSection">
                        <div class="card-body">
                            <div class="row g-3">
                                <div class="col-md-3">
                                    <label class="form-label small fw-semibold">النوع</label>
                                    <select name="new_adj_type" class="form-select form-select-sm">
                                        <option value="">— بدون —</option>
                                        <option value="bonus">مكافأة (إضافة)</option>
                                        <option value="expense">مصروف (إضافة)</option>
                                        <option value="deduction">خصم</option>
                                        <option value="other">أخرى</option>
                                    </select>
                                </div>
                                <div class="col-md-3">
                                    <label class="form-label small fw-semibold">المبلغ</label>
                                    <input type="number" name="new_adj_amount" class="form-control form-control-sm"
                                        placeholder="0.00" step="0.01" min="0.01">
                                </div>
                                <div class="col-md-6">
                                    <label class="form-label small fw-semibold">السبب / الوصف</label>
                                    <input type="text" name="new_adj_reason" class="form-control form-control-sm"
                                        placeholder="مثال: مكافأة أداء أسبوع كذا">
                                </div>
                            </div>
                        </div>
                    </div>
                </div>

                {{-- ===== طريقة الدفع + ملاحظات ===== --}}
                <div class="card shadow-sm mb-3">
                    <div class="card-body">
                        <div class="row g-3">
                            <div class="col-12">
                                <label class="form-label fw-semibold">طريقة الدفع <span class="text-danger">*</span></label>
                                <div class="d-flex gap-3 mt-1 flex-wrap">
                                    <div class="form-check">
                                        <input class="form-check-input" type="radio" name="payment_method" id="pm_bank" value="bank" onchange="updatePaymentHint(this)">
                                        <label class="form-check-label" for="pm_bank">🏦 تحويل بنكي</label>
                                    </div>
                                    <div class="form-check">
                                        <input class="form-check-input" type="radio" name="payment_method" id="pm_cash" value="cash" onchange="updatePaymentHint(this)">
                                        <label class="form-check-label" for="pm_cash">💵 كاش</label>
                                    </div>
                                    <div class="form-check">
                                        <input class="form-check-input" type="radio" name="payment_method" id="pm_defer" value="deferred" checked onchange="updatePaymentHint(this)">
                                        <label class="form-check-label fw-semibold text-primary" for="pm_defer">📋 ترحيل للرصيد</label>
                                    </div>
                                </div>
                                {{-- تلميح يتغير حسب الاختيار --}}
                                <div id="payment_hint" class="mt-2 small px-2 py-1 rounded"
                                     style="background:#e8f4fd;color:#1565c0">
                                    📋 سيُضاف الراتب الصافي لرصيد الموظف — يمكن صرفه لاحقاً من كشف الحساب
                                </div>
                            </div>
                            <div class="col-12">
                                <label class="form-label fw-semibold">ملاحظات</label>
                                <input type="text" name="notes" class="form-control form-control-sm" placeholder="اختياري">
                            </div>
                        </div>
                    </div>
                </div>

                {{-- ===== إشعار SMS ===== --}}
                <div class="card shadow-sm mb-3 border-0" style="background:#f0fdf4">
                    <div class="card-body py-3">
                        <div class="form-check mb-2">
                            <input class="form-check-input" type="checkbox" name="send_sms" id="send_sms" value="1"
                                {{ $data['employee']->mobile ? '' : 'disabled' }}
                                onchange="toggleSms(this)">
                            <label class="form-check-label fw-semibold" for="send_sms">
                                📱 إرسال SMS للموظف
                                @if(!$data['employee']->mobile)
                                    <span class="text-danger small">(لا يوجد رقم جوال مسجّل)</span>
                                @else
                                    <span class="text-muted small">({{ $data['employee']->mobile }})</span>
                                @endif
                            </label>
                        </div>
                        <div id="sms_section" style="display:none">
                            <textarea name="sms_message" id="sms_message" class="form-control form-control-sm" rows="2"></textarea>
                            <small class="text-muted">يمكنك تعديل نص الرسالة قبل الإرسال</small>
                        </div>
                    </div>
                </div>

                {{-- ===== زر الحفظ ===== --}}
                <div class="d-flex gap-2 justify-content-end">
                    <a href="{{ route('salary.create') }}" class="btn btn-outline-secondary">
                        <i class="fas fa-arrow-right me-1"></i> رجوع
                    </a>
                    <button type="submit" class="btn btn-success btn-lg px-5">
                        <i class="fas fa-save me-2"></i>تأكيد وحفظ الراتب
                    </button>
                </div>

            </div>
        </div>
    </form>
</div>

<script>
const SALARY_A   = {{ (float) $data['salaryA'] }};
const HOURLY_R   = {{ (float) $data['hourlyRate'] }};
const SALARY_MUL = {{ (float) $data['salaryMultiplier'] }};

let salaryB = {{ (float) $data['salaryB'] }};

// مزامنة الأوفرتايم عند تغيير الساعات أو المعامل
function syncOT() {
    const otHours = parseFloat(document.getElementById('ot_hours_input').value) || 0;
    const otRate  = parseFloat(document.getElementById('ot_rate_input').value)  || 1.5;

    // تحديث الحقول المخفية
    document.getElementById('overtime_hours_hidden').value = otHours;
    document.getElementById('overtime_rate_hidden').value  = otRate;

    // إعادة حساب قيمة B
    salaryB = Math.round(otHours * HOURLY_R * otRate * SALARY_MUL * 100) / 100;

    // عرض القيمة
    const bEl = document.getElementById('salary_b_display');
    if (bEl) bEl.textContent = '+ ' + salaryB.toFixed(2) + ' ₪';

    calcNet();
}

// مزامنة الرصيد القابل للتعديل
function syncBalance(val) {
    document.getElementById('balance_before_hidden').value = val || 0;
    calcNet();
}

function calcNet() {
    let additions = 0;
    let deductions = 0;
    let loanDeduct = 0;

    // A + B (B يتغير عند تعديل الأوفرتايم)
    additions += SALARY_A + salaryB;

    // التعديلات المحددة من القائمة المعلّقة
    document.querySelectorAll('.adj-check:checked').forEach(function(el) {
        const row = el.closest('tr');
        const amountText = row.querySelector('td:nth-child(4)').textContent.trim();
        const isPlus = amountText.startsWith('+');
        const val = parseFloat(amountText.replace(/[^0-9.]/g, '')) || 0;
        if (isPlus) additions += val;
        else deductions += val;
    });

    // خصومات الراتب (D1 + D2 + D3)
    deductions += parseFloat(document.getElementById('late_deduction').value) || 0;
    deductions += parseFloat(document.getElementById('absence_deduction').value) || 0;
    deductions += parseFloat(document.getElementById('manual_deductions').value) || 0;

    // قسط السلفة (E)
    loanDeduct = parseFloat(document.getElementById('loan_deduction').value) || 0;
    deductions += loanDeduct;

    const net = Math.max(0, additions - deductions);
    const el = document.getElementById('net_salary_display');
    el.textContent = net.toFixed(2) + ' ₪';

    // الرصيد المتوقع بعد الراتب
    const currentBal = parseFloat(document.getElementById('balance_input').value) || 0;
    const newBal = currentBal + net;
    const balEl = document.getElementById('expected_balance');
    if (balEl) {
        balEl.textContent = newBal.toFixed(2) + ' ₪';
        balEl.className = 'fw-bold ' + (newBal >= 0 ? 'text-success' : 'text-danger');
    }
}

// Listen to all calc inputs
document.querySelectorAll('.calc-input, .adj-check').forEach(el => {
    el.addEventListener('input', calcNet);
    el.addEventListener('change', calcNet);
});

// Select all adjustments checkbox
const selectAll = document.getElementById('selectAllAdj');
if (selectAll) {
    selectAll.addEventListener('change', function() {
        document.querySelectorAll('.adj-check').forEach(el => {
            el.checked = selectAll.checked;
        });
        calcNet();
    });
}

calcNet();

function toggleSms(el) {
    const section = document.getElementById('sms_section');
    section.style.display = el.checked ? 'block' : 'none';
    if (el.checked) updateSmsMessage();
}

function updateSmsMessage() {
    const net = parseFloat(document.getElementById('net_salary_display').textContent) || 0;
    const bankAccount = '{{ $data['employee']->bank_account ?? '' }}';
    const msg = `تم ايداع الراتب ${net.toFixed(2)} شيكل الى حسابك (${bankAccount || 'غير مسجّل'})`;
    document.getElementById('sms_message').value = msg;
}

// تحديث نص SMS عند تغيير الأرقام
document.querySelectorAll('.calc-input, .adj-check').forEach(el => {
    el.addEventListener('change', function() {
        if (document.getElementById('send_sms').checked) updateSmsMessage();
    });
});

function updatePaymentHint(el) {
    const hint = document.getElementById('payment_hint');
    const hints = {
        'bank':     { bg:'#e8f5e9', color:'#2e7d32', text:'🏦 سيُدفع الراتب بتحويل بنكي ويُخصم من الرصيد فوراً' },
        'cash':     { bg:'#fff8e1', color:'#e65100', text:'💵 سيُدفع الراتب نقداً ويُخصم من الرصيد فوراً' },
        'deferred': { bg:'#e8f4fd', color:'#1565c0', text:'📋 سيُضاف الراتب الصافي لرصيد الموظف — يمكن صرفه لاحقاً من كشف الحساب' },
    };
    const h = hints[el.value] || hints['deferred'];
    hint.style.background = h.bg;
    hint.style.color = h.color;
    hint.textContent = h.text;
}
</script>
</x-app-layout>