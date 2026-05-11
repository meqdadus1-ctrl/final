<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        if (!Schema::hasTable('tasks')) {
            Schema::create('tasks', function (Blueprint $table) {
                $table->id();
                $table->string('title');
                $table->text('description')->nullable();
                $table->enum('priority', ['low', 'medium', 'high'])->default('medium');
                $table->enum('status', ['pending', 'in_progress', 'completed'])->default('pending');
                $table->date('due_date')->nullable();
                $table->foreignId('department_id')->nullable()->constrained('departments')->nullOnDelete();
                $table->foreignId('parent_id')->nullable()->constrained('tasks')->cascadeOnDelete();
                $table->foreignId('created_by')->constrained('users')->cascadeOnDelete();
                $table->timestamps();

                $table->index(['status', 'priority']);
                $table->index('department_id');
                $table->index('due_date');
                $table->index('parent_id');
            });
        }
    }

    public function down(): void
    {
        Schema::dropIfExists('tasks');
    }
};
