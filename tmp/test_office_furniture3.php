<?php
require __DIR__.'/../vendor/autoload.php';
$app = require_once __DIR__.'/../bootstrap/app.php';
$kernel = $app->make(Illuminate\Contracts\Console\Kernel::class);
$kernel->bootstrap();

$request = Illuminate\Http\Request::create('/search', 'GET');
$controller = new App\Http\Controllers\SearchController();

$category_id = null;
$brand_id = null;

// Just fetch categories like index() does
$allCategories = \App\Models\Category::with('childrenCategories', 'coverImage')
    ->orderBy('order_level', 'desc')
    ->where('level', 0)
    ->get();

$officeFurniture = $allCategories->firstWhere('name', 'Office Furniture');

if ($officeFurniture) {
    echo "Office Furniture ID: " . $officeFurniture->id . "\n";
    echo "Children count: " . $officeFurniture->childrenCategories->count() . "\n";
    foreach($officeFurniture->childrenCategories as $child) {
        echo "- " . $child->name . " (ID: " . $child->id . ")\n";
    }
} else {
    echo "Office Furniture not found in level 0 categories!\n";
}
