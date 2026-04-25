{{-- resources/views/payslips/edit.blade.php --}}
<x-app-layout>
    <x-slot name="title">تعديل كشف الراتب</x-slot>
<div class="container-fluid py-4" dir="rtl">

    <div class="d-flex align-items-center gap-3 mb-4">
        <a href="{{ route('payslips.show', $payslip) }}" class="btn btn-outline-secondary btn-sm">← رجوع</a>
        <div>
            <h4 class="mb-0 fw-bold">تعديل كشف الراتب</h4>
            <small class="text-muted">{{ $payslip->employee->name }} — {{ $payslip->month_name }} {{ $payslip->year }}</small>
        </div>
    </div>

    @if($errors->any())
        <div class="alert alert-danger">
            <ul class="mb-0">@foreach($errors->all() as $e)<li>{{ $e }}</li>@endforeach</ul>
        </div>
    @endif

    <form action="{{ route('payslips.update', $payslip) }}" method="POST">
        @csrf @method('PUT')

        <div class="row g-4">

            {{-- Info (readonly) --}}
            <div class="col-12">
                <div class="card shadow-sm">
                    <div class="card-header fw-semibold bg-secondary text-white">👤 الموظف والفترة (للقراءة فقط)</div>
                    <div class="card-body">
                        <div class="row g-3">
                            <div class="col-md-6">
                                <label class="form-label fw-semibold">الموظف</label>
                                <input type="text" class="form-control" value="{{ $payslip->employee->name }}" readonly>
                            </div>
                            <div class="col-md-3">
                                <label class="form-label fw-semibold">الشهر</label>
                                <input type="text" class="form-control" value="{{ $payslip->month_name }}" readonly>
                            </div>
                            <div class="col-md-3">
                                <label class="form-label fw-semibold">السنة</label>
                                <input type="text" class="form-control" value="{{ $payslip->year }}" readonly>
                            </div>
                        </div>
                    </div>
                </div>
            </div>

            {{-- Allowances --}}
            <div class="col-md-6">
                <div class="card shadow-sm h-100">
                    <div class="card-header fw-semibold bg-success text-white">✅ الإيرادات والبدلات</div>
                    <div class="card-body">
                        <div class="row g-3">
                            <div class="col-12">
                                <label class="form-label fw-semibold">الراتب الأساسي <span class="text-danger">*</span></label>
                                <input type="number" name="basic_salary" step="0.01" min="0"
                                    class="form-control calc-field" value="{{ old('basic_salary', $payslip->basic_salary) }}" required>
                            </div>
                            <div class="col-6">
                                <label class="form-label">بدل سكن</label>
                                <input type="number" name="housing_allowance" step="0.01" min="0"
                                    class="form-control calc-field" value="{{ old('housing_allowance', $payslip->housing_allowance) }}">
                            </div>
                            <div class="col-6">
                                <label class="form-label">بدل مواصلات</label>
                                <input type="number" name="transport_allowance" step="0.01" min="0"
                                    class="form-control calc-field" value="{{ old('transport_allowance', $payslip->transport_allowance) }}">
                            </div>
                            <div class="col-6">
                                <label class="form-label">بدل طعام</label>
                                <input type="number" name="food_allowance" step="0.01" min="0"
                                    class="form-control calc-field" value="{{ old('food_allowance', $payslip->food_allowance) }}">
                            </div>
                            <div class="col-6">
                                <label class="form-label">بدلات أخرى</label>
                                <input type="number" name="other_allowances" step="0.01" min="0"
                                    class="form-control calc-field" value="{{ old('other_allowances', $payslip->other_allowances) }}">
                            </div>
                            <div class="col-12"><hr class="my-1"></div>
                            <div class="col-4">
                                <label class="form-label">ساعات إضافية</label>
                                <input type="number" name="overtime_hours" id="overtime_hours" step="0.5" min="0"
                                    class="form-control calc-field" value="{{ old('overtime_hours', $payslip->overtime_hours) }}">
                            </div>
                            <div class="col-4">
                                <label class="form-label">سعر الساعة</label>
                                <input type="number" name="overtime_rate" id="overtime_rate" step="0.01" min="0"
                                    class="form-control calc-field" value="{{ old('overtime_rate', $payslip->overtime_rate) }}">
                            </div>
                            <div class="col-4">
                                <label class="form-label">أوفرتايم</label>
                                <input type="text" id="overtime_total" class="form-control bg-light" readonly>
                            </div>
                            <div class="col-12">
                                <label class="form-label">مكافأة</label>
                                <input type="number" name="bonus" step="0.01" min="0"
                                    class="form-control calc-field" value="{{ old('bonus', $payslip->bonus) }}">
                            </div>
                            <div class="col-12">
                                <div class="alert alert-success py-2 mb-0 d-flex justify-content-between">
                                    <span class="fw-semibold">إجمالي الإيرادات</span>
                                    <span class="fw-bold" id="total_allowances">0.00</span>
                                </div>
                            </div>
                        </div>
                    </div>
                </div>
            </div>

            {{-- Deductions --}}
            <div class="col-md-6">
                <div class="card shadow-sm h-100">
                    <div class="card-header fw-semibold bg-danger text-white">❌ الخصومات</div>
                    <div class="card-body">
                        <div class="row g-3">
                            <div class="col-6">
                                <label class="form-label">خصم غياب</label>
                                <input type="number" name="deduction_absence" step="0.01" min="0"
                                    class="form-control calc-field" value="{{ old('deduction_absence', $payslip->deduction_absence) }}">
                            </div>
                            <div class="col-6">
                                <label class="form-label">خصم تأخير</label>
                                <input type="number" name="deduction_late" step="0.01" min="0"
                                    class="form-control calc-field" value="{{ old('deduction_late', $payslip->deduction_late) }}">
                            </div>
                            <div class="col-6">
                                <label class="form-label">تأمين اجتماعي</label>
                                <input type="number" name="deduction_insurance" step="0.01" min="0"
                                    class="form-control calc-field" value="{{ old('deduction_insurance', $payslip->deduction_insurance) }}">
                            </div>
                            <div class="col-6">
                                <label class="form-label">ضريبة</label>
                                <input type="number" name="deduction_tax" step="0.01" min="0"
                                    class="form-control calc-field" value="{{ old('deduction_tax', $payslip->deduction_tax) }}">
                            </div>
                            <div class="col-6">
                                <label class="form-label">قسط سلفة</label>
                                <input type="number" name="deduction_loan" step="0.01" min="0"
                                    class="form-control calc-field" value="{{ old('deduction_loan', $payslip->deduction_loan) }}">
                            </div>
                            <div class="col-6">
                                <label class="form-label">خصومات أخرى</label>
                                <input type="number" name="other_deductions" step="0.01" min="0"
                                    class="form-control calc-field" value="{{ old('other_deductions', $payslip->other_deductions) }}">
                            </div>
                            <div class="col-12">
                                <div class="alert alert-danger py-2 mb-0 d-flex justify-content-between">
                                    <span class="fw-semibold">إجمالي الخصومات</span>
                                    <span class="fw-bold" id="total_deductions">0.00</span>
                                </div>
                            </div>
                        </div>
                    </div>
                </div>
            </div>

            {{-- Net & Notes --}}
            <div class="col-12">
                <div class="card shadow-sm border-primary">
                    <div class="card-body">
                        <div class="row align-items-center g-3">
                            <div class="col-md-4">
                                <label class="form-label fw-bold fs-5">💵 الراتب الصافي</label>
                                <div class="input-group">
                                    <input type="text" id="net_salary_display" class="form-control form-control-lg fw-bold text-primary fs-4" readonly>
                                    <span class="input-group-text">₪</span>
                                </div>
                            </div>
                            <div class="col-md-8">
                                <label class="form-label fw-semibold">ملاحظات</label>
                                <textarea name="notes" class="form-control" rows="3">{{ old('notes', $payslip->notes) }}</textarea>
                            </div>
                        </div>
                    </div>
                </div>
            </div>

            <div class="col-12 d-flex gap-2 justify-content-end">
                <a href="{{ route('payslips.show', $payslip) }}" class="btn btn-secondary">إلغاء</a>
                <button type="submit" class="btn btn-primary px-4">حفظ التعديلات</button>
            </div>
        </div>
    </form>
</div>

<script>
function calculate() {
    const fields = document.querySelectorAll('.calc-field');
    const data = {};
    fields.forEach(f => { data[f.name] = parseFloat(f.value) || 0; });
    const overtimeTotal = data.overtime_hours * data.overtime_rate;
    document.getElementById('overtime_total').value = overtimeTotal.toFixed(2);
    const totalAllowances = data.basic_salary + data.housing_allowance + data.transport_allowance
        + data.food_allowance + data.other_allowances + overtimeTotal + data.bonus;
    const totalDeductions = data.deduction_absence + data.deduction_late + data.deduction_insurance
        + data.deduction_tax + data.deduction_loan + data.other_deductions;
    const net = totalAllowances - totalDeductions;
    document.getElementById('total_allowances').textContent   = totalAllowances.toFixed(2);
    document.getElementById('total_deductions').textContent   = totalDeductions.toFixed(2);
    document.getElementById('net_salary_display').value       = net.toFixed(2);
}
document.querySelectorAll('.calc-field').forEach(f => f.addEventListener('input', calculate));
calculate();
</script>
</x-app-layout>
