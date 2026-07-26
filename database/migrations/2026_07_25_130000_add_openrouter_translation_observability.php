<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        if (Schema::hasTable('product_translation_runs')) {
            Schema::table('product_translation_runs', function (Blueprint $table) {
                if (!Schema::hasColumn('product_translation_runs', 'provider')) $table->string('provider', 40)->nullable();
                if (!Schema::hasColumn('product_translation_runs', 'requested_model')) $table->string('requested_model', 160)->nullable();
                if (!Schema::hasColumn('product_translation_runs', 'actual_model')) $table->string('actual_model', 160)->nullable();
                if (!Schema::hasColumn('product_translation_runs', 'prompt_version')) $table->string('prompt_version', 80)->nullable();
                if (!Schema::hasColumn('product_translation_runs', 'last_operation_id')) $table->uuid('last_operation_id')->nullable();
                if (!Schema::hasColumn('product_translation_runs', 'last_request_duration_ms')) $table->unsignedInteger('last_request_duration_ms')->nullable();
                if (!Schema::hasColumn('product_translation_runs', 'last_input_characters')) $table->unsignedInteger('last_input_characters')->nullable();
                if (!Schema::hasColumn('product_translation_runs', 'last_prompt_tokens')) $table->unsignedInteger('last_prompt_tokens')->nullable();
                if (!Schema::hasColumn('product_translation_runs', 'last_completion_tokens')) $table->unsignedInteger('last_completion_tokens')->nullable();
                if (!Schema::hasColumn('product_translation_runs', 'last_total_tokens')) $table->unsignedInteger('last_total_tokens')->nullable();
                if (!Schema::hasColumn('product_translation_runs', 'last_retry_decision')) $table->string('last_retry_decision', 40)->nullable();
            });
        }

        if (Schema::hasTable('product_translation_run_items')) {
            Schema::table('product_translation_run_items', function (Blueprint $table) {
                if (!Schema::hasColumn('product_translation_run_items', 'provider')) $table->string('provider', 40)->nullable();
                if (!Schema::hasColumn('product_translation_run_items', 'requested_model')) $table->string('requested_model', 160)->nullable();
                if (!Schema::hasColumn('product_translation_run_items', 'actual_model')) $table->string('actual_model', 160)->nullable();
                if (!Schema::hasColumn('product_translation_run_items', 'operation_id')) $table->uuid('operation_id')->nullable();
                if (!Schema::hasColumn('product_translation_run_items', 'request_duration_ms')) $table->unsignedInteger('request_duration_ms')->nullable();
                if (!Schema::hasColumn('product_translation_run_items', 'input_characters')) $table->unsignedInteger('input_characters')->nullable();
                if (!Schema::hasColumn('product_translation_run_items', 'prompt_version')) $table->string('prompt_version', 80)->nullable();
                if (!Schema::hasColumn('product_translation_run_items', 'retry_decision')) $table->string('retry_decision', 40)->nullable();
                if (!Schema::hasColumn('product_translation_run_items', 'translation_hash')) $table->string('translation_hash', 64)->nullable()->index();
            });
        }
    }

    public function down(): void
    {
        foreach (['product_translation_runs', 'product_translation_run_items'] as $tableName) {
            if (!Schema::hasTable($tableName)) continue;
            Schema::table($tableName, function (Blueprint $table) use ($tableName) {
                $columns = $tableName === 'product_translation_runs'
                    ? ['provider', 'requested_model', 'actual_model', 'prompt_version', 'last_operation_id', 'last_request_duration_ms', 'last_input_characters', 'last_prompt_tokens', 'last_completion_tokens', 'last_total_tokens', 'last_retry_decision']
                    : ['provider', 'requested_model', 'actual_model', 'operation_id', 'request_duration_ms', 'input_characters', 'prompt_version', 'retry_decision', 'translation_hash'];
                foreach ($columns as $column) {
                    if (Schema::hasColumn($tableName, $column)) $table->dropColumn($column);
                }
            });
        }
    }
};
