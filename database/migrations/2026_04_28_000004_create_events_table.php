<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        if (!Schema::hasTable('events')) {
            Schema::create('events', function (Blueprint $table) {
                $table->id();
                $table->string('title');
                $table->text('description')->nullable();
                $table->datetime('start_date');
                $table->datetime('end_date')->nullable();
                $table->string('color', 20)->default('#1e3a5f');
                $table->boolean('all_day')->default(false);
                $table->foreignId('created_by')->constrained('users')->cascadeOnDelete();
                $table->timestamps();

                $table->index(['start_date', 'end_date']);
            });
        }
    }

    public function down(): void
    {
        Schema::dropIfExists('events');
    }
};
