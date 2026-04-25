<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Support\Facades\DB;

/**
 * يضيف قيمة 'deferred' لعمود payment_method في salary_payments
 * ويغيّر القيمة الافتراضية إلى 'deferred' (ترحيل للرصيد)
 */
return new class extends Migration
{
    public function up(): void
    {
        // MySQL: تعديل ENUM يتطلب تعريف كل القيم من جديد
        DB::statement("ALTER TABLE salary_payments MODIFY COLUMN payment_method ENUM('bank','cash','deferred') NOT NULL DEFAULT 'deferred'");
    }

    public function down(): void
    {
        // إعادة للوضع السابق (تحويل deferred → cash أولاً لتجنب خطأ)
        DB::statement("UPDATE salary_payments SET payment_method = 'cash' WHERE payment_method = 'deferred'");
        DB::statement("ALTER TABLE salary_payments MODIFY COLUMN payment_method ENUM('bank','cash') NOT NULL DEFAULT 'bank'");
    }
};
