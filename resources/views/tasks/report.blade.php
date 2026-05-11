<x-app-layout>
    <x-slot name="title">تقارير المهام</x-slot>

    {{-- Summary Cards --}}
    <div class="row g-3 mb-4">
        <div class="col-md-3 col-sm-6">
            <div class="card shadow-sm text-center p-3">
                <div class="text-primary fw-bold" style="font-size:2rem;">{{ $totalTasks }}</div>
                <div class="text-muted small">إجمالي المهام</div>
            </div>
        </div>
        <div class="col-md-3 col-sm-6">
            <div class="card shadow-sm text-center p-3">
                <div class="text-success fw-bold" style="font-size:2rem;">{{ $completed }}</div>
                <div class="text-muted small">مكتملة</div>
                @if($totalTasks > 0)
                    <div class="progress mt-2" style="height:4px;">
                        <div class="progress-bar bg-success" style="width:{{ round($completed/$totalTasks*100) }}%"></div>
                    </div>
                    <div class="small text-success mt-1">{{ round($completed/$totalTasks*100) }}%</div>
                @endif
            </div>
        </div>
        <div class="col-md-3 col-sm-6">
            <div class="card shadow-sm text-center p-3">
                <div class="text-secondary fw-bold" style="font-size:2rem;">{{ $pending }}</div>
                <div class="text-muted small">قيد الانتظار</div>
            </div>
        </div>
        <div class="col-md-3 col-sm-6">
            <div class="card shadow-sm text-center p-3">
                <div class="text-danger fw-bold" style="font-size:2rem;">{{ $overdue }}</div>
                <div class="text-muted small">متأخرة</div>
            </div>
        </div>
    </div>

    <div class="row g-3">

        {{-- Tasks by User --}}
        <div class="col-lg-6">
            <div class="card shadow-sm">
                <div class="card-header fw-semibold">
                    <i class="fas fa-user-check me-2"></i> المهام لكل موظف
                </div>
                <div class="card-body p-0">
                    <table class="table table-hover align-middle mb-0">
                        <thead class="table-dark">
                            <tr>
                                <th class="px-3">الموظف</th>
                                <th class="text-center">الإجمالي</th>
                                <th class="text-center">مكتملة</th>
                                <th class="text-center">انتظار</th>
                                <th class="text-center">جارية</th>
                            </tr>
                        </thead>
                        <tbody>
                            @forelse($tasksByUser as $row)
                            <tr>
                                <td class="px-3 fw-semibold">{{ $row->name }}</td>
                                <td class="text-center"><span class="badge bg-primary">{{ $row->total_tasks }}</span></td>
                                <td class="text-center"><span class="badge bg-success">{{ $row->completed_tasks }}</span></td>
                                <td class="text-center"><span class="badge bg-secondary">{{ $row->pending_tasks }}</span></td>
                                <td class="text-center"><span class="badge bg-warning text-dark">{{ $row->inprogress_tasks }}</span></td>
                            </tr>
                            @empty
                            <tr>
                                <td colspan="5" class="text-center text-muted py-4">لا توجد بيانات</td>
                            </tr>
                            @endforelse
                        </tbody>
                    </table>
                </div>
            </div>
        </div>

        {{-- Tasks by Department --}}
        <div class="col-lg-6">
            <div class="card shadow-sm">
                <div class="card-header fw-semibold">
                    <i class="fas fa-building me-2"></i> المهام لكل قسم
                </div>
                <div class="card-body p-0">
                    <table class="table table-hover align-middle mb-0">
                        <thead class="table-dark">
                            <tr>
                                <th class="px-3">القسم</th>
                                <th class="text-center">الإجمالي</th>
                                <th class="text-center">مكتملة</th>
                                <th class="text-center">انتظار</th>
                                <th class="text-center">الإنجاز</th>
                            </tr>
                        </thead>
                        <tbody>
                            @forelse($tasksByDepartment as $row)
                            <tr>
                                <td class="px-3 fw-semibold">{{ $row->name }}</td>
                                <td class="text-center"><span class="badge bg-primary">{{ $row->total_tasks }}</span></td>
                                <td class="text-center"><span class="badge bg-success">{{ $row->completed_tasks }}</span></td>
                                <td class="text-center"><span class="badge bg-secondary">{{ $row->pending_tasks }}</span></td>
                                <td class="text-center">
                                    @php $pct = $row->total_tasks > 0 ? round($row->completed_tasks/$row->total_tasks*100) : 0; @endphp
                                    <div class="progress" style="height:8px; min-width:60px;">
                                        <div class="progress-bar bg-success" style="width:{{ $pct }}%"></div>
                                    </div>
                                    <div class="small text-success">{{ $pct }}%</div>
                                </td>
                            </tr>
                            @empty
                            <tr>
                                <td colspan="5" class="text-center text-muted py-4">لا توجد بيانات</td>
                            </tr>
                            @endforelse
                        </tbody>
                    </table>
                </div>
            </div>
        </div>

        {{-- Overdue Tasks --}}
        @if($overdueTasks->isNotEmpty())
        <div class="col-12">
            <div class="card shadow-sm border-danger">
                <div class="card-header fw-semibold text-danger">
                    <i class="fas fa-exclamation-triangle me-2"></i>
                    المهام المتأخرة ({{ $overdueTasks->count() }})
                </div>
                <div class="card-body p-0">
                    <table class="table table-hover align-middle mb-0">
                        <thead class="table-dark">
                            <tr>
                                <th class="px-3">المهمة</th>
                                <th class="text-center">الأولوية</th>
                                <th class="text-center">القسم</th>
                                <th class="text-center">تاريخ الاستحقاق</th>
                                <th class="text-center">المعينون</th>
                                <th class="text-center">التأخير</th>
                            </tr>
                        </thead>
                        <tbody>
                            @foreach($overdueTasks as $task)
                            <tr class="table-danger">
                                <td class="px-3">
                                    <a href="{{ route('tasks.show', $task) }}" class="fw-semibold text-decoration-none text-dark">
                                        {{ $task->title }}
                                    </a>
                                </td>
                                <td class="text-center">
                                    <span class="badge bg-{{ $task->priority_color }}">{{ $task->priority_label }}</span>
                                </td>
                                <td class="text-center text-muted small">{{ $task->department?->name ?? '—' }}</td>
                                <td class="text-center text-danger fw-bold small">
                                    {{ $task->due_date->format('Y/m/d') }}
                                </td>
                                <td class="text-center small">
                                    {{ $task->users->pluck('name')->map(fn($n) => \Str::words($n, 1, ''))->join(', ') ?: '—' }}
                                </td>
                                <td class="text-center">
                                    <span class="badge bg-danger">
                                        {{ $task->due_date->diffInDays(now()) }} يوم
                                    </span>
                                </td>
                            </tr>
                            @endforeach
                        </tbody>
                    </table>
                </div>
            </div>
        </div>
        @endif

    </div>
</x-app-layout>
