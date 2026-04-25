<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Models\Employee;
use App\Models\LeaveRequest;
use App\Models\LeaveType;
use Carbon\Carbon;
use Illuminate\Http\Request;

class LeaveController extends Controller
{
    /**
     * GET /api/v1/leaves
     */
    public function index(Request $request)
    {
        $employee = Employee::where('user_id', $request->user()->id)->firstOrFail();

        $leaves = LeaveRequest::with('leaveType')
            ->where('employee_id', $employee->id)
            ->orderByDesc('created_at')
            ->paginate(10);

        $data = $leaves->map(fn($l) => $this->formatLeave($l));

        return response()->json([
            'success'     => true,
            'data'        => $data,
            'leave_types' => LeaveType::orderBy('name')->get(['id', 'name']),
            'meta'        => [
                'current_page' => $leaves->currentPage(),
                'last_page'    => $leaves->lastPage(),
                'total'        => $leaves->total(),
            ],
        ]);
    }

    /**
     * POST /api/v1/leaves
     */
    public function store(Request $request)
    {
        $employee = Employee::where('user_id', $request->user()->id)->firstOrFail();

        $request->validate([
            'leave_type_id' => 'required|exists:leave_types,id',
            'start_date'    => 'required|date|after_or_equal:today',
            'end_date'      => 'required|date|after_or_equal:start_date',
            'reason'        => 'nullable|string|max:500',
        ]);

        $start     = Carbon::parse($request->start_date);
        $end       = Carbon::parse($request->end_date);
        $totalDays = $start->diffInDays($end) + 1;

        $leave = LeaveRequest::create([
            'employee_id'   => $employee->id,
            'leave_type_id' => $request->leave_type_id,
            'start_date'    => $request->start_date,
            'end_date'      => $request->end_date,
            'total_days'    => $totalDays,
            'reason'        => $request->reason,
            'status'        => 'pending',
        ]);

        // إشعار الإدارة
        \App\Models\MobileNotification::create([
            'employee_id' => $employee->id,
            'type'        => 'leave_request',
            'title'       => 'طلب إجازة جديد',
            'body'        => "الموظف {$employee->name} يطلب إجازة من {$request->start_date} إلى {$request->end_date}",
            'data'        => json_encode(['leave_id' => $leave->id]),
            'target'      => 'admin',
        ]);

        return response()->json([
            'success' => true,
            'message' => 'تم إرسال طلب الإجازة بنجاح. سيتم الرد عليك قريباً.',
            'data'    => $this->formatLeave($leave->load('leaveType')),
        ], 201);
    }

    private function formatLeave(LeaveRequest $l): array
    {
        return [
            'id'               => $l->id,
            'leave_type'       => $l->leaveType?->name,
            'start_date'       => $l->start_date?->format('Y-m-d'),
            'end_date'         => $l->end_date?->format('Y-m-d'),
            'total_days'       => (int) $l->total_days,
            'status'           => $l->status,
            'reason'           => $l->reason,
            'rejection_reason' => $l->rejection_reason ?? null,
            'created_at'       => $l->created_at->format('Y-m-d'),
        ];
    }
}
