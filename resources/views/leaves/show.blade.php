<x-app-layout>
    <x-slot name="title">تفاصيل طلب الإجازة</x-slot>

    <div class="card" style="max-width: 800px;">
        <div class="card-header bg-primary text-white">
            <i class="fas fa-file-alt me-2"></i> تفاصيل طلب الإجازة
        </div>
        <div class="card-body">
            <!-- معلومات المتقدم -->
            <div class="row mb-4">
                <div class="col-md-6">
                    <h6 class="fw-semibold text-muted">الموظف</h6>
                    <p class="mb-0">{{ $leave->employee->name }}</p>
                </div>
                <div class="col-md-6">
                    <h6 class="fw-semibold text-muted">القسم</h6>
                    <p class="mb-0">{{ $leave->employee->department?->name ?? 'غير محدد' }}</p>
                </div>
            </div>

            <hr>

            <!-- معلومات الإجازة -->
            <div class="row mb-4">
                <div class="col-md-6">
                    <h6 class="fw-semibold text-muted">نوع الإجازة</h6>
                    <p class="mb-0">
                        {{ $leave->leaveType->name }}
                        @if($leave->leaveType->is_paid)
                            <span class="badge bg-success">مدفوعة</span>
                        @else
                            <span class="badge bg-warning">غير مدفوعة</span>
                        @endif
                    </p>
                </div>
                <div class="col-md-6">
                    <h6 class="fw-semibold text-muted">عدد الأيام</h6>
                    <p class="mb-0">{{ $leave->total_days }} يوم</p>
                </div>
            </div>

            <hr>

            <!-- التواريخ -->
            <div class="row mb-4">
                <div class="col-md-6">
                    <h6 class="fw-semibold text-muted">من تاريخ</h6>
                    <p class="mb-0">{{ $leave->start_date->format('Y-m-d (l)') }}</p>
                </div>
                <div class="col-md-6">
                    <h6 class="fw-semibold text-muted">إلى تاريخ</h6>
                    <p class="mb-0">{{ $leave->end_date->format('Y-m-d (l)') }}</p>
                </div>
            </div>

            @if($leave->reason)
                <hr>
                <div class="mb-4">
                    <h6 class="fw-semibold text-muted">السبب</h6>
                    <p class="mb-0">{{ $leave->reason }}</p>
                </div>
            @endif

            <hr>

            <!-- الحالة -->
            <div class="row mb-4">
                <div class="col-md-6">
                    <h6 class="fw-semibold text-muted">الحالة</h6>
                    <p class="mb-0">
                        @if($leave->status === 'pending')
                            <span class="badge bg-warning">قيد الانتظار</span>
                        @elseif($leave->status === 'approved')
                            <span class="badge bg-success">موافق عليه</span>
                        @elseif($leave->status === 'rejected')
                            <span class="badge bg-danger">مرفوض</span>
                        @endif
                    </p>
                </div>
                <div class="col-md-6">
                    <h6 class="fw-semibold text-muted">تاريخ التقديم</h6>
                    <p class="mb-0">{{ $leave->created_at->format('Y-m-d H:i') }}</p>
                </div>
            </div>

            @if($leave->reviewed_at)
                <hr>
                <div class="row mb-4">
                    <div class="col-md-6">
                        <h6 class="fw-semibold text-muted">راجع من قبل</h6>
                        <p class="mb-0">{{ $leave->reviewedBy?->name ?? 'غير محدد' }}</p>
                    </div>
                    <div class="col-md-6">
                        <h6 class="fw-semibold text-muted">تاريخ المراجعة</h6>
                        <p class="mb-0">{{ $leave->reviewed_at->format('Y-m-d H:i') }}</p>
                    </div>
                </div>
            @endif

            @if($leave->notes)
                <hr>
                <div class="mb-4">
                    <h6 class="fw-semibold text-muted">ملاحظات</h6>
                    <p class="mb-0">{{ $leave->notes }}</p>
                </div>
            @endif

            <!-- الأزرار -->
            <hr>
            <div class="d-flex gap-2">
                <a href="{{ route('leaves.index') }}" class="btn btn-outline-secondary">
                    <i class="fas fa-arrow-right me-1"></i> رجوع
                </a>

                @if($leave->status === 'pending' && auth()->user()->can('approve-leave'))
                    <form action="{{ route('leaves.approve', $leave) }}" method="POST" class="d-inline">
                        @csrf
                        <button type="submit" class="btn btn-success">
                            <i class="fas fa-check me-1"></i> الموافقة
                        </button>
                    </form>

                    <button type="button" class="btn btn-danger" data-bs-toggle="modal" data-bs-target="#rejectModal">
                        <i class="fas fa-times me-1"></i> الرفض
                    </button>
                @endif
            </div>
        </div>
    </div>

    <!-- Modal for rejection notes -->
    @if($leave->status === 'pending' && auth()->user()->can('approve-leave'))
        <div class="modal fade" id="rejectModal" tabindex="-1">
            <div class="modal-dialog">
                <div class="modal-content">
                    <form action="{{ route('leaves.reject', $leave) }}" method="POST">
                        @csrf
                        <div class="modal-header">
                            <h5 class="modal-title">رفض طلب الإجازة</h5>
                            <button type="button" class="btn-close" data-bs-dismiss="modal"></button>
                        </div>
                        <div class="modal-body">
                            <div class="mb-3">
                                <label class="form-label">ملاحظات الرفض</label>
                                <textarea name="notes" class="form-control" rows="3" required></textarea>
                            </div>
                        </div>
                        <div class="modal-footer">
                            <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">إلغاء</button>
                            <button type="submit" class="btn btn-danger">تأكيد الرفض</button>
                        </div>
                    </form>
                </div>
            </div>
        </div>
    @endif
</x-app-layout>
