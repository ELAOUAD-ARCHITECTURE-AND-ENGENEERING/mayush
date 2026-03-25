<?php
require 'vendor/autoload.php';
$app = require_once 'bootstrap/app.php';
$kernel = $app->make(Illuminate\Contracts\Console\Kernel::class);
$kernel->bootstrap();

$tables = ['flash_deals', 'flash_deal_products', 'products', 'shops', 'sellers', 'users'];
foreach ($tables as $table) {
    if (Schema::hasTable($table)) {
        echo "Table: $table\n";
        echo implode(', ', Schema::getColumnListing($table)) . "\n\n";
    } else {
        echo "Table: $table (NOT FOUND)\n\n";
    }
}
