<x-app-layout>
    <x-slot name="title">إضافة قسم جديد</x-slot>

    <div class="card">
        <div class="card-header">
            <i class="fas fa-plus me-2"></i> إضافة قسم جديد
        </div>
        <div class="card-body">
            <form action="{{ route('departments.store') }}" method="POST">
                @csrf
                <div class="row g-3">

                    <div class="col-md-6">
                        <label class="form-label fw-semibold">اسم القسم <span class="text-danger">*</span></label>
                        <input type="text" name="name" class="form-control @error('name') is-invalid @enderror"
                               value="{{ old('name') }}" required>
                        @error('name') <div class="invalid-feedback">{{ $message }}</div> @enderror
                    </div>

                    <div class="col-12">
                        <label class="form-label fw-semibold">الوصف</label>
                        <textarea name="description" class="form-control @error('description') is-invalid @enderror"
                                  rows="3">{{ old('description') }}</textarea>
                        @error('description') <div class="invalid-feedback">{{ $message }}</div> @enderror
                    </div>

                </div>

                <div class="d-flex gap-2 mt-4">
                    <button type="submit" class="btn btn-primary">
                        <i class="fas fa-save me-1"></i> حفظ
                    </button>
                    <a href="{{ route('departments.index') }}" class="btn btn-secondary">
                        <i class="fas fa-arrow-right me-1"></i> رجوع
                    </a>
                </div>
            </form>
        </div>
    </div>
</x-app-layout>