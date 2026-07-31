<?php

use Database\Seeders\SupportCasesSeeder;
use Illuminate\Database\Migrations\Migration;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    /**
     * Bootstrap the support-bot catalog on deployments that migrated the
     * tables but did not run the manual seeder.
     *
     * The existing seeder intentionally rebuilds the catalog, so this
     * migration only invokes it when every bot configuration table is empty.
     * It will not overwrite an existing or partially configured production
     * catalog.
     */
    public function up(): void
    {
        $configurationTables = [
            'support_categories',
            'support_cases',
            'case_question_variants',
            'case_required_fields',
            'case_resolution_steps',
            'case_escalation_rules',
        ];

        foreach ($configurationTables as $table) {
            if (!Schema::hasTable($table)) {
                return;
            }
        }

        foreach ($configurationTables as $table) {
            if (DB::table($table)->exists()) {
                return;
            }
        }

        DB::transaction(function (): void {
            app(SupportCasesSeeder::class)->run();
        });
    }

    public function down(): void
    {
        // The catalog is application data and must not be deleted on rollback.
    }
};
