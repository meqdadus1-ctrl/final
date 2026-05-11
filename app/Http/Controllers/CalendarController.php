<?php

namespace App\Http\Controllers;

use App\Models\Event;
use App\Models\Task;
use Illuminate\Http\Request;

class CalendarController extends Controller
{
    // عرض صفحة التقويم
    public function index()
    {
        return view('calendar.index');
    }

    // بيانات التقويم (JSON لـ FullCalendar)
    public function feed(Request $request)
    {
        $start = $request->query('start', now()->startOfMonth()->toDateString());
        $end   = $request->query('end',   now()->endOfMonth()->toDateString());

        $taskQuery = Task::with(['department'])
            ->whereNotNull('due_date')
            ->whereBetween('due_date', [$start, $end]);

        if (!auth()->user()->can('tasks.edit')) {
            $taskQuery->whereHas('users', fn($q) => $q->where('users.id', auth()->id()));
        }

        $tasks = $taskQuery->get()->map(fn($task) => [
            'id'    => 'task-' . $task->id,
            'title' => '📋 ' . $task->title,
            'start' => $task->due_date->toDateString(),
            'color' => $task->calendar_color,
            'url'   => route('tasks.show', $task->id),
            'extendedProps' => [
                'type'     => 'task',
                'task_id'  => $task->id,
                'priority' => $task->priority_label,
                'status'   => $task->status_label,
            ],
        ]);

        $events = Event::where(function ($q) use ($start, $end) {
            $q->whereBetween('start_date', [$start, $end])
              ->orWhereBetween('end_date', [$start, $end]);
        })->get()->map(fn($event) => [
            'id'     => 'event-' . $event->id,
            'title'  => $event->title,
            'start'  => $event->all_day
                ? $event->start_date->toDateString()
                : $event->start_date->toIso8601String(),
            'end'    => $event->end_date
                ? ($event->all_day
                    ? $event->end_date->toDateString()
                    : $event->end_date->toIso8601String())
                : null,
            'color'  => $event->color,
            'allDay' => $event->all_day,
            'extendedProps' => ['type' => 'event', 'event_id' => $event->id],
        ]);

        return response()->json($tasks->merge($events)->values());
    }
}
