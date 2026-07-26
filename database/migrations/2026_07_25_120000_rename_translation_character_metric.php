<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        foreach (['product_translation_runs', 'product_translation_run_items'] as $tableName) {
            if (!Schema::hasTable($tableName) || !Schema::hasColumn($tableName, 'azure_characters') || Schema::hasColumn($tableName, 'translated_characters')) {
                continue;
            }

            Schema::table($tableName, function (Blueprint $table) {
                $table->renameColumn('azure_characters', 'translated_characters');
            });
        }
    }

    public function down(): void
    {
        foreach (['product_translation_runs', 'product_translation_run_items'] as $tableName) {
            if (!Schema::hasTable($tableName) || !Schema::hasColumn($tableName, 'translated_characters') || Schema::hasColumn($tableName, 'azure_characters')) {
                continue;
            }

            Schema::table($tableName, function (Blueprint $table) {
                $table->renameColumn('translated_characters', 'azure_characters');
            });
        }
    }
};
