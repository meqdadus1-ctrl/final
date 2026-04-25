<x-app-layout>
    <x-slot name="title">تفاصيل سجل الحضور</x-slot>

    <div class="card">
        <div class="card-header d-flex justify-content-between align-items-center">
            <span><i class="fas fa-clock me-2"></i> سجل حضور - {{ \Carbon\Carbon::parse($attendance->date)->format('Y-m-d') }}</span>
            <div class="d-flex gap-2">
                <a href="{{ route('attendance.edit', $attendance->id) }}" class="btn btn-sm btn-outline-primary">
                    <i class="fas fa-edit me-1"></i> تعديل
                </a>
                <a href="{{ route('attendance.index') }}" class="btn btn-sm btn-secondary">
                    <i class="fas fa-arrow-right me-1"></i> رجوع
                </a>
            </div>
        </div>
        <div class="card-body">
            <div class="row g-3">
                <div class="col-md-4">
                    <div class="text-muted small">الموظف</div>
                    <div class="fw-semibold">
                        <a href="{{ route('employees.show', $attendance->employee_id) }}">
                            {{ $attendance->employee->name ?? '—' }}
                        </a>
                    </div>
                </div>
                <div class="col-md-4">
                    <div class="text-muted small">القسم</div>
                    <div class="fw-semibold">{{ $attendance->employee->department->name ?? '—' }}</div>
                </div>
                <div class="col-md-4">
                    <div class="text-muted small">التاريخ</div>
                    <div class="fw-semibold">
                        {{ \Carbon\Carbon::parse($attendance->date)->format('Y-m-d') }}
                        <small class="text-muted">({{ \Carbon\Carbon::parse($attendance->date)->locale('ar')->dayName }})</small>
                    </div>
                </div>

                <div class="col-md-3">
                    <div class="text-muted small">وقت الدخول</div>
                    <div class="fw-semibold">{{ $attendance->check_in ?? '—' }}</div>
                </div>
                <div class="col-md-3">
                    <div class="text-muted small">وقت الخروج</div>
                    <div class="fw-semibold">{{ $attendance->check_out ?? '—' }}</div>
                </div>
                <div class="col-md-3">
                    <div class="text-muted small">ساعات العمل</div>
                    <div class="fw-semibold">{{ $attendance->work_hours ?? 0 }}</div>
                </div>
                <div class="col-md-3">
                    <div class="text-muted small">الساعات الإضافية</div>
                    <div class="fw-semibold">{{ $attendance->overtime_hours ?? 0 }}</div>
                </div>

                <div class="col-md-3">
                    <div class="text-muted small">الحالة</div>
                    <div>
                        @switch($attendance->status)
                            @case('present') <span class="badge bg-success">حضور</span> @break
                            @case('absent')  <span class="badge bg-danger">غياب</span> @break
                            @case('late')    <span class="badge bg-warning">تأخير</span> @break
                            @case('leave')   <span class="badge bg-info">إجازة</span> @break
                            @case('holiday') <span class="badge bg-secondary">عطلة</span> @break
                            @default         <span class="badge bg-secondary">{{ $attendance->status }}</span>
                        @endswitch
                    </div>
                </div>
                <div class="col-md-3">
                    <div class="text-muted small">يدوي؟</div>
                    <div>
                        @if($attendance->is_manual)
                            <span class="badge bg-warning">يدوي</span>
                        @else
                            <span class="badge bg-primary">من الجهاز</span>
                        @endif
                    </div>
                </div>
                <div class="col-md-3">
                    <div class="text-muted small">تمت الموافقة على الإجازة</div>
                    <div>
                        @if($attendance->leave_approved)
                            <span class="badge bg-success">نعم</span>
                        @else
                            <span class="badge bg-secondary">لا</span>
                        @endif
                    </div>
                </div>
                <div class="col-md-3">
                    <div class="text-muted small">آخر تعديل بواسطة</div>
                    <div class="fw-semibold">{{ $attendance->updatedBy->name ?? '—' }}</div>
                </div>

                <div class="col-12">
                    <div class="text-muted small">سبب الإجازة / ملاحظات</div>
                    <div>{{ $attendance->leave_reason ?? '—' }}</div>
                </div>
            </div>
        </div>
    </div>
</x-app-layout>
