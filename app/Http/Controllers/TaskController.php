<?php

namespace App\Http\Controllers;

use App\Models\Department;
use App\Models\Task;
use App\Models\User;
use App\Notifications\TaskAssigned;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;

class TaskController extends Controller
{
    // عرض قائمة المهام
    public function index(Request $request)
    {
        $query = Task::with(['users', 'department'])
            ->withCount('comments')
            ->parentOnly();

        if ($request->filled('search')) {
            $term = trim($request->search);
            $query->where('title', 'like', "%{$term}%");
        }

        if ($request->filled('status')) {
            $query->where('status', $request->status);
        }

        if ($request->filled('priority')) {
            $query->where('priority', $request->priority);
        }

        if ($request->filled('department_id')) {
            $query->where('department_id', $request->department_id);
        }

        // الموظفون يرون المهام المسندة إليهم فقط
        if (!auth()->user()->can('tasks.edit')) {
            $query->whereHas('users', fn($q) => $q->where('users.id', auth()->id()));
        }

        $tasks       = $query->orderByDesc('created_at')->paginate(20)->withQueryString();
        $departments = Department::orderBy('name')->get();

        return view('tasks.index', compact('tasks', 'departments'));
    }

    // عرض لوحة كانبان
    public function kanban()
    {
        $base = fn() => Task::with(['users', 'department'])->withCount('comments')->parentOnly();

        if (!auth()->user()->can('tasks.edit')) {
            $filterUser = fn($q) => $q->whereHas('users', fn($u) => $u->where('users.id', auth()->id()));
            $pending    = $base()->tap($filterUser)->pending()->orderByDesc('created_at')->get();
            $inProgress = $base()->tap($filterUser)->inProgress()->orderByDesc('created_at')->get();
            $completed  = $base()->tap($filterUser)->completed()->orderByDesc('created_at')->get();
        } else {
            $pending    = $base()->pending()->orderByDesc('created_at')->get();
            $inProgress = $base()->inProgress()->orderByDesc('created_at')->get();
            $completed  = $base()->completed()->orderByDesc('created_at')->get();
        }

        return view('tasks.kanban', compact('pending', 'inProgress', 'completed'));
    }

    // نموذج إنشاء مهمة جديدة
    public function create()
    {
        $departments = Department::orderBy('name')->get();
        $users       = User::orderBy('name')->get();
        $parentTasks = Task::parentOnly()->orderBy('title')->get();

        return view('tasks.create', compact('departments', 'users', 'parentTasks'));
    }

    // حفظ المهمة الجديدة
    public function store(Request $request)
    {
        $data = $request->validate([
            'title'         => 'required|string|max:255',
            'description'   => 'nullable|string',
            'priority'      => 'required|in:low,medium,high',
            'status'        => 'required|in:pending,in_progress,completed',
            'due_date'      => 'nullable|date',
            'department_id' => 'nullable|exists:departments,id',
            'parent_id'     => 'nullable|exists:tasks,id',
            'user_ids'      => 'nullable|array',
            'user_ids.*'    => 'exists:users,id',
        ]);

        $userIds          = $request->input('user_ids', []);
        $data['created_by'] = auth()->id();
        unset($data['user_ids']);

        DB::transaction(function () use ($data, $userIds) {
            $task = Task::create($data);
            $task->users()->sync($userIds);

            if (!empty($userIds)) {
                foreach (User::whereIn('id', $userIds)->get() as $user) {
                    try {
                        $user->notify(new TaskAssigned($task));
                    } catch (\Exception) {}
                }
            }
        });

        return redirect()->route('tasks.index')->with('success', 'تم إنشاء المهمة بنجاح');
    }

    // عرض تفاصيل المهمة
    public function show(Task $task)
    {
        if (!auth()->user()->can('tasks.edit')) {
            abort_unless(
                $task->users()->where('users.id', auth()->id())->exists(),
                403,
                'ليس لديك صلاحية لعرض هذه المهمة.'
            );
        }

        $task->load(['users', 'comments.user', 'department', 'parent', 'subtasks.users', 'creator']);

        return view('tasks.show', compact('task'));
    }

    // نموذج تعديل المهمة
    public function edit(Task $task)
    {
        $departments = Department::orderBy('name')->get();
        $users       = User::orderBy('name')->get();
        $parentTasks = Task::parentOnly()->where('id', '!=', $task->id)->orderBy('title')->get();

        $task->load('users');

        return view('tasks.edit', compact('task', 'departments', 'users', 'parentTasks'));
    }

    // تحديث المهمة
    public function update(Request $request, Task $task)
    {
        $data = $request->validate([
            'title'         => 'required|string|max:255',
            'description'   => 'nullable|string',
            'priority'      => 'required|in:low,medium,high',
            'status'        => 'required|in:pending,in_progress,completed',
            'due_date'      => 'nullable|date',
            'department_id' => 'nullable|exists:departments,id',
            'parent_id'     => 'nullable|exists:tasks,id',
            'user_ids'      => 'nullable|array',
            'user_ids.*'    => 'exists:users,id',
        ]);

        $userIds         = $request->input('user_ids', []);
        $previousUserIds = $task->users()->pluck('users.id')->toArray();
        unset($data['user_ids']);

        DB::transaction(function () use ($task, $data, $userIds, $previousUserIds) {
            $task->update($data);
            $task->users()->sync($userIds);

            $newUserIds = array_diff($userIds, $previousUserIds);
            if (!empty($newUserIds)) {
                foreach (User::whereIn('id', $newUserIds)->get() as $user) {
                    try {
                        $user->notify(new TaskAssigned($task));
                    } catch (\Exception) {}
                }
            }
        });

        return redirect()->route('tasks.show', $task)->with('success', 'تم تحديث المهمة بنجاح');
    }

    // حذف المهمة
    public function destroy(Task $task)
    {
        $task->delete();
        return redirect()->route('tasks.index')->with('success', 'تم حذف المهمة بنجاح');
    }

    // تحديث الحالة عبر AJAX (كانبان)
    public function updateStatus(Request $request, Task $task)
    {
        $request->validate(['status' => 'required|in:pending,in_progress,completed']);
        $task->update(['status' => $request->status]);

        return response()->json(['success' => true, 'status_label' => $task->status_label]);
    }

    // تحديث تاريخ الاستحقاق عبر AJAX (تقويم)
    public function updateDueDate(Request $request, Task $task)
    {
        $request->validate(['due_date' => 'required|date']);
        $task->update(['due_date' => $request->due_date]);

        return response()->json(['success' => true]);
    }

    // تقارير المهام
    public function report()
    {
        $totalTasks = Task::parentOnly()->count();
        $completed  = Task::parentOnly()->completed()->count();
        $pending    = Task::parentOnly()->pending()->count();
        $inProgress = Task::parentOnly()->inProgress()->count();
        $overdue    = Task::parentOnly()->overdue()->count();

        $tasksByUser = DB::table('task_user')
            ->join('users', 'task_user.user_id', '=', 'users.id')
            ->join('tasks', 'task_user.task_id', '=', 'tasks.id')
            ->select(
                'users.id',
                'users.name',
                DB::raw('COUNT(tasks.id) as total_tasks'),
                DB::raw('SUM(tasks.status = "completed") as completed_tasks'),
                DB::raw('SUM(tasks.status = "pending") as pending_tasks'),
                DB::raw('SUM(tasks.status = "in_progress") as inprogress_tasks'),
            )
            ->whereNull('tasks.parent_id')
            ->groupBy('users.id', 'users.name')
            ->orderByDesc('total_tasks')
            ->get();

        $tasksByDepartment = DB::table('tasks')
            ->join('departments', 'tasks.department_id', '=', 'departments.id')
            ->select(
                'departments.id',
                'departments.name',
                DB::raw('COUNT(tasks.id) as total_tasks'),
                DB::raw('SUM(tasks.status = "completed") as completed_tasks'),
                DB::raw('SUM(tasks.status = "pending") as pending_tasks'),
                DB::raw('SUM(tasks.status = "in_progress") as inprogress_tasks'),
            )
            ->whereNull('tasks.parent_id')
            ->groupBy('departments.id', 'departments.name')
            ->orderByDesc('total_tasks')
            ->get();

        $overdueTasks = Task::with(['users', 'department'])
            ->parentOnly()
            ->overdue()
            ->orderBy('due_date')
            ->get();

        return view('tasks.report', compact(
            'totalTasks', 'completed', 'pending', 'inProgress', 'overdue',
            'tasksByUser', 'tasksByDepartment', 'overdueTasks'
        ));
    }
}
