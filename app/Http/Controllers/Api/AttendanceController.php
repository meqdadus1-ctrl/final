<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Models\Attendance;
use App\Models\Employee;
use Illuminate\Http\Request;

class AttendanceController extends Controller
{
    /**
     * GET /api/v1/attendance
     * الحصول على سجل الحضور للموظف الحالي
     */
    public function index(Request $request)
    {
        try {
            // الحصول على الموظف من المستخدم المصرح
            $employee = Employee::where('user_id', $request->user()->id)->firstOrFail();

            $query = Attendance::where('employee_id', $employee->id);

            // فلتر اختياري بالتاريخ
            if ($request->date) {
                $query->whereDate('date', $request->date);
            }

            // فلتر اختياري بالحالة
            if ($request->status) {
                $query->where('status', $request->status);
            }

            // ترتيب تنازلي حسب التاريخ
            $attendances = $query->orderByDesc('date')->get();

            // حساب الملخص
            $summary = [
                'present_days'  => $attendances->where('status', 'present')->count(),
                'absent_days'   => $attendances->where('status', 'absent')->count(),
                'late_days'     => $attendances->where('status', 'late')->count(),
                'holiday_days'  => $attendances->where('status', 'holiday')->count(),
                'total_hours'   => $attendances->sum('work_hours'),
                'total_overtime' => $attendances->sum('overtime_hours'),
            ];

            // تنسيق البيانات
            $data = $attendances->map(fn($a) => [
                'id'             => $a->id,
                'date'           => $a->date->format('Y-m-d'),
                'day_name'       => $this->getDayName($a->date),
                'check_in'       => $a->check_in,
                'check_out'      => $a->check_out,
                'hours_worked'   => (float) $a->work_hours,
                'overtime_hours' => (float) $a->overtime_hours,
                'status'         => $a->status,
                'late_minutes'   => $this->calculateLateMinutes($a),
                'leave_reason'   => $a->leave_reason,
            ]);

            return response()->json([
                'success' => true,
                'data'    => $data,
                'summary' => $summary,
            ]);

        } catch (\Exception $e) {
            return response()->json([
                'success' => false,
                'message' => 'خطأ في جلب بيانات الحضور: ' . $e->getMessage(),
            ], 500);
        }
    }

    /**
     * GET /api/v1/attendance/{id}
     * تفاصيل يوم حضور واحد
     */
    public function show(Request $request, $id)
    {
        try {
            $employee = Employee::where('user_id', $request->user()->id)->firstOrFail();

            $attendance = Attendance::where('employee_id', $employee->id)
                ->findOrFail($id);

            return response()->json([
                'success' => true,
                'data'    => [
                    'id'             => $attendance->id,
                    'date'           => $attendance->date->format('Y-m-d'),
                    'day_name'       => $this->getDayName($attendance->date),
                    'check_in'       => $attendance->check_in,
                    'check_out'      => $attendance->check_out,
                    'hours_worked'   => (float) $attendance->work_hours,
                    'overtime_hours' => (float) $attendance->overtime_hours,
                    'status'         => $attendance->status,
                    'leave_approved' => (bool) $attendance->leave_approved,
                    'leave_reason'   => $attendance->leave_reason,
                    'is_manual'      => (bool) $attendance->is_manual,
                ],
            ]);

        } catch (\Exception $e) {
            return response()->json([
                'success' => false,
                'message' => 'السجل غير موجود',
            ], 404);
        }
    }

    /**
     * دالة مساعدة: الحصول على اسم اليوم
     */
    private function getDayName($date)
    {
        $days = [
            'Sunday'    => 'الأحد',
            'Monday'    => 'الاثنين',
            'Tuesday'   => 'الثلاثاء',
            'Wednesday' => 'الأربعاء',
            'Thursday'  => 'الخميس',
            'Friday'    => 'الجمعة',
            'Saturday'  => 'السبت',
        ];

        return $days[$date->format('l')] ?? $date->format('l');
    }

    /**
     * دالة مساعدة: حساب دقائق التأخر
     */
    private function calculateLateMinutes($attendance)
    {
        if ($attendance->status !== 'late' || !$attendance->check_in) {
            return 0;
        }

        // الوقت المتوقع للحضور (مثلاً 8:00 AM)
        $expectedTime = strtotime('08:00:00');
        $actualTime   = strtotime($attendance->check_in);

        $minutes = max(0, round(($actualTime - $expectedTime) / 60));

        return (int) $minutes;
    }
}
