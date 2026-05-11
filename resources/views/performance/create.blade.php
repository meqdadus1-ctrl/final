<x-app-layout>
    <x-slot name="title">تقييم أداء جديد</x-slot>

    <div class="d-flex justify-content-between align-items-center mb-3">
        <h5 class="mb-0 fw-bold" style="color:var(--fox-brown)">تقييم أداء جديد</h5>
        <a href="{{ route('performance.index') }}" class="btn btn-secondary btn-sm">
            <i class="fas fa-arrow-right me-1"></i> رجوع
        </a>
    </div>

    @if($errors->any())
        <div class="alert alert-danger">{{ $errors->first() }}</div>
    @endif

    <form method="POST" action="{{ route('performance.store') }}">
        @csrf

        <div class="row g-3">
            {{-- معايير التقييم --}}
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
                                           value="{{ $i }}" {{ old($field) == $i ? 'checked' : '' }} required>
                                    <label class="form-check-label" for="{{ $field }}_{{ $i }}">
                                        {{ $i }} ⭐
                                    </label>
                                </div>
                                @endfor
                            </div>
                        </div>
                        @endforeach

                        <div class="mb-3">
                            <label class="form-label fw-semibold">نقاط القوة</label>
                            <textarea name="strengths" class="form-control" rows="3"
                                      placeholder="ما يتميز به الموظف هذا الأسبوع...">{{ old('strengths') }}</textarea>
                        </div>
                        <div class="mb-3">
                            <label class="form-label fw-semibold">مجالات التطوير</label>
                            <textarea name="improvements" class="form-control" rows="3"
                                      placeholder="ما يحتاج إلى تحسين...">{{ old('improvements') }}</textarea>
                        </div>
                        <div class="mb-3">
                            <label class="form-label fw-semibold">ملاحظات إضافية</label>
                            <textarea name="notes" class="form-control" rows="2">{{ old('notes') }}</textarea>
                        </div>

                    </div>
                </div>
            </div>

            {{-- بيانات التقييم --}}
            <div class="col-md-4">
                <div class="card mb-3">
                    <div class="card-header">بيانات التقييم</div>
                    <div class="card-body">

                        <div class="mb-3">
                            <label class="form-label fw-semibold">الموظف <span class="text-danger">*</span></label>
                            <select name="employee_id" class="form-select" required>
                                <option value="">اختر الموظف</option>
                                @foreach($employees as $emp)
                                    <option value="{{ $emp->id }}" {{ old('employee_id') == $emp->id ? 'selected' : '' }}>
                                        {{ $emp->name }}
                                    </option>
                                @endforeach
                            </select>
                        </div>

                        <div class="mb-3">
                            <label class="form-label fw-semibold">السنة <span class="text-danger">*</span></label>
                            <select name="year" class="form-select" required>
                                @foreach($years as $y)
                                    <option value="{{ $y }}" {{ old('year', now()->year) == $y ? 'selected' : '' }}>{{ $y }}</option>
                                @endforeach
                            </select>
                        </div>

                        <div class="mb-3">
                            <label class="form-label fw-semibold">رقم الأسبوع <span class="text-danger">*</span></label>
                            <select name="week_number" class="form-select" required>
                                @for($w = 1; $w <= 52; $w++)
                                    <option value="{{ $w }}" {{ old('week_number', $currentWeek) == $w ? 'selected' : '' }}>
                                        الأسبوع {{ $w }}
                                    </option>
                                @endfor
                            </select>
                        </div>

                        <div class="mb-3">
                            <label class="form-label fw-semibold">الحالة</label>
                            <select name="status" class="form-select">
                                <option value="draft" {{ old('status', 'draft') === 'draft' ? 'selected' : '' }}>مسودة</option>
                                <option value="final" {{ old('status') === 'final' ? 'selected' : '' }}>نهائي</option>
                            </select>
                        </div>

                    </div>
                </div>

                <button type="submit" class="btn btn-primary w-100">
                    <i class="fas fa-save me-1"></i> حفظ التقييم
                </button>
            </div>
        </div>
    </form>
</x-app-layout>
