<x-app-layout>
<x-slot name="title">تعديل راتب {{ $salary->employee?->name }}</x-slot>
<div class="container-fluid py-4" dir="rtl">

    <div class="d-flex justify-content-between align-items-center mb-4">
        <div>
            <h4 class="mb-0 fw-bold">✏️ تعديل الراتب</h4>
            <small class="text-muted">{{ $salary->employee?->name }} — {{ $salary->fiscal_period }}</small>
        </div>
        <a href="{{ route('salary.show', $salary) }}" class="btn btn-outline-secondary btn-sm">
            <i class="fas fa-arrow-right me-1"></i> العودة
        </a>
    </div>

    <div class="row justify-content-center">
        <div class="col-md-8">
            <div class="card shadow-sm">
                <div class="card-header">
                    <span class="fw-semibold">بيانات الراتب</span>
                </div>
                <div class="card-body">
                    <form action="{{ route('salary.update', $salary) }}" method="POST">
                        @csrf @method('PUT')

                        <div class="row g-3">
                            {{-- معلومات ثابتة --}}
                            <div class="col-md-6">
                                <label class="form-label fw-semibold">الموظف</label>
                                <input type="text" class="form-control" value="{{ $salary->employee?->name }}" disabled>
                            </div>
                            <div class="col-md-6">
                                <label class="form-label fw-semibold">الفترة</label>
                                <input type="text" class="form-control" value="{{ $salary->fiscal_period }}" disabled>
                            </div>
                            <div class="col-md-6">
                                <label class="form-label fw-semibold">الصافي</label>
                                <input type="text" class="form-control text-success fw-bold"
                                    value="{{ number_format($salary->net_salary, 2) }} ₪" disabled>
                            </div>
                            <div class="col-md-6">
                                <label class="form-label fw-semibold">طريقة الدفع</label>
                                <select name="payment_method" class="form-select">
                                    <option value="cash"     {{ $salary->payment_method === 'cash'     ? 'selected' : '' }}>💵 كاش</option>
                                    <option value="bank"     {{ $salary->payment_method === 'bank'     ? 'selected' : '' }}>🏦 بنك</option>
                                    <option value="deferred" {{ $salary->payment_method === 'deferred' ? 'selected' : '' }}>📋 ترحيل</option>
                                </select>
                            </div>
                            <div class="col-md-6">
                                <label class="form-label fw-semibold">إضافات يدوية ₪</label>
                                <input type="number" name="manual_additions" step="0.01" min="0"
                                    class="form-control" value="{{ $salary->manual_additions }}">
                            </div>
                            <div class="col-md-6">
                                <label class="form-label fw-semibold">خصومات يدوية ₪</label>
                                <input type="number" name="manual_deductions" step="0.01" min="0"
                                    class="form-control" value="{{ $salary->manual_deductions }}">
                            </div>
                            <div class="col-12">
                                <label class="form-label fw-semibold">ملاحظات</label>
                                <textarea name="notes" class="form-control" rows="3">{{ $salary->notes }}</textarea>
                            </div>
                        </div>

                        <div class="d-flex gap-2 mt-4">
                            <button type="submit" class="btn btn-primary">
                                <i class="fas fa-save me-1"></i> حفظ التعديلات
                            </button>
                            <a href="{{ route('salary.show', $salary) }}" class="btn btn-outline-secondary">إلغاء</a>
                        </div>
                    </form>
                </div>
            </div>
        </div>
    </div>

</div>
</x-app-layout>
