<x-app-layout>
    <x-slot name="title">إضافة مهمة جديدة</x-slot>

    <div class="card shadow-sm">
        <div class="card-header">
            <i class="fas fa-plus me-2"></i> إضافة مهمة جديدة
        </div>
        <div class="card-body">
            <form action="{{ route('tasks.store') }}" method="POST">
                @csrf

                <div class="row g-3">

                    {{-- Title --}}
                    <div class="col-12">
                        <label class="form-label fw-semibold">عنوان المهمة <span class="text-danger">*</span></label>
                        <input type="text" name="title"
                               class="form-control @error('title') is-invalid @enderror"
                               value="{{ old('title') }}" required placeholder="أدخل عنوان المهمة...">
                        @error('title') <div class="invalid-feedback">{{ $message }}</div> @enderror
                    </div>

                    {{-- Description --}}
                    <div class="col-12">
                        <label class="form-label fw-semibold">الوصف</label>
                        <textarea name="description" rows="4"
                                  class="form-control @error('description') is-invalid @enderror"
                                  placeholder="وصف تفصيلي للمهمة...">{{ old('description') }}</textarea>
                        @error('description') <div class="invalid-feedback">{{ $message }}</div> @enderror
                    </div>

                    {{-- Priority & Status --}}
                    <div class="col-md-4">
                        <label class="form-label fw-semibold">الأولوية <span class="text-danger">*</span></label>
                        <select name="priority" class="form-select @error('priority') is-invalid @enderror" required>
                            <option value="low"    {{ old('priority') === 'low'    ? 'selected' : '' }}>🟢 منخفضة</option>
                            <option value="medium" {{ old('priority', 'medium') === 'medium' ? 'selected' : '' }}>🟡 متوسطة</option>
                            <option value="high"   {{ old('priority') === 'high'   ? 'selected' : '' }}>🔴 عالية</option>
                        </select>
                        @error('priority') <div class="invalid-feedback">{{ $message }}</div> @enderror
                    </div>

                    <div class="col-md-4">
                        <label class="form-label fw-semibold">الحالة <span class="text-danger">*</span></label>
                        <select name="status" class="form-select @error('status') is-invalid @enderror" required>
                            <option value="pending"     {{ old('status', 'pending') === 'pending'     ? 'selected' : '' }}>قيد الانتظار</option>
                            <option value="in_progress" {{ old('status') === 'in_progress' ? 'selected' : '' }}>جارٍ التنفيذ</option>
                            <option value="completed"   {{ old('status') === 'completed'   ? 'selected' : '' }}>مكتملة</option>
                        </select>
                        @error('status') <div class="invalid-feedback">{{ $message }}</div> @enderror
                    </div>

                    {{-- Due Date --}}
                    <div class="col-md-4">
                        <label class="form-label fw-semibold">تاريخ الاستحقاق</label>
                        <input type="date" name="due_date"
                               class="form-control @error('due_date') is-invalid @enderror"
                               value="{{ old('due_date') }}">
                        @error('due_date') <div class="invalid-feedback">{{ $message }}</div> @enderror
                    </div>

                    {{-- Department --}}
                    <div class="col-md-6">
                        <label class="form-label fw-semibold">القسم</label>
                        <select name="department_id" class="form-select @error('department_id') is-invalid @enderror">
                            <option value="">— بدون قسم —</option>
                            @foreach($departments as $dep)
                                <option value="{{ $dep->id }}" {{ old('department_id') == $dep->id ? 'selected' : '' }}>
                                    {{ $dep->name }}
                                </option>
                            @endforeach
                        </select>
                        @error('department_id') <div class="invalid-feedback">{{ $message }}</div> @enderror
                    </div>

                    {{-- Parent Task --}}
                    <div class="col-md-6">
                        <label class="form-label fw-semibold">مهمة رئيسية (للمهام الفرعية)</label>
                        <select name="parent_id" class="form-select @error('parent_id') is-invalid @enderror">
                            <option value="">— مهمة رئيسية —</option>
                            @foreach($parentTasks as $pt)
                                <option value="{{ $pt->id }}" {{ old('parent_id') == $pt->id ? 'selected' : '' }}>
                                    {{ $pt->title }}
                                </option>
                            @endforeach
                        </select>
                        @error('parent_id') <div class="invalid-feedback">{{ $message }}</div> @enderror
                    </div>

                    {{-- Assign Users --}}
                    <div class="col-12">
                        <label class="form-label fw-semibold">تعيين الموظفين</label>
                        <div class="border rounded p-3" style="max-height:200px; overflow-y:auto;">
                            @foreach($users as $user)
                            <div class="form-check">
                                <input class="form-check-input" type="checkbox"
                                       name="user_ids[]" value="{{ $user->id }}"
                                       id="user_{{ $user->id }}"
                                       {{ in_array($user->id, old('user_ids', [])) ? 'checked' : '' }}>
                                <label class="form-check-label" for="user_{{ $user->id }}">
                                    {{ $user->name }}
                                    <span class="text-muted small">({{ $user->email }})</span>
                                </label>
                            </div>
                            @endforeach
                        </div>
                        @error('user_ids') <div class="text-danger small mt-1">{{ $message }}</div> @enderror
                    </div>

                </div>

                <div class="d-flex gap-2 mt-4">
                    <button type="submit" class="btn btn-primary">
                        <i class="fas fa-save me-1"></i> حفظ المهمة
                    </button>
                    <a href="{{ route('tasks.index') }}" class="btn btn-secondary">
                        <i class="fas fa-arrow-right me-1"></i> رجوع
                    </a>
                </div>

            </form>
        </div>
    </div>
</x-app-layout>
