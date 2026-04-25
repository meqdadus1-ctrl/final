{{-- resources/views/payslips/index.blade.php --}}
<x-app-layout>
    <x-slot name="title">كشوف الرواتب</x-slot>
<div class="container-fluid py-4" dir="rtl">

    {{-- Header --}}
    <div class="d-flex justify-content-between align-items-center mb-4">
        <div>
            <h4 class="mb-0 fw-bold">💰 كشوف الرواتب</h4>
            <small class="text-muted">إدارة وإصدار كشوف رواتب الموظفين</small>
        </div>
        <div class="d-flex gap-2">
            <button class="btn btn-outline-secondary btn-sm" data-bs-toggle="modal" data-bs-target="#bulkModal">
                ⚡ إنشاء جماعي
            </button>
            <a href="{{ route('payslips.create') }}" class="btn btn-primary btn-sm">
                + إنشاء كشف راتب
            </a>
        </div>
    </div>

    {{-- Alerts --}}
    @if(session('success'))
        <div class="alert alert-success alert-dismissible fade show" role="alert">
            {{ session('success') }}
            <button type="button" class="btn-close" data-bs-dismiss="alert"></button>
        </div>
    @endif
    @if(session('error'))
        <div class="alert alert-danger alert-dismissible fade show" role="alert">
            {{ session('error') }}
            <button type="button" class="btn-close" data-bs-dismiss="alert"></button>
        </div>
    @endif

    {{-- Filters --}}
    <div class="card shadow-sm mb-4">
        <div class="card-body">
            <form method="GET" class="row g-3 align-items-end">
                <div class="col-md-3">
                    <label class="form-label small fw-semibold">الموظف</label>
                    <select name="employee_id" class="form-select form-select-sm">
                        <option value="">الكل</option>
                        @foreach($employees as $emp)
                            <option value="{{ $emp->id }}" {{ request('employee_id') == $emp->id ? 'selected' : '' }}>
                                {{ $emp->name }}
                            </option>
                        @endforeach
                    </select>
                </div>
                <div class="col-md-2">
                    <label class="form-label small fw-semibold">الشهر</label>
                    <select name="month" class="form-select form-select-sm">
                        <option value="">الكل</option>
                        @foreach(['يناير','فبراير','مارس','أبريل','مايو','يونيو','يوليو','أغسطس','سبتمبر','أكتوبر','نوفمبر','ديسمبر'] as $i => $m)
                            <option value="{{ $i+1 }}" {{ request('month') == $i+1 ? 'selected' : '' }}>{{ $m }}</option>
                        @endforeach
                    </select>
                </div>
                <div class="col-md-2">
                    <label class="form-label small fw-semibold">السنة</label>
                    <select name="year" class="form-select form-select-sm">
                        <option value="">الكل</option>
                        @for($y = date('Y'); $y >= 2020; $y--)
                            <option value="{{ $y }}" {{ request('year') == $y ? 'selected' : '' }}>{{ $y }}</option>
                        @endfor
                    </select>
                </div>
                <div class="col-md-2">
                    <label class="form-label small fw-semibold">الحالة</label>
                    <select name="status" class="form-select form-select-sm">
                        <option value="">الكل</option>
                        <option value="draft"  {{ request('status')=='draft'  ? 'selected':'' }}>مسودة</option>
                        <option value="issued" {{ request('status')=='issued' ? 'selected':'' }}>صادر</option>
                        <option value="paid"   {{ request('status')=='paid'   ? 'selected':'' }}>مدفوع</option>
                    </select>
                </div>
                <div class="col-md-3 d-flex gap-2">
                    <button type="submit" class="btn btn-primary btn-sm flex-fill">بحث</button>
                    <a href="{{ route('payslips.index') }}" class="btn btn-outline-secondary btn-sm">مسح</a>
                </div>
            </form>
        </div>
    </div>

    {{-- Table --}}
    <div class="card shadow-sm">
        <div class="card-body p-0">
            <div class="table-responsive">
                <table class="table table-hover align-middle mb-0">
                    <thead class="table-light">
                        <tr>
                            <th class="px-3">#</th>
                            <th>الموظف</th>
                            <th>الشهر / السنة</th>
                            <th>الراتب الأساسي</th>
                            <th>الإجمالي</th>
                            <th>الخصومات</th>
                            <th>الصافي</th>
                            <th>الحالة</th>
                            <th class="text-center">الإجراءات</th>
                        </tr>
                    </thead>
                    <tbody>
                        @forelse($payslips as $p)
                        <tr>
                            <td class="px-3 text-muted small">{{ $p->id }}</td>
                            <td>
                                <div class="fw-semibold">{{ $p->employee?->name ?? '—' }}</div>
                                <small class="text-muted">{{ $p->employee?->department?->name ?? '—' }}</small>
                            </td>
                            <td>{{ $p->month_name }} {{ $p->year }}</td>
                            <td>{{ number_format($p->basic_salary, 2) }}</td>
                            <td class="text-success fw-semibold">{{ number_format($p->total_allowances, 2) }}</td>
                            <td class="text-danger">{{ number_format($p->total_deductions, 2) }}</td>
                            <td class="fw-bold">{{ number_format($p->net_salary, 2) }}</td>
                            <td>
                                <span class="badge bg-{{ $p->status_color }}">{{ $p->status_label }}</span>
                            </td>
                            <td class="text-center">
                                <div class="d-flex justify-content-center gap-1">
                                    <a href="{{ route('payslips.show', $p) }}" class="btn btn-sm btn-outline-primary" title="عرض">👁️</a>
                                    @if($p->status !== 'paid')
                                    <a href="{{ route('payslips.edit', $p) }}" class="btn btn-sm btn-outline-secondary" title="تعديل">✏️</a>
                                    @endif
                                    <a href="{{ route('payslips.pdf', $p) }}" class="btn btn-sm btn-outline-danger" title="PDF">📄</a>
                                    @if($p->status !== 'paid')
                                    <form action="{{ route('payslips.destroy', $p) }}" method="POST" onsubmit="return confirm('حذف هذا الكشف؟')">
                                        @csrf @method('DELETE')
                                        <button class="btn btn-sm btn-outline-danger" title="حذف">🗑️</button>
                                    </form>
                                    @endif
                                </div>
                            </td>
                        </tr>
                        @empty
                        <tr>
                            <td colspan="9" class="text-center py-5 text-muted">
                                لا توجد كشوف رواتب بعد
                            </td>
                        </tr>
                        @endforelse
                    </tbody>
                </table>
            </div>
        </div>
        @if($payslips->hasPages())
        <div class="card-footer">
            {{ $payslips->links() }}
        </div>
        @endif
    </div>
</div>

{{-- Bulk Modal --}}
<div class="modal fade" id="bulkModal" tabindex="-1">
    <div class="modal-dialog">
        <form action="{{ route('payslips.bulk') }}" method="POST" class="modal-content">
            @csrf
            <div class="modal-header">
                <h5 class="modal-title">⚡ إنشاء كشوف جماعي</h5>
                <button type="button" class="btn-close" data-bs-dismiss="modal"></button>
            </div>
            <div class="modal-body">
                <p class="text-muted small">سيتم إنشاء كشف راتب لجميع الموظفين النشطين في الشهر المختار.</p>
                <div class="row g-3">
                    <div class="col-6">
                        <label class="form-label fw-semibold">الشهر</label>
                        <select name="month" class="form-select" required>
                            @foreach(['يناير','فبراير','مارس','أبريل','مايو','يونيو','يوليو','أغسطس','سبتمبر','أكتوبر','نوفمبر','ديسمبر'] as $i => $m)
                                <option value="{{ $i+1 }}" {{ $i+1 == date('n') ? 'selected' : '' }}>{{ $m }}</option>
                            @endforeach
                        </select>
                    </div>
                    <div class="col-6">
                        <label class="form-label fw-semibold">السنة</label>
                        <select name="year" class="form-select" required>
                            @for($y = date('Y'); $y >= 2020; $y--)
                                <option value="{{ $y }}" {{ $y == date('Y') ? 'selected' : '' }}>{{ $y }}</option>
                            @endfor
                        </select>
                    </div>
                </div>
            </div>
            <div class="modal-footer">
                <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">إلغاء</button>
                <button type="submit" class="btn btn-primary">إنشاء الآن</button>
            </div>
        </form>
    </div>
</div>
</x-app-layout>
