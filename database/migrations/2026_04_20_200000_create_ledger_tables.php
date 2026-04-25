<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

/**
 * Ledger System Tables
 *
 * employee_ledger  → كشف الحساب الكامل (كل قيد مالي)
 * salary_adjustments → الإضافات/الخصومات اليدوية (بونص، مصروف، إلخ)
 *
 * منطق الـ Ledger:
 *   credit = مبلغ مُضاف لصالح الموظف (راتب، بونص، أوفرتايم)
 *   debit  = مبلغ مخصوم من رصيد الموظف (سلفة، خصم، دفعة)
 *   balance_after = الرصيد بعد كل قيد (مثل كشف البنك)
 *   موجب = الشركة مدينة للموظف
 *   سالب = الموظف مدين للشركة
 */
return new class extends Migration
{
    public function up(): void
    {
        /* =====================================================
         *  employee_ledger  — كشف الحساب
         * ===================================================== */
        if (!Schema::hasTable('employee_ledger')) {
            Schema::create('employee_ledger', function (Blueprint $table) {
                $table->id();
                $table->foreignId('employee_id')->constrained('employees')->cascadeOnDelete();

                $table->date('entry_date');
                $table->date('period_start')->nullable(); // بداية الفترة
                $table->date('period_end')->nullable();   // نهاية الفترة

                // نوع القيد
                $table->enum('entry_type', [
                    'salary',           // راتب أساسي (ساعات × أجر)
                    'overtime',         // أوفرتايم
                    'bonus',            // مكافأة
                    'deduction_late',   // خصم تأخير
                    'deduction_absence',// خصم غياب
                    'deduction_manual', // خصم يدوي
                    'loan_installment', // قسط سلفة
                    'loan_disbursement',// صرف سلفة
                    'withdrawal',       // سحب / مصروف
                    'payment',          // دفع للموظف (تصفية)
                    'adjustment',       // تسوية يدوية
                    'opening_balance',  // رصيد افتتاحي
                ])->default('salary');

                $table->decimal('credit', 12, 2)->default(0); // دائن (لصالح الموظف)
                $table->decimal('debit',  12, 2)->default(0); // مدين (على الموظف)
                $table->decimal('balance_after', 12, 2)->default(0); // الرصيد بعد القيد

                $table->string('description')->nullable();

                // ربط بالمصدر (polymorphic-style)
                $table->string('reference_type')->nullable(); // SalaryPayment, Loan, EmployeeWithdrawal, SalaryAdjustment
                $table->unsignedBigInteger('reference_id')->nullable();

                $table->string('fiscal_period', 20)->nullable(); // e.g. 2026-W17
                $table->foreignId('created_by')->nullable()->constrained('users')->nullOnDelete();
                $table->timestamps();

                $table->index(['employee_id', 'entry_date']);
                $table->index(['employee_id', 'entry_type']);
                $table->index(['reference_type', 'reference_id']);
            });
        }

        /* =====================================================
         *  salary_adjustments — الإضافات/الخصومات اليدوية
         * ===================================================== */
        if (!Schema::hasTable('salary_adjustments')) {
            Schema::create('salary_adjustments', function (Blueprint $table) {
                $table->id();
                $table->foreignId('employee_id')->constrained('employees')->cascadeOnDelete();

                // ربط بـ salary_payment اختياري (إذا أُضيف وقت الاحتساب)
                $table->foreignId('salary_payment_id')->nullable()
                      ->constrained('salary_payments')->nullOnDelete();

                $table->enum('type', ['bonus', 'expense', 'deduction', 'other'])->default('bonus');
                // bonus = إضافة, expense = مصروف/خصم, deduction = خصم تأنيبي, other = متنوع

                $table->decimal('amount', 12, 2);
                $table->date('adjustment_date');
                $table->string('reason');              // السبب (إلزامي)
                $table->text('notes')->nullable();

                $table->enum('status', ['pending', 'applied', 'cancelled'])->default('pending');
                // pending = لم يُطبَّق بعد, applied = طُبِّق في راتب, cancelled = ملغى

                $table->foreignId('created_by')->nullable()->constrained('users')->nullOnDelete();
                $table->timestamps();

                $table->index(['employee_id', 'status']);
                $table->index(['employee_id', 'adjustment_date']);
            });
        }

        /* =====================================================
         *  تحديث salary_payments — إضافة حقول مفيدة
         * ===================================================== */
        if (Schema::hasTable('salary_payments')) {
            Schema::table('salary_payments', function (Blueprint $table) {
                if (!Schema::hasColumn('salary_payments', 'hourly_rate')) {
                    $table->decimal('hourly_rate', 10, 4)->default(0)->after('overtime_hours');
                }
                if (!Schema::hasColumn('salary_payments', 'adjustments_total')) {
                    $table->decimal('adjustments_total', 10, 2)->default(0)->after('manual_deductions');
                }
                if (!Schema::hasColumn('salary_payments', 'status')) {
                    $table->enum('status', ['draft', 'confirmed', 'paid'])
                          ->default('confirmed')->after('payment_method');
                }
                if (!Schema::hasColumn('salary_payments', 'balance_before')) {
                    $table->decimal('balance_before', 12, 2)->default(0)->after('net_salary');
                }
                if (!Schema::hasColumn('salary_payments', 'balance_after')) {
                    $table->decimal('balance_after', 12, 2)->default(0)->after('balance_before');
                }
            });
        }
    }

    public function down(): void
    {
        Schema::dropIfExists('salary_adjustments');
        Schema::dropIfExists('employee_ledger');

        if (Schema::hasTable('salary_payments')) {
            Schema::table('salary_payments', function (Blueprint $table) {
                $columns = ['hourly_rate', 'adjustments_total', 'status', 'balance_before', 'balance_after'];
                foreach ($columns as $col) {
                    if (Schema::hasColumn('salary_payments', $col)) {
                        $table->dropColumn($col);
                    }
                }
            });
        }
    }
};
