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
        if (\Illuminate\Support\Facades\Schema::hasTable('frequently_bought_products')) {
            Schema::table('frequently_bought_products', function (Blueprint $table) {
                if (!Schema::hasColumn('frequently_bought_products', 'source')) {
                    $table->string('source')->default('manual')->after('category_id'); // 'manual' or 'automated'
                }
                if (!Schema::hasColumn('frequently_bought_products', 'affinity_score')) {
                    $table->float('affinity_score')->nullable()->after('source');
                }
            });
        }
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('frequently_bought_products', function (Blueprint $table) {
            $table->dropColumn(['source', 'affinity_score']);
        });
    }
};
