<?php

namespace App\Services;

use AizPackages\CombinationGenerate\Services\CombinationService;
use App\Models\ProductStock;
use App\Models\InventoryLog;
use App\Utility\ProductUtility;
use Illuminate\Support\Facades\Log;
use Auth;

class ProductStockService
{
    public function store(array $data, $product)
    {
        $previousTotalQty = $product->stocks()->sum('qty');
        
        //Log::info('Product Stock Request:', $data);
        $collection = collect($data);

        $options = ProductUtility::get_attribute_options($collection);
        
        //Generates the combinations of customer choice options
        $combinations = (new CombinationService())->generate_combination($options);
        
        $variant = '';
        if (count($combinations) > 0) {
            $product->variant_product = 1;
            $product->save();
            foreach ($combinations as $key => $combination) {
                $str = ProductUtility::get_combination_string($combination, $collection);
                $product_stock = new ProductStock();
                $product_stock->product_id = $product->id;
                $product_stock->variant = $str;
                $product_stock->price = request()['price_' . str_replace('.', '_', $str)];
                $product_stock->sku = request()['sku_' . str_replace('.', '_', $str)];
                $product_stock->qty = request()['qty_' . str_replace('.', '_', $str)];
                $product_stock->image = request()['img_' . str_replace('.', '_', $str)];

                // Parse dimensions from variant string if applicable
                foreach ($combination as $item) {
                    if (preg_match('/(\d+(?:\.\d+)?)\s*x\s*(\d+(?:\.\d+)?)\s*x\s*(\d+(?:\.\d+)?)\s*([a-zA-Z]+)/i', $item, $matches)) {
                        $product_stock->length = $matches[1];
                        $product_stock->width = $matches[2];
                        $product_stock->height = $matches[3];
                        $product_stock->dimension_unit = $matches[4];
                    }
                }

                $product_stock->save();

                // Log change (MA-105)
                InventoryLog::create([
                    'product_id' => $product->id,
                    'user_id' => Auth::check() ? Auth::user()->id : null,
                    'quantity_delta' => $product_stock->qty,
                    'previous_stock' => 0, // Since it recreates or we don't know the delta easily here
                    'current_stock' => $product_stock->qty,
                    'reason' => 'manual',
                ]);
            }
        } else {
            $product->variant_product = 0;
            $product->save();
            unset($collection['colors_active'], $collection['colors'], $collection['choice_no']);
            $qty = $collection['current_stock'];
            $price = $collection['unit_price'];
            unset($collection['current_stock']);

            $data = $collection->merge(compact('variant', 'qty', 'price'))->toArray();
            
            // Add dimensions to base stock tracking if variant-less
            $data['length'] = $collection['length'] ?? null;
            $data['width'] = $collection['width'] ?? null;
            $data['height'] = $collection['height'] ?? null;
            $data['dimension_unit'] = $collection['dimension_unit'] ?? 'cm';
            
            unset($data['colors_active'], $data['choice_no'], $data['unit_price'], $data['sku'], $data['current_stock'], $data['colors']);

            $product_stock = ProductStock::create($data);

            // Log change (MA-105)
            InventoryLog::create([
                'product_id' => $product->id,
                'user_id' => Auth::check() ? Auth::user()->id : null,
                'quantity_delta' => $product_stock->qty,
                'previous_stock' => 0, 
                'current_stock' => $product_stock->qty,
                'reason' => 'manual',
            ]);
        }

        // Fire restock event (MAY-107)
        $newTotalQty = $product->stocks()->sum('qty');
        if ($previousTotalQty <= 0 && $newTotalQty > 0) {
            event(new \App\Events\ProductRestockedEvent($product));
        }
    }

    public function product_duplicate_store($product_stocks , $product_new)
    {
        foreach ($product_stocks as $key => $stock) {
            $product_stock              = new ProductStock;
            $product_stock->product_id  = $product_new->id;
            $product_stock->variant     = $stock->variant;
            $product_stock->price       = $stock->price;
            $product_stock->sku         = null;
            $product_stock->qty         = $stock->qty;
            $product_stock->length      = $stock->length;
            $product_stock->width       = $stock->width;
            $product_stock->height      = $stock->height;
            $product_stock->dimension_unit = $stock->dimension_unit;
            $product_stock->save();
        }
    }
}
