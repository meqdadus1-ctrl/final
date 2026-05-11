<x-portal-layout>
<x-slot name="title">إجازاتي</x-slot>

<div class="row g-4">

    {{-- Request Form --}}
    <div class="col-lg-4">
        <div class="card">
            <div class="card-header">
                <i class="fas fa-plus text-success me-2"></i> تقديم طلب إجازة
            </div>
            <div class="card-body">
                <form action="{{ route('portal.leaves.store') }}" method="POST">
                    @csrf
                    <div class="mb-3">
                        <label class="form-label fw-semibold small">نوع الإجازة <span class="text-danger">*</span></label>
                        <select name="leave_type_id"
                                class="form-select @error('leave_type_id') is-invalid @enderror" required>
                            <option value="">— اختر النوع —</option>
                            @foreach($leaveTypes as $t)
                                <option value="{{ $t->id }}" {{ old('leave_type_id') == $t->id ? 'selected' : '' }}>
                                    {{ $t->name }}
                                </option>
                            @endforeach
                        </select>
                        @error('leave_type_id') <div class="invalid-feedback">{{ $message }}</div> @enderror
                    </div>

                    <div class="mb-3">
                        <label class="form-label fw-semibold small">من تاريخ <span class="text-danger">*</span></label>
                        <input type="date" name="start_date"
                               class="form-control @error('start_date') is-invalid @enderror"
                               value="{{ old('start_date') }}" required
                               min="{{ now()->toDateString() }}">
                        @error('start_date') <div class="invalid-feedback">{{ $message }}</div> @enderror
                    </div>

                    <div class="mb-3">
                        <label class="form-label fw-semibold small">إلى تاريخ <span class="text-danger">*</span></label>
                        <input type="date" name="end_date"
                               class="form-control @error('end_date') is-invalid @enderror"
                               value="{{ old('end_date') }}" required>
                        @error('end_date') <div class="invalid-feedback">{{ $message }}</div> @enderror
                    </div>

                    <div class="mb-4">
                        <label class="form-label fw-semibold small">السبب</label>
                        <textarea name="reason" rows="3"
                                  class="form-control @error('reason') is-invalid @enderror"
                                  placeholder="اختياري...">{{ old('reason') }}</textarea>
                        @error('reason') <div class="invalid-feedback">{{ $message }}</div> @enderror
                    </div>

                    <button type="submit" class="btn btn-primary w-100">
                        <i class="fas fa-paper-plane me-1"></i> تقديم الطلب
                    </button>
                </form>
            </div>
        </div>
    </div>

    {{-- Leave History --}}
    <div class="col-lg-8">
        <div class="card">
            <div class="card-header">
                <i class="fas fa-history text-primary me-2"></i>
                سجل إجازاتي ({{ $leaves->total() }})
            </div>
            <div class="card-body p-0">
                <div class="table-responsive">
                    <table class="table table-hover align-middle mb-0">
                        <thead class="table-dark">
                            <tr>
                                <th class="px-3">النوع</th>
                                <th class="text-center">من</th>
                                <th class="text-center">إلى</th>
                                <th class="text-center">الأيام</th>
                                <th class="text-center">الحالة</th>
                                <th>السبب</th>
                            </tr>
                        </thead>
                        <tbody>
                            @forelse($leaves as $leave)
                            <tr>
                                <td class="px-3 fw-semibold">{{ $leave->leaveType->name ?? '—' }}</td>
                                <td class="text-center small">{{ $leave->start_date->format('Y/m/d') }}</td>
                                <td class="text-center small">{{ $leave->end_date->format('Y/m/d') }}</td>
                                <td class="text-center">
                                    <span class="badge bg-info text-dark">{{ $leave->total_days }} أيام</span>
                                </td>
                                <td class="text-center">
                                    @if($leave->status === 'approved')
                                        <span class="badge bg-success">✓ موافق</span>
                                    @elseif($leave->status === 'rejected')
                                        <span class="badge bg-danger">✗ مرفوض</span>
                                    @else
                                        <span class="badge bg-warning text-dark">⏳ قيد المراجعة</span>
                                    @endif
                                </td>
                                <td class="text-muted small">{{ $leave->reason ?? '—' }}</td>
                            </tr>
                            @empty
                            <tr>
                                <td colspan="6" class="text-center text-muted py-5">
                                    <i class="fas fa-umbrella-beach fa-2x mb-2 d-block opacity-25"></i>
                                    لم تقدم أي طلب إجازة بعد
                                </td>
                            </tr>
                            @endforelse
                        </tbody>
                    </table>
                </div>
            </div>
            @if($leaves->hasPages())
            <div class="card-footer">{{ $leaves->links() }}</div>
            @endif
        </div>
    </div>

</div>
</x-portal-layout>
