<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        if (!Schema::hasTable('employee_withdrawals')) {
            Schema::create('employee_withdrawals', function (Blueprint $table) {
                $table->id();
                $table->foreignId('employee_id')->constrained('employees')->cascadeOnDelete();
                $table->date('date');
                $table->decimal('amount', 10, 2);
                $table->string('product_description', 500)->nullable();
                $table->decimal('product_price', 10, 2)->nullable();
                $table->enum('status', ['pending', 'settled', 'cancelled'])->default('pending');
                $table->timestamp('settled_at')->nullable();
                $table->foreignId('settled_in_payment_id')->nullable()
                      ->constrained('salary_payments')->nullOnDelete();
                $table->text('notes')->nullable();
                $table->foreignId('created_by')->nullable()->constrained('users')->nullOnDelete();
                $table->timestamps();

                $table->index(['employee_id', 'status']);
                $table->index('date');
            });
        }

        // تحويل السلف إلى أسبوعية: إضافة عمود installment_type (إذا غير موجود)
        if (Schema::hasTable('loans') && !Schema::hasColumn('loans', 'installment_type')) {
            Schema::table('loans', function (Blueprint $table) {
                $table->string('installment_type', 20)->default('weekly')->after('installment_amount');
            });
        }
    }

    public function down(): void
    {
        Schema::dropIfExists('employee_withdrawals');

        if (Schema::hasTable('loans') && Schema::hasColumn('loans', 'installment_type')) {
            Schema::table('loans', function (Blueprint $table) {
                $table->dropColumn('installment_type');
            });
        }
    }
};
