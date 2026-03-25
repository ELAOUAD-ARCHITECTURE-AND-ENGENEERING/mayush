<?php

$date_range = ""; // Empty date range
$date_var = explode(" to ", $date_range);

echo "Testing date range: '$date_range'\n";
try {
    $start_date = strtotime($date_var[0]);
    echo "Start date: $start_date\n";
    $end_date = strtotime($date_var[1]);
    echo "End date: $end_date\n";
} catch (\Throwable $e) {
    echo "Date Range Error: " . $e->getMessage() . "\n";
}

$products = null; // Missing products
echo "\nTesting products: null\n";
try {
    foreach ($products as $key => $product) {
        echo "Product: $product\n";
    }
} catch (\Throwable $e) {
    echo "Products Error: " . $e->getMessage() . "\n";
}
