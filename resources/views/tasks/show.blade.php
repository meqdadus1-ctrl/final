<x-app-layout>
    <x-slot name="title">{{ $task->title }}</x-slot>

    <div class="row g-3">

        {{-- Main Task Details --}}
        <div class="col-lg-8">

            {{-- Header Card --}}
            <div class="card shadow-sm mb-3">
                <div class="card-header d-flex justify-content-between align-items-start">
                    <div>
                        <h5 class="mb-1">{{ $task->title }}</h5>
                        @if($task->parent)
                            <div class="small text-muted">
                                <i class="fas fa-level-up-alt me-1"></i>
                                مهمة فرعية من:
                                <a href="{{ route('tasks.show', $task->parent) }}" class="text-decoration-none">
                                    {{ $task->parent->title }}
                                </a>
                            </div>
                        @endif
                    </div>
                    <div class="d-flex gap-2">
                        @can('tasks.edit')
                        <a href="{{ route('tasks.edit', $task) }}" class="btn btn-sm btn-outline-warning">
                            <i class="fas fa-edit me-1"></i> تعديل
                        </a>
                        @endcan
                        @can('tasks.delete')
                        <form action="{{ route('tasks.destroy', $task) }}" method="POST"
                              onsubmit="return confirm('هل أنت متأكد من حذف هذه المهمة؟')">
                            @csrf @method('DELETE')
                            <button class="btn btn-sm btn-outline-danger">
                                <i class="fas fa-trash me-1"></i> حذف
                            </button>
                        </form>
                        @endcan
                    </div>
                </div>
                <div class="card-body">

                    {{-- Status + Priority Badges --}}
                    <div class="d-flex gap-2 mb-3 flex-wrap">
                        <span class="badge bg-{{ $task->status_color }} fs-6">{{ $task->status_label }}</span>
                        <span class="badge bg-{{ $task->priority_color }} fs-6">{{ $task->priority_label }} الأولوية</span>
                        @if($task->is_overdue)
                            <span class="badge bg-danger fs-6">⚠️ متأخرة</span>
                        @endif
                    </div>

                    {{-- Description --}}
                    @if($task->description)
                        <div class="mb-3">
                            <h6 class="fw-semibold text-muted mb-2">الوصف</h6>
                            <div class="p-3 bg-light rounded" style="white-space:pre-wrap; line-height:1.7;">{{ $task->description }}</div>
                        </div>
                    @endif

                    {{-- Quick status change --}}
                    @can('tasks.edit')
                    <div class="mb-0">
                        <h6 class="fw-semibold text-muted mb-2">تغيير الحالة السريع</h6>
                        <div class="d-flex gap-2">
                            @foreach(['pending' => 'قيد الانتظار', 'in_progress' => 'جارٍ التنفيذ', 'completed' => 'مكتملة'] as $status => $label)
                            <button onclick="changeStatus('{{ $status }}')"
                                    class="btn btn-sm {{ $task->status === $status ? 'btn-primary' : 'btn-outline-secondary' }}">
                                {{ $label }}
                            </button>
                            @endforeach
                        </div>
                    </div>
                    @endcan

                </div>
            </div>

            {{-- Subtasks --}}
            @if($task->subtasks->isNotEmpty())
            <div class="card shadow-sm mb-3">
                <div class="card-header fw-semibold">
                    <i class="fas fa-project-diagram me-2"></i>
                    المهام الفرعية ({{ $task->subtasks->count() }})
                </div>
                <div class="list-group list-group-flush">
                    @foreach($task->subtasks as $sub)
                    <div class="list-group-item d-flex justify-content-between align-items-center">
                        <div>
                            <a href="{{ route('tasks.show', $sub) }}" class="text-decoration-none fw-semibold">
                                {{ $sub->title }}
                            </a>
                            <div class="small text-muted">
                                {{ $sub->users->pluck('name')->join(', ') ?: '—' }}
                            </div>
                        </div>
                        <div class="d-flex gap-2 align-items-center">
                            <span class="badge bg-{{ $sub->priority_color }}">{{ $sub->priority_label }}</span>
                            <span class="badge bg-{{ $sub->status_color }}">{{ $sub->status_label }}</span>
                        </div>
                    </div>
                    @endforeach
                </div>
            </div>
            @endif

            {{-- Comments Section --}}
            <div class="card shadow-sm">
                <div class="card-header fw-semibold">
                    <i class="fas fa-comments me-2"></i>
                    التعليقات ({{ $task->comments->count() }})
                </div>
                <div class="card-body">
                    @forelse($task->comments as $comment)
                    <div class="d-flex gap-3 mb-3 pb-3 border-bottom">
                        <div class="flex-shrink-0">
                            <div class="rounded-circle bg-primary text-white d-flex align-items-center justify-content-center"
                                 style="width:40px; height:40px; font-size:16px; font-weight:700;">
                                {{ mb_substr($comment->user->name, 0, 1) }}
                            </div>
                        </div>
                        <div class="flex-grow-1">
                            <div class="d-flex justify-content-between align-items-start">
                                <div>
                                    <span class="fw-semibold">{{ $comment->user->name }}</span>
                                    <span class="text-muted small ms-2">{{ $comment->created_at->diffForHumans() }}</span>
                                </div>
                                @if($comment->user_id === auth()->id() || auth()->user()->hasRole('admin'))
                                <form action="{{ route('tasks.comments.destroy', [$task, $comment]) }}" method="POST"
                                      onsubmit="return confirm('حذف التعليق؟')">
                                    @csrf @method('DELETE')
                                    <button class="btn btn-sm btn-link text-danger p-0">
                                        <i class="fas fa-trash-alt"></i>
                                    </button>
                                </form>
                                @endif
                            </div>
                            <div class="mt-1" style="white-space:pre-wrap;">{{ $comment->comment }}</div>
                        </div>
                    </div>
                    @empty
                    <div class="text-center text-muted py-3">
                        <i class="fas fa-comment-slash fa-2x mb-2 d-block opacity-25"></i>
                        لا توجد تعليقات بعد
                    </div>
                    @endforelse

                    {{-- Add comment form --}}
                    <form action="{{ route('tasks.comments.store', $task) }}" method="POST" class="mt-3">
                        @csrf
                        <label class="form-label fw-semibold">إضافة تعليق</label>
                        <textarea name="comment" rows="3"
                                  class="form-control @error('comment') is-invalid @enderror"
                                  placeholder="اكتب تعليقك هنا..." required>{{ old('comment') }}</textarea>
                        @error('comment') <div class="invalid-feedback">{{ $message }}</div> @enderror
                        <button type="submit" class="btn btn-primary btn-sm mt-2">
                            <i class="fas fa-paper-plane me-1"></i> إرسال
                        </button>
                    </form>
                </div>
            </div>

        </div>

        {{-- Sidebar Info --}}
        <div class="col-lg-4">

            <div class="card shadow-sm mb-3">
                <div class="card-header fw-semibold">
                    <i class="fas fa-info-circle me-2"></i> تفاصيل المهمة
                </div>
                <div class="card-body">
                    <table class="table table-sm table-borderless mb-0">
                        <tr>
                            <td class="text-muted small fw-semibold">القسم</td>
                            <td>{{ $task->department?->name ?? '—' }}</td>
                        </tr>
                        <tr>
                            <td class="text-muted small fw-semibold">تاريخ الاستحقاق</td>
                            <td>
                                @if($task->due_date)
                                    <span class="{{ $task->is_overdue ? 'text-danger fw-bold' : '' }}">
                                        {{ $task->due_date->format('Y/m/d') }}
                                    </span>
                                @else
                                    <span class="text-muted">—</span>
                                @endif
                            </td>
                        </tr>
                        <tr>
                            <td class="text-muted small fw-semibold">أنشئت بواسطة</td>
                            <td>{{ $task->creator?->name ?? '—' }}</td>
                        </tr>
                        <tr>
                            <td class="text-muted small fw-semibold">تاريخ الإنشاء</td>
                            <td>{{ $task->created_at->format('Y/m/d') }}</td>
                        </tr>
                        <tr>
                            <td class="text-muted small fw-semibold">آخر تحديث</td>
                            <td>{{ $task->updated_at->diffForHumans() }}</td>
                        </tr>
                    </table>
                </div>
            </div>

            {{-- Assigned Users --}}
            <div class="card shadow-sm">
                <div class="card-header fw-semibold">
                    <i class="fas fa-users me-2"></i> المعينون ({{ $task->users->count() }})
                </div>
                <div class="list-group list-group-flush">
                    @forelse($task->users as $user)
                    <div class="list-group-item d-flex align-items-center gap-2">
                        <div class="rounded-circle bg-secondary text-white d-flex align-items-center justify-content-center flex-shrink-0"
                             style="width:32px; height:32px; font-size:14px; font-weight:700;">
                            {{ mb_substr($user->name, 0, 1) }}
                        </div>
                        <div>
                            <div class="fw-semibold small">{{ $user->name }}</div>
                            <div class="text-muted" style="font-size:11px;">{{ $user->email }}</div>
                        </div>
                    </div>
                    @empty
                    <div class="list-group-item text-center text-muted small py-3">
                        لم يُعين أحد بعد
                    </div>
                    @endforelse
                </div>
            </div>

        </div>
    </div>

    @can('tasks.edit')
    <script>
    const csrfToken = document.querySelector('meta[name="csrf-token"]').content;

    function changeStatus(newStatus) {
        fetch('/tasks/{{ $task->id }}/status', {
            method: 'PATCH',
            headers: {
                'Content-Type': 'application/json',
                'X-CSRF-TOKEN': csrfToken,
            },
            body: JSON.stringify({ status: newStatus }),
        })
        .then(res => res.json())
        .then(() => window.location.reload());
    }
    </script>
    @endcan
</x-app-layout>
