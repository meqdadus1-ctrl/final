<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('messenger_settings', function (Blueprint $table) {
            $table->id();

            // المزود: bulksms | whatsapp_cloud
            $table->string('driver', 30)->default('bulksms');

            // ===== SMS API (BulkSMS وما شابه) =====
            $table->string('sms_api_url')->nullable();
            $table->text('sms_api_auth')->nullable();       // Basic auth أو API key

            // ===== WhatsApp Cloud API (Meta) =====
            $table->string('wa_phone_number_id')->nullable();
            $table->text('wa_access_token')->nullable();

            // ===== إعدادات عامة =====
            $table->string('country_code', 10)->default('970');  // كود الدولة الافتراضي
            $table->text('salary_template')->nullable();          // قالب رسالة الراتب
            $table->boolean('is_active')->default(false);

            $table->timestamps();
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('messenger_settings');
    }
};
