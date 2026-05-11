<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        // أضف index مستقل على employee_id حتى لا يعتمد FK على الـ unique index
        Schema::table('performance_reviews', function (Blueprint $table) {
            $table->index('employee_id', 'pr_employee_id_idx');
        });

        Schema::table('performance_reviews', function (Blueprint $table) {
            $table->dropUnique('performance_reviews_employee_id_year_quarter_unique');
            $table->dropColumn('quarter');
            $table->unsignedTinyInteger('week_number')->after('year')->comment('رقم الأسبوع 1-52');
        });

        Schema::table('performance_reviews', function (Blueprint $table) {
            $table->unique(['employee_id', 'year', 'week_number']);
        });
    }

    public function down(): void
    {
        Schema::table('performance_reviews', function (Blueprint $table) {
            $table->dropUnique(['employee_id', 'year', 'week_number']);
            $table->dropColumn('week_number');
            $table->enum('quarter', ['Q1', 'Q2', 'Q3', 'Q4'])->after('year');
            $table->unique(['employee_id', 'year', 'quarter']);
        });
    }
};
