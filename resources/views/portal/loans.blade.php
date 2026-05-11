<x-portal-layout>
<x-slot name="title">السلف</x-slot>

<div class="row g-4">

    {{-- Request Form --}}
    <div class="col-lg-4">
        <div class="card">
            <div class="card-header">
                <i class="fas fa-plus text-success me-2"></i> تقديم طلب سلفة
            </div>
            <div class="card-body">
                <form action="{{ route('portal.loans.store') }}" method="POST">
                    @csrf
                    <div class="mb-3">
                        <label class="form-label fw-semibold small">المبلغ المطلوب <span class="text-danger">*</span></label>
                        <div class="input-group">
                            <input type="number" name="total_amount" step="0.01" min="100"
                                   class="form-control @error('total_amount') is-invalid @enderror"
                                   value="{{ old('total_amount') }}" required placeholder="0.00">
                            <span class="input-group-text">₪</span>
                        </div>
                        @error('total_amount') <div class="text-danger small mt-1">{{ $message }}</div> @enderror
                    </div>

                    <div class="mb-3">
                        <label class="form-label fw-semibold small">عدد الأقساط (أسابيع) <span class="text-danger">*</span></label>
                        <input type="number" name="installments_total" min="1" max="104"
                               class="form-control @error('installments_total') is-invalid @enderror"
                               value="{{ old('installments_total', 8) }}" required>
                        @error('installments_total') <div class="invalid-feedback">{{ $message }}</div> @enderror
                        <div class="form-text">القسط = المبلغ ÷ عدد الأسابيع — حد أقصى 104</div>
                    </div>

                    <div class="mb-4">
                        <label class="form-label fw-semibold small">سبب السلفة</label>
                        <textarea name="description" rows="3"
                                  class="form-control @error('description') is-invalid @enderror"
                                  placeholder="اختياري...">{{ old('description') }}</textarea>
                        @error('description') <div class="invalid-feedback">{{ $message }}</div> @enderror
                    </div>

                    <button type="submit" class="btn btn-primary w-100">
                        <i class="fas fa-paper-plane me-1"></i> تقديم الطلب
                    </button>
                </form>
            </div>
        </div>
    </div>

    {{-- Loan History --}}
    <div class="col-lg-8">
        <div class="card">
            <div class="card-header">
                <i class="fas fa-history text-primary me-2"></i>
                سجل سلفي ({{ $loans->total() }})
            </div>
            <div class="card-body p-0">
                <div class="table-responsive">
                    <table class="table table-hover align-middle mb-0">
                        <thead class="table-dark">
                            <tr>
                                <th class="px-3">المبلغ</th>
                                <th class="text-center">القسط الأسبوعي</th>
                                <th class="text-center">المدفوع</th>
                                <th class="text-center">المتبقي</th>
                                <th class="text-center">الحالة</th>
                                <th class="text-center">التقدم</th>
                            </tr>
                        </thead>
                        <tbody>
                            @forelse($loans as $loan)
                            @php $pct = $loan->total_amount > 0 ? min(100, round($loan->amount_paid / $loan->total_amount * 100)) : 0; @endphp
                            <tr>
                                <td class="px-3 fw-bold">{{ number_format($loan->total_amount, 2) }} ₪</td>
                                <td class="text-center">
                                    {{ number_format($loan->installment_amount, 2) }} ₪
                                    <div class="text-muted" style="font-size:11px;">× {{ $loan->installments_total }} أسبوع</div>
                                </td>
                                <td class="text-center text-success fw-semibold">{{ number_format($loan->amount_paid, 2) }} ₪</td>
                                <td class="text-center text-danger fw-semibold">{{ number_format($loan->total_amount - $loan->amount_paid, 2) }} ₪</td>
                                <td class="text-center">
                                    @switch($loan->status)
                                        @case('active')    <span class="badge bg-primary">نشطة</span> @break
                                        @case('pending')   <span class="badge bg-warning text-dark">⏳ قيد المراجعة</span> @break
                                        @case('completed') <span class="badge bg-success">✓ مكتملة</span> @break
                                        @case('rejected')  <span class="badge bg-danger">✗ مرفوضة</span> @break
                                        @case('cancelled') <span class="badge bg-secondary">ملغاة</span> @break
                                        @default           <span class="badge bg-secondary">{{ $loan->status }}</span>
                                    @endswitch
                                </td>
                                <td style="min-width:80px;">
                                    <div class="progress" style="height:6px;">
                                        <div class="progress-bar bg-success" style="width:{{ $pct }}%"></div>
                                    </div>
                                    <div class="text-center text-muted" style="font-size:10px; margin-top:2px;">{{ $pct }}%</div>
                                </td>
                            </tr>
                            @empty
                            <tr>
                                <td colspan="6" class="text-center text-muted py-5">
                                    <i class="fas fa-hand-holding-usd fa-2x mb-2 d-block opacity-25"></i>
                                    لم تقدم أي طلب سلفة بعد
                                </td>
                            </tr>
                            @endforelse
                        </tbody>
                    </table>
                </div>
            </div>
            @if($loans->hasPages())
            <div class="card-footer">{{ $loans->links() }}</div>
            @endif
        </div>
    </div>

</div>
</x-portal-layout>
