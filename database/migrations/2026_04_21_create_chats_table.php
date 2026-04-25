<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    /**
     * Run the migrations.
     */
    public function up(): void
    {
        Schema::create('chats', function (Blueprint $table) {
            $table->id();
            $table->foreignId('employee_id')->constrained()->onDelete('cascade');
            $table->enum('sender_type', ['employee', 'admin'])->default('employee');
            $table->longText('message')->nullable();
            $table->string('attachment_path')->nullable();
            $table->enum('attachment_type', ['image', 'document'])->nullable();
            $table->string('attachment_name')->nullable();
            $table->boolean('is_read')->default(false);
            $table->timestamps();

            // Indexes
            $table->index('employee_id');
            $table->index('sender_type');
            $table->index('is_read');
            $table->index('created_at');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('chats');
    }
};
