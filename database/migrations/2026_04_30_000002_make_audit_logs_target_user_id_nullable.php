<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        if (!Schema::hasTable('audit_logs') || !Schema::hasColumn('audit_logs', 'target_user_id')) {
            return;
        }

        $driver = DB::getDriverName();
        if ($driver === 'mysql') {
            DB::statement('ALTER TABLE audit_logs MODIFY target_user_id BIGINT UNSIGNED NULL');
        } elseif ($driver === 'pgsql') {
            DB::statement('ALTER TABLE audit_logs ALTER COLUMN target_user_id DROP NOT NULL');
        }
    }

    public function down(): void
    {
        // Intentionally non-destructive: existing failed-login audit rows may have no target user.
    }
};
