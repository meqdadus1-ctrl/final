<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Models\Event;
use Illuminate\Http\Request;

class EventController extends Controller
{
    public function index(Request $request)
    {
        $query = Event::query();

        if ($request->filled('start')) $query->where('start_date', '>=', $request->start);
        if ($request->filled('end'))   $query->where('start_date', '<=', $request->end);

        $events = $query->orderBy('start_date')->paginate(50);

        return response()->json([
            'data' => $events->items(),
            'meta' => [
                'current_page' => $events->currentPage(),
                'last_page'    => $events->lastPage(),
                'total'        => $events->total(),
            ],
        ]);
    }

    public function store(Request $request)
    {
        $request->validate([
            'title'       => 'required|string|max:255',
            'description' => 'nullable|string',
            'start_date'  => 'required|date',
            'end_date'    => 'nullable|date|after_or_equal:start_date',
            'color'       => 'nullable|string|max:20',
            'all_day'     => 'nullable|boolean',
        ]);

        $event = Event::create([
            ...$request->all(),
            'created_by' => $request->user()->id,
            'all_day'    => $request->boolean('all_day'),
        ]);

        return response()->json(['data' => $event, 'message' => 'Event created successfully'], 201);
    }
}
