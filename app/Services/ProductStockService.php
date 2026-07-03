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

            $indexMap = [];
            foreach ($combinations as $key => $combination) {
                $str = ProductUtility::get_combination_string($combination, $collection);

                $normalized = strtolower((string) preg_replace('/\s+/', '', trim($str)));
                if (!isset($indexMap[$normalized])) {
                    $indexMap[$normalized] = 0;
                } else {
                    $indexMap[$normalized]++;
                }
                $occurrenceIndex = $indexMap[$normalized];

                $suffix = str_replace('.', '_', $str);

                $prices = request()['price_' . $suffix];
                $skus = request()->input('sku_' . $suffix);
                $qtys = request()['qty_' . $suffix];
                $images = request()['img_' . $suffix];
                $lengths = request()['length_' . $suffix];
                $widths = request()['width_' . $suffix];
                $heights = request()['height_' . $suffix];
                $units = request()['unit_' . $suffix];

                $product_stock = new ProductStock();
                $product_stock->product_id = $product->id;
                $product_stock->variant = $str;

                $product_stock->price = is_array($prices) ? ($prices[$occurrenceIndex] ?? 0) : $prices;
                $submittedSku = is_array($skus) ? ($skus[$occurrenceIndex] ?? '') : $skus;
                $product_stock->sku = $this->sku($submittedSku);
                $product_stock->qty = is_array($qtys) ? ($qtys[$occurrenceIndex] ?? 0) : $qtys;
                $product_stock->image = is_array($images) ? ($images[$occurrenceIndex] ?? null) : $images;

                // Variant-specific dimensions
                $product_stock->length = is_array($lengths) ? ($lengths[$occurrenceIndex] ?? 0) : ($lengths ?? 0);
                $product_stock->width = is_array($widths) ? ($widths[$occurrenceIndex] ?? 0) : ($widths ?? 0);
                $product_stock->height = is_array($heights) ? ($heights[$occurrenceIndex] ?? 0) : ($heights ?? 0);
                $product_stock->dimension_unit = is_array($units) ? ($units[$occurrenceIndex] ?? 'cm') : ($units ?? 'cm');

                // Parse dimensions from variant string if applicable (fallback)
                if ($product_stock->length == 0 && $product_stock->width == 0 && $product_stock->height == 0) {
                    foreach ($combination as $item) {
                        if (preg_match('/(\d+(?:\.\d+)?)\s*x\s*(\d+(?:\.\d+)?)\s*x\s*(\d+(?:\.\d+)?)\s*([a-zA-Z]+)/i', $item, $matches)) {
                            $product_stock->length = $matches[1];
                            $product_stock->width = $matches[2];
                            $product_stock->height = $matches[3];
                            $product_stock->dimension_unit = $matches[4];
                        }
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
            $data['product_id'] = $product->id;
            $data['sku'] = $this->sku($data['sku'] ?? null);
            
            // Add dimensions to base stock tracking if variant-less
            $data['length'] = $collection['length'] ?? null;
            $data['width'] = $collection['width'] ?? null;
            $data['height'] = $collection['height'] ?? null;
            $data['dimension_unit'] = $collection['dimension_unit'] ?? 'cm';
            
            unset($data['colors_active'], $data['choice_no'], $data['unit_price'], $data['current_stock'], $data['colors']);

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
            $product_stock->sku         = $this->sku(null);
            $product_stock->qty         = $stock->qty;
            $product_stock->length      = $stock->length;
            $product_stock->width       = $stock->width;
            $product_stock->height      = $stock->height;
            $product_stock->dimension_unit = $stock->dimension_unit;
            $product_stock->save();
        }
    }

    private function sku($submittedSku): string
    {
        return (new ProductSkuService())->available($submittedSku);
    }
}
