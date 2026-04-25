<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('salary_payments', function (Blueprint $table) {
            if (!Schema::hasColumn('salary_payments', 'overtime_rate')) {
                $table->decimal('overtime_rate', 6, 2)->default(1.5)
                      ->after('overtime_hours')
                      ->comment('معامل الأوفرتايم المستخدم عند احتساب هذا الراتب');
            }
            if (!Schema::hasColumn('salary_payments', 'salary_from_hours')) {
                $table->decimal('salary_from_hours', 10, 2)->default(0)
                      ->after('overtime_rate')
                      ->comment('راتب ساعات العمل الأساسية قبل الأوفرتايم');
            }
            if (!Schema::hasColumn('salary_payments', 'salary_from_overtime')) {
                $table->decimal('salary_from_overtime', 10, 2)->default(0)
                      ->after('salary_from_hours')
                      ->comment('قيمة الأوفرتايم');
            }
        });
    }

    public function down(): void
    {
        Schema::table('salary_payments', function (Blueprint $table) {
            $table->dropColumn(['overtime_rate', 'salary_from_hours', 'salary_from_overtime']);
        });
    }
};
