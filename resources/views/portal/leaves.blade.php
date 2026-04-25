<x-app-layout>
    <x-slot name="title">إجازاتي</x-slot>

    @if(session('error'))
        <div class="alert alert-danger alert-dismissible fade show" role="alert">
            {{ session('error') }}
            <button type="button" class="btn-close" data-bs-dismiss="alert"></button>
        </div>
    @endif

    <div class="row g-4">
        <div class="col-md-5">
            <div class="card">
                <div class="card-header">
                    <i class="fas fa-plus me-2"></i> تقديم طلب إجازة
                </div>
                <div class="card-body">
                    <form action="{{ route('portal.leaves.store') }}" method="POST">
                        @csrf
                        <div class="mb-3">
                            <label class="form-label fw-semibold">نوع الإجازة <span class="text-danger">*</span></label>
                            <select name="leave_type_id" class="form-select @error('leave_type_id') is-invalid @enderror" required>
                                <option value="">— اختر —</option>
                                @foreach($leaveTypes as $t)
                                    <option value="{{ $t->id }}" {{ old('leave_type_id') == $t->id ? 'selected' : '' }}>
                                        {{ $t->name }}
                                    </option>
                                @endforeach
                            </select>
                            @error('leave_type_id') <div class="invalid-feedback">{{ $message }}</div> @enderror
                        </div>
                        <div class="mb-3">
                            <label class="form-label fw-semibold">من تاريخ <span class="text-danger">*</span></label>
                            <input type="date" name="start_date" class="form-control @error('start_date') is-invalid @enderror"
                                   value="{{ old('start_date') }}" required>
                            @error('start_date') <div class="invalid-feedback">{{ $message }}</div> @enderror
                        </div>
                        <div class="mb-3">
                            <label class="form-label fw-semibold">إلى تاريخ <span class="text-danger">*</span></label>
                            <input type="date" name="end_date" class="form-control @error('end_date') is-invalid @enderror"
                                   value="{{ old('end_date') }}" required>
                            @error('end_date') <div class="invalid-feedback">{{ $message }}</div> @enderror
                        </div>
                        <div class="mb-3">
                            <label class="form-label fw-semibold">السبب</label>
                            <textarea name="reason" class="form-control @error('reason') is-invalid @enderror"
                                      rows="3">{{ old('reason') }}</textarea>
                            @error('reason') <div class="invalid-feedback">{{ $message }}</div> @enderror
                        </div>
                        <button type="submit" class="btn btn-primary w-100">
                            <i class="fas fa-paper-plane me-1"></i> تقديم الطلب
                        </button>
                    </form>
                </div>
            </div>
        </div>

        <div class="col-md-7">
            <div class="card">
                <div class="card-header">
                    <i class="fas fa-history me-2"></i> سجل إجازاتي ({{ $leaves->total() }})
                </div>
                <div class="card-body p-0">
                    <table class="table table-hover mb-0">
                        <thead>
                            <tr>
                                <th class="px-4">النوع</th>
                                <th>من</th>
                                <th>إلى</th>
                                <th>الأيام</th>
                                <th>الحالة</th>
                            </tr>
                        </thead>
                        <tbody>
                            @forelse($leaves as $leave)
                            <tr>
                                <td class="px-4 fw-semibold">{{ $leave->leaveType->name ?? '—' }}</td>
                                <td>{{ $leave->start_date->format('Y-m-d') }}</td>
                                <td>{{ $leave->end_date->format('Y-m-d') }}</td>
                                <td><span class="badge bg-info">{{ $leave->total_days }}</span></td>
                                <td>
                                    @if($leave->status === 'approved')
                                        <span class="badge bg-success">موافق</span>
                                    @elseif($leave->status === 'rejected')
                                        <span class="badge bg-danger">مرفوض</span>
                                    @else
                                        <span class="badge bg-warning">قيد المراجعة</span>
                                    @endif
                                </td>
                            </tr>
                            @empty
                            <tr>
                                <td colspan="5" class="text-center text-muted py-4">لم تقدم أي طلب إجازة بعد</td>
                            </tr>
                            @endforelse
                        </tbody>
                    </table>
                </div>
                @if($leaves->hasPages())
                <div class="card-footer">{{ $leaves->links() }}</div>
                @endif
            </div>
        </div>
    </div>
</x-app-layout>
