<x-app-layout>
<x-slot name="title">راتب {{ $salary->employee?->name }} — {{ $salary->fiscal_period }}</x-slot>
<div class="container-fluid py-4" dir="rtl">

    {{-- Header --}}
    <div class="d-flex justify-content-between align-items-center mb-4">
        <div>
            <h4 class="mb-0 fw-bold">🧾 تفاصيل الراتب الأسبوعي</h4>
            <small class="text-muted">{{ $salary->employee?->name }} — {{ $salary->fiscal_period }}</small>
        </div>
        <div class="d-flex gap-2">
            <a href="{{ route('salary.thermal', $salary) }}" target="_blank"
               class="btn btn-outline-dark btn-sm">
                <i class="fas fa-print me-1"></i> طباعة حرارية
            </a>
            <a href="{{ route('ledger.show', $salary->employee) }}" class="btn btn-outline-info btn-sm">
                <i class="fas fa-book me-1"></i> كشف الحساب
            </a>
            <a href="{{ route('salary.index') }}" class="btn btn-outline-secondary btn-sm">
                <i class="fas fa-arrow-right me-1"></i> العودة
            </a>
        </div>
    </div>

    @if(session('success'))
        <div class="alert alert-success alert-dismissible fade show">{{ session('success') }}<button type="button" class="btn-close" data-bs-dismiss="alert"></button></div>
    @endif

    <div class="row g-4">

        {{-- ===== COL-LEFT: بيانات الدفعة ===== --}}
        <div class="col-lg-4">

            {{-- بطاقة الموظف --}}
            <div class="card shadow-sm mb-3">
                <div class="card-header bg-dark text-white py-2">
                    <i class="fas fa-user me-2"></i>معلومات الموظف
                </div>
                <div class="card-body">
                    <h5 class="fw-bold mb-1">{{ $salary->employee?->name }}</h5>
                    <p class="text-muted small mb-2">{{ $salary->employee?->department?->name ?? '—' }}</p>
                    <hr class="my-2">
                    @php
                        $rows = [
                            ['الفترة', ($salary->week_start?->format('d/m') ?? '—') . ' — ' . ($salary->week_end?->format('d/m/Y') ?? '—')],
                            ['الكود المالي', '<span class="badge bg-secondary">' . $salary->fiscal_period . '</span>'],
                            ['تاريخ الدفع', $salary->payment_date?->format('d/m/Y') ?? '—'],
                            ['طريقة الدفع', match($salary->payment_method) {
                                'bank'     => '<span class="badge bg-primary">🏦 بنك</span>',
                                'cash'     => '<span class="badge bg-warning text-dark">💵 كاش</span>',
                                'deferred' => '<span class="badge bg-info text-dark">📋 ترحيل للرصيد</span>',
                                default    => '<span class="badge bg-secondary">' . $salary->payment_method . '</span>',
                            }],
                            ['أجر الساعة', number_format($salary->hourly_rate ?? 0, 2) . ' ₪'],
                            ['ساعات العمل', $salary->hours_worked . ' س'],
                            ['أوفرتايم', $salary->overtime_hours . ' س'],
                        ];
                    @endphp
                    @foreach($rows as [$label, $val])
                    <div class="d-flex justify-content-between small mb-1">
                        <span class="text-muted">{{ $label }}</span>
                        <span>{!! $val !!}</span>
                    </div>
                    @endforeach
                    @if($salary->notes)
                    <div class="mt-2 p-2 bg-light rounded small">
                        <i class="fas fa-sticky-note me-1 text-muted"></i>{{ $salary->notes }}
                    </div>
                    @endif
                </div>
            </div>

            {{-- بطاقة الرصيد --}}
            <div class="card shadow-sm border-0"
                style="background: linear-gradient(135deg,#e3f2fd,#e8f5e9)">
                <div class="card-body py-3">
                    <div class="row text-center">
                        <div class="col-6 border-end">
                            <div class="small text-muted mb-1">رصيد قبل</div>
                            <div class="fw-bold {{ ($salary->balance_before ?? 0) >= 0 ? 'text-success' : 'text-danger' }}">
                                {{ number_format($salary->balance_before ?? 0, 2) }} ₪
                            </div>
                        </div>
                        <div class="col-6">
                            <div class="small text-muted mb-1">رصيد بعد</div>
                            <div class="fw-bold fs-5 {{ ($salary->balance_after ?? 0) >= 0 ? 'text-success' : 'text-danger' }}">
                                {{ number_format($salary->balance_after ?? 0, 2) }} ₪
                            </div>
                        </div>
                    </div>
                </div>
            </div>

        </div>

        {{-- ===== COL-RIGHT: تفاصيل + Ledger ===== --}}
        <div class="col-lg-8">

            {{-- ملخص الراتب --}}
            <div class="card shadow-sm mb-3">
                <div class="card-header py-2 fw-semibold">
                    <i class="fas fa-receipt me-2"></i>تفاصيل احتساب الراتب
                </div>
                <div class="card-body p-0">
                    <table class="table mb-0">
                        <tbody>
                            {{-- الإضافات --}}
                            <tr class="table-light">
                                <td colspan="2" class="px-3 py-2 fw-semibold small text-uppercase text-muted">الإضافات</td>
                            </tr>
                            @php $salaryA = round($salary->hours_worked * ($salary->hourly_rate ?? 0), 2); @endphp
                            <tr>
                                <td class="px-3"><span class="badge bg-primary me-2">A</span>ساعات العمل ({{ $salary->hours_worked }} ساعة)</td>
                                <td class="text-success fw-bold text-end px-3">+ {{ number_format($salaryA, 2) }} ₪</td>
                            </tr>
                            @if($salary->overtime_hours > 0)
                            @php $salaryB = round($salary->overtime_hours * ($salary->hourly_rate ?? 0) * ($salary->employee?->overtime_rate ?? 1.5), 2); @endphp
                            <tr>
                                <td class="px-3"><span class="badge bg-info me-2">B</span>الأوفرتايم ({{ $salary->overtime_hours }} ساعة)</td>
                                <td class="text-success fw-bold text-end px-3">+ {{ number_format($salaryB, 2) }} ₪</td>
                            </tr>
                            @endif
                            @if($salary->manual_additions > 0)
                            <tr>
                                <td class="px-3"><span class="badge bg-success me-2">C</span>إضافات يدوية / تعديلات</td>
                                <td class="text-success fw-bold text-end px-3">+ {{ number_format($salary->manual_additions, 2) }} ₪</td>
                            </tr>
                            @endif

                            {{-- الخصومات --}}
                            <tr class="table-light">
                                <td colspan="2" class="px-3 py-2 fw-semibold small text-uppercase text-muted">الخصومات</td>
                            </tr>
                            @if($salary->late_deduction > 0)
                            <tr>
                                <td class="px-3"><span class="badge bg-warning text-dark me-2">D1</span>خصم التأخير ({{ $salary->late_minutes }} دقيقة)</td>
                                <td class="text-danger fw-bold text-end px-3">− {{ number_format($salary->late_deduction, 2) }} ₪</td>
                            </tr>
                            @endif
                            @if($salary->absence_deduction > 0)
                            <tr>
                                <td class="px-3"><span class="badge bg-warning text-dark me-2">D2</span>خصم الغياب بإذن</td>
                                <td class="text-danger fw-bold text-end px-3">− {{ number_format($salary->absence_deduction, 2) }} ₪</td>
                            </tr>
                            @endif
                            @if($salary->manual_deductions > 0)
                            <tr>
                                <td class="px-3"><span class="badge bg-danger me-2">D3</span>خصومات يدوية</td>
                                <td class="text-danger fw-bold text-end px-3">− {{ number_format($salary->manual_deductions, 2) }} ₪</td>
                            </tr>
                            @endif
                            @if($salary->loan_deduction_amount > 0)
                            <tr>
                                <td class="px-3"><span class="badge bg-danger me-2">E</span>خصم قسط السلفة</td>
                                <td class="text-danger fw-bold text-end px-3">− {{ number_format($salary->loan_deduction_amount, 2) }} ₪</td>
                            </tr>
                            @endif

                            {{-- الصافي --}}
                            <tr class="table-success">
                                <td class="px-3 py-3 fw-bold fs-5">الراتب الصافي</td>
                                <td class="text-success fw-bold fs-4 text-end px-3 py-3">{{ number_format($salary->net_salary, 2) }} ₪</td>
                            </tr>
                        </tbody>
                    </table>
                </div>
            </div>

            {{-- ===== قيود الـ Ledger ===== --}}
            @if($ledgerEntries->count() > 0)
            <div class="card shadow-sm">
                <div class="card-header py-2 fw-semibold">
                    <i class="fas fa-book me-2 text-primary"></i>قيود كشف الحساب
                    <span class="badge bg-secondary ms-1">{{ $ledgerEntries->count() }} قيد</span>
                </div>
                <div class="card-body p-0">
                    <div class="table-responsive">
                        <table class="table table-sm table-hover mb-0">
                            <thead class="table-dark">
                                <tr>
                                    <th class="px-3">البيان</th>
                                    <th class="text-end">دائن (له)</th>
                                    <th class="text-end">مدين (عليه)</th>
                                    <th class="text-end px-3">الرصيد</th>
                                </tr>
                            </thead>
                            <tbody>
                                @foreach($ledgerEntries as $entry)
                                <tr>
                                    <td class="px-3">
                                        @php
                                            $typeLabels = [
                                                'salary'             => ['label'=>'راتب ساعات العمل','color'=>'bg-primary'],
                                                'overtime'           => ['label'=>'أجر الأوفرتايم','color'=>'bg-info'],
                                                'bonus'              => ['label'=>'مكافأة','color'=>'bg-success'],
                                                'expense'            => ['label'=>'مصروف','color'=>'bg-secondary'],
                                                'adjustment'         => ['label'=>'تعديل','color'=>'bg-secondary'],
                                                'deduction_late'     => ['label'=>'خصم تأخير','color'=>'bg-warning text-dark'],
                                                'deduction_absence'  => ['label'=>'خصم غياب','color'=>'bg-warning text-dark'],
                                                'deduction_manual'   => ['label'=>'خصم يدوي','color'=>'bg-danger'],
                                                'loan_installment'   => ['label'=>'قسط سلفة','color'=>'bg-danger'],
                                                'payment'            => ['label'=>'صافي الدفع','color'=>'bg-dark'],
                                                'opening_balance'    => ['label'=>'رصيد افتتاحي','color'=>'bg-secondary'],
                                            ];
                                            $t = $typeLabels[$entry->entry_type] ?? ['label'=>$entry->entry_type,'color'=>'bg-secondary'];
                                        @endphp
                                        <span class="badge {{ $t['color'] }} me-2">{{ $t['label'] }}</span>
                                        <span class="small text-muted">{{ $entry->description }}</span>
                                    </td>
                                    <td class="text-end text-success fw-semibold">
                                        {{ $entry->credit > 0 ? number_format($entry->credit, 2) . ' ₪' : '—' }}
                                    </td>
                                    <td class="text-end text-danger fw-semibold">
                                        {{ $entry->debit > 0 ? number_format($entry->debit, 2) . ' ₪' : '—' }}
                                    </td>
                                    <td class="text-end px-3 fw-bold {{ $entry->balance_after >= 0 ? 'text-success' : 'text-danger' }}">
                                        {{ number_format($entry->balance_after, 2) }} ₪
                                    </td>
                                </tr>
                                @endforeach
                            </tbody>
                        </table>
                    </div>
                </div>
            </div>
            @endif

        </div>
    </div>
</div>
</x-app-layout>