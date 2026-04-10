<?php
require __DIR__.'/../vendor/autoload.php';
$app = require_once __DIR__.'/../bootstrap/app.php';
$kernel = $app->make(Illuminate\Contracts\Console\Kernel::class);
$kernel->bootstrap();

$request = Illuminate\Http\Request::create('/category/office-furniture', 'GET');
$controller = new App\Http\Controllers\SearchController();

// Test the view compilation
try {
    $response = $controller->listingByCategory($request, 'office-furniture');
    file_put_contents('tmp/test_view.html', $response->render());
    echo "Saved view.\n";
} catch (\Exception $e) {
    echo "Error: " . $e->getMessage() . "\n";
}
