<x-app-layout>
    <x-slot name="title">تعديل تقييم الأداء</x-slot>

    <div class="d-flex justify-content-between align-items-center mb-3">
        <h5 class="mb-0 fw-bold" style="color:var(--fox-brown)">
            تعديل تقييم: {{ $performance->employee->name }} — أسبوع {{ $performance->week_number }}
        </h5>
        <a href="{{ route('performance.show', $performance) }}" class="btn btn-secondary btn-sm">
            <i class="fas fa-arrow-right me-1"></i> رجوع
        </a>
    </div>

    @if($errors->any())
        <div class="alert alert-danger">{{ $errors->first() }}</div>
    @endif

    <form method="POST" action="{{ route('performance.update', $performance) }}">
        @csrf @method('PUT')

        <div class="row g-3">
            <div class="col-md-8">
                <div class="card">
                    <div class="card-header">معايير التقييم (1 = ضعيف &nbsp;·&nbsp; 5 = ممتاز)</div>
                    <div class="card-body">
                        @php
                        $criteria = [
                            'punctuality'   => 'الانضباط والحضور',
                            'quality'       => 'جودة العمل',
                            'productivity'  => 'الإنتاجية',
                            'teamwork'      => 'العمل الجماعي',
                            'communication' => 'التواصل',
                        ];
                        @endphp

                        @foreach($criteria as $field => $label)
                        <div class="mb-4">
                            <label class="form-label fw-semibold">{{ $label }}</label>
                            <div class="d-flex gap-3 flex-wrap">
                                @for($i = 1; $i <= 5; $i++)
                                <div class="form-check">
                                    <input class="form-check-input" type="radio"
                                           name="{{ $field }}" id="{{ $field }}_{{ $i }}"
                                           value="{{ $i }}"
                                           {{ old($field, $performance->$field) == $i ? 'checked' : '' }} required>
                                    <label class="form-check-label" for="{{ $field }}_{{ $i }}">{{ $i }} ⭐</label>
                                </div>
                                @endfor
                            </div>
                        </div>
                        @endforeach

                        <div class="mb-3">
                            <label class="form-label fw-semibold">نقاط القوة</label>
                            <textarea name="strengths" class="form-control" rows="3">{{ old('strengths', $performance->strengths) }}</textarea>
                        </div>
                        <div class="mb-3">
                            <label class="form-label fw-semibold">مجالات التطوير</label>
                            <textarea name="improvements" class="form-control" rows="3">{{ old('improvements', $performance->improvements) }}</textarea>
                        </div>
                        <div class="mb-3">
                            <label class="form-label fw-semibold">ملاحظات</label>
                            <textarea name="notes" class="form-control" rows="2">{{ old('notes', $performance->notes) }}</textarea>
                        </div>
                    </div>
                </div>
            </div>

            <div class="col-md-4">
                <div class="card mb-3">
                    <div class="card-header">معلومات التقييم</div>
                    <div class="card-body">
                        <p class="mb-1"><strong>الموظف:</strong> {{ $performance->employee->name }}</p>
                        <p class="mb-1"><strong>السنة:</strong> {{ $performance->year }}</p>
                        <p class="mb-1"><strong>الأسبوع:</strong> {{ $performance->week_number }}</p>
                        <p class="mb-3 text-muted small">{{ $performance->week_label }}</p>
                        <hr>
                        <label class="form-label fw-semibold">الحالة</label>
                        <select name="status" class="form-select">
                            <option value="draft" {{ old('status', $performance->status) === 'draft' ? 'selected' : '' }}>مسودة</option>
                            <option value="final" {{ old('status', $performance->status) === 'final' ? 'selected' : '' }}>نهائي</option>
                        </select>
                    </div>
                </div>
                <button type="submit" class="btn btn-primary w-100">
                    <i class="fas fa-save me-1"></i> حفظ التعديلات
                </button>
            </div>
        </div>
    </form>
</x-app-layout>
