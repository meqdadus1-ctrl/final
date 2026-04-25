<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('mobile_notifications', function (Blueprint $table) {
            $table->id();
            $table->foreignId('employee_id')->constrained()->cascadeOnDelete();
            $table->string('type');          // loan_request, leave_request, statement_request, bank_update, salary_paid, etc.
            $table->string('title');
            $table->text('body');
            $table->json('data')->nullable(); // بيانات إضافية (loan_id, leave_id, etc.)
            $table->enum('target', ['admin', 'employee'])->default('admin');
            $table->boolean('is_read')->default(false);
            $table->timestamp('read_at')->nullable();
            $table->timestamps();

            $table->index(['employee_id', 'is_read']);
            $table->index('target');
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('mobile_notifications');
    }
};
