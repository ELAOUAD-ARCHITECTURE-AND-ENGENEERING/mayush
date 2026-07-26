<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        if (!Schema::hasTable('product_translation_runs') || Schema::hasColumn('product_translation_runs', 'next_retry_at')) {
            return;
        }

        Schema::table('product_translation_runs', function (Blueprint $table): void {
            $table->timestamp('next_retry_at')->nullable()->after('last_progress_at')->index();
        });
    }

    public function down(): void
    {
        if (Schema::hasTable('product_translation_runs') && Schema::hasColumn('product_translation_runs', 'next_retry_at')) {
            Schema::table('product_translation_runs', function (Blueprint $table): void {
                $table->dropIndex(['next_retry_at']);
                $table->dropColumn('next_retry_at');
            });
        }
    }
};
