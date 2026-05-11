<x-app-layout>
    <x-slot name="title">تعديل الحدث</x-slot>

    <div class="card shadow-sm" style="max-width:700px; margin: 0 auto;">
        <div class="card-header d-flex justify-content-between align-items-center">
            <span><i class="fas fa-calendar-edit me-2"></i> تعديل الحدث: {{ $event->title }}</span>
            <form action="{{ route('events.destroy', $event) }}" method="POST"
                  onsubmit="return confirm('هل أنت متأكد من حذف هذا الحدث؟')">
                @csrf @method('DELETE')
                <button class="btn btn-sm btn-outline-danger">
                    <i class="fas fa-trash me-1"></i> حذف
                </button>
            </form>
        </div>
        <div class="card-body">
            <form action="{{ route('events.update', $event) }}" method="POST">
                @csrf @method('PUT')

                <div class="row g-3">

                    <div class="col-12">
                        <label class="form-label fw-semibold">عنوان الحدث <span class="text-danger">*</span></label>
                        <input type="text" name="title"
                               class="form-control @error('title') is-invalid @enderror"
                               value="{{ old('title', $event->title) }}" required>
                        @error('title') <div class="invalid-feedback">{{ $message }}</div> @enderror
                    </div>

                    <div class="col-12">
                        <label class="form-label fw-semibold">الوصف</label>
                        <textarea name="description" rows="3"
                                  class="form-control @error('description') is-invalid @enderror">{{ old('description', $event->description) }}</textarea>
                        @error('description') <div class="invalid-feedback">{{ $message }}</div> @enderror
                    </div>

                    <div class="col-md-6">
                        <label class="form-label fw-semibold">تاريخ البداية <span class="text-danger">*</span></label>
                        <input type="datetime-local" name="start_date"
                               class="form-control @error('start_date') is-invalid @enderror"
                               value="{{ old('start_date', $event->start_date->format('Y-m-d\TH:i')) }}" required>
                        @error('start_date') <div class="invalid-feedback">{{ $message }}</div> @enderror
                    </div>

                    <div class="col-md-6">
                        <label class="form-label fw-semibold">تاريخ النهاية</label>
                        <input type="datetime-local" name="end_date"
                               class="form-control @error('end_date') is-invalid @enderror"
                               value="{{ old('end_date', $event->end_date?->format('Y-m-d\TH:i')) }}">
                        @error('end_date') <div class="invalid-feedback">{{ $message }}</div> @enderror
                    </div>

                    <div class="col-md-6">
                        <label class="form-label fw-semibold">لون الحدث</label>
                        <div class="d-flex align-items-center gap-2">
                            <input type="color" name="color"
                                   class="form-control form-control-color"
                                   value="{{ old('color', $event->color) }}"
                                   style="width:60px; height:38px;">
                            <div class="d-flex gap-1 flex-wrap">
                                @foreach(['#1e3a5f','#dc3545','#198754','#ffc107','#0d6efd','#6f42c1','#fd7e14'] as $c)
                                <span class="rounded-circle border" style="width:24px;height:24px;background:{{ $c }};cursor:pointer;"
                                      onclick="document.querySelector('[name=color]').value='{{ $c }}'"></span>
                                @endforeach
                            </div>
                        </div>
                        @error('color') <div class="invalid-feedback d-block">{{ $message }}</div> @enderror
                    </div>

                    <div class="col-md-6 d-flex align-items-end">
                        <div class="form-check mb-1">
                            <input class="form-check-input" type="checkbox" name="all_day" value="1"
                                   id="all_day" {{ old('all_day', $event->all_day) ? 'checked' : '' }}>
                            <label class="form-check-label fw-semibold" for="all_day">
                                حدث طوال اليوم
                            </label>
                        </div>
                    </div>

                </div>

                <div class="d-flex gap-2 mt-4">
                    <button type="submit" class="btn btn-primary">
                        <i class="fas fa-save me-1"></i> حفظ التعديلات
                    </button>
                    <a href="{{ route('calendar.index') }}" class="btn btn-secondary">
                        <i class="fas fa-arrow-right me-1"></i> رجوع للتقويم
                    </a>
                </div>

            </form>
        </div>
    </div>
</x-app-layout>
