<?php
include 'vendor/autoload.php';
$app = include 'bootstrap/app.php';
$app->make('Illuminate\Contracts\Console\Kernel')->bootstrap();

use App\Http\Controllers\Api\AnalyticsController;
use Illuminate\Http\Request;

$controller = new AnalyticsController();

function testVerbose($controller, $method, $params = []) {
    echo "=== $method ===\n";
    try {
        $request = Request::create('/test', 'GET', $params);
        $response = $controller->$method($request);
        $data = json_decode($response->getContent(), true);
        if (is_null($data)) { echo "ERROR: null response\n"; return; }
        echo "OK. Keys: " . implode(', ', array_keys($data)) . "\n";
    } catch (\Throwable $e) {
        echo "FAILED: " . $e->getMessage() . "\n";
        echo "  at " . $e->getFile() . ":" . $e->getLine() . "\n";
    }
    echo "\n";
}

$p = ['start_date' => '2026-02-09', 'end_date' => '2026-03-11'];
testVerbose($controller, 'getVisitorStats', $p);
testVerbose($controller, 'getAutomatedInsights');
testVerbose($controller, 'getForecastingData', $p);
