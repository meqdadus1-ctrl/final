<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('salary_payments', function (Blueprint $table) {
            // طلب كشف حساب من الموظف
            $table->boolean('statement_requested')->default(false)->after('notes');
            $table->timestamp('statement_requested_at')->nullable()->after('statement_requested');
            $table->enum('statement_status', ['none', 'pending', 'approved', 'sent'])
                  ->default('none')->after('statement_requested_at');
        });
    }

    public function down(): void
    {
        Schema::table('salary_payments', function (Blueprint $table) {
            $table->dropColumn(['statement_requested', 'statement_requested_at', 'statement_status']);
        });
    }
};
