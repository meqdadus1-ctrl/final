<?php

namespace App\Http\Controllers;

use App\Models\LeaveRequest;
use App\Models\LeaveType;
use App\Models\LeaveBalance;
use App\Models\Employee;
use Illuminate\Http\Request;
use Carbon\Carbon;

class LeaveController extends Controller
{
    public function index()
    {
        $requests = LeaveRequest::with(['employee', 'leaveType'])->latest()->paginate(20);
        return view('leaves.index', compact('requests'));
    }

    public function create()
    {
        $employees   = Employee::active()->orderBy('name')->get();
        $leaveTypes  = LeaveType::all();
        return view('leaves.create', compact('employees', 'leaveTypes'));
    }

    public function store(Request $request)
    {
        $request->validate([
            'employee_id'   => 'required|exists:employees,id',
            'leave_type_id' => 'required|exists:leave_types,id',
            'start_date'    => 'required|date',
            'end_date'      => 'required|date|after_or_equal:start_date',
            'reason'        => 'nullable|string',
        ]);

        $start     = Carbon::parse($request->start_date);
        $end       = Carbon::parse($request->end_date);
        $totalDays = $start->diffInDays($end) + 1;

        LeaveRequest::create([
            'employee_id'   => $request->employee_id,
            'leave_type_id' => $request->leave_type_id,
            'start_date'    => $request->start_date,
            'end_date'      => $request->end_date,
            'total_days'    => $totalDays,
            'reason'        => $request->reason,
            'status'        => 'pending',
        ]);

        return redirect()->route('leaves.index')->with('success', 'تم تقديم طلب الإجازة');
    }

    public function show(LeaveRequest $leave)
    {
        $leave->load(['employee.department', 'leaveType', 'reviewedBy']);
        return view('leaves.show', compact('leave'));
    }

    public function approve(LeaveRequest $leave)
    {
        $leave->update([
            'status'      => 'approved',
            'reviewed_by' => auth()->id(),
            'reviewed_at' => now(),
        ]);

        // تحديث رصيد الإجازات
        $balance = LeaveBalance::firstOrCreate(
            [
                'employee_id'   => $leave->employee_id,
                'leave_type_id' => $leave->leave_type_id,
                'year'          => now()->year,
            ],
            ['entitled_days' => 30, 'used_days' => 0]
        );
        $balance->increment('used_days', $leave->total_days);

        return back()->with('success', 'تمت الموافقة على الإجازة ✅');
    }

    public function reject(Request $request, LeaveRequest $leave)
    {
        $leave->update([
            'status'           => 'rejected',
            'reviewed_by'      => auth()->id(),
            'reviewed_at'      => now(),
            'notes'            => $request->notes,
            'rejection_reason' => $request->notes,
        ]);

        return back()->with('success', 'تم رفض طلب الإجازة');
    }

    // ── حذف طلب إجازة (pending فقط)
    public function destroy(LeaveRequest $leave)
    {
        if ($leave->status !== 'pending') {
            return back()->with('error', 'لا يمكن حذف إجازة تمت مراجعتها');
        }

        $leave->delete();
        return redirect()->route('leaves.index')->with('success', 'تم حذف طلب الإجازة');
    }

    public function balances()
    {
        $employees = Employee::active()
            ->with(['leaveBalances' => function($q) {
                $q->where('year', now()->year)->with('leaveType');
            }])->orderBy('name')->get();

        return view('leaves.balances', compact('employees'));
    }

    public function types()
    {
        $types = LeaveType::all();
        return view('leaves.types', compact('types'));
    }

    public function storeType(Request $request)
    {
        $request->validate([
            'name'            => 'required|string|max:100',
            'max_days_yearly' => 'nullable|integer|min:1',
            'is_paid'         => 'nullable|boolean',
        ]);

        LeaveType::create([
            'name'            => $request->name,
            'max_days_yearly' => $request->max_days_yearly,
            // الـ checkbox يرسل 1 أو 0 — نحوّله لـ boolean صريح
            'is_paid'         => $request->input('is_paid', 0) == 1,
        ]);

        return back()->with('success', 'تم إضافة نوع الإجازة');
    }

    // ── حذف نوع إجازة
    public function destroyType(LeaveType $leaveType)
    {
        if ($leaveType->requests()->exists()) {
            return back()->with('error', 'لا يمكن حذف هذا النوع لوجود طلبات مرتبطة به');
        }

        $leaveType->delete();
        return back()->with('success', 'تم حذف نوع الإجازة');
    }
}
