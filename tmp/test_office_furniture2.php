<?php
require __DIR__.'/../vendor/autoload.php';
$app = require_once __DIR__.'/../bootstrap/app.php';
$kernel = $app->make(Illuminate\Contracts\Console\Kernel::class);
$kernel->bootstrap();

$filteredProductIds = filter_products(\App\Models\Product::query())->pluck('id')->toArray();

$mainCategories = \Illuminate\Support\Facades\DB::table('products')
    ->whereIn('id', $filteredProductIds)
    ->whereNotNull('category_id')
    ->select('id as product_id', 'category_id');

$pivotCategories = \Illuminate\Support\Facades\DB::table('product_categories')
    ->whereIn('product_id', $filteredProductIds)
    ->select('product_id', 'category_id');

$combinedCategories = $mainCategories->union($pivotCategories);

$productCountsSubCategory = \Illuminate\Support\Facades\DB::table(\Illuminate\Support\Facades\DB::raw("({$combinedCategories->toSql()}) as combined"))
    ->mergeBindings($combinedCategories)
    ->select('category_id', \Illuminate\Support\Facades\DB::raw('COUNT(DISTINCT product_id) as count'))
    ->groupBy('category_id')
    ->pluck('count', 'category_id');

$officeFurniture = \App\Models\Category::with('childrenCategories')->where('name', 'like', '%Office%')->first();
$controller = new App\Http\Controllers\SearchController();
$controller->categoryProductCount($officeFurniture, $productCountsSubCategory);

echo "Total products: " . count($filteredProductIds) . "\n";
echo "Office Furniture total count: " . $officeFurniture->products_count . "\n";

// Let's also check if any products are in Office Furniture directly via Product::where('category_id', 341)->count();
$directCount = \App\Models\Product::where('category_id', $officeFurniture->id)->count();
$filteredDirectCount = filter_products(\App\Models\Product::where('category_id', $officeFurniture->id))->count();

echo "Direct products (raw): $directCount\n";
echo "Direct products (filtered): $filteredDirectCount\n";

