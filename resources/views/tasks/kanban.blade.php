<x-app-layout>
    <x-slot name="title">لوحة كانبان</x-slot>

    <style>
        .kanban-wrapper { display: flex; gap: 16px; overflow-x: auto; padding-bottom: 16px; min-height: 80vh; }
        .kanban-col { flex: 1; min-width: 280px; max-width: 350px; display: flex; flex-direction: column; }
        .kanban-col-header { padding: 12px 16px; border-radius: 10px 10px 0 0; font-weight: 700; font-size: 14px; display: flex; align-items: center; justify-content: space-between; }
        .kanban-col-body { background: #f0f2f5; border-radius: 0 0 10px 10px; padding: 10px; flex: 1; min-height: 200px; transition: background 0.2s; }
        .kanban-col-body.drag-over { background: #dde3ec; border: 2px dashed #1e3a5f; }
        .task-card { background: #fff; border-radius: 8px; padding: 12px 14px; margin-bottom: 10px; box-shadow: 0 1px 3px rgba(0,0,0,0.1); cursor: grab; border-right: 4px solid transparent; transition: box-shadow 0.2s, transform 0.15s; }
        .task-card:hover { box-shadow: 0 4px 12px rgba(0,0,0,0.15); }
        .task-card.dragging { opacity: 0.5; transform: rotate(2deg); cursor: grabbing; }
        .task-card.priority-high   { border-right-color: #dc3545; }
        .task-card.priority-medium { border-right-color: #ffc107; }
        .task-card.priority-low    { border-right-color: #198754; }
        .task-card.overdue { background: #fff5f5; }
        .card-title-link { color: #1e3a5f; text-decoration: none; font-weight: 600; font-size: 14px; display: block; margin-bottom: 6px; }
        .card-title-link:hover { color: #2d5a8e; }
        .card-meta { font-size: 11px; color: #6c757d; display: flex; flex-wrap: wrap; gap: 6px; margin-top: 8px; }
        .card-meta span { display: flex; align-items: center; gap: 3px; }
    </style>

    {{-- Toolbar --}}
    <div class="d-flex justify-content-between align-items-center mb-3 flex-wrap gap-2">
        <div class="d-flex gap-2">
            <a href="{{ route('tasks.index') }}" class="btn btn-sm btn-outline-secondary">
                <i class="fas fa-list me-1"></i> قائمة
            </a>
            <a href="{{ route('tasks.kanban') }}" class="btn btn-sm btn-outline-secondary active">
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

    {{-- Kanban Board --}}
    <div class="kanban-wrapper">

        {{-- Column: Pending --}}
        <div class="kanban-col" data-status="pending">
            <div class="kanban-col-header bg-warning text-dark">
                <span><i class="fas fa-clock me-2"></i>قيد الانتظار</span>
                <span class="badge bg-dark column-count">{{ $pending->count() }}</span>
            </div>
            <div class="kanban-col-body"
                 ondragover="dragOver(event)"
                 ondragleave="dragLeave(event)"
                 ondrop="drop(event, 'pending')">
                @forelse($pending as $task)
                    @include('tasks._kanban_card', ['task' => $task])
                @empty
                    <div class="text-center text-muted py-4 small empty-placeholder">
                        <i class="fas fa-inbox fa-lg mb-2 d-block opacity-25"></i>
                        لا توجد مهام
                    </div>
                @endforelse
            </div>
        </div>

        {{-- Column: In Progress --}}
        <div class="kanban-col" data-status="in_progress">
            <div class="kanban-col-header text-white" style="background: #1e3a5f;">
                <span><i class="fas fa-spinner me-2"></i>جارٍ التنفيذ</span>
                <span class="badge bg-warning text-dark column-count">{{ $inProgress->count() }}</span>
            </div>
            <div class="kanban-col-body"
                 ondragover="dragOver(event)"
                 ondragleave="dragLeave(event)"
                 ondrop="drop(event, 'in_progress')">
                @forelse($inProgress as $task)
                    @include('tasks._kanban_card', ['task' => $task])
                @empty
                    <div class="text-center text-muted py-4 small empty-placeholder">
                        <i class="fas fa-inbox fa-lg mb-2 d-block opacity-25"></i>
                        لا توجد مهام
                    </div>
                @endforelse
            </div>
        </div>

        {{-- Column: Completed --}}
        <div class="kanban-col" data-status="completed">
            <div class="kanban-col-header bg-success text-white">
                <span><i class="fas fa-check-circle me-2"></i>مكتملة</span>
                <span class="badge bg-white text-success column-count">{{ $completed->count() }}</span>
            </div>
            <div class="kanban-col-body"
                 ondragover="dragOver(event)"
                 ondragleave="dragLeave(event)"
                 ondrop="drop(event, 'completed')">
                @forelse($completed as $task)
                    @include('tasks._kanban_card', ['task' => $task])
                @empty
                    <div class="text-center text-muted py-4 small empty-placeholder">
                        <i class="fas fa-inbox fa-lg mb-2 d-block opacity-25"></i>
                        لا توجد مهام
                    </div>
                @endforelse
            </div>
        </div>

    </div>

    <script>
    const csrfToken = document.querySelector('meta[name="csrf-token"]').content;
    let draggedCard  = null;
    let sourceColumn = null;

    function dragStart(event, taskId) {
        draggedCard = event.currentTarget;
        sourceColumn = draggedCard.parentElement;
        draggedCard.classList.add('dragging');
        event.dataTransfer.effectAllowed = 'move';
        event.dataTransfer.setData('taskId', taskId);
    }

    function dragEnd(event) {
        if (draggedCard) draggedCard.classList.remove('dragging');
    }

    function dragOver(event) {
        event.preventDefault();
        event.dataTransfer.dropEffect = 'move';
        const body = event.currentTarget;
        body.classList.add('drag-over');
    }

    function dragLeave(event) {
        event.currentTarget.classList.remove('drag-over');
    }

    function drop(event, newStatus) {
        event.preventDefault();
        event.currentTarget.classList.remove('drag-over');

        if (!draggedCard) return;
        const taskId = event.dataTransfer.getData('taskId');
        if (!taskId) return;

        const currentStatus = draggedCard.closest('.kanban-col').dataset.status;
        if (currentStatus === newStatus) return;

        // Optimistic UI move
        const targetBody = event.currentTarget;
        const placeholder = targetBody.querySelector('.empty-placeholder');
        if (placeholder) placeholder.remove();
        targetBody.appendChild(draggedCard);
        updateCounts();

        @can('tasks.edit')
        fetch(`/tasks/${taskId}/status`, {
            method: 'PATCH',
            headers: {
                'Content-Type': 'application/json',
                'X-CSRF-TOKEN': csrfToken,
            },
            body: JSON.stringify({ status: newStatus }),
        })
        .then(res => res.json())
        .then(data => {
            if (!data.success) {
                // Revert
                sourceColumn.appendChild(draggedCard);
                updateCounts();
            }
        })
        .catch(() => {
            sourceColumn.appendChild(draggedCard);
            updateCounts();
        });
        @endcan

        draggedCard = null;
        sourceColumn = null;
    }

    function updateCounts() {
        document.querySelectorAll('.kanban-col').forEach(col => {
            const count = col.querySelectorAll('.task-card').length;
            col.querySelector('.column-count').textContent = count;
        });
    }
    </script>
</x-app-layout>
