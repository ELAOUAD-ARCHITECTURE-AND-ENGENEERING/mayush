<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;
use Illuminate\Support\Facades\DB;
use App\Models\ProductStock;
use App\Models\Product;

return new class extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        DB::transaction(function () {
            // Process product_stocks to extract legacy dimensions
            ProductStock::chunk(100, function ($stocks) {
                foreach ($stocks as $stock) {
                    // Extract dimensions if they are missing in columns but exist in the variant string
                    if ($stock->length == 0 && $stock->width == 0 && $stock->height == 0 && !empty($stock->variant)) {
                        $str = $stock->variant;
                        if (preg_match('/(\d+(?:\.\d+)?)\s*x\s*(\d+(?:\.\d+)?)\s*x\s*(\d+(?:\.\d+)?)\s*([a-zA-Z]+)/i', $str, $matches)) {
                            $stock->length = $matches[1];
                            $stock->width = $matches[2];
                            $stock->height = $matches[3];
                            $stock->dimension_unit = strtolower($matches[4]);
                            $stock->save();
                        }
                    }
                }
            });

            // Note: The design decision is to preserve the dimensional details (L, W, H) 
            // inside the choice_options JSON column in the products table to ensure 
            // legacy order data and variant structures do not break.
            // Hence, we intentionally skip modifying the products.choice_options JSON here.
        });
    }

    /**
     * Reverse the migrations.
     *
     * @return void
     */
    public function down()
    {
        // No down migration required for data sanitization
    }
};
