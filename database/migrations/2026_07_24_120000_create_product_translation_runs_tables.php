<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        if (!Schema::hasTable('product_translation_runs')) {
            Schema::create('product_translation_runs', function (Blueprint $table) {
                $table->id();
                $table->unsignedBigInteger('user_id')->nullable()->index();
                $table->string('active_key', 40)->nullable()->unique();
                $table->string('status', 30)->index();
                $table->unsignedInteger('total_candidates')->default(0);
                $table->unsignedInteger('pending_count')->default(0);
                $table->unsignedInteger('processed_count')->default(0);
                $table->unsignedInteger('success_count')->default(0);
                $table->unsignedInteger('skipped_count')->default(0);
                $table->unsignedInteger('failed_count')->default(0);
                $table->unsignedInteger('translated_field_count')->default(0);
                $table->unsignedBigInteger('azure_characters')->default(0);
                $table->unsignedInteger('processing_product_id')->nullable()->index();
                $table->text('failure_reason')->nullable();
                $table->timestamp('started_at')->nullable();
                $table->timestamp('finished_at')->nullable();
                $table->timestamp('last_progress_at')->nullable();
                $table->timestamps();
            });
        }

        if (!Schema::hasTable('product_translation_run_items')) {
            Schema::create('product_translation_run_items', function (Blueprint $table) {
                $table->id();
                $table->foreignId('run_id')->constrained('product_translation_runs')->cascadeOnDelete();
                $table->unsignedInteger('product_id')->index();
                $table->string('status', 30)->index();
                $table->json('missing_fields')->nullable();
                $table->json('source_missing_fields')->nullable();
                $table->unsignedTinyInteger('attempt_count')->default(0);
                $table->unsignedTinyInteger('translated_field_count')->default(0);
                $table->unsignedInteger('azure_characters')->default(0);
                $table->text('error_message')->nullable();
                $table->timestamp('started_at')->nullable();
                $table->timestamp('completed_at')->nullable();
                $table->timestamps();
                $table->unique(['run_id', 'product_id']);
                $table->index(['run_id', 'status']);
            });
        }
    }

    public function down(): void
    {
        Schema::dropIfExists('product_translation_run_items');
        Schema::dropIfExists('product_translation_runs');
    }
};
