<x-app-layout>
    <x-slot name="title">السلف</x-slot>

    @if(session('error'))
        <div class="alert alert-danger alert-dismissible fade show" role="alert">
            {{ session('error') }}
            <button type="button" class="btn-close" data-bs-dismiss="alert"></button>
        </div>
    @endif

    <div class="row g-4">
        <div class="col-md-5">
            <div class="card">
                <div class="card-header">
                    <i class="fas fa-plus me-2"></i> تقديم طلب سلفة
                </div>
                <div class="card-body">
                    <form action="{{ route('portal.loans.store') }}" method="POST">
                        @csrf
                        <div class="mb-3">
                            <label class="form-label fw-semibold">المبلغ الإجمالي <span class="text-danger">*</span></label>
                            <div class="input-group">
                                <input type="number" name="total_amount" step="0.01" min="100"
                                       class="form-control @error('total_amount') is-invalid @enderror"
                                       value="{{ old('total_amount') }}" required>
                                <span class="input-group-text">₪</span>
                            </div>
                            @error('total_amount') <div class="text-danger small">{{ $message }}</div> @enderror
                        </div>
                        <div class="mb-3">
                            <label class="form-label fw-semibold">عدد الأقساط (أسابيع) <span class="text-danger">*</span></label>
                            <input type="number" name="installments_total" min="1" max="104"
                                   class="form-control @error('installments_total') is-invalid @enderror"
                                   value="{{ old('installments_total', 8) }}" required>
                            @error('installments_total') <div class="invalid-feedback">{{ $message }}</div> @enderror
                            <small class="text-muted">القسط الأسبوعي = المبلغ ÷ عدد الأسابيع — حد أقصى 104 أسبوع</small>
                        </div>
                        <div class="mb-3">
                            <label class="form-label fw-semibold">السبب / الوصف</label>
                            <textarea name="description" class="form-control @error('description') is-invalid @enderror"
                                      rows="3">{{ old('description') }}</textarea>
                            @error('description') <div class="invalid-feedback">{{ $message }}</div> @enderror
                        </div>
                        <button type="submit" class="btn btn-primary w-100">
                            <i class="fas fa-paper-plane me-1"></i> تقديم الطلب
                        </button>
                    </form>
                </div>
            </div>
        </div>

        <div class="col-md-7">
            <div class="card">
                <div class="card-header">
                    <i class="fas fa-history me-2"></i> سجل سلفي ({{ $loans->total() }})
                </div>
                <div class="card-body p-0">
                    <table class="table table-hover mb-0">
                        <thead>
                            <tr>
                                <th class="px-4">المبلغ</th>
                                <th>القسط</th>
                                <th>المدفوع</th>
                                <th>المتبقي</th>
                                <th>الحالة</th>
                            </tr>
                        </thead>
                        <tbody>
                            @forelse($loans as $loan)
                            <tr>
                                <td class="px-4 fw-semibold">{{ number_format($loan->total_amount, 2) }}</td>
                                <td>
                                    {{ number_format($loan->installment_amount, 2) }}
                                    <small class="text-muted d-block">× {{ $loan->installments_total }} أسبوع</small>
                                </td>
                                <td class="text-success">{{ number_format($loan->amount_paid, 2) }}</td>
                                <td class="text-danger">{{ number_format($loan->total_amount - $loan->amount_paid, 2) }}</td>
                                <td>
                                    @switch($loan->status)
                                        @case('active')     <span class="badge bg-primary">نشطة</span> @break
                                        @case('pending')    <span class="badge bg-warning">قيد المراجعة</span> @break
                                        @case('completed')  <span class="badge bg-success">مكتملة</span> @break
                                        @case('rejected')   <span class="badge bg-danger">مرفوضة</span> @break
                                        @default            <span class="badge bg-secondary">{{ $loan->status }}</span>
                                    @endswitch
                                </td>
                            </tr>
                            @empty
                            <tr>
                                <td colspan="5" class="text-center text-muted py-4">لم تقدم أي طلب سلفة بعد</td>
                            </tr>
                            @endforelse
                        </tbody>
                    </table>
                </div>
                @if($loans->hasPages())
                <div class="card-footer">{{ $loans->links() }}</div>
                @endif
            </div>
        </div>
    </div>
</x-app-layout>
