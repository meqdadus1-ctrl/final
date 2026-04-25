<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('payslips', function (Blueprint $table) {
            $table->increments('id');
            $table->integer('employee_id')->unsigned();
            $table->integer('month');
            $table->integer('year');
            $table->decimal('basic_salary',       10, 2)->default(0);
            $table->decimal('housing_allowance',  10, 2)->default(0);
            $table->decimal('transport_allowance',10, 2)->default(0);
            $table->decimal('food_allowance',     10, 2)->default(0);
            $table->decimal('other_allowances',   10, 2)->default(0);
            $table->decimal('overtime_hours',     10, 2)->default(0);
            $table->decimal('overtime_rate',      10, 2)->default(0);
            $table->decimal('bonus',              10, 2)->default(0);
            $table->decimal('deduction_absence',  10, 2)->default(0);
            $table->decimal('deduction_late',     10, 2)->default(0);
            $table->decimal('deduction_insurance',10, 2)->default(0);
            $table->decimal('deduction_tax',      10, 2)->default(0);
            $table->decimal('deduction_loan',     10, 2)->default(0);
            $table->decimal('other_deductions',   10, 2)->default(0);
            $table->decimal('net_salary',         10, 2)->default(0);
            $table->enum('status', ['draft','issued','paid'])->default('draft');
            $table->text('notes')->nullable();
            $table->unsignedBigInteger('created_by')->nullable();
            $table->timestamp('issued_at')->nullable();
            $table->timestamps();

            $table->unique(['employee_id','month','year']);
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('payslips');
    }
};
