<x-portal-layout>
<x-slot name="title">سجل الحضور</x-slot>

{{-- Month/Year Filter --}}
<div class="card mb-4">
    <div class="card-body py-3">
        <form method="GET" action="{{ route('portal.attendance') }}" class="row g-2 align-items-end">
            <div class="col-md-3 col-6">
                <label class="form-label fw-semibold small mb-1">الشهر</label>
                <select name="month" class="form-select form-select-sm">
                    @foreach(['يناير','فبراير','مارس','أبريل','مايو','يونيو','يوليو','أغسطس','سبتمبر','أكتوبر','نوفمبر','ديسمبر'] as $i => $m)
                        <option value="{{ $i+1 }}" {{ $month == $i+1 ? 'selected' : '' }}>{{ $m }}</option>
                    @endforeach
                </select>
            </div>
            <div class="col-md-2 col-6">
                <label class="form-label fw-semibold small mb-1">السنة</label>
                <input type="number" name="year" class="form-control form-control-sm"
                       value="{{ $year }}" min="2020" max="2035">
            </div>
            <div class="col-md-2 col-12">
                <button type="submit" class="btn btn-primary btn-sm w-100">
                    <i class="fas fa-search me-1"></i> عرض
                </button>
            </div>
        </form>
    </div>
</div>

{{-- Stats --}}
<div class="row g-3 mb-4">
    <div class="col-6 col-md-3">
        <div class="stat-card">
            <div class="stat-icon" style="background:#e8f5e9; color:#2e7d32;"><i class="fas fa-check"></i></div>
            <div>
                <div class="stat-value text-success">{{ $stats['present'] }}</div>
                <div class="stat-label">يوم حضور</div>
            </div>
        </div>
    </div>
    <div class="col-6 col-md-3">
        <div class="stat-card">
            <div class="stat-icon" style="background:#fce4ec; color:#c62828;"><i class="fas fa-times"></i></div>
            <div>
                <div class="stat-value text-danger">{{ $stats['absent'] }}</div>
                <div class="stat-label">يوم غياب</div>
            </div>
        </div>
    </div>
    <div class="col-6 col-md-3">
        <div class="stat-card">
            <div class="stat-icon" style="background:#fff8e1; color:#e65100;"><i class="fas fa-clock"></i></div>
            <div>
                <div class="stat-value text-warning">{{ $stats['late'] }}</div>
                <div class="stat-label">يوم تأخير</div>
            </div>
        </div>
    </div>
    <div class="col-6 col-md-3">
        <div class="stat-card">
            <div class="stat-icon" style="background:#e3f2fd; color:#1565c0;"><i class="fas fa-umbrella-beach"></i></div>
            <div>
                <div class="stat-value text-info">{{ $stats['leave'] }}</div>
                <div class="stat-label">يوم إجازة</div>
            </div>
        </div>
    </div>
</div>

{{-- Attendance Table --}}
<div class="card">
    <div class="card-header">
        <i class="fas fa-calendar-alt me-2 text-primary"></i>
        السجل التفصيلي —
        @php $months = ['يناير','فبراير','مارس','أبريل','مايو','يونيو','يوليو','أغسطس','سبتمبر','أكتوبر','نوفمبر','ديسمبر']; @endphp
        {{ $months[$month-1] }} {{ $year }}
    </div>
    <div class="card-body p-0">
        <div class="table-responsive">
            <table class="table table-hover align-middle mb-0">
                <thead class="table-dark">
                    <tr>
                        <th class="px-3">التاريخ</th>
                        <th class="text-center">وقت الدخول</th>
                        <th class="text-center">وقت الخروج</th>
                        <th class="text-center">ساعات العمل</th>
                        <th class="text-center">الحالة</th>
                        <th>ملاحظات</th>
                    </tr>
                </thead>
                <tbody>
                    @forelse($records as $r)
                    <tr>
                        <td class="px-3">
                            <div class="fw-semibold">{{ \Carbon\Carbon::parse($r->date)->format('d/m/Y') }}</div>
                            <div class="text-muted small">{{ \Carbon\Carbon::parse($r->date)->locale('ar')->dayName }}</div>
                        </td>
                        <td class="text-center">{{ $r->check_in ?? '—' }}</td>
                        <td class="text-center">{{ $r->check_out ?? '—' }}</td>
                        <td class="text-center">
                            @if($r->work_hours)
                                <span class="badge bg-light text-dark border">{{ $r->work_hours }} س</span>
                            @else
                                <span class="text-muted">—</span>
                            @endif
                        </td>
                        <td class="text-center">
                            @switch($r->status)
                                @case('present') <span class="badge bg-success">حضور</span> @break
                                @case('absent')  <span class="badge bg-danger">غياب</span>  @break
                                @case('late')    <span class="badge bg-warning text-dark">تأخير</span> @break
                                @case('leave')   <span class="badge bg-info text-dark">إجازة</span>   @break
                                @default         <span class="badge bg-secondary">{{ $r->status }}</span>
                            @endswitch
                        </td>
                        <td class="text-muted small">{{ $r->notes ?? '—' }}</td>
                    </tr>
                    @empty
                    <tr>
                        <td colspan="6" class="text-center text-muted py-5">
                            <i class="fas fa-calendar-times fa-2x mb-2 d-block opacity-25"></i>
                            لا توجد سجلات لهذا الشهر
                        </td>
                    </tr>
                    @endforelse
                </tbody>
            </table>
        </div>
    </div>
</div>
</x-portal-layout>
