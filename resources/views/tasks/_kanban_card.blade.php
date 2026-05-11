<div class="task-card priority-{{ $task->priority }} {{ $task->is_overdue ? 'overdue' : '' }}"
     data-task-id="{{ $task->id }}"
     draggable="true"
     ondragstart="dragStart(event, {{ $task->id }})"
     ondragend="dragEnd(event)">

    <a href="{{ route('tasks.show', $task) }}" class="card-title-link">
        {{ $task->title }}
    </a>

    @if($task->description)
        <div class="text-muted" style="font-size:12px; line-height:1.4; max-height:36px; overflow:hidden;">
            {{ Str::limit($task->description, 80) }}
        </div>
    @endif

    <div class="card-meta">
        <span><span class="badge bg-{{ $task->priority_color }} small">{{ $task->priority_label }}</span></span>

        @if($task->due_date)
            <span class="{{ $task->is_overdue ? 'text-danger fw-bold' : '' }}">
                <i class="fas fa-calendar-day"></i>
                {{ $task->due_date->format('m/d') }}
                @if($task->is_overdue) ⚠️ @endif
            </span>
        @endif

        @if($task->department)
            <span><i class="fas fa-building"></i> {{ $task->department->name }}</span>
        @endif

        @if($task->comments_count)
            <span><i class="fas fa-comment"></i> {{ $task->comments_count }}</span>
        @endif

        @if($task->users->count())
            <span><i class="fas fa-users"></i> {{ $task->users->count() }}</span>
        @endif
    </div>

    @if($task->users->isNotEmpty())
        <div class="mt-2">
            @foreach($task->users->take(4) as $user)
                <span class="badge bg-light text-dark border" style="font-size:10px;">
                    {{ Str::words($user->name, 1, '') }}
                </span>
            @endforeach
            @if($task->users->count() > 4)
                <span class="text-muted" style="font-size:10px;">+{{ $task->users->count() - 4 }}</span>
            @endif
        </div>
    @endif
</div>
