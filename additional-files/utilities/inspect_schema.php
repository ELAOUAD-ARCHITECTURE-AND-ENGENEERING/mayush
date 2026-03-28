<?php
include 'vendor/autoload.php';
$app = include 'bootstrap/app.php';
$app->make('Illuminate\Contracts\Console\Kernel')->bootstrap();

$checkTables = ['commission_histories', 'seller_withdraw_requests', 'orders', 'shops', 'categories', 'products', 'order_details'];

foreach ($checkTables as $table) {
    echo "=== TABLE: $table ===\n";
    if (Schema::hasTable($table)) {
        $columns = Schema::getColumnListing($table);
        foreach ($columns as $column) {
            echo "  - $column\n";
        }
    } else {
        echo "  NOT FOUND\n";
    }
    echo "\n";
}
