<?php
require __DIR__.'/vendor/autoload.php';
$app = require_once __DIR__.'/bootstrap/app.php';
$kernel = $app->make(Illuminate\Contracts\Console\Kernel::class);
$kernel->bootstrap();

try {
    $svc = app(App\Services\Analytics\FinanceAnalyticsService::class);
    $data = $svc->getDashboardMetrics(now()->subDays(30), now());
    echo "SUCCESS\n";
    print_r($data);
} catch (\Exception $e) {
    echo "ERROR: " . $e->getMessage() . "\n";
    echo $e->getTraceAsString();
}
