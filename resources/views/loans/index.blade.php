<x-app-layout>
    <x-slot name="title">السلف والقروض</x-slot>

    <div class="card">
        <div class="card-header d-flex justify-content-between align-items-center">
            <span><i class="fas fa-hand-holding-usd me-2"></i> السلف والقروض</span>
            <a href="{{ route('loans.create') }}" class="btn btn-primary btn-sm">
                <i class="fas fa-plus me-1"></i> إضافة سلفة
            </a>
        </div>
        <div class="card-body p-0">
            <table class="table table-hover mb-0">
                <thead>
                    <tr>
                        <th class="px-4">الموظف</th>
                        <th>إجمالي السلفة</th>
                        <th>القسط الأسبوعي</th>
                        <th>المدفوع</th>
                        <th>المتبقي</th>
                        <th>الأقساط</th>
                        <th>الحالة</th>
                        <th>الإجراءات</th>
                    </tr>
                </thead>
                <tbody>
                    @forelse($loans as $loan)
                    <tr>
                        <td class="px-4 fw-semibold">{{ $loan->employee->name }}</td>
                        <td>{{ number_format($loan->total_amount, 2) }} ₪</td>
                        <td>{{ number_format($loan->installment_amount, 2) }} ₪</td>
                        <td class="text-success">{{ number_format($loan->amount_paid, 2) }} ₪</td>
                        <td class="text-danger">{{ number_format($loan->remaining_amount, 2) }} ₪</td>
                        <td>
                            <span class="badge bg-info">
                                {{ $loan->installments_paid }} / {{ $loan->installments_total }}
                            </span>
                        </td>
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
                        <td>
                            <a href="{{ route('loans.show', $loan) }}" class="btn btn-sm btn-outline-primary">
                                <i class="fas fa-eye"></i>
                            </a>
                            @if($loan->status == 'active' && !$loan->is_paused)
                            <form action="{{ route('loans.pay', $loan) }}" method="POST" class="d-inline">
                                @csrf
                                <button class="btn btn-sm btn-success" onclick="return confirm('دفع قسط؟')">
                                    <i class="fas fa-money-bill"></i>
                                </button>
                            </form>
                            @endif
                            <form action="{{ route('loans.destroy', $loan) }}" method="POST" class="d-inline">
                                @csrf @method('DELETE')
                                <button class="btn btn-sm btn-outline-danger" onclick="return confirm('حذف السلفة؟')">
                                    <i class="fas fa-trash"></i>
                                </button>
                            </form>
                        </td>
                    </tr>
                    @empty
                    <tr><td colspan="8" class="text-center text-muted py-4">لا توجد سلف بعد</td></tr>
                    @endforelse
                </tbody>
            </table>
        </div>
        @if($loans->hasPages())
        <div class="card-footer">{{ $loans->links() }}</div>
        @endif
    </div>
</x-app-layout>