<?php
require __DIR__.'/../vendor/autoload.php';
$app = require_once __DIR__.'/../bootstrap/app.php';
$kernel = $app->make(Illuminate\Contracts\Console\Kernel::class);
$kernel->bootstrap();

use Illuminate\Support\Facades\DB;

try {
    $filteredProductIds = \App\Models\Product::query()->pluck('id')->toArray();
    
    // Fallback if empty to avoid SQL syntax error with empty IN clause
    if (empty($filteredProductIds)) {
        echo "SUCCESS: No products found to count.\n";
        exit;
    }

    $mainCategories = DB::table('products')
        ->whereIn('id', $filteredProductIds)
        ->whereNotNull('category_id')
        ->select('id as product_id', 'category_id');

    $pivotCategories = DB::table('product_categories')
        ->whereIn('product_id', $filteredProductIds)
        ->select('product_id', 'category_id');

    $combinedCategories = $mainCategories->union($pivotCategories);

    $productCountsSubCategory = DB::table(DB::raw("({$combinedCategories->toSql()}) as combined"))
        ->mergeBindings($combinedCategories)
        ->select('category_id', DB::raw('COUNT(DISTINCT product_id) as count'))
        ->groupBy('category_id')
        ->pluck('count', 'category_id');

    echo "SUCCESS: Product counts query executed.\n";
    echo "Categories mapped: " . $productCountsSubCategory->count() . "\n";
} catch (\Exception $e) {
    echo "ERROR: " . $e->getMessage() . "\n";
}
