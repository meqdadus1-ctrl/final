<?php

namespace App\Providers;

use App\Models\Employee;
use App\Models\EmployeeLedger;
use App\Models\Loan;
use App\Models\SalaryAdjustment;
use App\Models\SalaryPayment;
use App\Models\LeaveRequest;
use App\Observers\AuditObserver;
use Illuminate\Pagination\Paginator;
use Illuminate\Support\ServiceProvider;

class AppServiceProvider extends ServiceProvider
{
    public function register(): void {}

    public function boot(): void
    {
        Paginator::useBootstrapFive();

        $observer = new AuditObserver();
        Employee::observe($observer);
        SalaryPayment::observe($observer);
        EmployeeLedger::observe($observer);
        Loan::observe($observer);
        SalaryAdjustment::observe($observer);
        LeaveRequest::observe($observer);
    }
}
