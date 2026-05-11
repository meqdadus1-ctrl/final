<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('performance_reviews', function (Blueprint $table) {
            $table->id();
            $table->foreignId('employee_id')->constrained()->cascadeOnDelete();
            $table->foreignId('reviewer_id')->constrained('users')->cascadeOnDelete();

            $table->year('year');
            $table->enum('quarter', ['Q1', 'Q2', 'Q3', 'Q4']);

            // معايير التقييم (1-5)
            $table->unsignedTinyInteger('punctuality')->comment('الانضباط والحضور');
            $table->unsignedTinyInteger('quality')->comment('جودة العمل');
            $table->unsignedTinyInteger('productivity')->comment('الإنتاجية');
            $table->unsignedTinyInteger('teamwork')->comment('العمل الجماعي');
            $table->unsignedTinyInteger('communication')->comment('التواصل');

            $table->decimal('overall_score', 4, 2)->storedAs(
                '(punctuality + quality + productivity + teamwork + communication) / 5.0'
            );

            $table->text('strengths')->nullable();
            $table->text('improvements')->nullable();
            $table->text('notes')->nullable();

            $table->enum('status', ['draft', 'final'])->default('draft');
            $table->timestamps();

            $table->unique(['employee_id', 'year', 'quarter']);
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('performance_reviews');
    }
};
