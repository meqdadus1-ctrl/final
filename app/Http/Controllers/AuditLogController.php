<?php

namespace App\Http\Controllers;

use App\Models\AuditLog;
use App\Models\User;
use Illuminate\Http\Request;

class AuditLogController extends Controller
{
    public function index(Request $request)
    {
        $query = AuditLog::with('user')->latest();

        if ($request->filled('action')) {
            $query->where('action', $request->action);
        }

        if ($request->filled('model_type')) {
            $query->where('model_type', 'like', '%' . $request->model_type . '%');
        }

        if ($request->filled('user_id')) {
            $query->where('user_id', $request->user_id);
        }

        if ($request->filled('from')) {
            $query->whereDate('created_at', '>=', $request->from);
        }

        if ($request->filled('to')) {
            $query->whereDate('created_at', '<=', $request->to);
        }

        $logs  = $query->paginate(50)->withQueryString();
        $users = User::orderBy('name')->get(['id', 'name']);

        $modelTypes = [
            'Employee'         => 'موظف',
            'SalaryPayment'    => 'راتب',
            'EmployeeLedger'   => 'قيد محاسبي',
            'Loan'             => 'سلفة',
            'SalaryAdjustment' => 'تعديل راتب',
            'LeaveRequest'     => 'إجازة',
        ];

        return view('audit.index', compact('logs', 'users', 'modelTypes'));
    }
}
