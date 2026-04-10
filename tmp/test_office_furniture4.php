<?php
require __DIR__.'/../vendor/autoload.php';
$app = require_once __DIR__.'/../bootstrap/app.php';
$kernel = $app->make(Illuminate\Contracts\Console\Kernel::class);
$kernel->bootstrap();

$allCategories = \App\Models\Category::with('childrenCategories', 'coverImage')
    ->orderBy('order_level', 'desc')
    ->where('level', 0)
    ->get();

$controller = new App\Http\Controllers\SearchController();
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

foreach ($allCategories as $category1) {
    $controller->categoryProductCount($category1, $productCountsSubCategory);
}

// Now render the category list part
$html = "<ul class=\"hummingbird-treeview-converter2 list-unstyled\" data-checkbox-name=\"categories[]\">\n";
foreach ($allCategories as $category) {
    if ($category->name == 'Office Furniture') {
        $html .= "    <li d-item=\"{$category->products_count}\" id=\"generel_{$category->id}\">\n";
        $html .= "        {$category->getTranslation('name')}\n";
        if ($category->products_count > 0) {
            $html .= "        ({$category->products_count})\n";
        }
        $html .= "    </li>\n";
        
        foreach ($category->childrenCategories as $childCategory) {
            $html .= renderChild($childCategory);
        }
    }
}
$html .= "</ul>\n";

function renderChild($child) {
    $value = str_repeat('-', $child->level);
    $html = "    <li d-item=\"{$child->products_count}\" id=\"generel_{$child->id}\">{$value} {$child->getTranslation('name')}";
    if ($child->products_count > 0) {
        $html .= " ({$child->products_count})";
    }
    $html .= "</li>\n";
    if ($child->childrenCategories) {
        foreach ($child->childrenCategories as $sub) {
            $html .= renderChild($sub);
        }
    }
    return $html;
}

file_put_contents('tmp/test_output.html', $html);
echo "Saved to tmp/test_output.html\n";
