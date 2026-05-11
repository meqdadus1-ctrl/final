<x-portal-layout>
<x-slot name="title">كشوف الرواتب</x-slot>

<div class="card">
    <div class="card-header">
        <i class="fas fa-money-bill-wave text-primary me-2"></i>
        كشوف رواتبي ({{ $payslips->total() }})
    </div>
    <div class="card-body p-0">
        <div class="table-responsive">
            <table class="table table-hover align-middle mb-0">
                <thead class="table-dark">
                    <tr>
                        <th class="px-3">#</th>
                        <th>الفترة</th>
                        <th class="text-end">الراتب الأساسي</th>
                        <th class="text-end">البدلات</th>
                        <th class="text-end">الخصومات</th>
                        <th class="text-end">الصافي</th>
                        <th class="text-center">الحالة</th>
                        <th class="text-center">PDF</th>
                    </tr>
                </thead>
                <tbody>
                    @forelse($payslips as $ps)
                    <tr>
                        <td class="px-3 text-muted small">{{ $loop->iteration }}</td>
                        <td>
                            <div class="fw-semibold">{{ $ps->month_name ?? '—' }}</div>
                            <div class="text-muted small">{{ $ps->year ?? '' }}</div>
                        </td>
                        <td class="text-end">{{ number_format($ps->basic_salary ?? 0, 2) }} ₪</td>
                        <td class="text-end text-success">{{ number_format($ps->total_allowances ?? 0, 2) }} ₪</td>
                        <td class="text-end text-danger">{{ number_format($ps->total_deductions ?? 0, 2) }} ₪</td>
                        <td class="text-end fw-bold text-primary">{{ number_format($ps->net_salary ?? 0, 2) }} ₪</td>
                        <td class="text-center">
                            <span class="badge bg-{{ $ps->status_color ?? 'secondary' }}">{{ $ps->status_label ?? '—' }}</span>
                        </td>
                        <td class="text-center">
                            @if(Route::has('payslips.pdf'))
                            <a href="{{ route('payslips.pdf', $ps) }}" class="btn btn-sm btn-outline-danger" target="_blank">
                                <i class="fas fa-file-pdf"></i>
                            </a>
                            @endif
                        </td>
                    </tr>
                    @empty
                    <tr>
                        <td colspan="8" class="text-center text-muted py-5">
                            <i class="fas fa-file-invoice fa-2x mb-2 d-block opacity-25"></i>
                            لا توجد كشوف رواتب بعد
                        </td>
                    </tr>
                    @endforelse
                </tbody>
            </table>
        </div>
    </div>
    @if($payslips->hasPages())
    <div class="card-footer">{{ $payslips->links() }}</div>
    @endif
</div>
</x-portal-layout>
