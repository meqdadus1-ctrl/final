<?php

namespace App\Http\Controllers;

use App\Models\Event;
use Illuminate\Http\Request;

class EventController extends Controller
{
    // نموذج إنشاء حدث
    public function create()
    {
        return view('events.create');
    }

    // حفظ الحدث
    public function store(Request $request)
    {
        $data = $request->validate([
            'title'       => 'required|string|max:255',
            'description' => 'nullable|string',
            'start_date'  => 'required|date',
            'end_date'    => 'nullable|date|after_or_equal:start_date',
            'color'       => 'nullable|string|max:20',
            'all_day'     => 'nullable|boolean',
        ]);

        $data['created_by'] = auth()->id();
        $data['all_day']    = $request->boolean('all_day');

        Event::create($data);

        return redirect()->route('calendar.index')->with('success', 'تم إضافة الحدث بنجاح');
    }

    // نموذج تعديل الحدث
    public function edit(Event $event)
    {
        return view('events.edit', compact('event'));
    }

    // تحديث الحدث
    public function update(Request $request, Event $event)
    {
        $data = $request->validate([
            'title'       => 'required|string|max:255',
            'description' => 'nullable|string',
            'start_date'  => 'required|date',
            'end_date'    => 'nullable|date|after_or_equal:start_date',
            'color'       => 'nullable|string|max:20',
            'all_day'     => 'nullable|boolean',
        ]);

        $data['all_day'] = $request->boolean('all_day');
        $event->update($data);

        return redirect()->route('calendar.index')->with('success', 'تم تحديث الحدث بنجاح');
    }

    // حذف الحدث
    public function destroy(Event $event)
    {
        $event->delete();
        return redirect()->route('calendar.index')->with('success', 'تم حذف الحدث بنجاح');
    }

    // تحديث تواريخ الحدث عبر AJAX (تقويم)
    public function updateDates(Request $request, Event $event)
    {
        $request->validate([
            'start_date' => 'required|date',
            'end_date'   => 'nullable|date',
        ]);

        $event->update([
            'start_date' => $request->start_date,
            'end_date'   => $request->end_date,
        ]);

        return response()->json(['success' => true]);
    }
}
