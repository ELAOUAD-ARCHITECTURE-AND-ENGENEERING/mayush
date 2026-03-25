<?php
require __DIR__.'/vendor/autoload.php';
$app = require_once __DIR__.'/bootstrap/app.php';
$kernel = $app->make(Illuminate\Contracts\Http\Kernel::class);

$request = Illuminate\Http\Request::create('/brands', 'GET');
$response = $kernel->handle($request);
echo "Status: " . $response->getStatusCode() . "\n";
$content = $response->getContent();

if (preg_match('/analytics-tracker-[a-zA-Z0-9_-]+\.js/', $content, $matches)) {
    echo "Found tracker: " . $matches[0] . "\n";
} else {
    echo "Tracker not found in output.\n";
}
