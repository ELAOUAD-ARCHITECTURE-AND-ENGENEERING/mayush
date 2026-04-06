<?php
require __DIR__ . '/../vendor/autoload.php';
$app = require_once __DIR__ . '/../bootstrap/app.php';
$kernel = $app->make('Illuminate\Contracts\Console\Kernel');
$kernel->bootstrap();

$url = route('home.section.featured');
echo "route('home.section.featured') generates: {$url}\n";

// Let's also check if the DB is failing during load_featured_section
try {
    $controller = new \App\Http\Controllers\HomeController();
    $html = $controller->load_featured_section()->render();
    echo "Section rendered successfully. Length: " . strlen($html) . " bytes\n";
} catch (\Exception $e) {
    echo "ERROR rendering section: " . $e->getMessage() . "\n" . $e->getTraceAsString() . "\n";
}
