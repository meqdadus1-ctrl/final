<?php

namespace App\Http\Controllers;

use App\Models\Task;
use App\Models\TaskComment;
use Illuminate\Http\Request;

class TaskCommentController extends Controller
{
    // إضافة تعليق على المهمة
    public function store(Request $request, Task $task)
    {
        $request->validate([
            'comment' => 'required|string|max:2000',
        ]);

        // التحقق من إمكانية الوصول للمهمة
        if (!auth()->user()->can('tasks.edit')) {
            abort_unless(
                $task->users()->where('users.id', auth()->id())->exists(),
                403
            );
        }

        $task->comments()->create([
            'user_id' => auth()->id(),
            'comment' => $request->comment,
        ]);

        return back()->with('success', 'تم إضافة التعليق بنجاح');
    }

    // حذف تعليق
    public function destroy(Task $task, TaskComment $comment)
    {
        abort_unless(
            $comment->user_id === auth()->id() || auth()->user()->hasRole('admin'),
            403
        );

        $comment->delete();

        return back()->with('success', 'تم حذف التعليق');
    }
}
