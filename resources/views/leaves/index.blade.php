<x-app-layout>
    <x-slot name="title">طلبات الإجازات</x-slot>

    <div class="card">
        <div class="card-header d-flex justify-content-between align-items-center">
            <span><i class="fas fa-umbrella-beach me-2"></i> طلبات الإجازات</span>
            <div class="d-flex gap-2">
                <a href="{{ route('leaves.types') }}" class="btn btn-outline-secondary btn-sm">
                    <i class="fas fa-list me-1"></i> أنواع الإجازات
                </a>
                <a href="{{ route('leaves.balances') }}" class="btn btn-outline-info btn-sm">
                    <i class="fas fa-chart-bar me-1"></i> الأرصدة
                </a>
                <a href="{{ route('leaves.create') }}" class="btn btn-primary btn-sm">
                    <i class="fas fa-plus me-1"></i> طلب إجازة
                </a>
            </div>
        </div>
        <div class="card-body p-0">
            <table class="table table-hover mb-0">
                <thead>
                    <tr>
                        <th class="px-4">الموظف</th>
                        <th>نوع الإجازة</th>
                        <th>من</th>
                        <th>إلى</th>
                        <th>الأيام</th>
                        <th>الحالة</th>
                        <th>الإجراءات</th>
                    </tr>
                </thead>
                <tbody>
                    @forelse($requests as $req)
                    <tr>
                        <td class="px-4 fw-semibold">{{ $req->employee?->name ?? '—' }}</td>
                        <td>{{ $req->leaveType?->name ?? '—' }}</td>
                        <td>{{ $req->start_date->format('d/m/Y') }}</td>
                        <td>{{ $req->end_date->format('d/m/Y') }}</td>
                        <td><span class="badge bg-info">{{ $req->total_days }} يوم</span></td>
                        <td>
                            @if($req->status == 'pending')
                                <span class="badge bg-warning text-dark">قيد الانتظار</span>
                            @elseif($req->status == 'approved')
                                <span class="badge bg-success">موافق عليها</span>
                            @elseif($req->status == 'rejected')
                                <span class="badge bg-danger">مرفوضة</span>
                            @else
                                <span class="badge bg-secondary">ملغاة</span>
                            @endif
                        </td>
                        <td>
                            <a href="{{ route('leaves.show', $req) }}" class="btn btn-sm btn-outline-primary">
                                <i class="fas fa-eye"></i>
                            </a>
                            @if($req->status == 'pending')
                            <form action="{{ route('leaves.approve', $req) }}" method="POST" class="d-inline">
                                @csrf
                                <button class="btn btn-sm btn-success" onclick="return confirm('الموافقة على الإجازة؟')">
                                    <i class="fas fa-check"></i>
                                </button>
                            </form>
                            <button class="btn btn-sm btn-danger" onclick="rejectModal({{ $req->id }})">
                                <i class="fas fa-times"></i>
                            </button>
                            <form action="{{ route('leaves.destroy', $req) }}" method="POST" class="d-inline"
                                onsubmit="return confirm('هل أنت متأكد من حذف هذا الطلب؟')">
                                @csrf
                                @method('DELETE')
                                <button class="btn btn-sm btn-outline-danger">
                                    <i class="fas fa-trash"></i>
                                </button>
                            </form>
                            @endif
                        </td>
                    </tr>
                    @empty
                    <tr><td colspan="7" class="text-center text-muted py-4">لا توجد طلبات إجازة</td></tr>
                    @endforelse
                </tbody>
            </table>
        </div>
        @if($requests->hasPages())
        <div class="card-footer">{{ $requests->links() }}</div>
        @endif
    </div>

    <!-- Modal الرفض -->
    <div class="modal fade" id="rejectModal" tabindex="-1">
        <div class="modal-dialog">
            <div class="modal-content">
                <div class="modal-header">
                    <h5 class="modal-title">رفض الإجازة</h5>
                </div>
                <form id="rejectForm" method="POST">
                    @csrf
                    <div class="modal-body">
                        <label class="form-label">سبب الرفض</label>
                        <textarea name="notes" class="form-control" rows="3"></textarea>
                    </div>
                    <div class="modal-footer">
                        <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">إلغاء</button>
                        <button type="submit" class="btn btn-danger">تأكيد الرفض</button>
                    </div>
                </form>
            </div>
        </div>
    </div>

    <script>
        function rejectModal(id) {
            document.getElementById('rejectForm').action = '/leaves/' + id + '/reject';
            new bootstrap.Modal(document.getElementById('rejectModal')).show();
        }
    </script>
</x-app-layout>