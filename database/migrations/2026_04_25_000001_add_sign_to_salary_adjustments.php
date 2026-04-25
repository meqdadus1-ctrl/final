<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('salary_adjustments', function (Blueprint $table) {
            // +1 = إضافة لصالح الموظف | -1 = خصم على الموظف
            // يُستخدم فقط مع نوع "other"، البقية لها إشارة ثابتة
            $table->tinyInteger('sign')->default(-1)->after('type');
        });
    }

    public function down(): void
    {
        Schema::table('salary_adjustments', function (Blueprint $table) {
            $table->dropColumn('sign');
        });
    }
};
