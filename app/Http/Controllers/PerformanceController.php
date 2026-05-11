<?php

namespace App\Http\Controllers;

use App\Models\Employee;
use App\Models\PerformanceReview;
use Illuminate\Http\Request;

class PerformanceController extends Controller
{
    public function index(Request $request)
    {
        $query = PerformanceReview::with(['employee.department', 'reviewer'])->latest();

        if ($request->filled('employee_id')) $query->where('employee_id', $request->employee_id);
        if ($request->filled('year'))        $query->where('year', $request->year);
        if ($request->filled('week_number')) $query->where('week_number', $request->week_number);

        $reviews   = $query->paginate(20)->withQueryString();
        $employees = Employee::active()->orderBy('name')->get(['id', 'name']);
        $years     = range(now()->year, now()->year - 3);
        $weeks     = range(1, 52);

        return view('performance.index', compact('reviews', 'employees', 'years', 'weeks'));
    }

    public function create()
    {
        $employees   = Employee::active()->orderBy('name')->get(['id', 'name']);
        $years       = range(now()->year, now()->year - 1);
        $currentWeek = now()->weekOfYear;
        return view('performance.create', compact('employees', 'years', 'currentWeek'));
    }

    public function store(Request $request)
    {
        $data = $request->validate([
            'employee_id'  => 'required|exists:employees,id',
            'year'         => 'required|integer|min:2020|max:2100',
            'week_number'  => 'required|integer|min:1|max:52',
            'punctuality'  => 'required|integer|min:1|max:5',
            'quality'      => 'required|integer|min:1|max:5',
            'productivity' => 'required|integer|min:1|max:5',
            'teamwork'     => 'required|integer|min:1|max:5',
            'communication'=> 'required|integer|min:1|max:5',
            'strengths'    => 'nullable|string|max:1000',
            'improvements' => 'nullable|string|max:1000',
            'notes'        => 'nullable|string|max:1000',
            'status'       => 'required|in:draft,final',
        ]);

        $data['reviewer_id'] = auth()->id();

        $exists = PerformanceReview::where('employee_id', $data['employee_id'])
            ->where('year', $data['year'])
            ->where('week_number', $data['week_number'])
            ->exists();

        if ($exists) {
            return back()->withInput()
                ->with('error', 'يوجد تقييم مسبق لهذا الموظف في نفس الأسبوع.');
        }

        PerformanceReview::create($data);

        return redirect()->route('performance.index')
            ->with('success', 'تم حفظ التقييم بنجاح ✅');
    }

    public function show(PerformanceReview $performance)
    {
        $performance->load(['employee.department', 'reviewer']);
        return view('performance.show', compact('performance'));
    }

    public function edit(PerformanceReview $performance)
    {
        $employees = Employee::active()->orderBy('name')->get(['id', 'name']);
        $years     = range(now()->year, now()->year - 1);
        return view('performance.edit', compact('performance', 'employees', 'years'));
    }

    public function update(Request $request, PerformanceReview $performance)
    {
        $data = $request->validate([
            'punctuality'  => 'required|integer|min:1|max:5',
            'quality'      => 'required|integer|min:1|max:5',
            'productivity' => 'required|integer|min:1|max:5',
            'teamwork'     => 'required|integer|min:1|max:5',
            'communication'=> 'required|integer|min:1|max:5',
            'strengths'    => 'nullable|string|max:1000',
            'improvements' => 'nullable|string|max:1000',
            'notes'        => 'nullable|string|max:1000',
            'status'       => 'required|in:draft,final',
        ]);

        $performance->update($data);

        return redirect()->route('performance.show', $performance)
            ->with('success', 'تم تحديث التقييم بنجاح ✅');
    }

    public function destroy(PerformanceReview $performance)
    {
        $performance->delete();
        return redirect()->route('performance.index')
            ->with('success', 'تم حذف التقييم');
    }

    public function export(Request $request)
    {
        $query = PerformanceReview::with(['employee.department', 'reviewer'])->latest();

        if ($request->filled('employee_id')) $query->where('employee_id', $request->employee_id);
        if ($request->filled('year'))        $query->where('year', $request->year);
        if ($request->filled('week_number')) $query->where('week_number', $request->week_number);

        $reviews  = $query->get();
        $filename = 'تقييم_الأداء_' . now()->format('Y-m-d') . '.xlsx';

        return response()->streamDownload(function () use ($reviews) {
            $writer = new \OpenSpout\Writer\XLSX\Writer();
            $writer->openToFile('php://output');

            $writer->addRow(\OpenSpout\Common\Entity\Row::fromValues([
                'الموظف', 'القسم', 'السنة', 'الأسبوع', 'تاريخ الأسبوع',
                'الانضباط', 'الجودة', 'الإنتاجية', 'العمل الجماعي', 'التواصل',
                'المجموع', 'التقدير', 'الحالة',
            ]));

            foreach ($reviews as $r) {
                $writer->addRow(\OpenSpout\Common\Entity\Row::fromValues([
                    $r->employee->name ?? '',
                    $r->employee->department->name ?? '',
                    $r->year,
                    'الأسبوع ' . $r->week_number,
                    $r->week_label,
                    $r->punctuality,
                    $r->quality,
                    $r->productivity,
                    $r->teamwork,
                    $r->communication,
                    number_format($r->overall_score, 2),
                    $r->score_label,
                    $r->status === 'final' ? 'نهائي' : 'مسودة',
                ]));
            }
            $writer->close();
        }, $filename, ['Content-Type' => 'application/vnd.openxmlformats-officedocument.spreadsheetml.sheet']);
    }
}
