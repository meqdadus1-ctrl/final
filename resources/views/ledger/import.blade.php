<x-app-layout>
<x-slot name="title">استيراد حركات من Excel</x-slot>
<div class="container-fluid py-4" dir="rtl">

    {{-- Header --}}
    <div class="d-flex justify-content-between align-items-center mb-4">
        <div>
            <h4 class="mb-0 fw-bold">📥 استيراد حركات من Excel</h4>
            <small class="text-muted">استورد حركات الموظف من ملف الاكسل وسجّلها في كشف الحساب</small>
        </div>
        <a href="{{ route('ledger.show', request('employee_id', 1)) }}"
           class="btn btn-outline-secondary btn-sm">
            <i class="fas fa-arrow-right me-1"></i> رجوع
        </a>
    </div>

    @if(session('success'))
        <div class="alert alert-success alert-dismissible fade show">
            {{ session('success') }}
            <button type="button" class="btn-close" data-bs-dismiss="alert"></button>
        </div>
    @endif
    @if(session('error'))
        <div class="alert alert-danger alert-dismissible fade show">
            {{ session('error') }}
            <button type="button" class="btn-close" data-bs-dismiss="alert"></button>
        </div>
    @endif

    <div class="row g-4">

        {{-- ===== نموذج الرفع ===== --}}
        <div class="col-lg-4">
            <div class="card shadow-sm">
                <div class="card-header py-2 fw-semibold">
                    <i class="fas fa-upload me-2 text-primary"></i>رفع ملف Excel
                </div>
                <div class="card-body">
                    <form action="{{ route('ledger.import.preview') }}" method="POST" enctype="multipart/form-data">
                        @csrf

                        <div class="mb-3">
                            <label class="form-label fw-semibold">الموظف <span class="text-danger">*</span></label>
                            <select name="employee_id" class="form-select @error('employee_id') is-invalid @enderror" required>
                                <option value="">— اختر الموظف —</option>
                                @foreach($employees as $emp)
                                    <option value="{{ $emp->id }}"
                                        {{ (old('employee_id') == $emp->id || (isset($employee) && $employee->id == $emp->id)) ? 'selected' : '' }}>
                                        {{ $emp->name }}
                                    </option>
                                @endforeach
                            </select>
                            @error('employee_id')
                                <div class="invalid-feedback">{{ $message }}</div>
                            @enderror
                        </div>

                        <div class="mb-4">
                            <label class="form-label fw-semibold">ملف Excel <span class="text-danger">*</span></label>
                            <input type="file" name="file" accept=".xlsx,.xls"
                                class="form-control @error('file') is-invalid @enderror" required>
                            @error('file')
                                <div class="invalid-feedback">{{ $message }}</div>
                            @enderror
                            <div class="form-text text-muted">صيغ مدعومة: xlsx, xls — الحد الأقصى 5MB</div>
                        </div>

                        <button type="submit" class="btn btn-primary w-100">
                            <i class="fas fa-search me-2"></i>معاينة الحركات
                        </button>
                    </form>
                </div>
            </div>

            {{-- تعليمات --}}
            <div class="card shadow-sm mt-3 border-0" style="background:#f8f9fa">
                <div class="card-body py-3">
                    <div class="fw-semibold small mb-2">
                        <i class="fas fa-info-circle text-primary me-1"></i>كيف يعمل الاستيراد؟
                    </div>
                    <ul class="small text-muted mb-0 ps-3">
                        <li class="mb-1"><span class="badge bg-success">ايصال القبض</span> → دائن ↑ (يرفع الرصيد)</li>
                        <li class="mb-1"><span class="badge bg-danger">سند الصرف</span> → مدين ↓ (يخفض الرصيد)</li>
                        <li class="mb-1"><span class="badge bg-danger">فاتورة البيع</span> → خصم يدوي ↓</li>
                        <li class="mb-1"><span class="badge bg-warning text-dark">سند القيد</span> → تسوية (حسب العمود)</li>
                        <li class="mb-1"><span class="badge bg-secondary">كشف رواتب</span> → يُتخطى تلقائياً</li>
                        <li>القيود المكررة (نفس رقم القيد) تُتخطى تلقائياً</li>
                    </ul>
                </div>
            </div>
        </div>

        {{-- ===== المعاينة ===== --}}
        @isset($rows)
        <div class="col-lg-8">
            <div class="card shadow-sm">
                <div class="card-header py-2 d-flex justify-content-between align-items-center">
                    <span class="fw-semibold">
                        <i class="fas fa-table me-2 text-success"></i>
                        معاينة حركات {{ $employee->name }}
                    </span>
                    <div class="d-flex gap-2 align-items-center">
                        @if($newCount > 0)
                            <span class="badge bg-success">{{ $newCount }} جديد</span>
                        @endif
                        @if($skipCount > 0)
                            <span class="badge bg-secondary">{{ $skipCount }} مكرر</span>
                        @endif
                    </div>
                </div>

                <div class="card-body p-0">
                    <div class="table-responsive">
                        <table class="table table-sm table-hover align-middle mb-0">
                            <thead class="table-dark">
                                <tr>
                                    <th class="px-3">التاريخ</th>
                                    <th>التفصيل</th>
                                    <th>النوع</th>
                                    <th class="text-end">دائن</th>
                                    <th class="text-end">مدين</th>
                                    <th class="text-center px-3">الحالة</th>
                                </tr>
                            </thead>
                            <tbody>
                                @foreach($rows as $row)
                                @php
                                    $typeLabels = [
                                        'payment'          => ['label' => 'دفع/قبض',    'color' => $row['credit'] > 0 ? 'bg-success' : 'bg-danger'],
                                        'deduction_manual' => ['label' => 'خصم يدوي',   'color' => 'bg-danger'],
                                        'adjustment'       => ['label' => 'تسوية',       'color' => 'bg-warning text-dark'],
                                    ];
                                    $t = $typeLabels[$row['entry_type']] ?? ['label' => $row['entry_type'], 'color' => 'bg-secondary'];
                                @endphp
                                <tr class="{{ $row['duplicate'] ? 'opacity-50' : '' }}">
                                    <td class="px-3 small text-muted">{{ $row['date'] }}</td>
                                    <td class="small">{{ $row['detail'] }}</td>
                                    <td><span class="badge {{ $t['color'] }}">{{ $t['label'] }}</span></td>
                                    <td class="text-end text-success fw-semibold small">
                                        {{ $row['credit'] > 0 ? number_format($row['credit'], 2) . ' ₪' : '—' }}
                                    </td>
                                    <td class="text-end text-danger fw-semibold small">
                                        {{ $row['debit'] > 0 ? number_format($row['debit'], 2) . ' ₪' : '—' }}
                                    </td>
                                    <td class="text-center px-3">
                                        @if($row['duplicate'])
                                            <span class="badge bg-secondary">
                                                <i class="fas fa-ban me-1"></i>مكرر
                                            </span>
                                        @else
                                            <span class="badge bg-success">
                                                <i class="fas fa-check me-1"></i>جديد
                                            </span>
                                        @endif
                                    </td>
                                </tr>
                                @endforeach
                            </tbody>
                            <tfoot class="table-light">
                                <tr>
                                    <td colspan="3" class="px-3 fw-semibold small">المجموع (الجديد فقط)</td>
                                    <td class="text-end text-success fw-bold small">
                                        {{ number_format(collect($rows)->where('duplicate', false)->sum('credit'), 2) }} ₪
                                    </td>
                                    <td class="text-end text-danger fw-bold small">
                                        {{ number_format(collect($rows)->where('duplicate', false)->sum('debit'), 2) }} ₪
                                    </td>
                                    <td></td>
                                </tr>
                            </tfoot>
                        </table>
                    </div>
                </div>

                {{-- أزرار التأكيد --}}
                @if($newCount > 0)
                <div class="card-footer d-flex justify-content-between align-items-center">
                    <span class="small text-muted">
                        سيتم استيراد <strong class="text-success">{{ $newCount }}</strong> قيد
                        @if($skipCount > 0)
                            وتخطي <strong class="text-secondary">{{ $skipCount }}</strong> مكرر
                        @endif
                    </span>
                    <div class="d-flex gap-2">
                        <a href="{{ route('ledger.import') }}" class="btn btn-outline-secondary btn-sm">
                            <i class="fas fa-times me-1"></i>إلغاء
                        </a>
                        <form action="{{ route('ledger.import.store') }}" method="POST">
                            @csrf
                            <button type="submit" class="btn btn-success btn-sm px-4">
                                <i class="fas fa-file-import me-2"></i>تأكيد الاستيراد ({{ $newCount }} قيد)
                            </button>
                        </form>
                    </div>
                </div>
                @else
                <div class="card-footer">
                    <div class="alert alert-warning mb-0 py-2 small">
                        <i class="fas fa-exclamation-triangle me-1"></i>
                        جميع القيود في الملف مكررة — لا يوجد جديد للاستيراد.
                    </div>
                </div>
                @endif

            </div>
        </div>
        @endisset

    </div>
</div>
</x-app-layout>
