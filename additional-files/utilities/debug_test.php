<?php

require 'vendor/autoload.php';
$app = require_once 'bootstrap/app.php';
$kernel = $app->make(Illuminate\Contracts\Http\Kernel::class);
$kernel->bootstrap();

use Tests\Integration\Controllers\PromotionAccessControlTest;
use Illuminate\Support\Facades\Artisan;

$test = new PromotionAccessControlTest();
$test->setUp();

try {
    echo "Running customer_cannot_store_classified_product...\n";
    $test->customer_cannot_store_classified_product();
    echo "PASS\n";
} catch (\Throwable $e) {
    echo "FAIL: " . $e->getMessage() . "\n";
    echo $e->getTraceAsString() . "\n";
}

try {
    echo "\nRunning customer_cannot_promote_classified_product...\n";
    $test->customer_cannot_promote_classified_product();
    echo "PASS\n";
} catch (\Throwable $e) {
    echo "FAIL: " . $e->getMessage() . "\n";
    echo $e->getTraceAsString() . "\n";
}
