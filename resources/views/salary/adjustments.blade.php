<x-app-layout>
<x-slot name="title">التعديلات اليدوية</x-slot>
<div class="container-fluid py-4" dir="rtl">

    {{-- Header --}}
    <div class="d-flex justify-content-between align-items-center mb-4">
        <div>
            <h4 class="mb-0 fw-bold">⚙️ التعديلات اليدوية</h4>
            <small class="text-muted">مكافآت، مصاريف، وخصومات إضافية</small>
        </div>
        <a href="{{ route('salary.index') }}" class="btn btn-outline-secondary btn-sm">
            <i class="fas fa-arrow-right me-1"></i> الرواتب
        </a>
    </div>

    @if(session('success'))
        <div class="alert alert-success alert-dismissible fade show">{{ session('success') }}<button type="button" class="btn-close" data-bs-dismiss="alert"></button></div>
    @endif
    @if(session('error'))
        <div class="alert alert-danger alert-dismissible fade show">{{ session('error') }}<button type="button" class="btn-close" data-bs-dismiss="alert"></button></div>
    @endif

    <div class="row g-4">

        {{-- ===== فورم إضافة تعديل جديد ===== --}}
        <div class="col-lg-4">
            <div class="card shadow-sm">
                <div class="card-header bg-success text-white py-2">
                    <i class="fas fa-plus me-2"></i>إضافة تعديل جديد
                </div>
                <div class="card-body">
                    <form method="POST" action="{{ route('salary.adjustments.store') }}">
                        @csrf

                        <div class="mb-3">
                            <label class="form-label fw-semibold small">الموظف <span class="text-danger">*</span></label>
                            <select name="employee_id" class="form-select form-select-sm" required>
                                <option value="">— اختر موظف —</option>
                                @foreach($employees as $emp)
                                    <option value="{{ $emp->id }}" {{ old('employee_id') == $emp->id ? 'selected' : '' }}>
                                        {{ $emp->name }}
                                    </option>
                                @endforeach
                            </select>
                        </div>

                        <div class="mb-3">
                            <label class="form-label fw-semibold small">النوع <span class="text-danger">*</span></label>
                            <select name="type" class="form-select form-select-sm" required>
                                <option value="">— اختر —</option>
                                <option value="bonus"     {{ old('type') == 'bonus'     ? 'selected' : '' }}>✅ مكافأة (إضافة للراتب)</option>
                                <option value="expense"   {{ old('type') == 'expense'   ? 'selected' : '' }}>💰 مصروف مستحق للموظف (إضافة)</option>
                                <option value="deduction" {{ old('type') == 'deduction' ? 'selected' : '' }}>❌ خصم (حذف من الراتب)</option>
                                <option value="other"     {{ old('type') == 'other'     ? 'selected' : '' }}>📋 أخرى (يجب تحديد الإشارة)</option>
                            </select>
                        </div>

                        {{-- حقل الإشارة — يظهر فقط عند اختيار "أخرى" --}}
                        <div class="mb-3" id="sign-field" style="display: {{ old('type') === 'other' ? 'block' : 'none' }}">
                            <label class="form-label fw-semibold small">الإشارة <span class="text-danger">*</span></label>
                            <select name="sign" class="form-select form-select-sm">
                                <option value="1"  {{ old('sign') == '1'  ? 'selected' : '' }}>➕ إضافة (لصالح الموظف)</option>
                                <option value="-1" {{ old('sign', '-1') == '-1' ? 'selected' : '' }}>➖ خصم (على الموظف)</option>
                            </select>
                        </div>

                        <div class="mb-3">
                            <label class="form-label fw-semibold small">المبلغ (₪) <span class="text-danger">*</span></label>
                            <input type="number" name="amount" class="form-control form-control-sm"
                                value="{{ old('amount') }}" step="0.01" min="0.01" required>
                        </div>

                        <div class="mb-3">
                            <label class="form-label fw-semibold small">التاريخ <span class="text-danger">*</span></label>
                            <input type="date" name="adjustment_date" class="form-control form-control-sm"
                                value="{{ old('adjustment_date', now()->toDateString()) }}" required>
                        </div>

                        <div class="mb-3">
                            <label class="form-label fw-semibold small">السبب / الوصف <span class="text-danger">*</span></label>
                            <input type="text" name="reason" class="form-control form-control-sm"
                                value="{{ old('reason') }}" placeholder="مثال: مكافأة أداء شهر أبريل" required>
                        </div>

                        <div class="mb-4">
                            <label class="form-label fw-semibold small">ملاحظات إضافية</label>
                            <textarea name="notes" class="form-control form-control-sm" rows="2"
                                placeholder="اختياري">{{ old('notes') }}</textarea>
                        </div>

                        <div class="d-grid">
                            <button type="submit" class="btn btn-success btn-sm">
                                <i class="fas fa-plus me-1"></i> إضافة التعديل
                            </button>
                        </div>
                    </form>
                </div>
            </div>
        </div>

        {{-- ===== قائمة التعديلات ===== --}}
        <div class="col-lg-8">

            {{-- فلاتر --}}
            <div class="card shadow-sm mb-3">
                <div class="card-body py-2">
                    <form method="GET" class="row g-2 align-items-end">
                        <div class="col-md-5">
                            <label class="form-label small fw-semibold mb-1">الموظف</label>
                            <select name="employee_id" class="form-select form-select-sm">
                                <option value="">الكل</option>
                                @foreach($employees as $emp)
                                    <option value="{{ $emp->id }}" {{ request('employee_id') == $emp->id ? 'selected' : '' }}>{{ $emp->name }}</option>
                                @endforeach
                            </select>
                        </div>
                        <div class="col-md-4">
                            <label class="form-label small fw-semibold mb-1">الحالة</label>
                            <select name="status" class="form-select form-select-sm">
                                <option value="">الكل</option>
                                <option value="pending"   {{ request('status') == 'pending'   ? 'selected' : '' }}>معلّق</option>
                                <option value="applied"   {{ request('status') == 'applied'   ? 'selected' : '' }}>مُطبَّق</option>
                                <option value="cancelled" {{ request('status') == 'cancelled' ? 'selected' : '' }}>ملغي</option>
                            </select>
                        </div>
                        <div class="col-md-3 d-flex gap-2">
                            <button type="submit" class="btn btn-primary btn-sm flex-fill">بحث</button>
                            <a href="{{ route('salary.adjustments') }}" class="btn btn-outline-secondary btn-sm">مسح</a>
                        </div>
                    </form>
                </div>
            </div>

            {{-- الجدول --}}
            <div class="card shadow-sm">
                <div class="card-body p-0">
                    <div class="table-responsive">
                        <table class="table table-hover align-middle mb-0">
                            <thead class="table-dark">
                                <tr>
                                    <th class="px-3">الموظف</th>
                                    <th class="text-center">النوع</th>
                                    <th>السبب</th>
                                    <th class="text-end">المبلغ</th>
                                    <th>التاريخ</th>
                                    <th class="text-center">الحالة</th>
                                    <th></th>
                                </tr>
                            </thead>
                            <tbody>
                                @forelse($adjustments as $adj)
                                <tr>
                                    <td class="px-3">
                                        <div class="fw-semibold small">{{ $adj->employee?->name ?? '—' }}</div>
                                    </td>
                                    <td class="text-center">
                                        @php
                                            $typeMap = [
                                                'bonus'     => ['label'=>'مكافأة',  'class'=>'bg-success'],
                                                'expense'   => ['label'=>'مصروف',   'class'=>'bg-info text-dark'],
                                                'deduction' => ['label'=>'خصم',     'class'=>'bg-danger'],
                                                'other'     => ['label'=>'أخرى',    'class'=>'bg-secondary'],
                                            ];
                                            $tm = $typeMap[$adj->type] ?? ['label'=>$adj->type,'class'=>'bg-secondary'];
                                        @endphp
                                        <span class="badge {{ $tm['class'] }}">{{ $tm['label'] }}</span>
                                    </td>
                                    <td class="small">{{ $adj->reason }}</td>
                                    <td class="text-end fw-semibold {{ $adj->is_addition ? 'text-success' : 'text-danger' }}">
                                        {{ $adj->is_addition ? '+' : '−' }}
                                        {{ number_format($adj->amount, 2) }} ₪
                                    </td>
                                    <td class="small text-muted">{{ $adj->adjustment_date?->format('d/m/Y') }}</td>
                                    <td class="text-center">
                                        @if($adj->status === 'pending')
                                            <span class="badge bg-warning text-dark">معلّق</span>
                                        @elseif($adj->status === 'applied')
                                            <span class="badge bg-success">مُطبَّق</span>
                                        @else
                                            <span class="badge bg-secondary">ملغي</span>
                                        @endif
                                    </td>
                                    <td class="text-end pe-2">
                                        @if($adj->status === 'pending')
                                        <form method="POST" action="{{ route('salary.adjustments.cancel', $adj) }}"
                                            onsubmit="return confirm('إلغاء هذا التعديل؟')">
                                            @csrf
                                            <button type="submit" class="btn btn-sm btn-outline-danger" title="إلغاء">
                                                <i class="fas fa-times"></i>
                                            </button>
                                        </form>
                                        @endif
                                    </td>
                                </tr>
                                @empty
                                <tr>
                                    <td colspan="7" class="text-center py-5 text-muted">لا توجد تعديلات</td>
                                </tr>
                                @endforelse
                            </tbody>
                        </table>
                    </div>
                </div>
                @if($adjustments->hasPages())
                <div class="card-footer">{{ $adjustments->links() }}</div>
                @endif
            </div>

        </div>
    </div>
</div>
<script>
document.querySelector('select[name="type"]').addEventListener('change', function() {
    document.getElementById('sign-field').style.display = this.value === 'other' ? 'block' : 'none';
});
</script>
</x-app-layout>
