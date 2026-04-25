<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

/**
 * Core HR tables migration.
 *
 * This migration creates all the base tables the HR system depends on.
 * It runs BEFORE any "update" migrations, so later ALTER-style migrations
 * (001_update_employees_table.php, 2026_04_18_add_user_id_to_employees.php, etc.)
 * can safely add their additional columns on top.
 */
return new class extends Migration
{
    public function up(): void
    {
        /* =====================================================
         *  banks
         * ===================================================== */
        if (!Schema::hasTable('banks')) {
            Schema::create('banks', function (Blueprint $table) {
                $table->id();
                $table->string('name')->nullable();
                $table->string('bank_name')->nullable();
                $table->string('branch')->nullable();
                $table->string('swift_code')->nullable();
                $table->text('notes')->nullable();
                $table->timestamps();
            });
        }

        /* =====================================================
         *  departments
         * ===================================================== */
        if (!Schema::hasTable('departments')) {
            Schema::create('departments', function (Blueprint $table) {
                $table->id();
                $table->string('name')->unique();
                $table->text('description')->nullable();
                $table->timestamps();
            });
        }

        /* =====================================================
         *  employees (core columns only – other migrations add the rest)
         * ===================================================== */
        if (!Schema::hasTable('employees')) {
            Schema::create('employees', function (Blueprint $table) {
                $table->id();
                $table->string('name');
                $table->string('mobile', 30)->nullable();
                $table->string('email')->nullable();
                $table->foreignId('department_id')->nullable()
                      ->constrained('departments')->nullOnDelete();
                $table->foreignId('bank_id')->nullable()
                      ->constrained('banks')->nullOnDelete();
                $table->string('account_name')->nullable();
                $table->string('bank_account', 50)->nullable();

                $table->enum('salary_type', ['fixed','hourly'])->default('fixed');
                $table->decimal('base_salary', 12, 2)->default(0);
                $table->decimal('hourly_rate', 10, 2)->default(0);
                $table->decimal('salary', 12, 2)->default(0);
                $table->time('shift_start')->nullable();
                $table->time('shift_end')->nullable();
                $table->decimal('overtime_rate', 6, 2)->default(1.5);

                $table->integer('fingerprint_id')->nullable()->unique();
                $table->date('hire_date')->nullable();
                $table->enum('status', ['active','inactive'])->default('active');
                $table->timestamps();
            });
        }

        /* =====================================================
         *  attendance
         * ===================================================== */
        if (!Schema::hasTable('attendance')) {
            Schema::create('attendance', function (Blueprint $table) {
                $table->id();
                $table->foreignId('employee_id')->constrained('employees')->cascadeOnDelete();
                $table->date('date');
                $table->time('check_in')->nullable();
                $table->time('check_out')->nullable();
                $table->decimal('work_hours', 6, 2)->default(0);
                $table->decimal('overtime_hours', 6, 2)->default(0);
                $table->enum('status', ['present','absent','late','leave','holiday'])->default('present');
                $table->boolean('leave_approved')->default(false);
                $table->string('leave_reason')->nullable();
                $table->boolean('is_manual')->default(false);
                $table->foreignId('updated_by')->nullable()->constrained('users')->nullOnDelete();
                $table->timestamps();

                $table->unique(['employee_id','date']);
                $table->index('date');
            });
        }

        /* =====================================================
         *  leave_types
         * ===================================================== */
        if (!Schema::hasTable('leave_types')) {
            Schema::create('leave_types', function (Blueprint $table) {
                $table->id();
                $table->string('name', 100)->unique();
                $table->integer('max_days_yearly')->default(0);
                $table->boolean('is_paid')->default(true);
                $table->timestamps();
            });
        }

        /* =====================================================
         *  leave_requests
         * ===================================================== */
        if (!Schema::hasTable('leave_requests')) {
            Schema::create('leave_requests', function (Blueprint $table) {
                $table->id();
                $table->foreignId('employee_id')->constrained('employees')->cascadeOnDelete();
                $table->foreignId('leave_type_id')->constrained('leave_types')->cascadeOnDelete();
                $table->date('start_date');
                $table->date('end_date');
                $table->integer('total_days')->default(0);
                $table->text('reason')->nullable();
                $table->enum('status', ['pending','approved','rejected','cancelled'])->default('pending');
                $table->foreignId('reviewed_by')->nullable()->constrained('users')->nullOnDelete();
                $table->timestamp('reviewed_at')->nullable();
                $table->text('notes')->nullable();
                $table->timestamps();

                $table->index(['employee_id','status']);
                $table->index('reviewed_by');
            });
        }

        /* =====================================================
         *  leave_balances
         * ===================================================== */
        if (!Schema::hasTable('leave_balances')) {
            Schema::create('leave_balances', function (Blueprint $table) {
                $table->id();
                $table->foreignId('employee_id')->constrained('employees')->cascadeOnDelete();
                $table->foreignId('leave_type_id')->constrained('leave_types')->cascadeOnDelete();
                $table->integer('year');
                $table->integer('entitled_days')->default(0);
                $table->integer('used_days')->default(0);
                $table->timestamps();

                $table->unique(['employee_id','leave_type_id','year']);
            });
        }

        /* =====================================================
         *  loans
         * ===================================================== */
        if (!Schema::hasTable('loans')) {
            Schema::create('loans', function (Blueprint $table) {
                $table->id();
                $table->foreignId('employee_id')->constrained('employees')->cascadeOnDelete();
                $table->decimal('total_amount',       12, 2)->default(0);
                $table->decimal('installment_amount', 12, 2)->default(0);
                $table->decimal('amount_paid',        12, 2)->default(0);
                $table->integer('installments_total')->default(0);
                $table->integer('installments_paid')->default(0);
                $table->date('start_date')->nullable();
                $table->date('last_payment_date')->nullable();
                $table->boolean('is_paused')->default(false);
                $table->enum('status', ['pending','active','completed','cancelled','paid'])->default('active');
                $table->text('description')->nullable();
                $table->timestamps();

                $table->index(['employee_id','status']);
            });
        }

        /* =====================================================
         *  job_applications
         * ===================================================== */
        if (!Schema::hasTable('job_applications')) {
            Schema::create('job_applications', function (Blueprint $table) {
                $table->id();
                $table->string('full_name', 150);
                $table->string('mobile', 30);
                $table->string('email')->nullable();
                $table->string('national_id', 30)->nullable();
                $table->foreignId('department_id')->nullable()
                      ->constrained('departments')->nullOnDelete();
                $table->string('position', 150);
                $table->integer('experience_years')->default(0);
                $table->string('cv_path')->nullable();
                $table->text('notes')->nullable();
                $table->enum('status', ['new','reviewing','interview','accepted','rejected'])->default('new');
                $table->foreignId('reviewed_by')->nullable()->constrained('users')->nullOnDelete();
                $table->timestamp('reviewed_at')->nullable();
                $table->text('reviewer_notes')->nullable();
                $table->timestamps();

                $table->index('reviewed_by');
            });
        }

        /* =====================================================
         *  salary_payments
         * ===================================================== */
        if (!Schema::hasTable('salary_payments')) {
            Schema::create('salary_payments', function (Blueprint $table) {
                $table->id();
                $table->foreignId('employee_id')->constrained('employees')->cascadeOnDelete();
                $table->date('week_start');
                $table->date('week_end');
                $table->decimal('hours_worked',          10, 2)->default(0);
                $table->decimal('overtime_hours',        10, 2)->default(0);
                $table->integer('late_minutes')->default(0);
                $table->decimal('late_deduction',        10, 2)->default(0);
                $table->decimal('late_factor',            6, 2)->default(1);
                $table->decimal('absence_deduction',     10, 2)->default(0);
                $table->decimal('manual_additions',      10, 2)->default(0);
                $table->decimal('manual_deductions',     10, 2)->default(0);
                $table->decimal('loan_deduction_amount', 10, 2)->default(0);
                $table->decimal('gross_salary',          10, 2)->default(0);
                $table->decimal('total_allowances',      10, 2)->default(0);
                $table->decimal('total_deductions',      10, 2)->default(0);
                $table->decimal('loan_deduction',        10, 2)->default(0);
                $table->decimal('net_salary',            10, 2)->default(0);
                $table->date('payment_date')->nullable();
                $table->string('fiscal_period', 30)->nullable();
                $table->enum('payment_method', ['bank','cash','deferred'])->default('deferred');
                $table->text('notes')->nullable();
                $table->foreignId('created_by')->nullable()->constrained('users')->nullOnDelete();
                $table->timestamps();

                $table->index(['employee_id','week_start']);
                $table->index('created_by');
            });
        }
    }

    public function down(): void
    {
        Schema::dropIfExists('salary_payments');
        Schema::dropIfExists('job_applications');
        Schema::dropIfExists('loans');
        Schema::dropIfExists('leave_balances');
        Schema::dropIfExists('leave_requests');
        Schema::dropIfExists('leave_types');
        Schema::dropIfExists('attendance');
        Schema::dropIfExists('employees');
        Schema::dropIfExists('departments');
        Schema::dropIfExists('banks');
    }
};
