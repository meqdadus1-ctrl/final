<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::dropIfExists('employee_withdrawals');
    }

    public function down(): void
    {
        Schema::create('employee_withdrawals', function (Blueprint $table) {
            $table->id();
            $table->foreignId('employee_id')->constrained()->cascadeOnDelete();
            $table->date('date');
            $table->decimal('amount', 10, 2);
            $table->string('product_description', 500)->nullable();
            $table->decimal('product_price', 10, 2)->nullable();
            $table->enum('status', ['pending', 'settled', 'cancelled'])->default('pending');
            $table->timestamp('settled_at')->nullable();
            $table->foreignId('settled_in_payment_id')->nullable()->constrained('salary_payments')->nullOnDelete();
            $table->text('notes')->nullable();
            $table->foreignId('created_by')->nullable()->constrained('users')->nullOnDelete();
            $table->timestamps();
        });
    }
};
