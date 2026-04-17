<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;
use App\Models\Product;
use App\Models\ProductStock;

return new class extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        // Add dimension_unit to product_stocks
        if (!Schema::hasColumn('product_stocks', 'dimension_unit')) {
            Schema::table('product_stocks', function (Blueprint $table) {
                $table->string('dimension_unit')->nullable()->default('cm')->after('height');
            });
        }

        // Migrate existing data from products to product_stocks
        // Only if columns still exist in products
        if (Schema::hasColumn('products', 'length')) {
            Product::chunk(100, function ($products) {
                foreach ($products as $product) {
                    if ($product->length || $product->width || $product->height) {
                        ProductStock::where('product_id', $product->id)->each(function ($stock) use ($product) {
                            // Only update if variation fields are currently empty
                            if (!$stock->length && !$stock->width && !$stock->height) {
                                $stock->update([
                                    'length' => $product->length,
                                    'width'  => $product->width,
                                    'height' => $product->height,
                                    'dimension_unit' => 'cm'
                                ]);
                            }
                        });
                    }
                }
            });

            // Drop columns from products
            Schema::table('products', function (Blueprint $table) {
                $table->dropColumn(['length', 'width', 'height']);
            });
        }
    }

    /**
     * Reverse the migrations.
     *
     * @return void
     */
    public function down()
    {
        if (!Schema::hasColumn('products', 'length')) {
            Schema::table('products', function (Blueprint $table) {
                $table->double('length', 20, 2)->nullable()->after('weight');
                $table->double('width', 20, 2)->nullable()->after('length');
                $table->double('height', 20, 2)->nullable()->after('width');
            });
        }

        if (Schema::hasColumn('product_stocks', 'dimension_unit')) {
            Schema::table('product_stocks', function (Blueprint $table) {
                $table->dropColumn('dimension_unit');
            });
        }
    }
};
