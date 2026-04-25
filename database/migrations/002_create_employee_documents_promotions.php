<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('employee_documents', function (Blueprint $table) {
            $table->increments('id');
            $table->foreignId('employee_id')->constrained('employees')->cascadeOnDelete();
            $table->string('title');
            $table->enum('type', [
                'id_card','passport','contract','certificate','cv','medical','other',
            ])->default('other');
            $table->string('file_path');
            $table->string('file_name');
            $table->string('file_size')->nullable();
            $table->date('expiry_date')->nullable();
            $table->text('notes')->nullable();
            $table->foreignId('uploaded_by')->nullable()->constrained('users')->nullOnDelete();
            $table->timestamps();

            $table->index('employee_id');
        });

        Schema::create('employee_promotions', function (Blueprint $table) {
            $table->increments('id');
            $table->foreignId('employee_id')->constrained('employees')->cascadeOnDelete();
            $table->enum('type', ['promotion','transfer','demotion','title_change','salary_change'])->default('promotion');
            $table->string('from_title')->nullable();
            $table->string('to_title')->nullable();
            $table->foreignId('from_department_id')->nullable()->constrained('departments')->nullOnDelete();
            $table->foreignId('to_department_id')->nullable()->constrained('departments')->nullOnDelete();
            $table->decimal('from_salary', 10, 2)->nullable();
            $table->decimal('to_salary', 10, 2)->nullable();
            $table->date('effective_date');
            $table->text('reason')->nullable();
            $table->foreignId('approved_by')->nullable()->constrained('users')->nullOnDelete();
            $table->timestamps();

            $table->index('employee_id');
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('employee_promotions');
        Schema::dropIfExists('employee_documents');
    }
};