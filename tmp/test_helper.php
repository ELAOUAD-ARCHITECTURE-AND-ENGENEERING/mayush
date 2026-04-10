<?php
require __DIR__.'/../vendor/autoload.php';
$app = require_once __DIR__.'/../bootstrap/app.php';
$kernel = $app->make(Illuminate\Contracts\Console\Kernel::class);
$kernel->bootstrap();

echo "static_asset: " . static_asset('build/assets/app.js') . "\n";
echo "my_asset: " . my_asset('build/assets/app.js') . "\n";
echo "getBaseURL: " . getBaseURL() . "\n";
