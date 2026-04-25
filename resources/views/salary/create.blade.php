<x-app-layout>
<x-slot name="title">احتساب راتب أسبوعي</x-slot>
<div class="container-fluid py-4" dir="rtl">

    {{-- Header --}}
    <div class="d-flex justify-content-between align-items-center mb-4">
        <div>
            <h4 class="mb-0 fw-bold">💰 احتساب راتب أسبوعي</h4>
            <small class="text-muted">اختر الموظف والفترة ثم اضغط احتساب</small>
        </div>
        <a href="{{ route('salary.index') }}" class="btn btn-outline-secondary btn-sm">
            <i class="fas fa-arrow-right me-1"></i> العودة
        </a>
    </div>

    @if($errors->any())
        <div class="alert alert-danger alert-dismissible fade show">
            <ul class="mb-0">@foreach($errors->all() as $e)<li>{{ $e }}</li>@endforeach</ul>
            <button type="button" class="btn-close" data-bs-dismiss="alert"></button>
        </div>
    @endif

    <div class="row justify-content-center">
        <div class="col-lg-7">
            <div class="card shadow-sm">
                <div class="card-header bg-primary text-white py-3">
                    <h6 class="mb-0"><i class="fas fa-calculator me-2"></i>بيانات احتساب الراتب</h6>
                </div>
                <div class="card-body p-4">
                    <form method="POST" action="{{ route('salary.calculate') }}">
                        @csrf

                        {{-- الموظف --}}
                        <div class="mb-4">
                            <label class="form-label fw-semibold">الموظف <span class="text-danger">*</span></label>
                            <select name="employee_id" class="form-select" required>
                                <option value="">— اختر موظف —</option>
                                @foreach($employees as $emp)
                                    <option value="{{ $emp->id }}" {{ old('employee_id') == $emp->id ? 'selected' : '' }}>
                                        {{ $emp->name }}
                                        @if($emp->department) ({{ $emp->department->name }}) @endif
                                        — {{ $emp->salary_type === 'hourly' ? 'بالساعة' : 'راتب ثابت' }}
                                    </option>
                                @endforeach
                            </select>
                        </div>

                        {{-- الفترة الأسبوعية --}}
                        <div class="row mb-3">
                            <div class="col-md-6">
                                <label class="form-label fw-semibold">بداية الأسبوع (الخميس) <span class="text-danger">*</span></label>
                                <input type="date" name="week_start" id="week_start" class="form-control"
                                    value="{{ old('week_start', $weekStart) }}" required>
                            </div>
                            <div class="col-md-6">
                                <label class="form-label fw-semibold">نهاية الأسبوع (الأربعاء) <span class="text-danger">*</span></label>
                                <input type="date" name="week_end" id="week_end" class="form-control"
                                    value="{{ old('week_end', $weekEnd) }}" required>
                            </div>
                        </div>

                        {{-- أزرار سريعة للأسابيع --}}
                        <div class="mb-4">
                            <div class="d-flex gap-2 flex-wrap">
                                <button type="button" class="btn btn-outline-secondary btn-sm" onclick="setWeek(0)">الأسبوع الحالي</button>
                                <button type="button" class="btn btn-outline-secondary btn-sm" onclick="setWeek(-1)">الأسبوع الماضي</button>
                                <button type="button" class="btn btn-outline-secondary btn-sm" onclick="setWeek(-2)">قبل أسبوعين</button>
                            </div>
                        </div>

                        <hr>

                        {{-- إعدادات التأخير --}}
                        <h6 class="fw-semibold text-secondary mb-3">⚙️ إعدادات التأخير</h6>
                        <div class="row mb-4">
                            <div class="col-md-6">
                                <label class="form-label fw-semibold">مهلة التسامح <small class="text-muted fw-normal">(دقائق)</small></label>
                                <input type="number" name="late_grace" class="form-control"
                                    value="{{ old('late_grace', 5) }}" min="0" max="60">
                                <div class="form-text">التأخير أقل من هذا لا يُحسب</div>
                            </div>
                            <div class="col-md-6">
                                <label class="form-label fw-semibold">معامل التأخير</label>
                                <select name="late_factor" class="form-select">
                                    <option value="0"  {{ old('late_factor') == '0'   ? 'selected' : '' }}>× 0 (بدون خصم)</option>
                                    <option value="1"  {{ old('late_factor', '1') == '1' ? 'selected' : '' }}>× 1.0 (عادي)</option>
                                    <option value="1.5"{{ old('late_factor') == '1.5' ? 'selected' : '' }}>× 1.5</option>
                                    <option value="2"  {{ old('late_factor') == '2'   ? 'selected' : '' }}>× 2.0 (مضاعف)</option>
                                </select>
                                <div class="form-text">مضاعف خصم دقيقة التأخير من الراتب</div>
                            </div>
                        </div>

                        <hr>

                        {{-- معامل الراتب --}}
                        <h6 class="fw-semibold text-secondary mb-3">💰 معامل الراتب (لهذه الدفعة)</h6>
                        <div class="row mb-4">
                            <div class="col-md-6">
                                <label class="form-label fw-semibold">معامل الراتب</label>
                                {{-- الحقل المخفي يحتوي القيمة الفعلية المُرسَلة للسيرفر --}}
                                <input type="hidden" name="salary_multiplier" id="salary_multiplier_hidden"
                                    value="{{ old('salary_multiplier', '1') }}">
                                <select id="salary_multiplier_sel" class="form-select">
                                    @php $oldMul = old('salary_multiplier', '1'); @endphp
                                    <option value="1"   {{ $oldMul == '1'   ? 'selected' : '' }}>× 1 — عادي (افتراضي)</option>
                                    <option value="1.5" {{ $oldMul == '1.5' ? 'selected' : '' }}>× 1.5 — مرة ونصف</option>
                                    <option value="2"   {{ $oldMul == '2'   ? 'selected' : '' }}>× 2 — ضعف الراتب</option>
                                    <option value="custom" {{ !in_array($oldMul, ['1','1.5','2']) && $oldMul != '1' ? 'selected' : '' }}>مخصص...</option>
                                </select>
                                <div class="form-text">يضرب إجمالي الراتب (A+B) قبل الخصومات</div>
                            </div>
                            <div class="col-md-6" id="custom_multiplier_wrap" style="display:none">
                                <label class="form-label fw-semibold">قيمة مخصصة</label>
                                <input type="number" name="salary_multiplier_custom" id="salary_multiplier_custom"
                                    class="form-control" step="0.01" min="0.01" max="10"
                                    placeholder="مثال: 1.25" value="{{ old('salary_multiplier_custom') }}">
                                <div class="form-text">اكتب المعامل يدوياً</div>
                            </div>
                        </div>

                        <div class="d-grid">
                            <button type="submit" class="btn btn-primary btn-lg">
                                <i class="fas fa-calculator me-2"></i>احتساب الراتب
                            </button>
                        </div>
                    </form>
                </div>
            </div>

            <div class="alert alert-info mt-3 small">
                <i class="fas fa-info-circle me-1"></i>
                سيتم استخراج سجلات الحضور تلقائياً للفترة المحددة. الجمعة مستبعدة تلقائياً.
                ستظهر السلف النشطة والتعديلات المعلّقة في صفحة المراجعة.
            </div>
        </div>
    </div>
</div>

<script>
function setWeek(offsetWeeks) {
    const today = new Date();
    const dayOfWeek = today.getDay(); // 0=Sun, 4=Thu, 5=Fri, 6=Sat
    let daysBack = (dayOfWeek >= 4) ? (dayOfWeek - 4) : (dayOfWeek + 3);
    const weekStart = new Date(today);
    weekStart.setDate(today.getDate() - daysBack + (offsetWeeks * 7));
    const weekEnd = new Date(weekStart);
    weekEnd.setDate(weekStart.getDate() + 6);
    const fmt = d => d.toISOString().split('T')[0];
    document.getElementById('week_start').value = fmt(weekStart);
    document.getElementById('week_end').value = fmt(weekEnd);
}

// معامل الراتب — الـ select يتحكم في الـ hidden input
const mulSel    = document.getElementById('salary_multiplier_sel');
const mulHidden = document.getElementById('salary_multiplier_hidden');
const mulWrap   = document.getElementById('custom_multiplier_wrap');
const mulCustom = document.getElementById('salary_multiplier_custom');

function syncMultiplier() {
    if (mulSel.value === 'custom') {
        mulWrap.style.display = 'block';
        // لا تحدّث الـ hidden حتى يكتب المستخدم قيمة
    } else {
        mulWrap.style.display = 'none';
        mulHidden.value = mulSel.value;
    }
}

mulSel.addEventListener('change', syncMultiplier);
mulCustom.addEventListener('input', function() {
    mulHidden.value = this.value || '1';
});

// عند submit: تحقق أن القيمة صالحة
document.querySelector('form').addEventListener('submit', function(e) {
    if (mulSel.value === 'custom') {
        const val = parseFloat(mulCustom.value);
        if (!val || val <= 0 || val > 10) {
            e.preventDefault();
            alert('يرجى إدخال قيمة معامل راتب صحيحة (بين 0.01 و 10)');
            return;
        }
        mulHidden.value = val;
    }
});

// تهيئة أولية
syncMultiplier();
</script>
</x-app-layout>