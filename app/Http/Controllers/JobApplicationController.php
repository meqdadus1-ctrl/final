<?php

namespace App\Http\Controllers;

use App\Models\JobApplication;
use App\Models\Department;
use App\Models\Employee;
use Illuminate\Http\Request;

class JobApplicationController extends Controller
{
    public function index(Request $request)
    {
        $query = JobApplication::with('department');

        if ($request->status) {
            $query->where('status', $request->status);
        }
        if ($request->department_id) {
            $query->where('department_id', $request->department_id);
        }

        $applications = $query->latest()->paginate(20);
        $departments  = Department::all();

        return view('jobs.index', compact('applications', 'departments'));
    }

    public function create()
    {
        $departments = Department::all();
        return view('jobs.create', compact('departments'));
    }

    public function store(Request $request)
    {
        $request->validate([
            'full_name'        => 'required|string|max:150',
            'mobile'           => 'required|string|max:20',
            'email'            => 'nullable|email',
            'national_id'      => 'nullable|string|max:20',
            'department_id'    => 'nullable|exists:departments,id',
            'position'         => 'required|string|max:100',
            'experience_years' => 'nullable|integer|min:0',
            'cv'               => 'nullable|file|mimes:pdf,doc,docx|max:5120',
            'notes'            => 'nullable|string',
        ]);

        $cvPath = null;
        if ($request->hasFile('cv')) {
            $cvPath = $request->file('cv')->store('cvs', 'public');
        }

        JobApplication::create([
            'full_name'        => $request->full_name,
            'mobile'           => $request->mobile,
            'email'            => $request->email,
            'national_id'      => $request->national_id,
            'department_id'    => $request->department_id,
            'position'         => $request->position,
            'experience_years' => $request->experience_years ?? 0,
            'cv_path'          => $cvPath,
            'notes'            => $request->notes,
            'status'           => 'new',
        ]);

        return redirect()->route('jobs.index')->with('success', 'تم إضافة طلب التوظيف بنجاح');
    }

    public function show(JobApplication $job)
    {
        return view('jobs.show', compact('job'));
    }

    public function updateStatus(Request $request, JobApplication $job)
    {
        $request->validate([
            'status'         => 'required|in:new,reviewing,interview,accepted,rejected',
            'reviewer_notes' => 'nullable|string',
        ]);

        $job->update([
            'status'         => $request->status,
            'reviewed_by'    => auth()->id(),
            'reviewed_at'    => now(),
            'reviewer_notes' => $request->reviewer_notes,
        ]);

        return back()->with('success', 'تم تحديث حالة الطلب');
    }

    public function destroy(JobApplication $job)
    {
        $job->delete();
        return redirect()->route('jobs.index')->with('success', 'تم حذف الطلب');
    }
}