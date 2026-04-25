<?php

namespace App\Http\Controllers;

use App\Models\Department;
use Illuminate\Http\Request;

class DepartmentController extends Controller
{
    public function index()
    {
        $departments = Department::withCount('employees')->orderByDesc('created_at')->paginate(15);
        return view('departments.index', compact('departments'));
    }

    public function create()
    {
        return view('departments.create');
    }

    public function store(Request $request)
    {
        $request->validate([
            'name'        => 'required|string|max:100|unique:departments,name',
            'description' => 'nullable|string',
        ]);

        Department::create($request->only('name', 'description'));

        return redirect()->route('departments.index')
            ->with('success', 'تم إضافة القسم بنجاح');
    }

    public function show(Department $department)
    {
        $department->loadCount('employees');
        $department->load([
            'employees' => fn($q) => $q->with('activeLoan')->orderBy('name'),
        ]);
        return view('departments.show', compact('department'));
    }

    public function edit(Department $department)
    {
        return view('departments.edit', compact('department'));
    }

    public function update(Request $request, Department $department)
    {
        $request->validate([
            'name'        => 'required|string|max:100|unique:departments,name,' . $department->id,
            'description' => 'nullable|string',
        ]);

        $department->update($request->only('name', 'description'));

        return redirect()->route('departments.index')
            ->with('success', 'تم تعديل القسم بنجاح');
    }

    public function destroy(Department $department)
    {
        if ($department->employees()->count() > 0) {
            return redirect()->route('departments.index')
                ->with('error', 'لا يمكن حذف القسم لأنه يحتوي على موظفين');
        }

        $department->delete();

        return redirect()->route('departments.index')
            ->with('success', 'تم حذف القسم بنجاح');
    }
}