<?php

namespace App\Http\Controllers;

use App\Models\{Employee, EmployeeDocument, EmployeePromotion, Department, Bank};
use Illuminate\Http\Request;
use Illuminate\Support\Facades\{Auth, Storage};

class EmployeeProfileController extends Controller
{
    /* =====================================================
     *  SHOW PROFILE – /employees/{employee}/profile
     * ===================================================== */
    public function show(Employee $employee)
    {
        $employee->load([
            'department',
            'manager',
            'bank',
            'documents',
            'promotions.fromDepartment',
            'promotions.toDepartment',
            'promotions.approver',
            'activeLoan',
            'salaryPayments' => fn($q) => $q->orderByDesc('payment_date')->limit(20),
        ]);

        $banks = Bank::orderBy('name')->get(['id','name','bank_name']);

        return view('employees.profile', compact('employee', 'banks'));
    }

    /* =====================================================
     *  EDIT PROFILE – /employees/{employee}/edit
     * ===================================================== */
    public function edit(Employee $employee)
    {
        $departments = Department::orderBy('name')->get();
        $managers    = Employee::where('id', '!=', $employee->id)
            ->where('status', 'active')
            ->orderBy('name')
            ->get();

        return view('employees.edit', compact('employee', 'departments', 'managers'));
    }

    /* =====================================================
     *  UPDATE PROFILE – PUT /employees/{employee}
     * ===================================================== */
    public function update(Request $request, Employee $employee)
    {
        $data = $request->validate([
            'name'                      => 'required|string|max:255',
            'national_id'               => 'nullable|string|max:50|unique:employees,national_id,'.$employee->id,
            'birth_date'                => 'nullable|date',
            'gender'                    => 'nullable|in:male,female',
            'marital_status'            => 'nullable|in:single,married,divorced,widowed',
            'nationality'               => 'nullable|string|max:100',
            'religion'                  => 'nullable|string|max:50',
            'email'                     => 'nullable|email|unique:employees,email,'.$employee->id,
            'personal_email'            => 'nullable|email',
            'mobile'                    => 'nullable|string|max:20',
            'phone'                     => 'nullable|string|max:20',
            'phone2'                    => 'nullable|string|max:20',
            'address'                   => 'nullable|string|max:500',
            'city'                      => 'nullable|string|max:100',
            'emergency_contact_name'    => 'nullable|string|max:255',
            'emergency_contact_phone'   => 'nullable|string|max:20',
            'emergency_contact_relation'=> 'nullable|string|max:50',
            'employee_number'           => 'nullable|string|max:50|unique:employees,employee_number,'.$employee->id,
            'job_title'                 => 'nullable|string|max:255',
            'department_id'             => 'nullable|exists:departments,id',
            'work_location'             => 'nullable|string|max:255',
            'contract_start'            => 'nullable|date',
            'contract_end'              => 'nullable|date|after_or_equal:contract_start',
            'contract_type'             => 'nullable|in:permanent,temporary,part_time,freelance',
            'salary'                    => 'nullable|numeric|min:0',
            'salary_type'               => 'nullable|string|max:50',
            'work_email'                => 'nullable|email',
            'work_phone'                => 'nullable|string|max:20',
            'manager_id'                => 'nullable|exists:employees,id',
            'hire_date'                 => 'nullable|date',
            'status'                    => 'nullable|in:active,inactive',
            'education_level'           => 'nullable|string|max:100',
            'education_major'           => 'nullable|string|max:255',
            'university'                => 'nullable|string|max:255',
            'graduation_year'           => 'nullable|integer|min:1970|max:'.(date('Y')+1),
            'notes'                     => 'nullable|string|max:2000',
            // بيانات البنك
            'bank_id'                   => 'nullable|exists:banks,id',
            'account_name'              => 'nullable|string|max:255',
            'bank_account'              => 'nullable|string|max:50',
        ]);

        // رفع الصورة
        if ($request->hasFile('photo')) {
            $request->validate(['photo' => 'image|mimes:jpg,jpeg,png,webp|max:2048']);
            if ($employee->photo) Storage::disk('public')->delete($employee->photo);
            $data['photo'] = $request->file('photo')->store('employees/photos', 'public');
        }

        $employee->update($data);

        return redirect()->route('employees.profile', $employee)
            ->with('success', 'تم تحديث بيانات الموظف بنجاح.');
    }

    /* =====================================================
     *  UPLOAD DOCUMENT – POST /employees/{employee}/documents
     * ===================================================== */
    public function uploadDocument(Request $request, Employee $employee)
    {
        $request->validate([
            'title'       => 'required|string|max:255',
            'type'        => 'required|in:id_card,passport,contract,certificate,cv,medical,other',
            'file'        => 'required|file|mimes:pdf,jpg,jpeg,png,doc,docx|max:5120',
            'expiry_date' => 'nullable|date',
            'notes'       => 'nullable|string|max:500',
        ]);

        $file     = $request->file('file');
        $path     = $file->store('employees/documents/' . $employee->id, 'public');

        EmployeeDocument::create([
            'employee_id'  => $employee->id,
            'title'        => $request->title,
            'type'         => $request->type,
            'file_path'    => $path,
            'file_name'    => $file->getClientOriginalName(),
            'file_size'    => $this->formatFileSize($file->getSize()),
            'expiry_date'  => $request->expiry_date,
            'notes'        => $request->notes,
            'uploaded_by'  => Auth::id(),
        ]);

        return back()->with('success', 'تم رفع المستند بنجاح.');
    }

    /* =====================================================
     *  DELETE DOCUMENT – DELETE /employees/documents/{document}
     * ===================================================== */
    public function deleteDocument(EmployeeDocument $document)
    {
        Storage::disk('public')->delete($document->file_path);
        $employeeId = $document->employee_id;
        $document->delete();

        return back()->with('success', 'تم حذف المستند.');
    }

    /* =====================================================
     *  ADD PROMOTION – POST /employees/{employee}/promotions
     * ===================================================== */
    public function addPromotion(Request $request, Employee $employee)
    {
        $data = $request->validate([
            'type'               => 'required|in:promotion,transfer,demotion,title_change,salary_change',
            'from_title'         => 'nullable|string|max:255',
            'to_title'           => 'nullable|string|max:255',
            'from_department_id' => 'nullable|exists:departments,id',
            'to_department_id'   => 'nullable|exists:departments,id',
            'from_salary'        => 'nullable|numeric|min:0',
            'to_salary'          => 'nullable|numeric|min:0',
            'effective_date'     => 'required|date',
            'reason'             => 'nullable|string|max:1000',
        ]);

        $data['employee_id'] = $employee->id;
        $data['approved_by'] = Auth::id();

        EmployeePromotion::create($data);

        // تحديث بيانات الموظف تلقائياً
        $updates = [];
        if ($data['to_title'])         $updates['job_title']      = $data['to_title'];
        if ($data['to_department_id']) $updates['department_id']  = $data['to_department_id'];
        if ($data['to_salary'])        $updates['salary']         = $data['to_salary'];
        if ($updates) $employee->update($updates);

        return back()->with('success', 'تم تسجيل الحركة الوظيفية بنجاح.');
    }

    /* =====================================================
     *  DELETE PROMOTION
     * ===================================================== */
    public function deletePromotion(EmployeePromotion $promotion)
    {
        $promotion->delete();
        return back()->with('success', 'تم حذف السجل.');
    }

    /* ---------- Helper ---------- */
    private function formatFileSize(int $bytes): string
    {
        if ($bytes >= 1048576) return round($bytes / 1048576, 1) . ' MB';
        if ($bytes >= 1024)    return round($bytes / 1024, 1) . ' KB';
        return $bytes . ' B';
    }
}
