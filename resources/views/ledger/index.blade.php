<x-app-layout>
<x-slot name="title">كشف الحسابات</x-slot>

<div class="container-fluid" dir="rtl">

    {{-- العنوان --}}
    <div class="d-flex align-items-center justify-content-between mb-4">
        <div>
            <h4 class="mb-0 fw-bold"><i class="fas fa-book-open me-2 text-primary"></i>كشف الحسابات</h4>
            <small class="text-muted">الرصيد الحالي لجميع الموظفين — اضغط على أي موظف لعرض كشفه التفصيلي</small>
        </div>
    </div>

    {{-- فلاتر --}}
    <form method="GET" action="{{ route('ledger.index') }}" class="mb-3">
        <div class="row g-2 align-items-end">
            <div class="col-md-4">
                <input type="text" name="search" class="form-control form-control-sm"
                    placeholder="🔍 بحث باسم الموظف..." value="{{ request('search') }}">
            </div>
            <div class="col-md-3">
                <select name="dept" class="form-select form-select-sm">
                    <option value="">كل الأقسام</option>
                    @foreach($departments as $dept)
                        <option value="{{ $dept->id }}" {{ request('dept') == $dept->id ? 'selected' : '' }}>
                            {{ $dept->name }}
                        </option>
                    @endforeach
                </select>
            </div>
            <div class="col-md-2">
                <select name="status" class="form-select form-select-sm">
                    <option value="all"    {{ request('status','active') === 'all'     ? 'selected' : '' }}>الكل</option>
                    <option value="active" {{ request('status','active') === 'active'  ? 'selected' : '' }}>نشط</option>
                    <option value="inactive" {{ request('status') === 'inactive'       ? 'selected' : '' }}>غير نشط</option>
                </select>
            </div>
            <div class="col-md-auto">
                <button type="submit" class="btn btn-primary btn-sm">
                    <i class="fas fa-filter me-1"></i>تصفية
                </button>
                <a href="{{ route('ledger.index') }}" class="btn btn-outline-secondary btn-sm ms-1">
                    <i class="fas fa-times me-1"></i>مسح
                </a>
            </div>
        </div>
    </form>

    {{-- الجدول --}}
    <div class="card shadow-sm">
        <div class="card-header py-2 d-flex justify-content-between align-items-center">
            <span class="fw-semibold small">
                <i class="fas fa-users me-1 text-primary"></i>
                {{ $employees->count() }} موظف
            </span>
            @php
                $totalPositive = $balances->filter(fn($b) => $b >= 0)->sum();
                $totalNegative = $balances->filter(fn($b) => $b < 0)->sum();
            @endphp
            <div class="d-flex gap-3 small">
                <span class="text-success fw-semibold">
                    <i class="fas fa-arrow-up me-1"></i>إجمالي موجب: {{ number_format($totalPositive, 2) }} ₪
                </span>
                @if($totalNegative < 0)
                <span class="text-danger fw-semibold">
                    <i class="fas fa-arrow-down me-1"></i>إجمالي سالب: {{ number_format($totalNegative, 2) }} ₪
                </span>
                @endif
            </div>
        </div>

        <div class="table-responsive">
            <table class="table table-hover mb-0" style="font-size:13px">
                <thead class="table-light">
                    <tr>
                        <th class="text-end" style="width:40px">#</th>
                        <th class="text-end">الموظف</th>
                        <th class="text-end">القسم</th>
                        <th class="text-end">المسمى الوظيفي</th>
                        <th class="text-center">الحالة</th>
                        <th class="text-end">الرصيد الحالي</th>
                        <th class="text-center" style="width:80px">الكشف</th>
                    </tr>
                </thead>
                <tbody>
                    @forelse($employees as $emp)
                    @php $balance = (float)($balances[$emp->id] ?? 0); @endphp
                    <tr style="cursor:pointer" onclick="window.location='{{ route('ledger.show', $emp->id) }}'">
                        <td class="text-end text-muted small">{{ $emp->employee_number ?? $emp->id }}</td>
                        <td class="text-end fw-semibold">{{ $emp->name }}</td>
                        <td class="text-end text-muted">{{ $emp->department->name ?? '—' }}</td>
                        <td class="text-end text-muted small">{{ $emp->job_title ?? '—' }}</td>
                        <td class="text-center">
                            <span class="badge {{ $emp->status === 'active' ? 'bg-success' : 'bg-secondary' }} rounded-pill px-2">
                                {{ $emp->status === 'active' ? 'نشط' : 'غير نشط' }}
                            </span>
                        </td>
                        <td class="text-end">
                            <span class="fw-bold fs-6 {{ $balance > 0 ? 'text-success' : ($balance < 0 ? 'text-danger' : 'text-muted') }}">
                                {{ number_format($balance, 2) }} ₪
                            </span>
                        </td>
                        <td class="text-center">
                            <a href="{{ route('ledger.show', $emp->id) }}"
                               class="btn btn-outline-primary btn-sm py-0 px-2"
                               onclick="event.stopPropagation()">
                                <i class="fas fa-book-open"></i>
                            </a>
                        </td>
                    </tr>
                    @empty
                    <tr>
                        <td colspan="7" class="text-center text-muted py-5">
                            <i class="fas fa-inbox fa-2x mb-2 d-block opacity-25"></i>
                            لا يوجد موظفون مطابقون للبحث
                        </td>
                    </tr>
                    @endforelse
                </tbody>
                @if($employees->isNotEmpty())
                <tfoot class="table-light fw-bold">
                    <tr>
                        <td colspan="5" class="text-end">صافي إجمالي الأرصدة</td>
                        @php $netTotal = $balances->sum(); @endphp
                        <td class="text-end {{ $netTotal >= 0 ? 'text-success' : 'text-danger' }}">
                            {{ number_format($netTotal, 2) }} ₪
                        </td>
                        <td></td>
                    </tr>
                </tfoot>
                @endif
            </table>
        </div>
    </div>

</div>
</x-app-layout>
