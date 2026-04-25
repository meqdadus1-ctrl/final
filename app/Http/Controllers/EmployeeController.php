<?php

namespace App\Http\Controllers;

use App\Models\Employee;
use App\Models\Department;
use App\Models\Bank;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Storage;

class EmployeeController extends Controller
{
    // عرض قائمة الموظفين
    public function index(Request $request)
    {
        $query = Employee::with(['department', 'bank']);

        // بحث متعدد (اسم / موبايل / هوية / رقم بصمة / رقم وظيفي)
        if ($request->filled('search')) {
            $term = trim($request->search);
            $query->where(function ($q) use ($term) {
                $q->where('name',            'like', "%{$term}%")
                  ->orWhere('mobile',        'like', "%{$term}%")
                  ->orWhere('national_id',   'like', "%{$term}%")
                  ->orWhere('employee_number','like', "%{$term}%");
                if (ctype_digit($term)) {
                    $q->orWhere('fingerprint_id', $term);
                }
            });
        }

        // فلتر بالقسم
        if ($request->filled('department_id')) {
            $query->where('department_id', $request->department_id);
        }

        // فلتر بالحالة
        if ($request->filled('status')) {
            $query->where('status', $request->status);
        }

        $employees   = $query->orderByDesc('created_at')->paginate(15)->withQueryString();
        $departments = Department::all();

        return view('employees.index', compact('employees', 'departments'));
    }

    // صفحة إضافة موظف
    public function create()
    {
        $departments = Department::all();
        $banks       = Bank::all();
        return view('employees.create', compact('departments', 'banks'));
    }

    // حفظ موظف جديد
    public function store(Request $request)
    {
        $request->validate([
            'name'            => 'required|string|max:150',
            'mobile'          => 'nullable|string|max:20',
            'national_id'     => 'nullable|string|max:20|unique:employees,national_id',
            'department_id'   => 'required|exists:departments,id',
            'bank_id'         => 'nullable|exists:banks,id',
            'account_name'    => 'nullable|string|max:150',
            'bank_account'    => 'nullable|string|max:50',
            'salary_type'     => 'required|in:fixed,hourly',
            'base_salary'     => 'nullable|numeric|min:0|required_if:salary_type,fixed',
            'hourly_rate'     => 'required_if:salary_type,hourly|numeric|min:0',
            'shift_start'     => 'nullable|date_format:H:i',
            'shift_end'       => 'nullable|date_format:H:i',
            'overtime_rate'   => 'nullable|numeric|min:0',
            'hire_date'       => 'nullable|date|before_or_equal:today',
            'status'          => 'required|in:active,inactive',
            'employee_number' => 'nullable|string|max:50|unique:employees,employee_number',
            'fingerprint_id'  => 'required|integer|min:1|unique:employees,fingerprint_id',
            'photo'           => 'nullable|image|max:2048',
        ]);

        $data = $request->except('photo');

        // رفع الصورة
        if ($request->hasFile('photo')) {
            $data['photo'] = $request->file('photo')->store('employees', 'public');
        }

        Employee::create($data);

        return redirect()->route('employees.index')
            ->with('success', 'تم إضافة الموظف بنجاح');
    }

    // عرض تفاصيل موظف
    public function show(Employee $employee)
    {
        $employee->load(['department', 'bank', 'attendances' => function($q) {
            $q->orderByDesc('date')->limit(10);
        }]);
        return view('employees.show', compact('employee'));
    }

    // صفحة تعديل موظف
    public function edit(Employee $employee)
    {
        $departments = Department::all();
        $banks       = Bank::all();
        $managers    = Employee::where('id', '!=', $employee->id)
                                ->where('status', 'active')
                                ->orderBy('name')
                                ->get(['id', 'name']);
        return view('employees.edit', compact('employee', 'departments', 'banks', 'managers'));
    }

    // حفظ التعديل
    public function update(Request $request, Employee $employee)
    {
        $request->validate([
            'name'            => 'required|string|max:150',
            'mobile'          => 'nullable|string|max:20',
            'national_id'     => 'nullable|string|max:20|unique:employees,national_id,' . $employee->id,
            'department_id'   => 'required|exists:departments,id',
            'bank_id'         => 'nullable|exists:banks,id',
            'account_name'    => 'nullable|string|max:150',
            'bank_account'    => 'nullable|string|max:50',
            'salary_type'     => 'required|in:fixed,hourly',
            'base_salary'     => 'nullable|numeric|min:0|required_if:salary_type,fixed',
            'hourly_rate'     => 'required_if:salary_type,hourly|numeric|min:0',
            'shift_start'     => 'nullable|date_format:H:i',
            'shift_end'       => 'nullable|date_format:H:i',
            'overtime_rate'   => 'nullable|numeric|min:0',
            'hire_date'       => 'nullable|date|before_or_equal:today',
            'status'          => 'required|in:active,inactive',
            'employee_number' => 'nullable|string|max:50|unique:employees,employee_number,' . $employee->id,
            'fingerprint_id'  => 'required|integer|min:1|unique:employees,fingerprint_id,' . $employee->id,
            'photo'           => 'nullable|image|max:2048',
        ]);

        $data = $request->except('photo');

        // رفع صورة جديدة
        if ($request->hasFile('photo')) {
            // حذف الصورة القديمة
            if ($employee->photo && $employee->photo !== 'default_user.png') {
                Storage::disk('public')->delete($employee->photo);
            }
            $data['photo'] = $request->file('photo')->store('employees', 'public');
        }

        $employee->update($data);

        return redirect()->route('employees.index')
            ->with('success', 'تم تعديل بيانات الموظف بنجاح');
    }

    // حذف موظف
    public function destroy(Employee $employee)
    {
        if ($employee->photo && $employee->photo !== 'default_user.png') {
            Storage::disk('public')->delete($employee->photo);
        }
        $employee->delete();

        return redirect()->route('employees.index')
            ->with('success', 'تم حذف الموظف بنجاح');
    }
}