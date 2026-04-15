<?php
require 'vendor/autoload.php';
$app = require_once 'bootstrap/app.php';
$app->make(Illuminate\Contracts\Console\Kernel::class)->bootstrap();

// Let's get the counts using the SearchController logic
$filteredProductIds = App\Http\Helpers::filter_products(App\Models\Product::query())->pluck('id')->toArray();

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

$categoriesMap = App\Models\Category::pluck('name', 'id')->toArray();

echo "Counts from combined query (unique by category):\n";
foreach ([1, 8, 341] as $id) {
    $count = $productCountsSubCategory[$id] ?? 0;
    echo "{$categoriesMap[$id]} ($id): {$count}\n";
}

$allCategories = App\Models\Category::with('childrenCategories')->where('level', 0)->get();

function getCategoryCount($category, $productCounts) {
    $ownCount = $productCounts[$category->id] ?? 0;
    $totalCount = $ownCount;
    if (!empty($category->childrenCategories)) {
        foreach ($category->childrenCategories as $child) {
            $totalCount += getCategoryCount($child, $productCounts);
        }
    }
    $category->products_count = $totalCount;
    return $totalCount;
}

foreach ($allCategories as $cat) {
    getCategoryCount($cat, $productCountsSubCategory);
}

echo "\nAfter recursive summation:\n";
foreach ($allCategories as $cat) {
    if (in_array($cat->id, [1, 8, 341])) {
        echo "{$cat->name} ({$cat->id}): {$cat->products_count}\n";
    }
    foreach ($cat->childrenCategories as $child) {
        if (in_array($child->id, [1, 8, 341])) {
            echo "  {$child->name} ({$child->id}): {$child->products_count}\n";
        }
    }
}

// Now let's do a TRUE distinct count for the whole branch
function getAllDescendantsIds($categoryId) {
    $ids = [$categoryId];
    $children = App\Models\Category::where('parent_id', $categoryId)->pluck('id')->toArray();
    foreach ($children as $childId) {
        $ids = array_merge($ids, getAllDescendantsIds($childId));
    }
    return $ids;
}

echo "\nTrue unique product counts (no double counting):\n";
foreach ([1, 8, 341] as $id) {
    $branchIds = getAllDescendantsIds($id);
    
    $mainCount = DB::table('products')
        ->whereIn('id', $filteredProductIds)
        ->whereIn('category_id', $branchIds)
        ->select('id as product_id');
        
    $pivotCount = DB::table('product_categories')
        ->whereIn('product_id', $filteredProductIds)
        ->whereIn('category_id', $branchIds)
        ->select('product_id');
        
    $combinedCount = $mainCount->union($pivotCount);
    
    $trueCount = DB::table(DB::raw("({$combinedCount->toSql()}) as combined"))
        ->mergeBindings($combinedCount)
        ->count(DB::raw('DISTINCT product_id'));
        
    echo "{$categoriesMap[$id]} ($id): {$trueCount}\n";
}

