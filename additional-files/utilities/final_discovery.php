<?php
require 'vendor/autoload.php';
$app = require_once 'bootstrap/app.php';
$kernel = $app->make(Illuminate\Contracts\Console\Kernel::class);
$kernel->bootstrap();

$data = [
    'flash_deals' => Schema::getColumnListing('flash_deals'),
    'flash_deal_products' => Schema::getColumnListing('flash_deal_products'),
    'products' => Schema::getColumnListing('products'),
    'shops' => Schema::getColumnListing('shops'),
    'homepage_select' => get_setting('homepage_select'),
];

file_put_contents('discovery_result.json', json_encode($data, JSON_PRETTY_PRINT));
echo "Done\n";
