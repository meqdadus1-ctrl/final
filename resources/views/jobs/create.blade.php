<x-app-layout>
    <x-slot name="title">إضافة طلب توظيف</x-slot>

    <div class="card" style="max-width: 700px;">
        <div class="card-header">
            <i class="fas fa-briefcase me-2"></i> إضافة طلب توظيف جديد
        </div>
        <div class="card-body">
            <form action="{{ route('jobs.store') }}" method="POST" enctype="multipart/form-data">
                @csrf
                <div class="row g-3">
                    <div class="col-md-6">
                        <label class="form-label fw-semibold">الاسم الكامل</label>
                        <input type="text" name="full_name"
                            class="form-control @error('full_name') is-invalid @enderror"
                            value="{{ old('full_name') }}" required>
                        @error('full_name')<div class="invalid-feedback">{{ $message }}</div>@enderror
                    </div>
                    <div class="col-md-6">
                        <label class="form-label fw-semibold">رقم الموبايل</label>
                        <input type="text" name="mobile"
                            class="form-control @error('mobile') is-invalid @enderror"
                            value="{{ old('mobile') }}" required>
                        @error('mobile')<div class="invalid-feedback">{{ $message }}</div>@enderror
                    </div>
                    <div class="col-md-6">
                        <label class="form-label fw-semibold">البريد الإلكتروني</label>
                        <input type="email" name="email"
                            class="form-control @error('email') is-invalid @enderror"
                            value="{{ old('email') }}">
                        @error('email')<div class="invalid-feedback">{{ $message }}</div>@enderror
                    </div>
                    <div class="col-md-6">
                        <label class="form-label fw-semibold">رقم الهوية</label>
                        <input type="text" name="national_id"
                            class="form-control @error('national_id') is-invalid @enderror"
                            value="{{ old('national_id') }}">
                        @error('national_id')<div class="invalid-feedback">{{ $message }}</div>@enderror
                    </div>
                    <div class="col-md-6">
                        <label class="form-label fw-semibold">المسمى الوظيفي المطلوب</label>
                        <input type="text" name="position"
                            class="form-control @error('position') is-invalid @enderror"
                            value="{{ old('position') }}" required>
                        @error('position')<div class="invalid-feedback">{{ $message }}</div>@enderror
                    </div>
                    <div class="col-md-6">
                        <label class="form-label fw-semibold">القسم</label>
                        <select name="department_id" class="form-select">
                            <option value="">-- غير محدد --</option>
                            @foreach($departments as $dep)
                                <option value="{{ $dep->id }}" {{ old('department_id') == $dep->id ? 'selected' : '' }}>
                                    {{ $dep->name }}
                                </option>
                            @endforeach
                        </select>
                    </div>
                    <div class="col-md-6">
                        <label class="form-label fw-semibold">سنوات الخبرة</label>
                        <input type="number" name="experience_years"
                            class="form-control" value="{{ old('experience_years', 0) }}" min="0">
                    </div>
                    <div class="col-md-6">
                        <label class="form-label fw-semibold">رفع السيرة الذاتية (PDF)</label>
                        <input type="file" name="cv" class="form-control" accept=".pdf,.doc,.docx">
                        @error('cv')<div class="text-danger small">{{ $message }}</div>@enderror
                    </div>
                    <div class="col-12">
                        <label class="form-label fw-semibold">ملاحظات</label>
                        <textarea name="notes" class="form-control" rows="3">{{ old('notes') }}</textarea>
                    </div>
                </div>

                <div class="mt-4 d-flex gap-2">
                    <button type="submit" class="btn btn-primary">
                        <i class="fas fa-save me-1"></i> حفظ الطلب
                    </button>
                    <a href="{{ route('jobs.index') }}" class="btn btn-outline-secondary">إلغاء</a>
                </div>
            </form>
        </div>
    </div>
</x-app-layout>