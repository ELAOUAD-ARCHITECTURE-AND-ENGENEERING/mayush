<?php
require __DIR__.'/../vendor/autoload.php';
$app = require_once __DIR__.'/../bootstrap/app.php';
$kernel = $app->make(Illuminate\Contracts\Console\Kernel::class);
$kernel->bootstrap();

$request = Illuminate\Http\Request::create('/search', 'GET');
$controller = new App\Http\Controllers\SearchController();

// We just want to call categoryProductCount logic
$filteredProductIds = \App\Models\Product::query()->pluck('id')->toArray();
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

if ($officeFurniture) {
    echo "Found category: " . $officeFurniture->name . " (ID: " . $officeFurniture->id . ")\n";
    $controller->categoryProductCount($officeFurniture, $productCountsSubCategory);
    echo "Direct product count: " . $officeFurniture->products_count . "\n";
    
    foreach ($officeFurniture->childrenCategories as $child) {
        $controller->categoryProductCount($child, $productCountsSubCategory);
        echo "- Child: " . $child->name . " (ID: " . $child->id . "), Count: " . $child->products_count . "\n";
    }
} else {
    echo "Office Furniture not found.\n";
}
