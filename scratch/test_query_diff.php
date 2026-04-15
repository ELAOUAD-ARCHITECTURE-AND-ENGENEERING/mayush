<?php
require dirname(__DIR__).'/vendor/autoload.php';
$app = require_once dirname(__DIR__).'/bootstrap/app.php';
$app->make(Illuminate\Contracts\Console\Kernel::class)->bootstrap();
use Illuminate\Support\Facades\DB;
use App\Models\Product;

echo "Count 1: " . Product::where('category_id', 341)->count() . "\n";
echo "Count 2: " . DB::table('product_categories')->where('category_id', 341)->count() . "\n";

$products = Product::query();
$category_list = [341];

$products->where(function ($query) use ($category_list) {
    $query->whereIn('category_id', $category_list)
            ->orWhereHas('categories', function ($q) use ($category_list) {
                $q->whereIn('categories.id', $category_list);
            });
});
echo "Count from OrWhereHas: " . $products->count() . "\n";

echo "Products found from OrWhereHas:\n";
$ids = $products->pluck('id')->toArray();
echo implode(', ', $ids) . "\n";

$directIds = Product::where('category_id', 341)->pluck('id')->toArray();
$pivotIds = DB::table('product_categories')->where('category_id', 341)->pluck('product_id')->toArray();

echo "Direct IDs: " . implode(', ', $directIds) . "\n";
echo "Pivot IDs: " . implode(', ', $pivotIds) . "\n";
