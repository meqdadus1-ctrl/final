<x-app-layout>
    <x-slot name="title">تعديل البنك</x-slot>

    <div class="card">
        <div class="card-header">
            <i class="fas fa-edit me-2"></i> تعديل البنك - {{ $bank->name }}
        </div>
        <div class="card-body">
            <form action="{{ route('banks.update', $bank->id) }}" method="POST">
                @csrf
                @method('PUT')
                <div class="row g-3">

                    <div class="col-md-6">
                        <label class="form-label fw-semibold">الاسم <span class="text-danger">*</span></label>
                        <input type="text" name="name" class="form-control @error('name') is-invalid @enderror"
                               value="{{ old('name', $bank->name) }}" required>
                        @error('name') <div class="invalid-feedback">{{ $message }}</div> @enderror
                    </div>

                    <div class="col-md-6">
                        <label class="form-label fw-semibold">اسم البنك الرسمي</label>
                        <input type="text" name="bank_name" class="form-control @error('bank_name') is-invalid @enderror"
                               value="{{ old('bank_name', $bank->bank_name) }}">
                        @error('bank_name') <div class="invalid-feedback">{{ $message }}</div> @enderror
                    </div>

                    <div class="col-md-6">
                        <label class="form-label fw-semibold">الفرع</label>
                        <input type="text" name="branch" class="form-control @error('branch') is-invalid @enderror"
                               value="{{ old('branch', $bank->branch) }}">
                        @error('branch') <div class="invalid-feedback">{{ $message }}</div> @enderror
                    </div>

                    <div class="col-md-6">
                        <label class="form-label fw-semibold">رمز السويفت</label>
                        <input type="text" name="swift_code" class="form-control @error('swift_code') is-invalid @enderror"
                               value="{{ old('swift_code', $bank->swift_code) }}">
                        @error('swift_code') <div class="invalid-feedback">{{ $message }}</div> @enderror
                    </div>

                    <div class="col-12">
                        <label class="form-label fw-semibold">ملاحظات</label>
                        <textarea name="notes" class="form-control @error('notes') is-invalid @enderror"
                                  rows="3">{{ old('notes', $bank->notes) }}</textarea>
                        @error('notes') <div class="invalid-feedback">{{ $message }}</div> @enderror
                    </div>

                </div>

                <div class="d-flex gap-2 mt-4">
                    <button type="submit" class="btn btn-primary">
                        <i class="fas fa-save me-1"></i> حفظ التعديلات
                    </button>
                    <a href="{{ route('banks.index') }}" class="btn btn-secondary">
                        <i class="fas fa-arrow-right me-1"></i> رجوع
                    </a>
                </div>
            </form>
        </div>
    </div>
</x-app-layout>
