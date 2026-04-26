<?php

use Illuminate\Support\Facades\Route;
use App\Http\Controllers\ProfileController;
use App\Http\Controllers\DepartmentController;
use App\Http\Controllers\BankController;
use App\Http\Controllers\BankReportController;
use App\Http\Controllers\EmployeeController;
use App\Http\Controllers\AttendanceController;
use App\Http\Controllers\LoanController;
use App\Http\Controllers\SalaryController;
use App\Http\Controllers\LeaveController;
use App\Http\Controllers\JobApplicationController;
use App\Http\Controllers\EmployeeProfileController;
use App\Http\Controllers\EmployeePortalController;
use App\Http\Controllers\EmployeeFinanceController;
use App\Http\Controllers\PayslipController;
use App\Http\Controllers\RoleController;
use App\Http\Controllers\LedgerImportController;
use App\Http\Controllers\EmployeeUserController;
use App\Http\Controllers\ReportController;

Route::get('/', function () {
    return redirect()->route('dashboard');
});

Route::middleware(['auth'])->group(function () {

    Route::get('/dashboard', function () {
        return view('dashboard');
    })->name('dashboard');

    // ===== حساب المستخدم (Profile) =====
    Route::get('/profile',    [ProfileController::class, 'edit'])->name('profile.edit');
    Route::patch('/profile',  [ProfileController::class, 'update'])->name('profile.update');
    Route::delete('/profile', [ProfileController::class, 'destroy'])->name('profile.destroy');

    // ===== الأقسام =====
    Route::get('departments',                    [DepartmentController::class, 'index'])->middleware('permission:departments.view')->name('departments.index');
    Route::get('departments/create',             [DepartmentController::class, 'create'])->middleware('permission:departments.create')->name('departments.create');
    Route::post('departments',                   [DepartmentController::class, 'store'])->middleware('permission:departments.create')->name('departments.store');
    Route::get('departments/{department}',       [DepartmentController::class, 'show'])->middleware('permission:departments.view')->name('departments.show');
    Route::get('departments/{department}/edit',  [DepartmentController::class, 'edit'])->middleware('permission:departments.edit')->name('departments.edit');
    Route::put('departments/{department}',       [DepartmentController::class, 'update'])->middleware('permission:departments.edit')->name('departments.update');
    Route::delete('departments/{department}',    [DepartmentController::class, 'destroy'])->middleware('permission:departments.delete')->name('departments.destroy');

    // ===== الموظفون (CRUD الأساسي) =====
    Route::get('employees',                      [EmployeeController::class, 'index'])->middleware('permission:employees.view')->name('employees.index');
    Route::get('employees/create',               [EmployeeController::class, 'create'])->middleware('permission:employees.create')->name('employees.create');
    Route::post('employees',                     [EmployeeController::class, 'store'])->middleware('permission:employees.create')->name('employees.store');
    Route::get('employees/{employee}',           [EmployeeController::class, 'show'])->middleware('permission:employees.view')->name('employees.show');
    Route::get('employees/{employee}/edit',      [EmployeeController::class, 'edit'])->middleware('permission:employees.edit')->name('employees.edit');
    Route::put('employees/{employee}',           [EmployeeController::class, 'update'])->middleware('permission:employees.edit')->name('employees.update');
    Route::delete('employees/{employee}',        [EmployeeController::class, 'destroy'])->middleware('permission:employees.delete')->name('employees.destroy');

    // ===== إدارة حساب الموظف (user account) =====
    Route::prefix('employees')->name('employees.')->middleware('role:admin|manager')->group(function () {
        Route::post('/{employee}/user/create',         [EmployeeUserController::class, 'createUser'])->name('user.create');
        Route::post('/{employee}/user/link',           [EmployeeUserController::class, 'linkUser'])->name('user.link');
        Route::delete('/{employee}/user/unlink',       [EmployeeUserController::class, 'unlinkUser'])->name('user.unlink');
        Route::post('/{employee}/user/reset-password', [EmployeeUserController::class, 'resetPassword'])->name('user.reset-password');
    });

    // ===== صفحة الموظف التفصيلية (Profile + Documents + Promotions) =====
    Route::prefix('employees')->name('employees.')->middleware('permission:employees.profile')->group(function () {
        Route::get('/{employee}/profile',             [EmployeeProfileController::class, 'show'])->name('profile');
        Route::get('/{employee}/profile/edit',        [EmployeeProfileController::class, 'edit'])->name('profile.edit');
        Route::put('/{employee}/profile',             [EmployeeProfileController::class, 'update'])->name('profile.update');
        Route::post('/{employee}/documents',          [EmployeeProfileController::class, 'uploadDocument'])->name('documents.upload');
        Route::delete('/documents/{document}',        [EmployeeProfileController::class, 'deleteDocument'])->name('documents.delete');
        Route::post('/{employee}/promotions',         [EmployeeProfileController::class, 'addPromotion'])->name('promotions.store');
        Route::delete('/promotions/{promotion}',      [EmployeeProfileController::class, 'deletePromotion'])->name('promotions.delete');

        // ===== الرصيد الافتتاحي =====
        Route::post('/{employee}/opening-balance',             [EmployeeFinanceController::class, 'updateOpeningBalance'])->name('opening-balance.update');
        Route::post('/{employee}/reset-opening-balance',      [EmployeeFinanceController::class, 'resetOpeningBalance'])->name('opening-balance.reset');
    });

    // ===== البنوك =====
    Route::get('banks/report',       [BankReportController::class, 'index'])->middleware('permission:banks.view')->name('banks.report');
    Route::get('banks',              [BankController::class, 'index'])->middleware('permission:banks.view')->name('banks.index');
    Route::get('banks/create',       [BankController::class, 'create'])->middleware('permission:banks.create')->name('banks.create');
    Route::post('banks',             [BankController::class, 'store'])->middleware('permission:banks.create')->name('banks.store');
    Route::get('banks/{bank}',       [BankController::class, 'show'])->middleware('permission:banks.view')->name('banks.show');
    Route::get('banks/{bank}/edit',  [BankController::class, 'edit'])->middleware('permission:banks.edit')->name('banks.edit');
    Route::put('banks/{bank}',       [BankController::class, 'update'])->middleware('permission:banks.edit')->name('banks.update');
    Route::delete('banks/{bank}',    [BankController::class, 'destroy'])->middleware('permission:banks.delete')->name('banks.destroy');

    // ===== الحضور =====
    Route::post('attendance/import-excel',       [AttendanceController::class, 'importExcel'])->middleware('permission:attendance.create')->name('attendance.import.excel');
    Route::get('attendance/pull-device',         [AttendanceController::class, 'pullDevicePage'])->middleware('permission:attendance.create')->name('attendance.pull.page');
    Route::post('attendance/pull-device',        [AttendanceController::class, 'pullFromDevice'])->middleware('permission:attendance.create')->name('attendance.pull');
    Route::post('attendance/ping-device',        [AttendanceController::class, 'pingDevice'])->middleware('permission:attendance.create')->name('attendance.ping');
    Route::get('attendance',                     [AttendanceController::class, 'index'])->middleware('permission:attendance.view|attendance.view_own')->name('attendance.index');
    Route::get('attendance/create',              [AttendanceController::class, 'create'])->middleware('permission:attendance.create')->name('attendance.create');
    Route::post('attendance',                    [AttendanceController::class, 'store'])->middleware('permission:attendance.create')->name('attendance.store');
    Route::get('attendance/{attendance}',        [AttendanceController::class, 'show'])->middleware('permission:attendance.view')->name('attendance.show');
    Route::get('attendance/{attendance}/edit',   [AttendanceController::class, 'edit'])->middleware('permission:attendance.edit')->name('attendance.edit');
    Route::put('attendance/{attendance}',        [AttendanceController::class, 'update'])->middleware('permission:attendance.edit')->name('attendance.update');
    Route::delete('attendance/{attendance}',     [AttendanceController::class, 'destroy'])->middleware('permission:attendance.delete')->name('attendance.destroy');

    // ===== السلف =====
    Route::get('loans',              [LoanController::class, 'index'])->middleware('permission:loans.view|loans.view_own')->name('loans.index');
    Route::get('loans/create',       [LoanController::class, 'create'])->middleware('permission:loans.create')->name('loans.create');
    Route::post('loans',             [LoanController::class, 'store'])->middleware('permission:loans.create')->name('loans.store');
    Route::get('loans/{loan}',       [LoanController::class, 'show'])->middleware('permission:loans.view|loans.view_own')->name('loans.show');
    Route::get('loans/{loan}/edit',  [LoanController::class, 'edit'])->middleware('permission:loans.edit')->name('loans.edit');
    Route::put('loans/{loan}',       [LoanController::class, 'update'])->middleware('permission:loans.edit')->name('loans.update');
    Route::delete('loans/{loan}',    [LoanController::class, 'destroy'])->middleware('permission:loans.delete')->name('loans.destroy');
    Route::post('loans/{loan}/pay',  [LoanController::class, 'payInstallment'])->middleware('permission:loans.approve')->name('loans.pay');

    // ===== الرواتب الأسبوعية + Ledger =====
    Route::prefix('salary')->name('salary.')->group(function () {
        Route::get('/',                          [SalaryController::class, 'index'])->middleware('permission:payslips.view')->name('index');
        Route::get('/create',                    [SalaryController::class, 'create'])->middleware('permission:payslips.create')->name('create');
        Route::post('/calculate',                [SalaryController::class, 'calculate'])->middleware('permission:payslips.create')->name('calculate');
        Route::get('/calculate',                 fn() => redirect()->route('salary.create'));
        Route::post('/',                         [SalaryController::class, 'store'])->middleware('permission:payslips.create')->name('store');
        Route::get('/{salary}',                  [SalaryController::class, 'show'])->middleware('permission:payslips.view')->name('show');
        Route::get('/{salary}/thermal',          [SalaryController::class, 'thermal'])->middleware('permission:payslips.view')->name('thermal');
        Route::get('/{salary}/edit',             [SalaryController::class, 'edit'])->middleware('permission:payslips.edit')->name('edit');
        Route::put('/{salary}',                  [SalaryController::class, 'update'])->middleware('permission:payslips.edit')->name('update');
        Route::delete('/{salary}',               [SalaryController::class, 'destroy'])->middleware('permission:payslips.delete')->name('destroy');

        // ===== التعديلات اليدوية (Bonus / Expense) =====
        Route::get('/adjustments/list',          [SalaryController::class, 'adjustments'])->middleware('permission:payslips.view')->name('adjustments');
        Route::post('/adjustments',              [SalaryController::class, 'storeAdjustment'])->middleware('permission:payslips.create')->name('adjustments.store');
        Route::post('/adjustments/{adjustment}/cancel', [SalaryController::class, 'cancelAdjustment'])->middleware('permission:payslips.edit')->name('adjustments.cancel');
    });

    // ===== Payslips (deprecated → redirect) =====
    Route::get('payslips/{any?}', fn() => redirect()->route('salary.index')
        ->with('info', 'تم الانتقال لنظام الرواتب الجديد.')
    )->where('any', '.*')->name('payslips.index');

    // ===== كشف الحساب (Employee Ledger) =====
    Route::prefix('ledger')->name('ledger.')->middleware('permission:payslips.view')->group(function () {
        Route::get('/import',                      [LedgerImportController::class, 'showImportForm'])->name('import');
        Route::post('/import/preview',             [LedgerImportController::class, 'preview'])->name('import.preview');
        Route::post('/import/store',               [LedgerImportController::class, 'store'])->name('import.store');
        Route::get('/{employee}',                  [\App\Http\Controllers\LedgerController::class, 'show'])->name('show');
        Route::get('/{employee}/pdf',              [\App\Http\Controllers\LedgerController::class, 'pdf'])->name('pdf');
        Route::post('/{employee}/opening-balance', [\App\Http\Controllers\LedgerController::class, 'setOpeningBalance'])->name('opening-balance');
        Route::post('/{employee}/payment',         [\App\Http\Controllers\LedgerController::class, 'recordPayment'])->name('payment');

        // ===== إدارة القيود المحاسبية =====
        Route::post('/{employee}/entry',           [\App\Http\Controllers\LedgerController::class, 'storeEntry'])->name('entry.store');
        Route::put('/entry/{entry}',               [\App\Http\Controllers\LedgerController::class, 'updateEntry'])->name('entry.update');
        Route::delete('/entry/{entry}',            [\App\Http\Controllers\LedgerController::class, 'destroyEntry'])->name('entry.destroy');
    });

    // ===== الإجازات =====
    Route::get('leaves/create',           [LeaveController::class, 'create'])->middleware('permission:leaves.create')->name('leaves.create');
    Route::get('leaves/balances',         [LeaveController::class, 'balances'])->middleware('permission:leaves.view')->name('leaves.balances');
    Route::get('leaves/types',            [LeaveController::class, 'types'])->middleware('permission:leaves.view')->name('leaves.types');
    Route::post('leaves/types',             [LeaveController::class, 'storeType'])->middleware('permission:leaves.edit')->name('leaves.types.store');
    Route::delete('leaves/types/{leaveType}', [LeaveController::class, 'destroyType'])->middleware('permission:leaves.edit')->name('leaves.types.destroy');
    Route::get('leaves',                  [LeaveController::class, 'index'])->middleware('permission:leaves.view|leaves.view_own')->name('leaves.index');
    Route::post('leaves',                 [LeaveController::class, 'store'])->middleware('permission:leaves.create')->name('leaves.store');
    Route::get('leaves/{leave}',          [LeaveController::class, 'show'])->middleware('permission:leaves.view|leaves.view_own')->name('leaves.show');
    Route::delete('leaves/{leave}',       [LeaveController::class, 'destroy'])->middleware('permission:leaves.edit')->name('leaves.destroy');
    Route::post('leaves/{leave}/approve', [LeaveController::class, 'approve'])->middleware('permission:leaves.approve')->name('leaves.approve');
    Route::post('leaves/{leave}/reject',  [LeaveController::class, 'reject'])->middleware('permission:leaves.reject')->name('leaves.reject');

    // ===== التقارير =====
    Route::prefix('reports')->name('reports.')->middleware('permission:payslips.view')->group(function () {
        Route::get('/',          [ReportController::class, 'index'])->name('index');
        Route::get('/generate',  [ReportController::class, 'generate'])->name('generate');
        Route::get('/pdf',       [ReportController::class, 'pdf'])->name('pdf');
    });

    // ===== التوظيف =====
    Route::get('jobs',               [JobApplicationController::class, 'index'])->middleware('permission:jobs.view')->name('jobs.index');
    Route::get('jobs/create',        [JobApplicationController::class, 'create'])->middleware('permission:jobs.create')->name('jobs.create');
    Route::post('jobs',              [JobApplicationController::class, 'store'])->middleware('permission:jobs.create')->name('jobs.store');
    Route::get('jobs/{job}',         [JobApplicationController::class, 'show'])->middleware('permission:jobs.view')->name('jobs.show');
    Route::delete('jobs/{job}',      [JobApplicationController::class, 'destroy'])->middleware('permission:jobs.delete')->name('jobs.destroy');
    Route::post('jobs/{job}/status', [JobApplicationController::class, 'updateStatus'])->middleware('permission:jobs.edit')->name('jobs.status');

    // ===== بوابة الموظف (Employee Portal) =====
    Route::prefix('portal')->name('portal.')->middleware('employee.portal')->group(function () {
        Route::get('/', [EmployeePortalController::class, 'index'])->name('index');
    });

    // ===== محادثات الموظفين =====
    Route::prefix('chat')->name('chat.')->middleware('role:admin|manager')->group(function () {
        Route::get('/',                         [\App\Http\Controllers\AdminChatController::class, 'index'])->name('index');
        Route::get('/{employee}',               [\App\Http\Controllers\AdminChatController::class, 'show'])->name('show');
        Route::post('/{employee}/send',         [\App\Http\Controllers\AdminChatController::class, 'send'])->name('send');
        Route::delete('/message/{chat}',        [\App\Http\Controllers\AdminChatController::class, 'destroy'])->name('destroy');
        Route::get('/{employee}/poll',          [\App\Http\Controllers\AdminChatController::class, 'poll'])->name('poll');
        Route::get('/list/poll',                [\App\Http\Controllers\AdminChatController::class, 'pollList'])->name('poll.list');
    });

    // ===== لوحة تحكم الموبايل =====
    Route::prefix('mobile')->name('mobile.')->middleware('role:admin|manager')->group(function () {
        Route::get('/',                                    [\App\Http\Controllers\MobileAdminController::class, 'index'])->name('index');
        // البنك
        Route::post('/bank/{employee}/approve',            [\App\Http\Controllers\MobileAdminController::class, 'approveBank'])->name('bank.approve');
        Route::post('/bank/{employee}/reject',             [\App\Http\Controllers\MobileAdminController::class, 'rejectBank'])->name('bank.reject');
        Route::post('/bank/{employee}/lock',               [\App\Http\Controllers\MobileAdminController::class, 'lockBank'])->name('bank.lock');
        Route::post('/bank/{employee}/unlock',             [\App\Http\Controllers\MobileAdminController::class, 'unlockBank'])->name('bank.unlock');
        // السلف
        Route::post('/loans/{loan}/approve',               [\App\Http\Controllers\MobileAdminController::class, 'approveLoan'])->name('loans.approve');
        Route::post('/loans/{loan}/reject',                [\App\Http\Controllers\MobileAdminController::class, 'rejectLoan'])->name('loans.reject');
        // الإجازات
        Route::post('/leaves/{leave}/approve',             [\App\Http\Controllers\MobileAdminController::class, 'approveLeave'])->name('leaves.approve');
        Route::post('/leaves/{leave}/reject',              [\App\Http\Controllers\MobileAdminController::class, 'rejectLeave'])->name('leaves.reject');
        // كشف الحساب
        Route::post('/salary/{salary}/approve-statement',  [\App\Http\Controllers\MobileAdminController::class, 'approveStatement'])->name('salary.statement');
    });

    // ===== الأدوار والصلاحيات - Admin فقط =====
    Route::prefix('roles')->name('roles.')->middleware('role:admin')->group(function () {
        Route::get('/',                [RoleController::class, 'index'])->name('index');
        Route::post('/assign',         [RoleController::class, 'assign'])->name('assign');
        Route::post('/remove',         [RoleController::class, 'remove'])->name('remove');
        Route::get('/{role}',          [RoleController::class, 'show'])->name('show');
        Route::put('/{role}',          [RoleController::class, 'update'])->name('update');
        Route::post('/users/create',   [RoleController::class, 'createUser'])->name('users.create');
        Route::delete('/users/{user}', [RoleController::class, 'deleteUser'])->name('users.delete');
    });

});

require __DIR__.'/auth.php';
