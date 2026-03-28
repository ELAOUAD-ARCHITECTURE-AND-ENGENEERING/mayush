<?php

use Illuminate\Support\Facades\Request;
use Carbon\Carbon;

require __DIR__.'/vendor/autoload.php';
$app = require_once __DIR__.'/bootstrap/app.php';

$kernel = $app->make(Illuminate\Contracts\Http\Kernel::class);
$response = $kernel->handle(
    $request = Illuminate\Http\Request::capture()
);

$request->merge(['start_date' => '2026-02-11', 'end_date' => '2026-03-13']);

$controller = app(App\Http\Controllers\Api\AnalyticsController::class);

echo "Testing Visitor Stats...\n";
try {
    $res = $controller->getVisitorStats($request);
    echo "SUCCESS: " . json_encode($res->original) . "\n";
} catch (\Throwable $e) {
    echo "ERROR in Visitor Stats: " . $e->getMessage() . " in " . $e->getFile() . ":" . $e->getLine() . "\n";
    echo $e->getTraceAsString() . "\n";
}

echo "\nTesting Finance Analytics...\n";
try {
    $res = $controller->getFinanceAnalytics($request);
    echo "SUCCESS: " . json_encode($res->original) . "\n";
} catch (\Throwable $e) {
    echo "ERROR in Finance Analytics: " . $e->getMessage() . " in " . $e->getFile() . ":" . $e->getLine() . "\n";
    echo $e->getTraceAsString() . "\n";
}
