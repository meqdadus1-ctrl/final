<x-app-layout>
    <x-slot name="title">صناديق المدفوعات</x-slot>

    <!-- فلتر -->
    <div class="card mb-4">
        <div class="card-body">
            <form method="GET" action="{{ route('banks.report') }}" class="row g-3 align-items-end">
                <div class="col-md-3">
                    <label class="form-label fw-semibold">السنة</label>
                    <input type="number" name="year" class="form-control"
                        value="{{ $year }}" min="2020" max="2030">
                </div>
                <div class="col-md-3">
                    <label class="form-label fw-semibold">الأسبوع (اختياري)</label>
                    <select name="week" class="form-select">
                        <option value="">كل الأسابيع</option>
                        @foreach($weeks as $num => $label)
                            <option value="{{ $num }}" {{ $week == $num ? 'selected' : '' }}>
                                {{ $label }}
                            </option>
                        @endforeach
                    </select>
                </div>
                <div class="col-md-2">
                    <button type="submit" class="btn btn-primary w-100">
                        <i class="fas fa-filter me-1"></i> تصفية
                    </button>
                </div>
                <div class="col-md-4 text-end">
                    <div class="fs-5 fw-bold text-primary">
                        الإجمالي الكلي: {{ number_format($grandTotal, 2) }} ₪
                    </div>
                </div>
            </form>
        </div>
    </div>

    <!-- الصناديق -->
    <div class="row g-4">
        @foreach($bankData as $item)
        <div class="col-md-6">
            <div class="card h-100">
                @php
                    $headerBg = match($item['type']) {
                        'cash'     => '#2d6a4f',
                        'deferred' => '#6c757d',
                        default    => '#1e3a5f',
                    };
                @endphp
                <div class="card-header d-flex justify-content-between align-items-center"
                    style="background: {{ $headerBg }}; color: white; border-radius: 12px 12px 0 0 !important;">
                    <span>
                        <i class="{{ $item['icon'] }} me-2"></i>
                        {{ $item['label'] }}
                    </span>
                    <span class="badge bg-light text-dark">{{ $item['count'] }} دفعة</span>
                </div>
                <div class="card-body">
                    <div class="text-center mb-3">
                        <div class="fs-2 fw-bold {{ $item['type'] === 'deferred' ? 'text-secondary' : 'text-primary' }}">
                            {{ number_format($item['total'], 2) }} ₪
                        </div>
                        <div class="text-muted">
                            {{ $item['type'] === 'deferred' ? 'إجمالي المرحّل' : 'إجمالي المدفوعات' }}
                        </div>
                    </div>

                    @if($item['payments']->count() > 0)
                    <hr>
                    <table class="table table-sm mb-0">
                        <thead>
                            <tr>
                                <th>الموظف</th>
                                <th>الفترة</th>
                                <th class="text-end">المبلغ</th>
                            </tr>
                        </thead>
                        <tbody>
                            @foreach($item['payments'] as $p)
                            <tr>
                                <td>{{ $p->employee->name ?? '—' }}</td>
                                <td><small class="text-muted">{{ $p->fiscal_period }}</small></td>
                                <td class="text-end fw-bold">{{ number_format($p->net_salary, 2) }} ₪</td>
                            </tr>
                            @endforeach
                        </tbody>
                    </table>
                    @else
                    <div class="text-center text-muted py-2">
                        <i class="fas fa-inbox fa-2x mb-2 d-block opacity-25"></i>
                        لا توجد مدفوعات
                    </div>
                    @endif
                </div>
            </div>
        </div>
        @endforeach
    </div>
</x-app-layout>