<?php
require dirname(__DIR__).'/vendor/autoload.php';
$app = require_once dirname(__DIR__).'/bootstrap/app.php';
$app->make(Illuminate\Contracts\Console\Kernel::class)->bootstrap();
use Illuminate\Support\Facades\DB;
use App\Models\Product;

$category_list = [341];

$products = Product::where([]);

if (count($category_list) > 0) {
    $products->where(function ($query) use ($category_list) {
        $query->whereIn('category_id', $category_list)
                ->orWhereHas('categories', function ($q) use ($category_list) {
                    $q->whereIn('categories.id', $category_list);
                });
    });
}

// Emulate filter_products
$products = $products->where('published', 1)->where('approved', 1)->where('auction_product', 0);
$count = $products->count();

echo "Count with just category_list=[341]: $count\n\n";

// Emulate index2 full query exactly
$request = new \Illuminate\Http\Request([
    'categories' => ['341']
]);
$controller = new \App\Http\Controllers\SearchController();
// It dies here if we call it because it tries to render view, but we can dump the query builder
$products2 = Product::query();
if (count($category_list) > 0) {
    $products2->where(function ($query) use ($category_list) {
        $query->whereIn('category_id', $category_list)
                ->orWhereHas('categories', function ($q) use ($category_list) {
                    $q->whereIn('categories.id', $category_list);
                });
    });
}
echo "Query: " . $products2->toSql() . "\n";
echo "Count: " . $products2->count() . "\n";

// What if 'category_list' was empty?
echo "Count if category list is empty: " . Product::where('published', 1)->where('approved', 1)->count() . "\n";

// What if the issue is in the frontend JS sending wrong data? Let's check products matching any string in names
$count37 = Product::where('published', 1)->where('approved', 1)->count();
echo "Total published: $count37 \n";
