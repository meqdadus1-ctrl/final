<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Models\Task;
use App\Models\TaskComment;
use Illuminate\Http\Request;

class TaskController extends Controller
{
    public function index(Request $request)
    {
        $user  = $request->user();
        $query = Task::with(['users', 'department'])
            ->withCount('comments')
            ->whereNull('parent_id');

        if (!$user->can('tasks.edit')) {
            $query->whereHas('users', fn($q) => $q->where('users.id', $user->id));
        }

        if ($request->filled('status'))   $query->where('status', $request->status);
        if ($request->filled('priority')) $query->where('priority', $request->priority);

        $tasks = $query->orderByDesc('created_at')->get();

        return response()->json([
            'success' => true,
            'data'    => $tasks->map(fn($t) => $this->formatTask($t))->values(),
        ]);
    }

    public function show(Request $request, Task $task)
    {
        $user = $request->user();

        if (!$user->can('tasks.edit')) {
            if (!$task->users()->where('users.id', $user->id)->exists()) {
                return response()->json(['success' => false, 'message' => 'غير مصرح'], 403);
            }
        }

        $task->loadCount('comments');
        $task->load(['users', 'comments.user', 'department', 'subtasks', 'creator']);

        return response()->json([
            'success' => true,
            'data'    => $this->formatTask($task, true),
        ]);
    }

    public function addComment(Request $request, Task $task)
    {
        $request->validate(['comment' => 'required|string|max:2000']);

        $user = $request->user();

        if (!$user->can('tasks.edit')) {
            if (!$task->users()->where('users.id', $user->id)->exists()) {
                return response()->json(['success' => false, 'message' => 'غير مصرح'], 403);
            }
        }

        TaskComment::create([
            'task_id' => $task->id,
            'user_id' => $user->id,
            'comment' => $request->comment,
        ]);

        return response()->json([
            'success' => true,
            'message' => 'تم إضافة التعليق بنجاح.',
        ]);
    }

    public function store(Request $request)
    {
        $request->validate([
            'title'         => 'required|string|max:255',
            'description'   => 'nullable|string',
            'priority'      => 'required|in:low,medium,high',
            'status'        => 'required|in:pending,in_progress,completed',
            'due_date'      => 'nullable|date',
            'department_id' => 'nullable|exists:departments,id',
            'user_ids'      => 'nullable|array',
            'user_ids.*'    => 'exists:users,id',
        ]);

        $task = Task::create([
            ...$request->except('user_ids'),
            'created_by' => $request->user()->id,
        ]);
        $task->users()->sync($request->input('user_ids', []));
        $task->load(['users', 'department']);

        return response()->json([
            'success' => true,
            'data'    => $this->formatTask($task),
            'message' => 'تم إنشاء المهمة بنجاح.',
        ], 201);
    }

    public function update(Request $request, Task $task)
    {
        $request->validate([
            'title'       => 'sometimes|required|string|max:255',
            'description' => 'nullable|string',
            'priority'    => 'sometimes|required|in:low,medium,high',
            'status'      => 'sometimes|required|in:pending,in_progress,completed',
            'due_date'    => 'nullable|date',
            'user_ids'    => 'nullable|array',
            'user_ids.*'  => 'exists:users,id',
        ]);

        $task->update($request->except('user_ids'));

        if ($request->has('user_ids')) {
            $task->users()->sync($request->input('user_ids', []));
        }

        return response()->json([
            'success' => true,
            'data'    => $this->formatTask($task->fresh(['users', 'department'])),
            'message' => 'تم تحديث المهمة.',
        ]);
    }

    public function destroy(Task $task)
    {
        $task->delete();
        return response()->json(['success' => true, 'message' => 'تم حذف المهمة.']);
    }

    private function formatTask(Task $task, bool $detailed = false): array
    {
        $data = [
            'id'             => $task->id,
            'title'          => $task->title,
            'description'    => $task->description,
            'status'         => $task->status,
            'priority'       => $task->priority,
            'due_date'       => $task->due_date?->format('Y-m-d'),
            'department'     => $task->department?->name,
            'comments_count' => $task->comments_count ?? 0,
            'assigned_users' => $task->relationLoaded('users')
                ? $task->users->map(fn($u) => ['id' => $u->id, 'name' => $u->name])->values()->toArray()
                : [],
            'comments'       => [],
        ];

        if ($detailed && $task->relationLoaded('comments')) {
            $data['comments'] = $task->comments->map(fn($c) => [
                'id'         => $c->id,
                'comment'    => $c->comment,
                'user_name'  => $c->user?->name ?? 'مجهول',
                'created_at' => $c->created_at->toIso8601String(),
                'time'       => $c->created_at->format('h:i A'),
            ])->values()->toArray();
        }

        return $data;
    }
}
