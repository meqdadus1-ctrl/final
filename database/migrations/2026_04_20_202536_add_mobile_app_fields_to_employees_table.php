<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('employees', function (Blueprint $table) {

            // FCM token للإشعارات
            $table->string('fcm_token')->nullable()->after('user_id');

            // نوع البنك (bank_of_palestine / pal_pay / jawwal_pay)
            $table->string('bank_type')->nullable()->after('bank_id');

            // بيانات البنك المعلّقة (تنتظر اعتماد الإدارة)
            $table->string('pending_bank_type')->nullable()->after('bank_account');
            $table->string('pending_account_name')->nullable()->after('pending_bank_type');
            $table->string('pending_bank_account')->nullable()->after('pending_account_name');
            $table->boolean('bank_info_pending')->default(false)->after('pending_bank_account');

            // قفل بيانات البنك بعد الاعتماد
            $table->boolean('bank_info_locked')->default(false)->after('bank_info_pending');
        });
    }

    public function down(): void
    {
        Schema::table('employees', function (Blueprint $table) {
            $table->dropColumn([
                'fcm_token',
                'bank_type',
                'pending_bank_type',
                'pending_account_name',
                'pending_bank_account',
                'bank_info_pending',
                'bank_info_locked',
            ]);
        });
    }
};
