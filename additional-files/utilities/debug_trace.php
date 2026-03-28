<?php
use Illuminate\Support\Facades\Request;

require __DIR__.'/vendor/autoload.php';
$app = require_once __DIR__.'/bootstrap/app.php';

$kernel = $app->make(Illuminate\Contracts\Http\Kernel::class);

// Mock the request properly for CLI context
$request = Illuminate\Http\Request::create('/api/admin/analytics/visitor-stats', 'GET', [
    'start_date' => '2026-02-11',
    'end_date' => '2026-03-13'
]);

// Bypass IP check errors in middleware during CLI by mocking IP
$request->server->set('REMOTE_ADDR', '127.0.0.1');

$response = $kernel->handle($request);

$controller = app(App\Http\Controllers\Api\AnalyticsController::class);

$out = [];
try {
    $res = $controller->getVisitorStats($request);
    $out['visitor_stats_success'] = true;
    $out['visitor_stats_data_keys'] = array_keys(json_decode($res->getContent(), true));
} catch (\Throwable $e) {
    $out['visitor_stats_error'] = [
        'message' => $e->getMessage(),
        'file' => $e->getFile(),
        'line' => $e->getLine()
    ];
}

try {
    $res = $controller->getFinanceAnalytics($request);
    $out['finance_success'] = true;
    $out['finance_data_keys'] = array_keys(json_decode($res->getContent(), true));
} catch (\Throwable $e) {
    $out['finance_error'] = [
        'message' => $e->getMessage(),
        'file' => $e->getFile(),
        'line' => $e->getLine()
    ];
}

file_put_contents('trace2.json', json_encode($out, JSON_PRETTY_PRINT));
echo "Saved trace2.json\n";
