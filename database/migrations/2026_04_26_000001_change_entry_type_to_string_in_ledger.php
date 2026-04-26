<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        // SQLite doesn't support changeColumn on enum — use raw for MySQL/MariaDB
        DB::statement('ALTER TABLE employee_ledger MODIFY entry_type VARCHAR(100) NOT NULL DEFAULT \'adjustment\'');
    }

    public function down(): void
    {
        // Revert is destructive if values don't match enum — skipped safely
    }
};
