<x-app-layout>
    <x-slot name="title">تعديل القسم</x-slot>

    <div class="card">
        <div class="card-header">
            <i class="fas fa-edit me-2"></i> تعديل القسم - {{ $department->name }}
        </div>
        <div class="card-body">
            <form action="{{ route('departments.update', $department->id) }}" method="POST">
                @csrf
                @method('PUT')
                <div class="row g-3">

                    <div class="col-md-6">
                        <label class="form-label fw-semibold">اسم القسم <span class="text-danger">*</span></label>
                        <input type="text" name="name" class="form-control @error('name') is-invalid @enderror"
                               value="{{ old('name', $department->name) }}" required>
                        @error('name') <div class="invalid-feedback">{{ $message }}</div> @enderror
                    </div>

                    <div class="col-12">
                        <label class="form-label fw-semibold">الوصف</label>
                        <textarea name="description" class="form-control @error('description') is-invalid @enderror"
                                  rows="3">{{ old('description', $department->description) }}</textarea>
                        @error('description') <div class="invalid-feedback">{{ $message }}</div> @enderror
                    </div>

                </div>

                <div class="d-flex gap-2 mt-4">
                    <button type="submit" class="btn btn-primary">
                        <i class="fas fa-save me-1"></i> حفظ التعديلات
                    </button>
                    <a href="{{ route('departments.index') }}" class="btn btn-secondary">
                        <i class="fas fa-arrow-right me-1"></i> رجوع
                    </a>
                </div>
            </form>
        </div>
    </div>
</x-app-layout>