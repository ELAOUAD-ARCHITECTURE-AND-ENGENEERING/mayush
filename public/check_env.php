<?php
require __DIR__.'/../vendor/autoload.php';
$app = require_once __DIR__.'/../bootstrap/app.php';
$kernel = $app->make(Illuminate\Contracts\Http\Kernel::class);
$response = $kernel->handle(
    $request = Illuminate\Http\Request::capture()
);

echo "Homepage Select: " . get_setting('homepage_select') . "\n";
echo "Flash Deals Nav Activation: " . get_setting('flash_deals_navigation_activation') . "\n";
echo "Active Flash Deals Count: " . count(get_active_flash_deals()) . "\n";
?>
