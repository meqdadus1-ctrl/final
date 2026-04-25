{{-- resources/views/payslips/create.blade.php --}}
<x-app-layout>
    <x-slot name="title">إنشاء كشف راتب</x-slot>
<div class="container-fluid py-4" dir="rtl">

    <div class="d-flex align-items-center gap-3 mb-4">
        <a href="{{ route('payslips.index') }}" class="btn btn-outline-secondary btn-sm">← رجوع</a>
        <div>
            <h4 class="mb-0 fw-bold">إنشاء كشف راتب جديد</h4>
        </div>
    </div>

    @if($errors->any())
        <div class="alert alert-danger">
            <ul class="mb-0">@foreach($errors->all() as $e)<li>{{ $e }}</li>@endforeach</ul>
        </div>
    @endif

    <form action="{{ route('payslips.store') }}" method="POST">
        @csrf

        <div class="row g-4">

            {{-- Employee & Period --}}
            <div class="col-12">
                <div class="card shadow-sm">
                    <div class="card-header fw-semibold bg-primary text-white">👤 بيانات الموظف والفترة</div>
                    <div class="card-body">
                        <div class="row g-3">
                            <div class="col-md-6">
                                <label class="form-label fw-semibold">الموظف <span class="text-danger">*</span></label>
                                <select name="employee_id" class="form-select" required onchange="loadEmployeeSalary(this.value)">
                                    <option value="">اختر الموظف...</option>
                                    @foreach($employees as $emp)
                                        <option value="{{ $emp->id }}"
                                            data-salary="{{ $emp->salary ?? 0 }}"
                                            {{ old('employee_id') == $emp->id ? 'selected' : '' }}>
                                            {{ $emp->name }}
                                        </option>
                                    @endforeach
                                </select>
                            </div>
                            <div class="col-md-3">
                                <label class="form-label fw-semibold">الشهر <span class="text-danger">*</span></label>
                                <select name="month" class="form-select" required>
                                    @foreach(['يناير','فبراير','مارس','أبريل','مايو','يونيو','يوليو','أغسطس','سبتمبر','أكتوبر','نوفمبر','ديسمبر'] as $i => $m)
                                        <option value="{{ $i+1 }}" {{ old('month', date('n')) == $i+1 ? 'selected' : '' }}>{{ $m }}</option>
                                    @endforeach
                                </select>
                            </div>
                            <div class="col-md-3">
                                <label class="form-label fw-semibold">السنة <span class="text-danger">*</span></label>
                                <select name="year" class="form-select" required>
                                    @for($y = date('Y'); $y >= 2020; $y--)
                                        <option value="{{ $y }}" {{ old('year', date('Y')) == $y ? 'selected' : '' }}>{{ $y }}</option>
                                    @endfor
                                </select>
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
                                <input type="number" name="basic_salary" id="basic_salary" step="0.01" min="0"
                                    class="form-control calc-field" value="{{ old('basic_salary', 0) }}" required>
                            </div>
                            <div class="col-6">
                                <label class="form-label">بدل سكن</label>
                                <input type="number" name="housing_allowance" step="0.01" min="0"
                                    class="form-control calc-field" value="{{ old('housing_allowance', 0) }}">
                            </div>
                            <div class="col-6">
                                <label class="form-label">بدل مواصلات</label>
                                <input type="number" name="transport_allowance" step="0.01" min="0"
                                    class="form-control calc-field" value="{{ old('transport_allowance', 0) }}">
                            </div>
                            <div class="col-6">
                                <label class="form-label">بدل طعام</label>
                                <input type="number" name="food_allowance" step="0.01" min="0"
                                    class="form-control calc-field" value="{{ old('food_allowance', 0) }}">
                            </div>
                            <div class="col-6">
                                <label class="form-label">بدلات أخرى</label>
                                <input type="number" name="other_allowances" step="0.01" min="0"
                                    class="form-control calc-field" value="{{ old('other_allowances', 0) }}">
                            </div>
                            <div class="col-12"><hr class="my-1"></div>
                            <div class="col-4">
                                <label class="form-label">ساعات إضافية</label>
                                <input type="number" name="overtime_hours" id="overtime_hours" step="0.5" min="0"
                                    class="form-control calc-field" value="{{ old('overtime_hours', 0) }}">
                            </div>
                            <div class="col-4">
                                <label class="form-label">سعر الساعة</label>
                                <input type="number" name="overtime_rate" id="overtime_rate" step="0.01" min="0"
                                    class="form-control calc-field" value="{{ old('overtime_rate', 0) }}">
                            </div>
                            <div class="col-4">
                                <label class="form-label">أوفرتايم</label>
                                <input type="text" id="overtime_total" class="form-control bg-light" readonly value="0.00">
                            </div>
                            <div class="col-12">
                                <label class="form-label">مكافأة / حوافز</label>
                                <input type="number" name="bonus" step="0.01" min="0"
                                    class="form-control calc-field" value="{{ old('bonus', 0) }}">
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
                                    class="form-control calc-field" value="{{ old('deduction_absence', 0) }}">
                            </div>
                            <div class="col-6">
                                <label class="form-label">خصم تأخير</label>
                                <input type="number" name="deduction_late" step="0.01" min="0"
                                    class="form-control calc-field" value="{{ old('deduction_late', 0) }}">
                            </div>
                            <div class="col-6">
                                <label class="form-label">تأمين اجتماعي</label>
                                <input type="number" name="deduction_insurance" step="0.01" min="0"
                                    class="form-control calc-field" value="{{ old('deduction_insurance', 0) }}">
                            </div>
                            <div class="col-6">
                                <label class="form-label">ضريبة</label>
                                <input type="number" name="deduction_tax" step="0.01" min="0"
                                    class="form-control calc-field" value="{{ old('deduction_tax', 0) }}">
                            </div>
                            <div class="col-6">
                                <label class="form-label">قسط سلفة</label>
                                <input type="number" name="deduction_loan" step="0.01" min="0"
                                    class="form-control calc-field" value="{{ old('deduction_loan', 0) }}">
                            </div>
                            <div class="col-6">
                                <label class="form-label">خصومات أخرى</label>
                                <input type="number" name="other_deductions" step="0.01" min="0"
                                    class="form-control calc-field" value="{{ old('other_deductions', 0) }}">
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
                                    <input type="text" id="net_salary_display" class="form-control form-control-lg fw-bold text-primary fs-4" readonly value="0.00">
                                    <span class="input-group-text">₪</span>
                                </div>
                            </div>
                            <div class="col-md-8">
                                <label class="form-label fw-semibold">ملاحظات</label>
                                <textarea name="notes" class="form-control" rows="3" placeholder="أي ملاحظات على كشف الراتب...">{{ old('notes') }}</textarea>
                            </div>
                        </div>
                    </div>
                </div>
            </div>

            <div class="col-12 d-flex gap-2 justify-content-end">
                <a href="{{ route('payslips.index') }}" class="btn btn-secondary">إلغاء</a>
                <button type="submit" class="btn btn-primary px-4">حفظ كشف الراتب</button>
            </div>

        </div>
    </form>
</div>

<script>
function val(id) {
    return parseFloat(document.getElementById ? 0 : 0) || 0;
}

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

function loadEmployeeSalary(id) {
    const opt = document.querySelector(`option[value="${id}"]`);
    if (opt) {
        document.getElementById('basic_salary').value = opt.dataset.salary || 0;
        calculate();
    }
}

calculate();
</script>
</x-app-layout>
