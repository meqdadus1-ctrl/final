<x-app-layout>
    <x-slot name="title">سجل الحضور</x-slot>

    {{-- Filter --}}
    <div class="card mb-4">
        <div class="card-body">
            <form method="GET" action="{{ route('portal.attendance') }}" class="row g-3 align-items-end">
                <div class="col-md-3">
                    <label class="form-label fw-semibold">الشهر</label>
                    <select name="month" class="form-select">
                        @foreach(['يناير','فبراير','مارس','أبريل','مايو','يونيو','يوليو','أغسطس','سبتمبر','أكتوبر','نوفمبر','ديسمبر'] as $i => $m)
                            <option value="{{ $i+1 }}" {{ $month == $i+1 ? 'selected' : '' }}>{{ $m }}</option>
                        @endforeach
                    </select>
                </div>
                <div class="col-md-3">
                    <label class="form-label fw-semibold">السنة</label>
                    <input type="number" name="year" class="form-control" value="{{ $year }}" min="2020" max="2035">
                </div>
                <div class="col-md-2">
                    <button type="submit" class="btn btn-primary w-100">
                        <i class="fas fa-filter me-1"></i> تصفية
                    </button>
                </div>
            </form>
        </div>
    </div>

    {{-- Stats --}}
    <div class="row g-3 mb-4">
        <div class="col-md-3">
            <div class="card text-center border-success">
                <div class="card-body">
                    <div class="fw-bold fs-2 text-success">{{ $stats['present'] }}</div>
                    <div class="text-muted">حضور</div>
                </div>
            </div>
        </div>
        <div class="col-md-3">
            <div class="card text-center border-danger">
                <div class="card-body">
                    <div class="fw-bold fs-2 text-danger">{{ $stats['absent'] }}</div>
                    <div class="text-muted">غياب</div>
                </div>
            </div>
        </div>
        <div class="col-md-3">
            <div class="card text-center border-warning">
                <div class="card-body">
                    <div class="fw-bold fs-2 text-warning">{{ $stats['late'] }}</div>
                    <div class="text-muted">تأخير</div>
                </div>
            </div>
        </div>
        <div class="col-md-3">
            <div class="card text-center border-info">
                <div class="card-body">
                    <div class="fw-bold fs-2 text-info">{{ $stats['leave'] }}</div>
                    <div class="text-muted">إجازة</div>
                </div>
            </div>
        </div>
    </div>

    <div class="card">
        <div class="card-header">
            <i class="fas fa-calendar-alt me-2"></i> السجل التفصيلي
        </div>
        <div class="card-body p-0">
            <table class="table table-hover mb-0">
                <thead>
                    <tr>
                        <th class="px-4">التاريخ</th>
                        <th>وقت الدخول</th>
                        <th>وقت الخروج</th>
                        <th>ساعات العمل</th>
                        <th>الحالة</th>
                        <th>ملاحظات</th>
                    </tr>
                </thead>
                <tbody>
                    @forelse($records as $r)
                    <tr>
                        <td class="px-4 fw-semibold">
                            {{ \Carbon\Carbon::parse($r->date)->format('Y-m-d') }}
                            <small class="text-muted d-block">{{ \Carbon\Carbon::parse($r->date)->locale('ar')->dayName }}</small>
                        </td>
                        <td>{{ $r->check_in ?? '—' }}</td>
                        <td>{{ $r->check_out ?? '—' }}</td>
                        <td>{{ $r->work_hours ?? '—' }}</td>
                        <td>
                            @switch($r->status)
                                @case('present') <span class="badge bg-success">حضور</span> @break
                                @case('absent')  <span class="badge bg-danger">غياب</span> @break
                                @case('late')    <span class="badge bg-warning">تأخير</span> @break
                                @case('leave')   <span class="badge bg-info">إجازة</span> @break
                                @default         <span class="badge bg-secondary">{{ $r->status }}</span>
                            @endswitch
                        </td>
                        <td class="text-muted">{{ $r->notes ?? '—' }}</td>
                    </tr>
                    @empty
                    <tr>
                        <td colspan="6" class="text-center text-muted py-4">لا توجد سجلات لهذا الشهر</td>
                    </tr>
                    @endforelse
                </tbody>
            </table>
        </div>
    </div>
</x-app-layout>
