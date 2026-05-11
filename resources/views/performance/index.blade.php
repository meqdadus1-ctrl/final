<x-app-layout>
    <x-slot name="title">تقييم الأداء</x-slot>

    <div class="d-flex justify-content-between align-items-center mb-3">
        <h5 class="mb-0 fw-bold" style="color:var(--fox-brown)">تقييم الأداء الأسبوعي</h5>
        <div class="d-flex gap-2">
            <a href="{{ route('performance.export', request()->query()) }}" class="btn btn-success btn-sm">
                <i class="fas fa-file-excel me-1"></i> تصدير Excel
            </a>
            <a href="{{ route('performance.create') }}" class="btn btn-primary btn-sm">
                <i class="fas fa-plus me-1"></i> تقييم جديد
            </a>
        </div>
    </div>

    {{-- فلاتر --}}
    <div class="card mb-3">
        <div class="card-body py-2">
            <form method="GET" class="row g-2 align-items-end">
                <div class="col-md-3">
                    <select name="employee_id" class="form-select form-select-sm">
                        <option value="">كل الموظفين</option>
                        @foreach($employees as $emp)
                            <option value="{{ $emp->id }}" {{ request('employee_id') == $emp->id ? 'selected' : '' }}>
                                {{ $emp->name }}
                            </option>
                        @endforeach
                    </select>
                </div>
                <div class="col-md-2">
                    <select name="year" class="form-select form-select-sm">
                        <option value="">كل السنوات</option>
                        @foreach($years as $y)
                            <option value="{{ $y }}" {{ request('year') == $y ? 'selected' : '' }}>{{ $y }}</option>
                        @endforeach
                    </select>
                </div>
                <div class="col-md-2">
                    <select name="week_number" class="form-select form-select-sm">
                        <option value="">كل الأسابيع</option>
                        @foreach($weeks as $w)
                            <option value="{{ $w }}" {{ request('week_number') == $w ? 'selected' : '' }}>
                                الأسبوع {{ $w }}
                            </option>
                        @endforeach
                    </select>
                </div>
                <div class="col-md-auto">
                    <button class="btn btn-primary btn-sm">بحث</button>
                    <a href="{{ route('performance.index') }}" class="btn btn-secondary btn-sm">مسح</a>
                </div>
            </form>
        </div>
    </div>

    <div class="card">
        <div class="card-body p-0">
            <table class="table table-hover mb-0 text-center align-middle">
                <thead>
                    <tr>
                        <th class="text-start">الموظف</th>
                        <th>القسم</th>
                        <th>السنة</th>
                        <th>الأسبوع</th>
                        <th>الانضباط</th>
                        <th>الجودة</th>
                        <th>الإنتاجية</th>
                        <th>التواصل</th>
                        <th>الفريق</th>
                        <th>المجموع</th>
                        <th>التقدير</th>
                        <th>الحالة</th>
                        <th></th>
                    </tr>
                </thead>
                <tbody>
                    @forelse($reviews as $r)
                    <tr>
                        <td class="text-start fw-semibold">{{ $r->employee->name }}</td>
                        <td>{{ $r->employee->department->name ?? '—' }}</td>
                        <td>{{ $r->year }}</td>
                        <td>
                            <span class="badge bg-secondary">أسبوع {{ $r->week_number }}</span>
                        </td>
                        <td>{{ $r->punctuality }}/5</td>
                        <td>{{ $r->quality }}/5</td>
                        <td>{{ $r->productivity }}/5</td>
                        <td>{{ $r->communication }}/5</td>
                        <td>{{ $r->teamwork }}/5</td>
                        <td><strong>{{ number_format($r->overall_score, 2) }}</strong></td>
                        <td><span class="badge bg-{{ $r->score_color }}">{{ $r->score_label }}</span></td>
                        <td>
                            <span class="badge {{ $r->status === 'final' ? 'bg-success' : 'bg-warning text-dark' }}">
                                {{ $r->status === 'final' ? 'نهائي' : 'مسودة' }}
                            </span>
                        </td>
                        <td>
                            <a href="{{ route('performance.show', $r) }}" class="btn btn-sm btn-outline-primary">عرض</a>
                        </td>
                    </tr>
                    @empty
                    <tr><td colspan="13" class="text-muted py-4">لا توجد تقييمات</td></tr>
                    @endforelse
                </tbody>
            </table>
        </div>
    </div>

    <div class="mt-3">{{ $reviews->links() }}</div>
</x-app-layout>
