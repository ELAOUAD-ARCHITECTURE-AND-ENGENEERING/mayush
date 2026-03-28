<?php
require 'vendor/autoload.php';
$app = require_once 'bootstrap/app.php';
$kernel = $app->make('Illuminate\Contracts\Console\Kernel');
$kernel->bootstrap();

echo "--- START VERIFICATION ---\n";
echo "APP_URL: " . config('app.url') . "\n";
echo "FORCE_HTTPS: " . env('FORCE_HTTPS') . "\n";
echo "Asset (vendors.css): " . asset('assets/css/vendors.css') . "\n";
echo "Vite Asset: " . asset('build/assets/dashboard.js') . "\n";
echo "Static Asset Helper: " . static_asset('assets/css/vendors.css') . "\n";
echo "--- END VERIFICATION ---\n";
