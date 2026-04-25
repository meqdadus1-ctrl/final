<x-app-layout>
<x-slot name="title">الرواتب الأسبوعية</x-slot>
<div class="container-fluid py-4" dir="rtl">

    {{-- Header --}}
    <div class="d-flex justify-content-between align-items-center mb-4">
        <div>
            <h4 class="mb-0 fw-bold">💰 الرواتب الأسبوعية</h4>
            <small class="text-muted">نظام الرواتب مع كشف الحساب</small>
        </div>
        <div class="d-flex gap-2">
            <a href="{{ route('salary.adjustments') }}" class="btn btn-outline-secondary btn-sm">
                <i class="fas fa-sliders-h me-1"></i> التعديلات
            </a>
            <a href="{{ route('ledger.import') }}" class="btn btn-outline-success btn-sm">
                <i class="fas fa-file-import me-1"></i> استيراد Excel
            </a>
            <a href="{{ route('salary.create') }}" class="btn btn-primary btn-sm">
                <i class="fas fa-plus me-1"></i> احتساب راتب
            </a>
        </div>
    </div>

    @if(session('success'))
        <div class="alert alert-success alert-dismissible fade show">{{ session('success') }}<button type="button" class="btn-close" data-bs-dismiss="alert"></button></div>
    @endif
    @if(session('info'))
        <div class="alert alert-info alert-dismissible fade show">{{ session('info') }}<button type="button" class="btn-close" data-bs-dismiss="alert"></button></div>
    @endif

    {{-- فلاتر --}}
    <div class="card shadow-sm mb-4">
        <div class="card-body py-3">
            <form method="GET" class="row g-2 align-items-end">
                <div class="col-md-4">
                    <label class="form-label small fw-semibold">بحث نصي</label>
                    <input type="text" name="search" class="form-control form-control-sm"
                        placeholder="اسم الموظف / الرقم الوظيفي / رقم البصمة / الفترة"
                        value="{{ request('search') }}">
                </div>
                <div class="col-md-3">
                    <label class="form-label small fw-semibold">الموظف</label>
                    <select name="employee_id" class="form-select form-select-sm">
                        <option value="">الكل</option>
                        @foreach($employees as $emp)
                            <option value="{{ $emp->id }}" {{ request('employee_id') == $emp->id ? 'selected' : '' }}>{{ $emp->name }}</option>
                        @endforeach
                    </select>
                </div>
                <div class="col-md-2">
                    <label class="form-label small fw-semibold">الفترة</label>
                    <input type="text" name="fiscal_period" class="form-control form-control-sm"
                        placeholder="مثال: 2026-W17" value="{{ request('fiscal_period') }}">
                </div>
                <div class="col-md-3 d-flex gap-2">
                    <button type="submit" class="btn btn-primary btn-sm flex-fill">بحث</button>
                    <a href="{{ route('salary.index') }}" class="btn btn-outline-secondary btn-sm">مسح</a>
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
                            <th>الفترة</th>
                            <th class="text-center">ساعات العمل</th>
                            <th class="text-center">الأوفرتايم</th>
                            <th class="text-end">الإجمالي</th>
                            <th class="text-end">الخصومات</th>
                            <th class="text-end text-success fw-bold">الصافي</th>
                            <th class="text-end">الرصيد بعد</th>
                            <th class="text-center">الدفع</th>
                            <th></th>
                        </tr>
                    </thead>
                    <tbody>
                        @forelse($payments as $p)
                        <tr>
                            <td class="px-3">
                                <div class="fw-semibold">{{ $p->employee?->name ?? '—' }}</div>
                                <small class="text-muted">{{ $p->employee?->department?->name ?? '—' }}</small>
                            </td>
                            <td>
                                <span class="badge bg-secondary">{{ $p->fiscal_period }}</span><br>
                                <small class="text-muted">
                                    {{ $p->week_start?->format('d/m') }} — {{ $p->week_end?->format('d/m/Y') }}
                                </small>
                            </td>
                            <td class="text-center">{{ $p->hours_worked }} س</td>
                            <td class="text-center text-info">{{ $p->overtime_hours }} س</td>
                            <td class="text-end">{{ number_format($p->gross_salary, 2) }}</td>
                            <td class="text-end text-danger">{{ number_format($p->total_deductions, 2) }}</td>
                            <td class="text-end fw-bold text-success">{{ number_format($p->net_salary, 2) }} ₪</td>
                            <td class="text-end">
                                @php $bal = $p->balance_after; @endphp
                                <span class="fw-semibold {{ $bal >= 0 ? 'text-success' : 'text-danger' }}">
                                    {{ number_format($bal, 2) }} ₪
                                </span>
                            </td>
                            <td class="text-center">
                                @if($p->payment_method === 'bank')
                                    <span class="badge bg-primary">🏦 بنك</span>
                                @elseif($p->payment_method === 'cash')
                                    <span class="badge bg-warning text-dark">💵 كاش</span>
                                @else
                                    <span class="badge bg-info text-dark">📋 ترحيل</span>
                                @endif
                            </td>
                            <td>
                                <div class="d-flex gap-1">
                                    <a href="{{ route('salary.show', $p) }}" class="btn btn-sm btn-outline-primary" title="عرض">
                                        <i class="fas fa-eye"></i>
                                    </a>
                                    <a href="{{ route('salary.edit', $p) }}" class="btn btn-sm btn-outline-warning" title="تعديل">
                                        <i class="fas fa-edit"></i>
                                    </a>
                                    <form action="{{ route('salary.destroy', $p) }}" method="POST" class="d-inline">
                                        @csrf @method('DELETE')
                                        <button class="btn btn-sm btn-outline-danger" title="حذف"
                                            onclick="return confirm('هل أنت متأكد من حذف هذا الراتب؟')">
                                            <i class="fas fa-trash"></i>
                                        </button>
                                    </form>
                                </div>
                            </td>
                        </tr>
                        @empty
                        <tr>
                            <td colspan="10" class="text-center py-5 text-muted">لا توجد رواتب بعد</td>
                        </tr>
                        @endforelse
                    </tbody>
                </table>
            </div>
        </div>
        @if($payments->hasPages())
        <div class="card-footer">{{ $payments->links() }}</div>
        @endif
    </div>
</div>
</x-app-layout>
