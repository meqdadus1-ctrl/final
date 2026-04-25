<x-app-layout>
    <x-slot name="title">طلب إجازة جديدة</x-slot>

    <div class="card" style="max-width: 700px;">
        <div class="card-header">
            <i class="fas fa-umbrella-beach me-2"></i> طلب إجازة جديدة
        </div>
        <div class="card-body">
            @if(count($leaveTypes) == 0)
            <div class="alert alert-warning">
                لا توجد أنواع إجازات. <a href="{{ route('leaves.types') }}">أضف نوع إجازة أولاً</a>
            </div>
            @else
            <form action="{{ route('leaves.store') }}" method="POST">
                @csrf
                <div class="mb-3">
                    <label class="form-label fw-semibold">الموظف</label>
                    <select name="employee_id" class="form-select @error('employee_id') is-invalid @enderror" required>
                        <option value="">-- اختر موظف --</option>
                        @foreach($employees as $emp)
                            <option value="{{ $emp->id }}">{{ $emp->name }}</option>
                        @endforeach
                    </select>
                    @error('employee_id')<div class="invalid-feedback">{{ $message }}</div>@enderror
                </div>

                <div class="mb-3">
                    <label class="form-label fw-semibold">نوع الإجازة</label>
                    <select name="leave_type_id" class="form-select @error('leave_type_id') is-invalid @enderror" required>
                        <option value="">-- اختر النوع --</option>
                        @foreach($leaveTypes as $type)
                            <option value="{{ $type->id }}">
                                {{ $type->name }}
                                {{ $type->max_days_yearly ? '(الحد: '.$type->max_days_yearly.' يوم)' : '' }}
                                — {{ $type->is_paid ? 'مدفوعة' : 'غير مدفوعة' }}
                            </option>
                        @endforeach
                    </select>
                    @error('leave_type_id')<div class="invalid-feedback">{{ $message }}</div>@enderror
                </div>

                <div class="row g-3">
                    <div class="col-md-6">
                        <label class="form-label fw-semibold">من تاريخ</label>
                        <input type="date" name="start_date"
                            class="form-control @error('start_date') is-invalid @enderror"
                            value="{{ old('start_date', date('Y-m-d')) }}" required>
                        @error('start_date')<div class="invalid-feedback">{{ $message }}</div>@enderror
                    </div>
                    <div class="col-md-6">
                        <label class="form-label fw-semibold">إلى تاريخ</label>
                        <input type="date" name="end_date"
                            class="form-control @error('end_date') is-invalid @enderror"
                            value="{{ old('end_date', date('Y-m-d')) }}" required>
                        @error('end_date')<div class="invalid-feedback">{{ $message }}</div>@enderror
                    </div>
                </div>

                <div class="mb-3 mt-3">
                    <label class="form-label fw-semibold">السبب</label>
                    <textarea name="reason" class="form-control" rows="3"
                        placeholder="اختياري">{{ old('reason') }}</textarea>
                </div>

                <div class="d-flex gap-2">
                    <button type="submit" class="btn btn-primary">
                        <i class="fas fa-paper-plane me-1"></i> تقديم الطلب
                    </button>
                    <a href="{{ route('leaves.index') }}" class="btn btn-outline-secondary">إلغاء</a>
                </div>
            </form>
            @endif
        </div>
    </div>
</x-app-layout>