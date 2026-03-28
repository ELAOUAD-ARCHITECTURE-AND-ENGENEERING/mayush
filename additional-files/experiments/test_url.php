<?php
require __DIR__.'/vendor/autoload.php';
$app = require_once __DIR__.'/bootstrap/app.php';
$kernel = $app->make(Illuminate\Contracts\Console\Kernel::class);
$kernel->bootstrap();

echo "getBaseURL: " . getBaseURL() . PHP_EOL;
echo "getFileBaseURL: " . getFileBaseURL() . PHP_EOL;
