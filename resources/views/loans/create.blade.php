<x-app-layout>
<x-slot name="title">إضافة سلفة جديدة</x-slot>
<div class="container-fluid py-4" dir="rtl">

    <div class="d-flex justify-content-between align-items-center mb-4">
        <div>
            <h4 class="mb-0 fw-bold">💳 إضافة سلفة جديدة</h4>
            <small class="text-muted">أدخل المبلغ وقيمة القسط أو عدد الأسابيع</small>
        </div>
        <a href="{{ route('loans.index') }}" class="btn btn-outline-secondary btn-sm">
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
                    <h6 class="mb-0"><i class="fas fa-hand-holding-usd me-2"></i>بيانات السلفة</h6>
                </div>
                <div class="card-body p-4">
                    <form action="{{ route('loans.store') }}" method="POST">
                        @csrf
                        <input type="hidden" name="installment_type" value="weekly">

                        {{-- الموظف --}}
                        <div class="mb-4">
                            <label class="form-label fw-semibold">الموظف <span class="text-danger">*</span></label>
                            <select name="employee_id" class="form-select @error('employee_id') is-invalid @enderror" required>
                                <option value="">— اختر موظف —</option>
                                @foreach($employees as $emp)
                                    <option value="{{ $emp->id }}" {{ old('employee_id') == $emp->id ? 'selected' : '' }}>
                                        {{ $emp->name }}
                                        @if($emp->department) ({{ $emp->department->name }}) @endif
                                    </option>
                                @endforeach
                            </select>
                            @error('employee_id')<div class="invalid-feedback">{{ $message }}</div>@enderror
                        </div>

                        {{-- الحقول الثلاثة المترابطة --}}
                        <div class="card bg-light border-0 mb-4 p-3">
                            <div class="text-muted small mb-3 fw-semibold">
                                <i class="fas fa-calculator me-1"></i>
                                أدخل أي حقلين وسيُحسب الثالث تلقائياً
                            </div>
                            <div class="row g-3">
                                {{-- إجمالي السلفة --}}
                                <div class="col-md-4">
                                    <label class="form-label fw-semibold small">إجمالي السلفة (₪) <span class="text-danger">*</span></label>
                                    <input type="number" name="total_amount" id="total_amount"
                                        class="form-control @error('total_amount') is-invalid @enderror"
                                        value="{{ old('total_amount') }}"
                                        step="0.01" min="1" placeholder="0.00"
                                        oninput="calcFrom('total')" required>
                                    @error('total_amount')<div class="invalid-feedback">{{ $message }}</div>@enderror
                                </div>

                                {{-- قيمة القسط --}}
                                <div class="col-md-4">
                                    <label class="form-label fw-semibold small">قسط الأسبوع (₪) <span class="text-danger">*</span></label>
                                    <input type="number" name="installment_amount" id="installment_amount"
                                        class="form-control @error('installment_amount') is-invalid @enderror"
                                        value="{{ old('installment_amount') }}"
                                        step="0.01" min="1" placeholder="0.00"
                                        oninput="calcFrom('installment')" required>
                                    @error('installment_amount')<div class="invalid-feedback">{{ $message }}</div>@enderror
                                </div>

                                {{-- عدد الأقساط --}}
                                <div class="col-md-4">
                                    <label class="form-label fw-semibold small">عدد الأسابيع <span class="text-danger">*</span></label>
                                    <input type="number" name="installments_total" id="installments_total"
                                        class="form-control @error('installments_total') is-invalid @enderror"
                                        value="{{ old('installments_total') }}"
                                        step="1" min="1" max="104" placeholder="0"
                                        oninput="calcFrom('weeks')" required>
                                    @error('installments_total')<div class="invalid-feedback">{{ $message }}</div>@enderror
                                </div>
                            </div>

                            {{-- ملخص حي --}}
                            <div id="loan_summary" class="mt-3 p-3 rounded text-center d-none"
                                 style="background:#e8f5e9;border:1px solid #a5d6a7">
                                <div class="row">
                                    <div class="col-4 border-end">
                                        <div class="small text-muted">المبلغ الكلي</div>
                                        <div class="fw-bold text-success" id="sum_total">—</div>
                                    </div>
                                    <div class="col-4 border-end">
                                        <div class="small text-muted">قسط أسبوعي</div>
                                        <div class="fw-bold text-primary" id="sum_inst">—</div>
                                    </div>
                                    <div class="col-4">
                                        <div class="small text-muted">مدة السداد</div>
                                        <div class="fw-bold text-dark" id="sum_weeks">—</div>
                                    </div>
                                </div>
                                <div class="mt-2 small" id="sum_end_date" style="color:#555"></div>
                            </div>
                        </div>

                        {{-- تاريخ البداية + الوصف --}}
                        <div class="row g-3 mb-4">
                            <div class="col-md-6">
                                <label class="form-label fw-semibold">تاريخ بدء الخصم <span class="text-danger">*</span></label>
                                <input type="date" name="start_date" id="start_date"
                                    class="form-control @error('start_date') is-invalid @enderror"
                                    value="{{ old('start_date', date('Y-m-d')) }}"
                                    oninput="updateEndDate()" required>
                                @error('start_date')<div class="invalid-feedback">{{ $message }}</div>@enderror
                            </div>
                            <div class="col-md-6">
                                <label class="form-label fw-semibold">تاريخ الانتهاء المتوقع</label>
                                <input type="text" id="end_date_display" class="form-control bg-light" readonly placeholder="يُحسب تلقائياً">
                            </div>
                        </div>

                        <div class="mb-4">
                            <label class="form-label fw-semibold">الوصف / ملاحظات</label>
                            <textarea name="description" class="form-control" rows="2"
                                placeholder="اختياري">{{ old('description') }}</textarea>
                        </div>

                        <div class="d-flex gap-2">
                            <button type="submit" class="btn btn-primary px-4">
                                <i class="fas fa-save me-2"></i>حفظ السلفة
                            </button>
                            <a href="{{ route('loans.index') }}" class="btn btn-outline-secondary">إلغاء</a>
                        </div>
                    </form>
                </div>
            </div>
        </div>
    </div>
</div>

<script>
// آخر حقل تم تعديله — لا نعيد حسابه
let lastChanged = null;

function calcFrom(source) {
    lastChanged = source;

    const total   = parseFloat(document.getElementById('total_amount').value)        || 0;
    const inst    = parseFloat(document.getElementById('installment_amount').value)  || 0;
    const weeks   = parseInt(document.getElementById('installments_total').value)    || 0;

    if (source === 'total' || source === 'installment') {
        // عُدِّل المبلغ أو القسط → نحسب عدد الأسابيع
        if (total > 0 && inst > 0) {
            const w = Math.ceil(total / inst);
            document.getElementById('installments_total').value = w;
        }
    } else if (source === 'weeks') {
        // عُدِّل عدد الأسابيع → نحسب القسط
        if (total > 0 && weeks > 0) {
            const i = (total / weeks).toFixed(2);
            document.getElementById('installment_amount').value = i;
        } else if (inst > 0 && weeks > 0) {
            // أو نحسب المبلغ الكلي
            const t = (inst * weeks).toFixed(2);
            document.getElementById('total_amount').value = t;
        }
    }

    updateSummary();
}

function updateSummary() {
    const total = parseFloat(document.getElementById('total_amount').value)       || 0;
    const inst  = parseFloat(document.getElementById('installment_amount').value) || 0;
    const weeks = parseInt(document.getElementById('installments_total').value)   || 0;
    const box   = document.getElementById('loan_summary');

    if (total > 0 && inst > 0 && weeks > 0) {
        box.classList.remove('d-none');
        document.getElementById('sum_total').textContent = total.toFixed(2) + ' ₪';
        document.getElementById('sum_inst').textContent  = inst.toFixed(2)  + ' ₪ / أسبوع';
        document.getElementById('sum_weeks').textContent = weeks + ' أسبوع';
        updateEndDate();
    } else {
        box.classList.add('d-none');
    }
}

function updateEndDate() {
    const weeks   = parseInt(document.getElementById('installments_total').value) || 0;
    const startVal = document.getElementById('start_date').value;
    const display  = document.getElementById('end_date_display');
    const sumEnd   = document.getElementById('sum_end_date');

    if (weeks > 0 && startVal) {
        const start = new Date(startVal);
        const end   = new Date(start);
        end.setDate(start.getDate() + (weeks * 7));

        const fmt = d => d.toLocaleDateString('ar-SA', { year:'numeric', month:'short', day:'numeric' });
        display.value = fmt(end);
        if (sumEnd) sumEnd.textContent = 'تاريخ الانتهاء المتوقع: ' + fmt(end);
    } else {
        display.value = '';
        if (sumEnd) sumEnd.textContent = '';
    }
}

// احسب عند تحميل الصفحة إذا كان في old() values
window.addEventListener('DOMContentLoaded', () => {
    const total = document.getElementById('total_amount').value;
    const inst  = document.getElementById('installment_amount').value;
    if (total && inst) calcFrom('total');
    updateEndDate();
});
</script>
</x-app-layout>