{{-- resources/views/payslips/show.blade.php --}}
<x-app-layout>
    <x-slot name="title">{{ 'كشف راتب - ' . $payslip->employee->name }}</x-slot>
<div class="container-fluid py-4" dir="rtl">

    {{-- Actions bar --}}
    <div class="d-flex justify-content-between align-items-center mb-4">
        <div class="d-flex align-items-center gap-3">
            <a href="{{ route('payslips.index') }}" class="btn btn-outline-secondary btn-sm">← رجوع</a>
            <h5 class="mb-0 fw-bold">كشف راتب — {{ $payslip->employee->name }}</h5>
            <span class="badge bg-{{ $payslip->status_color }} fs-6">{{ $payslip->status_label }}</span>
        </div>
        <div class="d-flex gap-2">
            @if($payslip->status !== 'paid')
            <a href="{{ route('payslips.edit', $payslip) }}" class="btn btn-outline-secondary btn-sm">✏️ تعديل</a>
            @endif
            <a href="{{ route('payslips.pdf', $payslip) }}" class="btn btn-danger btn-sm" target="_blank">📄 تحميل PDF</a>

            {{-- Status change dropdown --}}
            @if($payslip->status !== 'paid')
            <div class="dropdown">
                <button class="btn btn-primary btn-sm dropdown-toggle" data-bs-toggle="dropdown">
                    تغيير الحالة
                </button>
                <ul class="dropdown-menu">
                    @if($payslip->status === 'draft')
                    <li>
                        <form action="{{ route('payslips.status', $payslip) }}" method="POST">
                            @csrf
                            <input type="hidden" name="status" value="issued">
                            <button class="dropdown-item">📤 إصدار الكشف</button>
                        </form>
                    </li>
                    @endif
                    @if($payslip->status === 'issued')
                    <li>
                        <form action="{{ route('payslips.status', $payslip) }}" method="POST">
                            @csrf
                            <input type="hidden" name="status" value="paid">
                            <button class="dropdown-item text-success">✅ تأكيد الدفع</button>
                        </form>
                    </li>
                    @endif
                </ul>
            </div>
            @endif
        </div>
    </div>

    @if(session('success'))
        <div class="alert alert-success alert-dismissible fade show">
            {{ session('success') }}<button type="button" class="btn-close" data-bs-dismiss="alert"></button>
        </div>
    @endif

    {{-- Payslip Card --}}
    <div class="card shadow" id="payslip-card">
        <div class="card-body p-4">

            {{-- Company Header --}}
            <div class="row align-items-center border-bottom pb-3 mb-4">
                <div class="col-md-6">
                    <h4 class="fw-bold mb-1">🏢 اسم الشركة</h4>
                    <small class="text-muted">نظام الموارد البشرية</small>
                </div>
                <div class="col-md-6 text-md-end">
                    <h5 class="fw-bold text-primary mb-1">كشف الراتب</h5>
                    <div class="text-muted">{{ $payslip->month_name }} {{ $payslip->year }}</div>
                    @if($payslip->issued_at)
                    <small class="text-muted">تاريخ الإصدار: {{ $payslip->issued_at->format('Y/m/d') }}</small>
                    @endif
                </div>
            </div>

            {{-- Employee Info --}}
            <div class="row g-3 mb-4 p-3 bg-light rounded">
                <div class="col-md-3">
                    <div class="small text-muted">اسم الموظف</div>
                    <div class="fw-semibold">{{ $payslip->employee->name }}</div>
                </div>
                <div class="col-md-3">
                    <div class="small text-muted">القسم</div>
                    <div class="fw-semibold">{{ $payslip->employee->department->name ?? '—' }}</div>
                </div>
                <div class="col-md-3">
                    <div class="small text-muted">نوع الراتب</div>
                    <div class="fw-semibold">{{ $payslip->employee->salary_type ?? '—' }}</div>
                </div>
                <div class="col-md-3">
                    <div class="small text-muted">تاريخ التعيين</div>
                    <div class="fw-semibold">{{ $payslip->employee->hire_date ? \Carbon\Carbon::parse($payslip->employee->hire_date)->format('Y/m/d') : '—' }}</div>
                </div>
            </div>

            {{-- Allowances + Deductions side by side --}}
            <div class="row g-4 mb-4">

                {{-- Allowances --}}
                <div class="col-md-6">
                    <h6 class="fw-bold text-success border-bottom border-success pb-2 mb-3">✅ الإيرادات والبدلات</h6>
                    <table class="table table-sm">
                        <tbody>
                            <tr>
                                <td>الراتب الأساسي</td>
                                <td class="text-end fw-semibold">{{ number_format($payslip->basic_salary, 2) }}</td>
                            </tr>
                            @if($payslip->housing_allowance)
                            <tr>
                                <td>بدل سكن</td>
                                <td class="text-end">{{ number_format($payslip->housing_allowance, 2) }}</td>
                            </tr>
                            @endif
                            @if($payslip->transport_allowance)
                            <tr>
                                <td>بدل مواصلات</td>
                                <td class="text-end">{{ number_format($payslip->transport_allowance, 2) }}</td>
                            </tr>
                            @endif
                            @if($payslip->food_allowance)
                            <tr>
                                <td>بدل طعام</td>
                                <td class="text-end">{{ number_format($payslip->food_allowance, 2) }}</td>
                            </tr>
                            @endif
                            @if($payslip->other_allowances)
                            <tr>
                                <td>بدلات أخرى</td>
                                <td class="text-end">{{ number_format($payslip->other_allowances, 2) }}</td>
                            </tr>
                            @endif
                            @if($payslip->overtime_hours)
                            <tr>
                                <td>أوفرتايم ({{ $payslip->overtime_hours }} ساعة × {{ $payslip->overtime_rate }})</td>
                                <td class="text-end">{{ number_format($payslip->overtime_hours * $payslip->overtime_rate, 2) }}</td>
                            </tr>
                            @endif
                            @if($payslip->bonus)
                            <tr>
                                <td>مكافأة / حوافز</td>
                                <td class="text-end">{{ number_format($payslip->bonus, 2) }}</td>
                            </tr>
                            @endif
                        </tbody>
                        <tfoot class="table-success">
                            <tr>
                                <td class="fw-bold">إجمالي الإيرادات</td>
                                <td class="text-end fw-bold">{{ number_format($payslip->total_allowances, 2) }}</td>
                            </tr>
                        </tfoot>
                    </table>
                </div>

                {{-- Deductions --}}
                <div class="col-md-6">
                    <h6 class="fw-bold text-danger border-bottom border-danger pb-2 mb-3">❌ الخصومات</h6>
                    <table class="table table-sm">
                        <tbody>
                            @if($payslip->deduction_absence)
                            <tr>
                                <td>خصم غياب</td>
                                <td class="text-end text-danger">{{ number_format($payslip->deduction_absence, 2) }}</td>
                            </tr>
                            @endif
                            @if($payslip->deduction_late)
                            <tr>
                                <td>خصم تأخير</td>
                                <td class="text-end text-danger">{{ number_format($payslip->deduction_late, 2) }}</td>
                            </tr>
                            @endif
                            @if($payslip->deduction_insurance)
                            <tr>
                                <td>تأمين اجتماعي</td>
                                <td class="text-end text-danger">{{ number_format($payslip->deduction_insurance, 2) }}</td>
                            </tr>
                            @endif
                            @if($payslip->deduction_tax)
                            <tr>
                                <td>ضريبة</td>
                                <td class="text-end text-danger">{{ number_format($payslip->deduction_tax, 2) }}</td>
                            </tr>
                            @endif
                            @if($payslip->deduction_loan)
                            <tr>
                                <td>قسط سلفة</td>
                                <td class="text-end text-danger">{{ number_format($payslip->deduction_loan, 2) }}</td>
                            </tr>
                            @endif
                            @if($payslip->other_deductions)
                            <tr>
                                <td>خصومات أخرى</td>
                                <td class="text-end text-danger">{{ number_format($payslip->other_deductions, 2) }}</td>
                            </tr>
                            @endif
                            @if($payslip->total_deductions == 0)
                            <tr><td colspan="2" class="text-muted text-center py-3">لا توجد خصومات</td></tr>
                            @endif
                        </tbody>
                        <tfoot class="table-danger">
                            <tr>
                                <td class="fw-bold">إجمالي الخصومات</td>
                                <td class="text-end fw-bold text-danger">{{ number_format($payslip->total_deductions, 2) }}</td>
                            </tr>
                        </tfoot>
                    </table>
                </div>
            </div>

            {{-- Net Salary --}}
            <div class="alert alert-primary d-flex justify-content-between align-items-center py-3 mb-3">
                <div>
                    <div class="fw-bold fs-5">💵 الراتب الصافي</div>
                    <small class="opacity-75">{{ $payslip->month_name }} {{ $payslip->year }}</small>
                </div>
                <div class="text-end">
                    <div class="fw-bold fs-3">{{ number_format($payslip->net_salary, 2) }} <small>₪</small></div>
                </div>
            </div>

            {{-- Notes --}}
            @if($payslip->notes)
            <div class="bg-light rounded p-3">
                <div class="small fw-semibold text-muted mb-1">ملاحظات</div>
                <div>{{ $payslip->notes }}</div>
            </div>
            @endif

        </div>
    </div>
</div>
</x-app-layout>
