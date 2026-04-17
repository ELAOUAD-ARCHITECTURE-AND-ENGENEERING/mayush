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
        Schema::table('products', function (Blueprint $blueprint) {
            $blueprint->decimal('length', 10, 2)->nullable()->after('weight');
            $blueprint->decimal('width', 10, 2)->nullable()->after('length');
            $blueprint->decimal('height', 10, 2)->nullable()->after('width');
        });

        Schema::table('product_stocks', function (Blueprint $blueprint) {
            $blueprint->decimal('length', 10, 2)->nullable()->after('variant');
            $blueprint->decimal('width', 10, 2)->nullable()->after('length');
            $blueprint->decimal('height', 10, 2)->nullable()->after('width');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('products', function (Blueprint $blueprint) {
            $blueprint->dropColumn(['length', 'width', 'height']);
        });

        Schema::table('product_stocks', function (Blueprint $blueprint) {
            $blueprint->dropColumn(['length', 'width', 'height']);
        });
    }
};
