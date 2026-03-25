<?php
include 'vendor/autoload.php';
$app = include 'bootstrap/app.php';
$kernel = $app->make(Illuminate\Contracts\Console\Kernel::class);
$kernel->bootstrap();

use App\Http\Controllers\Api\AnalyticsController;
use Illuminate\Http\Request;

$controller = new AnalyticsController();

function testMethod($controller, $method, $params = []) {
    echo "Testing $method...\n";
    try {
        $request = Request::create('/test', 'GET', $params);
        $response = $controller->$method($request);
        echo "RESULT: SUCCESS\n";
    } catch (\Throwable $e) {
        echo "RESULT: FAILED\n";
        echo "Error: " . $e->getMessage() . "\n";
        echo "File: " . $e->getFile() . ":" . $e->getLine() . "\n";
    }
    echo "=====================================\n";
}

$dateParams = [
    'start_date' => '2026-02-08',
    'end_date' => '2026-03-10'
];

testMethod($controller, 'getVisitorStats', $dateParams);
testMethod($controller, 'getHealthStats');
testMethod($controller, 'getAutomatedInsights');
testMethod($controller, 'getCartStats', $dateParams);
testMethod($controller, 'getTrafficSources', $dateParams);
testMethod($controller, 'getPagePerformance', $dateParams);
testMethod($controller, 'getBehaviorFlow', $dateParams);
testMethod($controller, 'getInteractionHeatmap', array_merge($dateParams, ['url' => '/']));
testMethod($controller, 'getVisitorFlow');
testMethod($controller, 'getForecastingData', $dateParams);
testMethod($controller, 'getTopVendors', $dateParams);
testMethod($controller, 'getSystemStatus');
testMethod($controller, 'getVendorAnalytics', $dateParams);
testMethod($controller, 'getFinanceAnalytics', $dateParams);
testMethod($controller, 'getMarketingAnalytics', $dateParams);
