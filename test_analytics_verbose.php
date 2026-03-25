<?php
include 'vendor/autoload.php';
$app = include 'bootstrap/app.php';
$app->make('Illuminate\Contracts\Console\Kernel')->bootstrap();

use App\Http\Controllers\Api\AnalyticsController;
use Illuminate\Http\Request;

$controller = new AnalyticsController();

function testMethodVerbose($controller, $method, $params = []) {
    echo "=== Testing $method ===\n";
    try {
        $request = Request::create('/test', 'GET', $params);
        $response = $controller->$method($request);
        $data = json_decode($response->getContent(), true);
        echo "RESULT: SUCCESS\n";
        // Print first-level keys to confirm structure
        if (is_array($data)) {
            foreach ($data as $key => $val) {
                $type = gettype($val);
                if (is_array($val)) $type = 'array[' . count($val) . ']';
                if ($val === null) $type = 'null';
                echo "  $key => $type\n";
            }
        }
    } catch (\Throwable $e) {
        echo "RESULT: FAILED\n";
        echo "Error: " . $e->getMessage() . "\n";
        echo "File: " . $e->getFile() . ":" . $e->getLine() . "\n";
    }
    echo "\n";
}

$params = ['start_date' => '2026-02-09', 'end_date' => '2026-03-11'];

testMethodVerbose($controller, 'getVisitorStats', $params);
testMethodVerbose($controller, 'getAutomatedInsights');
testMethodVerbose($controller, 'getForecastingData', $params);
testMethodVerbose($controller, 'getMarketingAnalytics', $params);
testMethodVerbose($controller, 'getVendorAnalytics', $params);
testMethodVerbose($controller, 'getFinanceAnalytics', $params);
