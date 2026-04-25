<x-app-layout>
    <x-slot name="title">تعديل سجل الحضور</x-slot>

    <div class="card">
        <div class="card-header">
            <i class="fas fa-edit me-2"></i> تعديل سجل الحضور - {{ $attendance->employee->name ?? '—' }}
        </div>
        <div class="card-body">
            <form action="{{ route('attendance.update', $attendance->id) }}" method="POST">
                @csrf
                @method('PUT')
                <div class="row g-3">

                    {{-- الموظف --}}
                    <div class="col-md-6">
                        <label class="form-label fw-semibold">الموظف <span class="text-danger">*</span></label>
                        <select name="employee_id" class="form-select @error('employee_id') is-invalid @enderror" required>
                            <option value="">-- اختر الموظف --</option>
                            @foreach($employees as $emp)
                                <option value="{{ $emp->id }}" {{ old('employee_id', $attendance->employee_id) == $emp->id ? 'selected' : '' }}>
                                    {{ $emp->name }}
                                </option>
                            @endforeach
                        </select>
                        @error('employee_id')
                            <div class="invalid-feedback">{{ $message }}</div>
                        @enderror
                    </div>

                    {{-- التاريخ --}}
                    <div class="col-md-6">
                        <label class="form-label fw-semibold">التاريخ <span class="text-danger">*</span></label>
                        <input type="date" name="date" class="form-control @error('date') is-invalid @enderror"
                               value="{{ old('date', $attendance->date->format('Y-m-d')) }}" required>
                        @error('date')
                            <div class="invalid-feedback">{{ $message }}</div>
                        @enderror
                    </div>

                    {{-- وقت الدخول --}}
                    <div class="col-md-6">
                        <label class="form-label fw-semibold">وقت الدخول</label>
                        <input type="time" name="check_in" class="form-control @error('check_in') is-invalid @enderror"
                               value="{{ old('check_in', $attendance->check_in ? \Carbon\Carbon::parse($attendance->check_in)->format('H:i') : '') }}">
                        @error('check_in')
                            <div class="invalid-feedback">{{ $message }}</div>
                        @enderror
                    </div>

                    {{-- وقت الخروج --}}
                    <div class="col-md-6">
                        <label class="form-label fw-semibold">وقت الخروج</label>
                        <input type="time" name="check_out" class="form-control @error('check_out') is-invalid @enderror"
                               value="{{ old('check_out', $attendance->check_out ? \Carbon\Carbon::parse($attendance->check_out)->format('H:i') : '') }}">
                        @error('check_out')
                            <div class="invalid-feedback">{{ $message }}</div>
                        @enderror
                    </div>

                    {{-- الحالة --}}
                    <div class="col-md-6">
                        <label class="form-label fw-semibold">الحالة <span class="text-danger">*</span></label>
                        <select name="status" class="form-select @error('status') is-invalid @enderror" required id="statusSelect">
                            <option value="present" {{ old('status', $attendance->status) == 'present' ? 'selected' : '' }}>حاضر</option>
                            <option value="absent"  {{ old('status', $attendance->status) == 'absent'  ? 'selected' : '' }}>غائب</option>
                            <option value="late"    {{ old('status', $attendance->status) == 'late'    ? 'selected' : '' }}>متأخر</option>
                            <option value="leave"   {{ old('status', $attendance->status) == 'leave'   ? 'selected' : '' }}>إجازة</option>
                            <option value="holiday" {{ old('status', $attendance->status) == 'holiday' ? 'selected' : '' }}>عطلة</option>
                        </select>
                        @error('status')
                            <div class="invalid-feedback">{{ $message }}</div>
                        @enderror
                    </div>

                    {{-- سبب الإجازة --}}
                    <div class="col-md-6" id="leaveSection" style="display:none;">
                        <label class="form-label fw-semibold">سبب الإجازة</label>
                        <input type="text" name="leave_reason" class="form-control"
                               value="{{ old('leave_reason', $attendance->leave_reason) }}"
                               placeholder="اكتب سبب الإجازة...">
                        <div class="form-check mt-2">
                            <input type="checkbox" name="leave_approved" class="form-check-input" id="leaveApproved"
                                   {{ old('leave_approved', $attendance->leave_approved) ? 'checked' : '' }}>
                            <label class="form-check-label" for="leaveApproved">الإجازة معتمدة</label>
                        </div>
                    </div>

                    {{-- معلومات إضافية (للعرض فقط) --}}
                    <div class="col-12">
                        <div class="alert alert-light border mt-2 mb-0">
                            <div class="row text-center">
                                <div class="col-md-3">
                                    <div class="text-muted small">ساعات العمل</div>
                                    <div class="fw-bold">{{ $attendance->work_hours ?? '0' }} ساعة</div>
                                </div>
                                <div class="col-md-3">
                                    <div class="text-muted small">الأوفرتايم</div>
                                    <div class="fw-bold text-warning">{{ $attendance->overtime_hours ?? '0' }} ساعة</div>
                                </div>
                                <div class="col-md-3">
                                    <div class="text-muted small">المصدر</div>
                                    <div class="fw-bold">
                                        @if($attendance->is_manual)
                                            <span class="badge bg-secondary">يدوي</span>
                                        @else
                                            <span class="badge bg-primary">بصمة</span>
                                        @endif
                                    </div>
                                </div>
                                <div class="col-md-3">
                                    <div class="text-muted small">آخر تعديل</div>
                                    <div class="fw-bold">{{ $attendance->updated_at->format('Y-m-d H:i') }}</div>
                                </div>
                            </div>
                        </div>
                    </div>

                </div>

                {{-- أزرار --}}
                <div class="d-flex gap-2 mt-4">
                    <button type="submit" class="btn btn-primary">
                        <i class="fas fa-save me-1"></i> حفظ التعديلات
                    </button>
                    <a href="{{ route('attendance.index') }}" class="btn btn-secondary">
                        <i class="fas fa-arrow-right me-1"></i> رجوع
                    </a>
                </div>
            </form>
        </div>
    </div>

    <script>
        const statusSelect = document.getElementById('statusSelect');
        const leaveSection = document.getElementById('leaveSection');

        function toggleLeave() {
            leaveSection.style.display = statusSelect.value === 'leave' ? 'block' : 'none';
        }

        statusSelect.addEventListener('change', toggleLeave);
        toggleLeave();
    </script>
</x-app-layout>