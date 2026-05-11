<x-app-layout>
<x-slot name="title">القيود المحاسبية</x-slot>
<div class="container-fluid py-4" dir="rtl">

    {{-- Header --}}
    <div class="d-flex justify-content-between align-items-center mb-4">
        <div>
            <h4 class="mb-0 fw-bold"><i class="fas fa-journal-whills text-primary me-2"></i>القيود المحاسبية</h4>
            <small class="text-muted">جميع القيود المالية مرتبة زمنياً</small>
        </div>
        <a href="{{ route('ledger.index') }}" class="btn btn-outline-secondary btn-sm">
            <i class="fas fa-book-open me-1"></i> كشف الحسابات
        </a>
    </div>

    {{-- Filters --}}
    <div class="card shadow-sm mb-4">
        <div class="card-body pb-2">
            <form method="GET" action="{{ route('ledger.journal') }}" class="row g-2 align-items-end">
                <div class="col-md-2">
                    <label class="form-label small mb-1">من تاريخ</label>
                    <input type="date" name="from" class="form-control form-control-sm" value="{{ request('from') }}">
                </div>
                <div class="col-md-2">
                    <label class="form-label small mb-1">إلى تاريخ</label>
                    <input type="date" name="to" class="form-control form-control-sm" value="{{ request('to') }}">
                </div>
                <div class="col-md-2">
                    <label class="form-label small mb-1">الموظف</label>
                    <select name="employee" class="form-select form-select-sm">
                        <option value="">— الكل —</option>
                        @foreach($employees as $emp)
                            <option value="{{ $emp->id }}" {{ request('employee') == $emp->id ? 'selected' : '' }}>
                                {{ $emp->name }}
                            </option>
                        @endforeach
                    </select>
                </div>
                <div class="col-md-2">
                    <label class="form-label small mb-1">القسم</label>
                    <select name="dept" class="form-select form-select-sm">
                        <option value="">— الكل —</option>
                        @foreach($departments as $dept)
                            <option value="{{ $dept->id }}" {{ request('dept') == $dept->id ? 'selected' : '' }}>
                                {{ $dept->name }}
                            </option>
                        @endforeach
                    </select>
                </div>
                <div class="col-md-2">
                    <label class="form-label small mb-1">نوع القيد</label>
                    <select name="entry_type" class="form-select form-select-sm">
                        <option value="">— الكل —</option>
                        @foreach($entryTypes as $key => $label)
                            <option value="{{ $key }}" {{ request('entry_type') === $key ? 'selected' : '' }}>
                                {{ $label }}
                            </option>
                        @endforeach
                    </select>
                </div>
                <div class="col-md-1">
                    <label class="form-label small mb-1">الجهة</label>
                    <select name="side" class="form-select form-select-sm">
                        <option value="">الكل</option>
                        <option value="credit" {{ request('side') === 'credit' ? 'selected' : '' }}>دائن</option>
                        <option value="debit"  {{ request('side') === 'debit'  ? 'selected' : '' }}>مدين</option>
                    </select>
                </div>
                <div class="col-md-1 d-flex gap-1">
                    <button type="submit" class="btn btn-primary btn-sm w-100">
                        <i class="fas fa-search"></i>
                    </button>
                    <a href="{{ route('ledger.journal') }}" class="btn btn-outline-secondary btn-sm">
                        <i class="fas fa-times"></i>
                    </a>
                </div>
            </form>
        </div>
    </div>

    {{-- Totals --}}
    <div class="row g-3 mb-4">
        <div class="col-md-4">
            <div class="card border-0 bg-success bg-opacity-10 text-center py-2">
                <div class="card-body py-2">
                    <div class="fs-5 fw-bold text-success">{{ number_format($totals->total_credit ?? 0, 2) }} ₪</div>
                    <div class="small text-muted">إجمالي الدائن (للموظف)</div>
                </div>
            </div>
        </div>
        <div class="col-md-4">
            <div class="card border-0 bg-danger bg-opacity-10 text-center py-2">
                <div class="card-body py-2">
                    <div class="fs-5 fw-bold text-danger">{{ number_format($totals->total_debit ?? 0, 2) }} ₪</div>
                    <div class="small text-muted">إجمالي المدين (من الموظف)</div>
                </div>
            </div>
        </div>
        <div class="col-md-4">
            <div class="card border-0 bg-primary bg-opacity-10 text-center py-2">
                <div class="card-body py-2">
                    <div class="fs-5 fw-bold text-primary">{{ number_format($totals->total_count ?? 0) }}</div>
                    <div class="small text-muted">إجمالي القيود</div>
                </div>
            </div>
        </div>
    </div>

    {{-- Table --}}
    <div class="card shadow-sm">
        <div class="card-body p-0">
            <div class="table-responsive">
                <table class="table table-hover table-sm mb-0 align-middle">
                    <thead class="table-dark">
                        <tr>
                            <th class="text-center" style="width:60px">#</th>
                            <th>التاريخ</th>
                            <th>الموظف</th>
                            <th>القسم</th>
                            <th>نوع القيد</th>
                            <th>البيان</th>
                            <th class="text-end text-success">دائن ₪</th>
                            <th class="text-end text-danger">مدين ₪</th>
                            <th class="text-end">الرصيد بعده</th>
                            <th class="text-center">المرجع</th>
                        </tr>
                    </thead>
                    <tbody>
                        @forelse($entries as $entry)
                            <tr>
                                <td class="text-center text-muted small">{{ $entry->id }}</td>
                                <td class="small text-nowrap">{{ $entry->entry_date->format('Y/m/d') }}</td>
                                <td>
                                    <a href="{{ route('ledger.show', $entry->employee) }}" class="text-decoration-none fw-semibold">
                                        {{ $entry->employee->name }}
                                    </a>
                                </td>
                                <td class="small text-muted">{{ $entry->employee->department?->name ?? '—' }}</td>
                                <td>
                                    <span class="badge bg-{{ $entry->entry_type_color }} bg-opacity-75">
                                        {{ $entry->entry_type_label }}
                                    </span>
                                </td>
                                <td class="small">{{ $entry->description }}</td>
                                <td class="text-end fw-semibold text-success">
                                    {{ $entry->credit > 0 ? number_format($entry->credit, 2) : '—' }}
                                </td>
                                <td class="text-end fw-semibold text-danger">
                                    {{ $entry->debit > 0 ? number_format($entry->debit, 2) : '—' }}
                                </td>
                                <td class="text-end small">
                                    <span class="{{ $entry->balance_after >= 0 ? 'text-success' : 'text-danger' }}">
                                        {{ number_format($entry->balance_after, 2) }}
                                    </span>
                                </td>
                                <td class="text-center small text-muted">
                                    {{ $entry->reference_type ?? '—' }}
                                </td>
                            </tr>
                        @empty
                            <tr>
                                <td colspan="10" class="text-center text-muted py-4">
                                    <i class="fas fa-inbox fa-2x mb-2 d-block opacity-25"></i>
                                    لا توجد قيود محاسبية بهذا الفلتر
                                </td>
                            </tr>
                        @endforelse
                    </tbody>
                </table>
            </div>
        </div>

        @if($entries->hasPages())
            <div class="card-footer d-flex justify-content-between align-items-center">
                <span class="small text-muted">
                    عرض {{ $entries->firstItem() }}–{{ $entries->lastItem() }} من {{ $entries->total() }} قيد
                </span>
                {{ $entries->links() }}
            </div>
        @endif
    </div>

</div>
</x-app-layout>
