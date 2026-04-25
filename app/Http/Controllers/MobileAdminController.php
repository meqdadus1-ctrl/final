<?php

namespace App\Http\Controllers;

use App\Models\Employee;
use App\Models\Loan;
use App\Models\LeaveRequest;
use App\Models\SalaryPayment;
use App\Models\MobileNotification;
use App\Services\FcmService;
use Illuminate\Http\Request;

class MobileAdminController extends Controller
{
    /* =====================================================
     *  INDEX — لوحة التحكم الرئيسية للموبايل
     * ===================================================== */
    public function index()
    {
        // طلبات تعديل البنك المعلّقة
        $pendingBanks = Employee::where('bank_info_pending', true)
            ->with('department')
            ->get();

        // طلبات السلف المعلّقة
        $pendingLoans = Loan::where('status', 'pending')
            ->with('employee.department')
            ->latest()
            ->get();

        // طلبات الإجازات المعلّقة
        $pendingLeaves = LeaveRequest::where('status', 'pending')
            ->with(['employee.department', 'leaveType'])
            ->latest()
            ->get();

        // طلبات كشف الحساب المعلّقة
        $pendingStatements = SalaryPayment::where('statement_requested', true)
            ->where('statement_status', 'pending')
            ->with('employee')
            ->latest()
            ->get();

        // كل الموظفين لإدارة قفل البنك
        $allEmployees = Employee::with('department')
            ->where('status', 'active')
            ->orderBy('name')
            ->get();

        $totalPending = $pendingBanks->count()
            + $pendingLoans->count()
            + $pendingLeaves->count()
            + $pendingStatements->count();

        return view('mobile.index', compact(
            'pendingBanks',
            'pendingLoans',
            'pendingLeaves',
            'pendingStatements',
            'totalPending',
            'allEmployees'
        ));
    }

    /* =====================================================
     *  البنك — اعتماد
     * ===================================================== */
    public function approveBank(Employee $employee)
    {
        $employee->update([
            'bank_type'            => $employee->pending_bank_type,
            'account_name'         => $employee->pending_account_name,
            'bank_account'         => $employee->pending_bank_account,
            'pending_bank_type'    => null,
            'pending_account_name' => null,
            'pending_bank_account' => null,
            'bank_info_pending'    => false,
            'bank_info_locked'     => true,
        ]);

        $this->notifyEmployee($employee, 'bank_approved',
            '✅ تم اعتماد بيانات البنك',
            'تم اعتماد بيانات حسابك البنكي بنجاح.'
        );

        return back()->with('success', "✅ تم اعتماد بيانات بنك {$employee->name}");
    }

    /* =====================================================
     *  البنك — رفض
     * ===================================================== */
    public function rejectBank(Request $request, Employee $employee)
    {
        $request->validate(['reason' => 'nullable|string|max:500']);

        $employee->update([
            'pending_bank_type'    => null,
            'pending_account_name' => null,
            'pending_bank_account' => null,
            'bank_info_pending'    => false,
        ]);

        $this->notifyEmployee($employee, 'bank_rejected',
            '❌ تم رفض تعديل بيانات البنك',
            $request->reason ?? 'تم رفض طلب تعديل البنك.'
        );

        return back()->with('success', "تم رفض طلب بنك {$employee->name}");
    }

    /* =====================================================
     *  السلفة — اعتماد
     * ===================================================== */
    public function approveLoan(Loan $loan)
    {
        $loan->update(['status' => 'active']);

        $this->notifyEmployee($loan->employee, 'loan_approved',
            '✅ تمت الموافقة على طلب السلفة',
            "تمت الموافقة على سلفتك بمبلغ {$loan->total_amount} ₪."
        );

        return back()->with('success', "✅ تم اعتماد سلفة {$loan->employee->name}");
    }

    /* =====================================================
     *  السلفة — رفض
     * ===================================================== */
    public function rejectLoan(Request $request, Loan $loan)
    {
        $request->validate(['reason' => 'required|string|max:500']);

        $loan->update([
            'status'           => 'rejected',
            'rejection_reason' => $request->reason,
        ]);

        $this->notifyEmployee($loan->employee, 'loan_rejected',
            '❌ تم رفض طلب السلفة',
            "تم رفض طلب السلفة. السبب: {$request->reason}"
        );

        return back()->with('success', "تم رفض سلفة {$loan->employee->name}");
    }

    /* =====================================================
     *  الإجازة — اعتماد
     * ===================================================== */
    public function approveLeave(LeaveRequest $leave)
    {
        $leave->update(['status' => 'approved']);

        $this->notifyEmployee($leave->employee, 'leave_approved',
            '✅ تمت الموافقة على طلب الإجازة',
            "تمت الموافقة على إجازتك من {$leave->start_date->format('Y-m-d')} إلى {$leave->end_date->format('Y-m-d')}."
        );

        return back()->with('success', "✅ تم اعتماد إجازة {$leave->employee->name}");
    }

    /* =====================================================
     *  الإجازة — رفض
     * ===================================================== */
    public function rejectLeave(Request $request, LeaveRequest $leave)
    {
        $request->validate(['reason' => 'required|string|max:500']);

        $leave->update([
            'status'           => 'rejected',
            'rejection_reason' => $request->reason,
        ]);

        $this->notifyEmployee($leave->employee, 'leave_rejected',
            '❌ تم رفض طلب الإجازة',
            "تم رفض طلب الإجازة. السبب: {$request->reason}"
        );

        return back()->with('success', "تم رفض إجازة {$leave->employee->name}");
    }

    /* =====================================================
     *  كشف الحساب — اعتماد وإرسال
     * ===================================================== */
    public function approveStatement(SalaryPayment $salary)
    {
        $salary->update([
            'statement_status' => 'approved',
        ]);

        $this->notifyEmployee($salary->employee, 'statement_ready',
            '📄 كشف الحساب جاهز',
            "كشف حسابك للفترة {$salary->fiscal_period} جاهز. يمكنك تحميله من التطبيق."
        );

        return back()->with('success', "✅ تم اعتماد كشف حساب {$salary->employee->name}");
    }

    /* =====================================================
     *  البنك — قفل التعديل
     * ===================================================== */
    public function lockBank(Employee $employee)
    {
        $employee->update(['bank_info_locked' => true]);

        $this->notifyEmployee($employee, 'bank_locked',
            '🔒 تم قفل بيانات البنك',
            'تم قفل إمكانية تعديل بيانات حسابك البنكي من قِبل الإدارة.'
        );

        return back()->with('success', "🔒 تم قفل بيانات بنك {$employee->name}");
    }

    /* =====================================================
     *  البنك — فتح التعديل
     * ===================================================== */
    public function unlockBank(Employee $employee)
    {
        $employee->update(['bank_info_locked' => false]);

        $this->notifyEmployee($employee, 'bank_unlocked',
            '🔓 تم فتح تعديل بيانات البنك',
            'يمكنك الآن تعديل بيانات حسابك البنكي من التطبيق.'
        );

        return back()->with('success', "🔓 تم فتح تعديل بنك {$employee->name}");
    }

    /* =====================================================
     *  Helper — إرسال إشعار للموظف
     * ===================================================== */
    private function notifyEmployee(Employee $employee, string $type, string $title, string $body): void
    {
        MobileNotification::create([
            'employee_id' => $employee->id,
            'type'        => $type,
            'title'       => $title,
            'body'        => $body,
            'target'      => 'employee',
        ]);

        // إرسال FCM Push Notification
        if ($employee->fcm_token) {
            app(FcmService::class)->sendToEmployee(
                $employee->fcm_token,
                $title,
                $body,
                ['type' => $type, 'employee_id' => (string) $employee->id]
            );
        }
    }
}
