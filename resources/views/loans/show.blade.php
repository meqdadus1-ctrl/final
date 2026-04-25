<x-app-layout>
    <x-slot name="title">تفاصيل السلفة</x-slot>

    <div class="row g-4">
        <div class="col-md-6">
            <div class="card h-100">
                <div class="card-header">
                    <i class="fas fa-info-circle me-2"></i> معلومات السلفة
                </div>
                <div class="card-body">
                    <table class="table table-borderless mb-0">
                        <tr>
                            <th class="text-muted" width="40%">الموظف</th>
                            <td class="fw-semibold">{{ $loan->employee->name }}</td>
                        </tr>
                        <tr>
                            <th class="text-muted">إجمالي السلفة</th>
                            <td>{{ number_format($loan->total_amount, 2) }} ₪</td>
                        </tr>
                        <tr>
                            <th class="text-muted">القسط الأسبوعي</th>
                            <td>{{ number_format($loan->installment_amount, 2) }} ₪</td>
                        </tr>
                        <tr>
                            <th class="text-muted">المبلغ المدفوع</th>
                            <td class="text-success fw-bold">{{ number_format($loan->amount_paid, 2) }} ₪</td>
                        </tr>
                        <tr>
                            <th class="text-muted">المبلغ المتبقي</th>
                            <td class="text-danger fw-bold">{{ number_format($loan->remaining_amount, 2) }} ₪</td>
                        </tr>
                        <tr>
                            <th class="text-muted">تاريخ البداية</th>
                            <td>{{ $loan->start_date }}</td>
                        </tr>
                        <tr>
                            <th class="text-muted">آخر دفعة</th>
                            <td>{{ $loan->last_payment_date ?? '—' }}</td>
                        </tr>
                        <tr>
                            <th class="text-muted">الحالة</th>
                            <td>
                                @if($loan->status == 'active' && !$loan->is_paused)
                                    <span class="badge bg-success">نشطة</span>
                                @elseif($loan->is_paused)
                                    <span class="badge bg-warning">موقوفة</span>
                                @elseif($loan->status == 'completed')
                                    <span class="badge bg-secondary">مكتملة</span>
                                @else
                                    <span class="badge bg-danger">ملغاة</span>
                                @endif
                            </td>
                        </tr>
                        @if($loan->description)
                        <tr>
                            <th class="text-muted">الوصف</th>
                            <td>{{ $loan->description }}</td>
                        </tr>
                        @endif
                    </table>
                </div>
            </div>
        </div>

        <div class="col-md-6">
            <div class="card h-100">
                <div class="card-header">
                    <i class="fas fa-chart-pie me-2"></i> تقدم السداد
                </div>
                <div class="card-body d-flex flex-column justify-content-center">
                    @php
                        $percent = $loan->installments_total > 0
                            ? round(($loan->installments_paid / $loan->installments_total) * 100)
                            : 0;
                    @endphp
                    <div class="text-center mb-3">
                        <div class="fs-1 fw-bold text-primary">{{ $percent }}%</div>
                        <div class="text-muted">نسبة السداد</div>
                    </div>
                    <div class="progress" style="height: 20px;">
                        <div class="progress-bar bg-success" style="width: {{ $percent }}%">
                            {{ $percent }}%
                        </div>
                    </div>
                    <div class="mt-3 text-center text-muted">
                        {{ $loan->installments_paid }} قسط مدفوع من أصل {{ $loan->installments_total }}
                    </div>

                    @if($loan->status == 'active' && !$loan->is_paused)
                    <form action="{{ route('loans.pay', $loan) }}" method="POST" class="mt-4 text-center">
                        @csrf
                        <button class="btn btn-success px-4" onclick="return confirm('تأكيد دفع قسط بقيمة {{ number_format($loan->installment_amount, 2) }} ₪؟')">
                            <i class="fas fa-money-bill me-2"></i>
                            دفع قسط — {{ number_format($loan->installment_amount, 2) }} ₪
                        </button>
                    </form>
                    @endif
                </div>
            </div>
        </div>
    </div>

    <div class="mt-3">
        <a href="{{ route('loans.index') }}" class="btn btn-outline-secondary">
            <i class="fas fa-arrow-right me-1"></i> رجوع
        </a>

        @if($loan->status == 'active')
        <form action="{{ route('loans.update', $loan) }}" method="POST" class="d-inline">
            @csrf @method('PUT')
            <input type="hidden" name="is_paused" value="{{ $loan->is_paused ? 0 : 1 }}">
            <button class="btn {{ $loan->is_paused ? 'btn-success' : 'btn-warning' }}">
                <i class="fas fa-{{ $loan->is_paused ? 'play' : 'pause' }} me-1"></i>
                {{ $loan->is_paused ? 'استئناف السلفة' : 'إيقاف مؤقت' }}
            </button>
        </form>
        @endif
    </div>
</x-app-layout>