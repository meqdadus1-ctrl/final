<x-app-layout>
    <x-slot name="title">تقييم أداء — {{ $performance->employee->name }}</x-slot>

    <div class="d-flex justify-content-between align-items-center mb-3">
        <h5 class="mb-0 fw-bold" style="color:var(--fox-brown)">
            تقييم أداء: {{ $performance->employee->name }}
        </h5>
        <div class="d-flex gap-2">
            <a href="{{ route('performance.edit', $performance) }}" class="btn btn-warning btn-sm">
                <i class="fas fa-edit me-1"></i> تعديل
            </a>
            <a href="{{ route('performance.index') }}" class="btn btn-secondary btn-sm">
                <i class="fas fa-arrow-right me-1"></i> رجوع
            </a>
        </div>
    </div>

    <div class="row g-3">
        {{-- بطاقة النتيجة --}}
        <div class="col-md-4">
            <div class="card text-center h-100">
                <div class="card-body d-flex flex-column justify-content-center py-4">
                    <div style="font-size:64px;font-weight:800;color:var(--fox-orange);line-height:1">
                        {{ number_format($performance->overall_score, 1) }}
                    </div>
                    <div class="text-muted mb-2">من 5.0</div>
                    <span class="badge bg-{{ $performance->score_color }} fs-6 px-3 py-2">
                        {{ $performance->score_label }}
                    </span>
                    <hr>
                    <div class="text-start small">
                        <div class="mb-1"><strong>الموظف:</strong> {{ $performance->employee->name }}</div>
                        <div class="mb-1"><strong>القسم:</strong> {{ $performance->employee->department->name ?? '—' }}</div>
                        <div class="mb-1"><strong>السنة:</strong> {{ $performance->year }}</div>
                        <div class="mb-1"><strong>الأسبوع:</strong> {{ $performance->week_number }}</div>
                        <div class="mb-1 text-muted" style="font-size:11px">{{ $performance->week_label }}</div>
                        <div class="mb-1"><strong>المقيِّم:</strong> {{ $performance->reviewer->name }}</div>
                        <div>
                            <span class="badge {{ $performance->status === 'final' ? 'bg-success' : 'bg-warning text-dark' }}">
                                {{ $performance->status === 'final' ? 'نهائي' : 'مسودة' }}
                            </span>
                        </div>
                    </div>
                </div>
            </div>
        </div>

        {{-- تفاصيل المعايير --}}
        <div class="col-md-8">
            <div class="card mb-3">
                <div class="card-header">تفاصيل التقييم</div>
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
                    <div class="mb-3">
                        <div class="d-flex justify-content-between mb-1">
                            <span class="fw-semibold">{{ $label }}</span>
                            <span class="text-muted">{{ $performance->$field }} / 5</span>
                        </div>
                        <div class="progress" style="height:10px;border-radius:6px">
                            <div class="progress-bar" role="progressbar"
                                 style="width:{{ $performance->$field * 20 }}%;background:var(--fox-orange);border-radius:6px">
                            </div>
                        </div>
                    </div>
                    @endforeach
                </div>
            </div>

            @if($performance->strengths || $performance->improvements || $performance->notes)
            <div class="card">
                <div class="card-header">التعليقات</div>
                <div class="card-body">
                    @if($performance->strengths)
                    <p class="mb-2"><strong class="text-success">نقاط القوة:</strong><br>{{ $performance->strengths }}</p>
                    @endif
                    @if($performance->improvements)
                    <p class="mb-2"><strong style="color:var(--fox-orange)">مجالات التطوير:</strong><br>{{ $performance->improvements }}</p>
                    @endif
                    @if($performance->notes)
                    <p class="mb-0"><strong>ملاحظات:</strong><br>{{ $performance->notes }}</p>
                    @endif
                </div>
            </div>
            @endif
        </div>
    </div>

    <div class="mt-3 text-end">
        <form method="POST" action="{{ route('performance.destroy', $performance) }}"
              onsubmit="return confirm('هل أنت متأكد من حذف هذا التقييم؟')">
            @csrf @method('DELETE')
            <button type="submit" class="btn btn-outline-danger btn-sm">
                <i class="fas fa-trash me-1"></i> حذف التقييم
            </button>
        </form>
    </div>
</x-app-layout>
