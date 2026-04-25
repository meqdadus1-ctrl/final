<x-app-layout>
    <x-slot name="title">أرصدة الإجازات</x-slot>

    <div class="card">
        <div class="card-header d-flex justify-content-between align-items-center">
            <span><i class="fas fa-chart-bar me-2"></i> أرصدة الإجازات — {{ now()->year }}</span>
            <a href="{{ route('leaves.index') }}" class="btn btn-outline-secondary btn-sm">رجوع</a>
        </div>
        <div class="card-body p-0">
            <table class="table table-hover mb-0">
                <thead>
                    <tr>
                        <th class="px-4">الموظف</th>
                        <th>نوع الإجازة</th>
                        <th>المستحق</th>
                        <th>المستخدم</th>
                        <th>المتبقي</th>
                    </tr>
                </thead>
                <tbody>
                    @forelse($employees as $emp)
                        @if($emp->leaveBalances->count() > 0)
                            @foreach($emp->leaveBalances as $balance)
                            <tr>
                                <td class="px-4 fw-semibold">{{ $emp->name }}</td>
                                <td>{{ $balance->leaveType->name }}</td>
                                <td>{{ $balance->entitled_days }} يوم</td>
                                <td class="text-warning">{{ $balance->used_days }} يوم</td>
                                <td>
                                    <span class="badge {{ $balance->remaining_days > 5 ? 'bg-success' : 'bg-danger' }}">
                                        {{ $balance->remaining_days }} يوم
                                    </span>
                                </td>
                            </tr>
                            @endforeach
                        @else
                        <tr>
                            <td class="px-4 fw-semibold">{{ $emp->name }}</td>
                            <td colspan="4" class="text-muted">لا توجد أرصدة مسجلة</td>
                        </tr>
                        @endif
                    @empty
                    <tr><td colspan="5" class="text-center text-muted py-4">لا يوجد موظفون</td></tr>
                    @endforelse
                </tbody>
            </table>
        </div>
    </div>
</x-app-layout>