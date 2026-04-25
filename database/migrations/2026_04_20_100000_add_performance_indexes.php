<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

/**
 * إضافة indexes لتحسين أداء الاستعلامات.
 * هذه الـ indexes تسرّع عمليات JOIN والبحث في الجداول الرئيسية.
 */
return new class extends Migration
{
    public function up(): void
    {
        // =====================================================
        //  salary_payments - created_by index
        // =====================================================
        if (Schema::hasTable('salary_payments') && !$this->indexExists('salary_payments', 'salary_payments_created_by_index')) {
            Schema::table('salary_payments', function (Blueprint $table) {
                $table->index('created_by', 'salary_payments_created_by_index');
            });
        }

        // =====================================================
        //  leave_requests - reviewed_by index (إذا لم يكن موجوداً)
        // =====================================================
        if (Schema::hasTable('leave_requests') && !$this->indexExists('leave_requests', 'leave_requests_reviewed_by_index')) {
            Schema::table('leave_requests', function (Blueprint $table) {
                $table->index('reviewed_by', 'leave_requests_reviewed_by_index');
            });
        }

        // =====================================================
        //  job_applications - reviewed_by index
        // =====================================================
        if (Schema::hasTable('job_applications') && !$this->indexExists('job_applications', 'job_applications_reviewed_by_index')) {
            Schema::table('job_applications', function (Blueprint $table) {
                $table->index('reviewed_by', 'job_applications_reviewed_by_index');
            });
        }

        // =====================================================
        //  employees - status index (للـ active scope)
        // =====================================================
        if (Schema::hasTable('employees') && !$this->indexExists('employees', 'employees_status_index')) {
            Schema::table('employees', function (Blueprint $table) {
                $table->index('status', 'employees_status_index');
            });
        }

        // =====================================================
        //  loans - employee_id + status (للـ activeLoan)
        // =====================================================
        if (Schema::hasTable('loans') && !$this->indexExists('loans', 'loans_employee_status_index')) {
            Schema::table('loans', function (Blueprint $table) {
                $table->index(['employee_id', 'status'], 'loans_employee_status_index');
            });
        }

        // =====================================================
        //  payslips - employee_id + year + month
        // =====================================================
        if (Schema::hasTable('payslips') && !$this->indexExists('payslips', 'payslips_employee_year_month_index')) {
            Schema::table('payslips', function (Blueprint $table) {
                $table->index(['employee_id', 'year', 'month'], 'payslips_employee_year_month_index');
            });
        }
    }

    public function down(): void
    {
        $indexes = [
            'salary_payments' => 'salary_payments_created_by_index',
            'leave_requests'  => 'leave_requests_reviewed_by_index',
            'job_applications'=> 'job_applications_reviewed_by_index',
            'employees'       => 'employees_status_index',
            'loans'           => 'loans_employee_status_index',
            'payslips'        => 'payslips_employee_year_month_index',
        ];

        foreach ($indexes as $table => $index) {
            if (Schema::hasTable($table) && $this->indexExists($table, $index)) {
                Schema::table($table, function (Blueprint $t) use ($index) {
                    $t->dropIndex($index);
                });
            }
        }
    }

    /**
     * التحقق من وجود index بالاسم قبل إضافته (لتجنب الأخطاء)
     */
    private function indexExists(string $table, string $indexName): bool
    {
        $indexes = \DB::select("SHOW INDEX FROM `{$table}` WHERE Key_name = ?", [$indexName]);
        return count($indexes) > 0;
    }
};
