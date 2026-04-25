<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('salary_payments', function (Blueprint $table) {
            $table->decimal('salary_multiplier', 5, 2)->default(1)->after('hourly_rate')
                  ->comment('معامل الراتب: 1 = عادي، 2 = ضعف الراتب');
        });
    }

    public function down(): void
    {
        Schema::table('salary_payments', function (Blueprint $table) {
            $table->dropColumn('salary_multiplier');
        });
    }
};
