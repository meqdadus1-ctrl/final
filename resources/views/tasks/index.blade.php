<x-app-layout>
    <x-slot name="title">قائمة المهام</x-slot>

    {{-- Toolbar --}}
    <div class="d-flex justify-content-between align-items-center mb-3 flex-wrap gap-2">
        <div class="d-flex gap-2">
            <a href="{{ route('tasks.index') }}" class="btn btn-sm btn-outline-secondary {{ !request()->routeIs('tasks.kanban') ? 'active' : '' }}">
                <i class="fas fa-list me-1"></i> قائمة
            </a>
            <a href="{{ route('tasks.kanban') }}" class="btn btn-sm btn-outline-secondary">
                <i class="fas fa-columns me-1"></i> كانبان
            </a>
            @can('tasks.view')
            <a href="{{ route('tasks.report') }}" class="btn btn-sm btn-outline-info">
                <i class="fas fa-chart-bar me-1"></i> التقارير
            </a>
            @endcan
        </div>
        @can('tasks.create')
        <a href="{{ route('tasks.create') }}" class="btn btn-primary btn-sm">
            <i class="fas fa-plus me-1"></i> إضافة مهمة
        </a>
        @endcan
    </div>

    {{-- Filters --}}
    <div class="card mb-3">
        <div class="card-body py-2">
            <form method="GET" action="{{ route('tasks.index') }}" class="row g-2 align-items-end">
                <div class="col-md-4">
                    <input type="text" name="search" class="form-control form-control-sm"
                           placeholder="البحث عن مهمة..." value="{{ request('search') }}">
                </div>
                <div class="col-md-2">
                    <select name="status" class="form-select form-select-sm">
                        <option value="">كل الحالات</option>
                        <option value="pending"     {{ request('status') === 'pending'     ? 'selected' : '' }}>قيد الانتظار</option>
                        <option value="in_progress" {{ request('status') === 'in_progress' ? 'selected' : '' }}>جارٍ التنفيذ</option>
                        <option value="completed"   {{ request('status') === 'completed'   ? 'selected' : '' }}>مكتملة</option>
                    </select>
                </div>
                <div class="col-md-2">
                    <select name="priority" class="form-select form-select-sm">
                        <option value="">كل الأولويات</option>
                        <option value="low"    {{ request('priority') === 'low'    ? 'selected' : '' }}>منخفضة</option>
                        <option value="medium" {{ request('priority') === 'medium' ? 'selected' : '' }}>متوسطة</option>
                        <option value="high"   {{ request('priority') === 'high'   ? 'selected' : '' }}>عالية</option>
                    </select>
                </div>
                <div class="col-md-2">
                    <select name="department_id" class="form-select form-select-sm">
                        <option value="">كل الأقسام</option>
                        @foreach($departments as $dep)
                            <option value="{{ $dep->id }}" {{ request('department_id') == $dep->id ? 'selected' : '' }}>
                                {{ $dep->name }}
                            </option>
                        @endforeach
                    </select>
                </div>
                <div class="col-md-2 d-flex gap-1">
                    <button type="submit" class="btn btn-primary btn-sm flex-grow-1">
                        <i class="fas fa-search"></i>
                    </button>
                    <a href="{{ route('tasks.index') }}" class="btn btn-secondary btn-sm">
                        <i class="fas fa-times"></i>
                    </a>
                </div>
            </form>
        </div>
    </div>

    {{-- Table --}}
    <div class="card shadow-sm">
        <div class="card-header d-flex justify-content-between align-items-center py-2">
            <span class="fw-semibold"><i class="fas fa-tasks me-2"></i>المهام ({{ $tasks->total() }})</span>
        </div>
        <div class="card-body p-0">
            <table class="table table-hover align-middle mb-0">
                <thead class="table-dark">
                    <tr>
                        <th class="px-3" style="width:40%">المهمة</th>
                        <th class="text-center">الأولوية</th>
                        <th class="text-center">الحالة</th>
                        <th class="text-center">القسم</th>
                        <th class="text-center">تاريخ الاستحقاق</th>
                        <th class="text-center">المعينون</th>
                        <th class="text-center">إجراءات</th>
                    </tr>
                </thead>
                <tbody>
                    @forelse($tasks as $task)
                    <tr class="{{ $task->is_overdue ? 'table-danger' : '' }}">
                        <td class="px-3">
                            <div class="fw-semibold">
                                <a href="{{ route('tasks.show', $task) }}" class="text-decoration-none text-dark">
                                    {{ $task->title }}
                                </a>
                                @if($task->subtasks_count ?? $task->subtasks->count())
                                    <span class="badge bg-light text-secondary ms-1 small">
                                        {{ $task->subtasks_count ?? $task->subtasks->count() }} مهام فرعية
                                    </span>
                                @endif
                            </div>
                            @if($task->description)
                                <div class="text-muted small mt-1">{{ Str::limit($task->description, 60) }}</div>
                            @endif
                            <span class="badge bg-secondary small">
                                <i class="fas fa-comment me-1"></i>{{ $task->comments_count }}
                            </span>
                        </td>
                        <td class="text-center">
                            <span class="badge bg-{{ $task->priority_color }}">{{ $task->priority_label }}</span>
                        </td>
                        <td class="text-center">
                            <span class="badge bg-{{ $task->status_color }}">{{ $task->status_label }}</span>
                        </td>
                        <td class="text-center">
                            <span class="text-muted small">{{ $task->department?->name ?? '—' }}</span>
                        </td>
                        <td class="text-center">
                            @if($task->due_date)
                                <span class="{{ $task->is_overdue ? 'text-danger fw-bold' : 'text-muted' }} small">
                                    <i class="fas fa-calendar-day me-1"></i>
                                    {{ $task->due_date->format('Y/m/d') }}
                                    @if($task->is_overdue)
                                        <br><span class="badge bg-danger">متأخرة</span>
                                    @endif
                                </span>
                            @else
                                <span class="text-muted">—</span>
                            @endif
                        </td>
                        <td class="text-center">
                            @foreach($task->users->take(3) as $user)
                                <span class="badge bg-light text-dark border small">{{ Str::words($user->name, 1, '') }}</span>
                            @endforeach
                            @if($task->users->count() > 3)
                                <span class="text-muted small">+{{ $task->users->count() - 3 }}</span>
                            @endif
                        </td>
                        <td class="text-center">
                            <div class="d-flex gap-1 justify-content-center">
                                <a href="{{ route('tasks.show', $task) }}" class="btn btn-sm btn-outline-primary" title="عرض">
                                    <i class="fas fa-eye"></i>
                                </a>
                                @can('tasks.edit')
                                <a href="{{ route('tasks.edit', $task) }}" class="btn btn-sm btn-outline-warning" title="تعديل">
                                    <i class="fas fa-edit"></i>
                                </a>
                                @endcan
                                @can('tasks.delete')
                                <form action="{{ route('tasks.destroy', $task) }}" method="POST" class="d-inline"
                                      onsubmit="return confirm('هل أنت متأكد من حذف هذه المهمة؟')">
                                    @csrf @method('DELETE')
                                    <button class="btn btn-sm btn-outline-danger" title="حذف">
                                        <i class="fas fa-trash"></i>
                                    </button>
                                </form>
                                @endcan
                            </div>
                        </td>
                    </tr>
                    @empty
                    <tr>
                        <td colspan="7" class="text-center text-muted py-5">
                            <i class="fas fa-tasks fa-2x mb-2 d-block opacity-25"></i>
                            لا توجد مهام
                        </td>
                    </tr>
                    @endforelse
                </tbody>
            </table>
        </div>
        @if($tasks->hasPages())
        <div class="card-footer">
            {{ $tasks->links() }}
        </div>
        @endif
    </div>
</x-app-layout>
