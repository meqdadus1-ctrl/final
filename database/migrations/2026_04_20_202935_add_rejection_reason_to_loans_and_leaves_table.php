<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        // إضافة سبب الرفض على جدول السلف
        Schema::table('loans', function (Blueprint $table) {
            $table->text('rejection_reason')->nullable()->after('description');
        });

        // إضافة سبب الرفض على جدول الإجازات
        Schema::table('leave_requests', function (Blueprint $table) {
            $table->text('rejection_reason')->nullable()->after('reason');
        });
    }

    public function down(): void
    {
        Schema::table('loans', function (Blueprint $table) {
            $table->dropColumn('rejection_reason');
        });

        Schema::table('leave_requests', function (Blueprint $table) {
            $table->dropColumn('rejection_reason');
        });
    }
};
