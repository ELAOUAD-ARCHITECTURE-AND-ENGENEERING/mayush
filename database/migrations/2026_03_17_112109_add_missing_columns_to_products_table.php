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
        Schema::table('products', function (Blueprint $table) {
            if (!Schema::hasColumn('products', 'flat_shipping_cost')) {
                $table->double('flat_shipping_cost', 20, 2)->default(0)->after('unit_price');
            }
            if (!Schema::hasColumn('products', 'is_quantity_multiplied')) {
                $table->integer('is_quantity_multiplied')->default(0)->after('flat_shipping_cost');
            }
            if (!Schema::hasColumn('products', 'est_shipping_days')) {
                $table->integer('est_shipping_days')->default(0)->after('is_quantity_multiplied');
            }
            if (!Schema::hasColumn('products', 'wholesale_product')) {
                $table->integer('wholesale_product')->default(0)->after('digital');
            }
            if (!Schema::hasColumn('products', 'auction_product')) {
                $table->integer('auction_product')->default(0)->after('wholesale_product');
            }
            if (!Schema::hasColumn('products', 'earn_point')) {
                $table->double('earn_point', 8, 2)->default(0)->after('auction_product');
            }
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('products', function (Blueprint $table) {
            //
        });
    }
};
