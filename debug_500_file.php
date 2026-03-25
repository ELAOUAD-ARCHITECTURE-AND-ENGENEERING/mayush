<?php

use Illuminate\Support\Facades\Request;

require __DIR__.'/vendor/autoload.php';
$app = require_once __DIR__.'/bootstrap/app.php';

$kernel = $app->make(Illuminate\Contracts\Http\Kernel::class);
$response = $kernel->handle(
    $request = Illuminate\Http\Request::capture()
);

$request->merge(['start_date' => '2026-02-11', 'end_date' => '2026-03-13']);

$controller = app(App\Http\Controllers\Api\AnalyticsController::class);

$out = "Testing Visitor Stats...\n";
try {
    $res = $controller->getVisitorStats($request);
    $out .= "SUCCESS\n";
} catch (\Throwable $e) {
    $out .= "ERROR: " . $e->getMessage() . " in " . $e->getFile() . ":" . $e->getLine() . "\n";
    $out .= $e->getTraceAsString() . "\n\n";
}

$out .= "Testing Finance Analytics...\n";
try {
    $res = $controller->getFinanceAnalytics($request);
    $out .= "SUCCESS\n";
} catch (\Throwable $e) {
    $out .= "ERROR: " . $e->getMessage() . " in " . $e->getFile() . ":" . $e->getLine() . "\n";
    $out .= $e->getTraceAsString() . "\n\n";
}

file_put_contents('err.log', $out);
echo "Written to err.log\n";
