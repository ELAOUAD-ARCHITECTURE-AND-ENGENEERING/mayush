<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        if (!Schema::hasTable('product_translation_runs') || Schema::hasColumn('product_translation_runs', 'limit_count')) {
            return;
        }

        Schema::table('product_translation_runs', function (Blueprint $table): void {
            $table->unsignedInteger('limit_count')->nullable()->after('total_candidates');
        });
    }

    public function down(): void
    {
        if (Schema::hasTable('product_translation_runs') && Schema::hasColumn('product_translation_runs', 'limit_count')) {
            Schema::table('product_translation_runs', function (Blueprint $table): void {
                $table->dropColumn('limit_count');
            });
        }
    }
};
