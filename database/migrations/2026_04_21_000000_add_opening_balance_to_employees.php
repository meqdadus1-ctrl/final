<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('employees', function (Blueprint $table) {
            if (!Schema::hasColumn('employees', 'opening_balance')) {
                // الرصيد الافتتاحي: موجب = له على الشركة، سالب = عليه للشركة
                $table->decimal('opening_balance', 12, 2)->default(0)->after('status');
            }
        });
    }

    public function down(): void
    {
        Schema::table('employees', function (Blueprint $table) {
            if (Schema::hasColumn('employees', 'opening_balance')) {
                $table->dropColumn('opening_balance');
            }
        });
    }
};
