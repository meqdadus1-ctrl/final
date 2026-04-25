<x-app-layout>
    <x-slot name="title">كشوف الرواتب</x-slot>

    <div class="card">
        <div class="card-header d-flex justify-content-between align-items-center">
            <span><i class="fas fa-money-bill-wave me-2"></i> كشوف رواتبي ({{ $payslips->total() }})</span>
            <a href="{{ route('portal.index') }}" class="btn btn-sm btn-secondary">
                <i class="fas fa-arrow-right me-1"></i> رجوع
            </a>
        </div>
        <div class="card-body p-0">
            <table class="table table-hover mb-0">
                <thead>
                    <tr>
                        <th class="px-4">#</th>
                        <th>الشهر</th>
                        <th>السنة</th>
                        <th>الراتب الأساسي</th>
                        <th>البدلات</th>
                        <th>الخصومات</th>
                        <th>الصافي</th>
                        <th>الحالة</th>
                        <th>إجراءات</th>
                    </tr>
                </thead>
                <tbody>
                    @forelse($payslips as $ps)
                    <tr>
                        <td class="px-4">{{ $loop->iteration }}</td>
                        <td class="fw-semibold">{{ $ps->month_name }}</td>
                        <td>{{ $ps->year }}</td>
                        <td>{{ number_format($ps->basic_salary, 2) }}</td>
                        <td class="text-success">{{ number_format($ps->total_allowances, 2) }}</td>
                        <td class="text-danger">{{ number_format($ps->total_deductions, 2) }}</td>
                        <td class="fw-bold text-primary">{{ number_format($ps->net_salary, 2) }} ₪</td>
                        <td>
                            <span class="badge bg-{{ $ps->status_color }}">{{ $ps->status_label }}</span>
                        </td>
                        <td>
                            <a href="{{ route('payslips.pdf', $ps) }}" class="btn btn-sm btn-outline-danger" target="_blank">
                                <i class="fas fa-file-pdf me-1"></i> PDF
                            </a>
                        </td>
                    </tr>
                    @empty
                    <tr>
                        <td colspan="9" class="text-center text-muted py-4">لا توجد كشوف رواتب بعد</td>
                    </tr>
                    @endforelse
                </tbody>
            </table>
        </div>
        @if($payslips->hasPages())
        <div class="card-footer">
            {{ $payslips->links() }}
        </div>
        @endif
    </div>
</x-app-layout>
