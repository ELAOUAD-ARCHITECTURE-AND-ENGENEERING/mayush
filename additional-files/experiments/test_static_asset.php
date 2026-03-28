<?php
require __DIR__.'/vendor/autoload.php';
$app = require_once __DIR__.'/bootstrap/app.php';
$kernel = $app->make(Illuminate\Contracts\Console\Kernel::class);
$kernel->bootstrap();

echo "static_asset('assets/images/logo.png'): " . static_asset('assets/images/logo.png') . PHP_EOL;
echo "asset('assets/images/logo.png'): " . asset('assets/images/logo.png') . PHP_EOL;
echo "asset('public/assets/images/logo.png'): " . asset('public/assets/images/logo.png') . PHP_EOL;
