<?php
require dirname(__DIR__).'/vendor/autoload.php';
$app = require_once dirname(__DIR__).'/bootstrap/app.php';
$app->make(Illuminate\Contracts\Console\Kernel::class)->bootstrap();
use Illuminate\Support\Facades\DB;
use App\Models\Product;
use App\Models\Category;

echo "=== GROUND TRUTH DATABASE COUNTS ===\n\n";

// 1. Total products in DB
$totalProducts = DB::table('products')->count();
$filteredCount = filter_products(Product::query())->count();
echo "Total products in DB: $totalProducts\n";
echo "After filter_products(): $filteredCount\n\n";

// 2. What does filter_products do?
$published = DB::table('products')->where('published', 1)->count();
$approved = DB::table('products')->where('approved', 1)->count();
$auction = DB::table('products')->where('auction_product', 0)->count();
$pubApproved = DB::table('products')->where('published', 1)->where('approved', 1)->count();
echo "Published=1: $published\n";
echo "Approved=1: $approved\n";
echo "Auction=0: $auction\n";
echo "Published+Approved: $pubApproved\n\n";

// 3. Get all top-level categories and their TRUE product counts
echo "=== ALL TOP-LEVEL CATEGORIES (level=0) ===\n";
$filteredProductIds = filter_products(Product::query())->pluck('id')->toArray();
echo "Filtered product IDs count: " . count($filteredProductIds) . "\n\n";

$topCategories = Category::where('level', 0)->orderBy('order_level', 'desc')->get();

foreach ($topCategories as $cat) {
    // Direct main category_id
    $mainCount = DB::table('products')
        ->whereIn('id', $filteredProductIds)
        ->where('category_id', $cat->id)
        ->count();
    
    // Pivot table
    $pivotCount = DB::table('product_categories')
        ->whereIn('product_id', $filteredProductIds)
        ->where('category_id', $cat->id)
        ->count();
    
    // Get all descendant category IDs
    $descendantIds = getAllDescendants($cat->id);
    $allBranchIds = array_merge([$cat->id], $descendantIds);
    
    // True unique count for entire branch
    $mainBranch = DB::table('products')
        ->whereIn('id', $filteredProductIds)
        ->whereIn('category_id', $allBranchIds)
        ->pluck('id')->toArray();
    
    $pivotBranch = DB::table('product_categories')
        ->whereIn('product_id', $filteredProductIds)
        ->whereIn('category_id', $allBranchIds)
        ->pluck('product_id')->toArray();
    
    $trueUnique = count(array_unique(array_merge($mainBranch, $pivotBranch)));
    
    $childCount = count($descendantIds);
    echo "{$cat->name} (ID {$cat->id}): own_main=$mainCount, own_pivot=$pivotCount, descendants=$childCount, TRUE_BRANCH_UNIQUE=$trueUnique\n";
}

// 4. Specifically check Office Furniture (341) subcategories
echo "\n=== OFFICE FURNITURE (341) FULL TREE ===\n";
printCategoryTree(341, $filteredProductIds, 0);

function getAllDescendants($categoryId) {
    $ids = [];
    $children = DB::table('categories')->where('parent_id', $categoryId)->pluck('id')->toArray();
    foreach ($children as $childId) {
        $ids[] = $childId;
        $ids = array_merge($ids, getAllDescendants($childId));
    }
    return $ids;
}

function printCategoryTree($categoryId, $filteredProductIds, $depth) {
    $cat = Category::find($categoryId);
    if (!$cat) return;
    
    $mainCount = DB::table('products')
        ->whereIn('id', $filteredProductIds)
        ->where('category_id', $cat->id)
        ->count();
    
    $pivotCount = DB::table('product_categories')
        ->whereIn('product_id', $filteredProductIds)
        ->where('category_id', $cat->id)
        ->count();
    
    $indent = str_repeat('  ', $depth);
    echo "{$indent}{$cat->name} (ID {$cat->id}): main=$mainCount, pivot=$pivotCount\n";
    
    $children = DB::table('categories')->where('parent_id', $categoryId)->get();
    foreach ($children as $child) {
        printCategoryTree($child->id, $filteredProductIds, $depth + 1);
    }
}
