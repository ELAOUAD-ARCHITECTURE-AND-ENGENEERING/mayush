<?php
require dirname(__DIR__).'/vendor/autoload.php';
$app = require_once dirname(__DIR__).'/bootstrap/app.php';
$app->make(Illuminate\Contracts\Console\Kernel::class)->bootstrap();

use App\Models\Product;
use Illuminate\Http\Request;

echo "Calling filter_products on Product::where('category_id', 341)...\n";
$products = filter_products(Product::where('category_id', 341));
echo "Count: " . $products->count() . "\n";

echo "Creating the exact index2 products query...\n";
$category_list = [341];
$products2 = Product::query();

if (count($category_list) > 0) {
    $products2->where(function ($query) use ($category_list) {
        $query->whereIn('category_id', $category_list)
              ->orWhereHas('categories', function ($q) use ($category_list) {
                  $q->whereIn('categories.id', $category_list);
              });
    });
}
$products2 = filter_products($products2);
echo "index2 Count: " . $products2->count() . "\n";

echo "And what about the children? Office Furniture (341) has children!\n";
$category_ids = \App\Utility\CategoryUtility::children_ids(341);
$category_ids[] = 341;
echo count($category_ids) . " category IDs in the branch.\n";

$products3 = Product::query();
$products3->where(function ($query) use ($category_ids) {
    $query->whereIn('category_id', $category_ids)
          ->orWhereHas('categories', function ($q) use ($category_ids) {
              $q->whereIn('categories.id', $category_ids);
          });
});
$products3 = filter_products($products3);
echo "index Count (with children): " . $products3->count() . "\n";

