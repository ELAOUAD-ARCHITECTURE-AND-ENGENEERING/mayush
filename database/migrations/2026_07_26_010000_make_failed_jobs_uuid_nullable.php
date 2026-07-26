<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        if (DB::getDriverName() !== 'mysql' || !Schema::hasTable('failed_jobs') || !Schema::hasColumn('failed_jobs', 'uuid')) {
            return;
        }

        // Laravel's database failed-job provider in this application version
        // does not always include a UUID in its INSERT payload. A non-null
        // unique column then turns the original queue exception into a second
        // duplicate-key exception and hides the real failure.
        DB::statement("UPDATE failed_jobs SET uuid = NULL WHERE uuid = ''");
        DB::statement('ALTER TABLE failed_jobs MODIFY uuid VARCHAR(255) NULL');
    }

    public function down(): void
    {
        if (DB::getDriverName() !== 'mysql' || !Schema::hasTable('failed_jobs') || !Schema::hasColumn('failed_jobs', 'uuid')) {
            return;
        }

        DB::statement("UPDATE failed_jobs SET uuid = CONCAT('legacy-', id) WHERE uuid IS NULL OR uuid = ''");
        DB::statement('ALTER TABLE failed_jobs MODIFY uuid VARCHAR(255) NOT NULL');
    }
};
