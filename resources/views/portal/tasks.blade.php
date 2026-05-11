<x-portal-layout>
<x-slot name="title">مهامي</x-slot>

<div class="card">
    <div class="card-header d-flex justify-content-between align-items-center">
        <span><i class="fas fa-tasks text-primary me-2"></i> المهام المسندة إليّ ({{ $tasks->total() }})</span>
        <div class="d-flex gap-2">
            <span class="badge bg-secondary">{{ $tasks->where('status', 'pending')->count() }} انتظار</span>
            <span class="badge bg-primary">{{ $tasks->where('status', 'in_progress')->count() }} جارية</span>
            <span class="badge bg-success">{{ $tasks->where('status', 'completed')->count() }} مكتملة</span>
        </div>
    </div>
    <div class="card-body p-0">
        <div class="table-responsive">
            <table class="table table-hover align-middle mb-0">
                <thead class="table-dark">
                    <tr>
                        <th class="px-3" style="width:40%">المهمة</th>
                        <th class="text-center">الأولوية</th>
                        <th class="text-center">الحالة</th>
                        <th class="text-center">القسم</th>
                        <th class="text-center">الاستحقاق</th>
                        <th class="text-center">تعليقات</th>
                        <th class="text-center">عرض</th>
                    </tr>
                </thead>
                <tbody>
                    @forelse($tasks as $task)
                    <tr class="{{ $task->is_overdue ? 'table-danger' : '' }}">
                        <td class="px-3">
                            <div class="fw-semibold">{{ $task->title }}</div>
                            @if($task->description)
                                <div class="text-muted small mt-1">{{ Str::limit($task->description, 60) }}</div>
                            @endif
                        </td>
                        <td class="text-center">
                            <span class="badge bg-{{ $task->priority_color }}">{{ $task->priority_label }}</span>
                        </td>
                        <td class="text-center">
                            <span class="badge bg-{{ $task->status_color }}">{{ $task->status_label }}</span>
                        </td>
                        <td class="text-center text-muted small">{{ $task->department?->name ?? '—' }}</td>
                        <td class="text-center">
                            @if($task->due_date)
                                <span class="{{ $task->is_overdue ? 'text-danger fw-bold' : 'text-muted' }} small">
                                    {{ $task->due_date->format('Y/m/d') }}
                                    @if($task->is_overdue) <br><span class="badge bg-danger">متأخرة</span> @endif
                                </span>
                            @else
                                <span class="text-muted">—</span>
                            @endif
                        </td>
                        <td class="text-center">
                            <span class="badge bg-light text-dark border">
                                <i class="fas fa-comment me-1"></i>{{ $task->comments_count }}
                            </span>
                        </td>
                        <td class="text-center">
                            <a href="{{ route('tasks.show', $task) }}" class="btn btn-sm btn-outline-primary">
                                <i class="fas fa-eye"></i>
                            </a>
                        </td>
                    </tr>
                    @empty
                    <tr>
                        <td colspan="7" class="text-center text-muted py-5">
                            <i class="fas fa-tasks fa-2x mb-2 d-block opacity-25"></i>
                            لا توجد مهام مسندة إليك حالياً
                        </td>
                    </tr>
                    @endforelse
                </tbody>
            </table>
        </div>
    </div>
    @if($tasks->hasPages())
    <div class="card-footer">{{ $tasks->links() }}</div>
    @endif
</div>
</x-portal-layout>
