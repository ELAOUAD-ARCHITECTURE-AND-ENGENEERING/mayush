<?php
require __DIR__.'/../vendor/autoload.php';
$app = require_once __DIR__.'/../bootstrap/app.php';
$kernel = $app->make(Illuminate\Contracts\Console\Kernel::class);
$kernel->bootstrap();

$request = Illuminate\Http\Request::create('/search', 'GET');
$controller = new App\Http\Controllers\SearchController();

$category_id = null;
$allCategories = \App\Models\Category::with('childrenCategories', 'coverImage')
    ->orderBy('order_level', 'desc')
    ->where('level', 0)
    ->get();

$view = view('frontend.product_listing_page_child_category', ['child_category' => $allCategories->firstWhere('name', 'Office Furniture')->childrenCategories->first()]);
echo $view->render();
