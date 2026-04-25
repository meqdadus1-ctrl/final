<x-app-layout>
<x-slot name="title">تعديل السلفة — {{ $loan->employee->name ?? '' }}</x-slot>
<div class="container-fluid py-4" dir="rtl">

    <div class="d-flex justify-content-between align-items-center mb-4">
        <div>
            <h4 class="mb-0 fw-bold">✏️ تعديل السلفة</h4>
            <small class="text-muted">{{ $loan->employee->name ?? '—' }}</small>
        </div>
        <a href="{{ route('loans.show', $loan->id) }}" class="btn btn-outline-secondary btn-sm">
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
                <div class="card-header py-3 fw-semibold">
                    <i class="fas fa-edit me-2"></i>بيانات السلفة
                </div>
                <div class="card-body p-4">
                    <form action="{{ route('loans.update', $loan->id) }}" method="POST">
                        @csrf
                        @method('PUT')

                        {{-- معلومات ثابتة --}}
                        <div class="row g-3 mb-4">
                            <div class="col-md-12">
                                <label class="form-label fw-semibold small">الموظف</label>
                                <input type="text" class="form-control bg-light" value="{{ $loan->employee->name ?? '—' }}" disabled>
                            </div>
                        </div>

                        {{-- الحقول الثلاثة المترابطة --}}
                        <div class="card bg-light border-0 mb-4 p-3">
                            <div class="text-muted small mb-3 fw-semibold">
                                <i class="fas fa-calculator me-1"></i>
                                عدّل أي حقلين وسيُحسب الثالث تلقائياً
                            </div>
                            <div class="row g-3">
                                <div class="col-md-4">
                                    <label class="form-label fw-semibold small">إجمالي السلفة (₪)</label>
                                    <input type="number" name="total_amount" id="total_amount"
                                        class="form-control"
                                        value="{{ old('total_amount', $loan->total_amount) }}"
                                        step="0.01" min="1"
                                        oninput="calcFrom('total')" required>
                                </div>
                                <div class="col-md-4">
                                    <label class="form-label fw-semibold small">قسط الأسبوع (₪)</label>
                                    <input type="number" name="installment_amount" id="installment_amount"
                                        class="form-control"
                                        value="{{ old('installment_amount', $loan->installment_amount) }}"
                                        step="0.01" min="1"
                                        oninput="calcFrom('installment')" required>
                                </div>
                                <div class="col-md-4">
                                    <label class="form-label fw-semibold small">عدد الأسابيع</label>
                                    <input type="number" name="installments_total" id="installments_total"
                                        class="form-control"
                                        value="{{ old('installments_total', $loan->installments_total) }}"
                                        step="1" min="1" max="104"
                                        oninput="calcFrom('weeks')" required>
                                </div>
                            </div>

                            {{-- ملخص حي --}}
                            <div id="loan_summary" class="mt-3 p-3 rounded text-center"
                                 style="background:#e8f5e9;border:1px solid #a5d6a7">
                                <div class="row">
                                    <div class="col-4 border-end">
                                        <div class="small text-muted">المبلغ الكلي</div>
                                        <div class="fw-bold text-success" id="sum_total">{{ number_format($loan->total_amount, 2) }} ₪</div>
                                    </div>
                                    <div class="col-4 border-end">
                                        <div class="small text-muted">قسط أسبوعي</div>
                                        <div class="fw-bold text-primary" id="sum_inst">{{ number_format($loan->installment_amount, 2) }} ₪ / أسبوع</div>
                                    </div>
                                    <div class="col-4">
                                        <div class="small text-muted">مدة السداد</div>
                                        <div class="fw-bold text-dark" id="sum_weeks">{{ $loan->installments_total }} أسبوع</div>
                                    </div>
                                </div>
                                <div class="mt-2 small text-muted" id="sum_end_date"></div>
                            </div>

                            {{-- تقدم السداد --}}
                            @if($loan->installments_paid > 0)
                            <div class="mt-2 p-2 bg-white rounded border small">
                                <div class="d-flex justify-content-between mb-1">
                                    <span class="text-muted">تم سداده</span>
                                    <span class="fw-semibold text-success">{{ number_format($loan->amount_paid, 2) }} ₪ ({{ $loan->installments_paid }} قسط)</span>
                                </div>
                                <div class="progress" style="height:6px">
                                    <div class="progress-bar bg-success"
                                         style="width:{{ min(100, round($loan->amount_paid / $loan->total_amount * 100)) }}%">
                                    </div>
                                </div>
                                <div class="text-muted mt-1">المتبقي: {{ number_format($loan->total_amount - $loan->amount_paid, 2) }} ₪</div>
                            </div>
                            @endif
                        </div>

                        {{-- الحالة + الإيقاف --}}
                        <div class="row g-3 mb-4">
                            <div class="col-md-6">
                                <label class="form-label fw-semibold">الحالة</label>
                                <select name="status" class="form-select @error('status') is-invalid @enderror">
                                    <option value="active"    {{ (old('status', $loan->status)) === 'active'    ? 'selected' : '' }}>✅ نشطة</option>
                                    <option value="completed" {{ (old('status', $loan->status)) === 'completed' ? 'selected' : '' }}>🏁 مكتملة</option>
                                    <option value="cancelled" {{ (old('status', $loan->status)) === 'cancelled' ? 'selected' : '' }}>❌ ملغية</option>
                                </select>
                                @error('status')<div class="invalid-feedback">{{ $message }}</div>@enderror
                            </div>
                            <div class="col-md-6 d-flex align-items-end pb-1">
                                <div class="form-check form-switch">
                                    <input type="hidden" name="is_paused" value="0">
                                    <input type="checkbox" name="is_paused" value="1"
                                           class="form-check-input" id="is_paused"
                                           {{ $loan->is_paused ? 'checked' : '' }}>
                                    <label class="form-check-label" for="is_paused">
                                        ⏸️ إيقاف الخصم مؤقتاً
                                    </label>
                                </div>
                            </div>
                        </div>

                        <div class="mb-4">
                            <label class="form-label fw-semibold">الوصف / ملاحظات</label>
                            <textarea name="description" class="form-control @error('description') is-invalid @enderror"
                                rows="2">{{ old('description', $loan->description) }}</textarea>
                            @error('description')<div class="invalid-feedback">{{ $message }}</div>@enderror
                        </div>

                        <div class="d-flex gap-2">
                            <button type="submit" class="btn btn-primary px-4">
                                <i class="fas fa-save me-2"></i>حفظ التعديلات
                            </button>
                            <a href="{{ route('loans.show', $loan->id) }}" class="btn btn-outline-secondary">إلغاء</a>
                        </div>
                    </form>
                </div>
            </div>
        </div>
    </div>
</div>

<script>
function calcFrom(source) {
    const total = parseFloat(document.getElementById('total_amount').value)        || 0;
    const inst  = parseFloat(document.getElementById('installment_amount').value)  || 0;
    const weeks = parseInt(document.getElementById('installments_total').value)    || 0;

    if (source === 'total' || source === 'installment') {
        if (total > 0 && inst > 0) {
            document.getElementById('installments_total').value = Math.ceil(total / inst);
        }
    } else if (source === 'weeks') {
        if (total > 0 && weeks > 0) {
            document.getElementById('installment_amount').value = (total / weeks).toFixed(2);
        } else if (inst > 0 && weeks > 0) {
            document.getElementById('total_amount').value = (inst * weeks).toFixed(2);
        }
    }
    updateSummary();
}

function updateSummary() {
    const total = parseFloat(document.getElementById('total_amount').value)       || 0;
    const inst  = parseFloat(document.getElementById('installment_amount').value) || 0;
    const weeks = parseInt(document.getElementById('installments_total').value)   || 0;

    if (total > 0) document.getElementById('sum_total').textContent = total.toFixed(2) + ' ₪';
    if (inst  > 0) document.getElementById('sum_inst').textContent  = inst.toFixed(2)  + ' ₪ / أسبوع';
    if (weeks > 0) document.getElementById('sum_weeks').textContent = weeks + ' أسبوع';

    // تاريخ الانتهاء
    const startVal = '{{ $loan->start_date ? $loan->start_date->toDateString() : now()->toDateString() }}';
    if (weeks > 0 && startVal) {
        const start = new Date(startVal);
        const end   = new Date(start);
        end.setDate(start.getDate() + (weeks * 7));
        const fmt = d => d.toLocaleDateString('ar-SA', { year:'numeric', month:'short', day:'numeric' });
        document.getElementById('sum_end_date').textContent = 'تاريخ الانتهاء المتوقع: ' + fmt(end);
    }
}

window.addEventListener('DOMContentLoaded', updateSummary);
</script>
</x-app-layout>
