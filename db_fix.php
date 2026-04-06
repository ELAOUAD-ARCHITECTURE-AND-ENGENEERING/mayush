<?php
require 'vendor/autoload.php';
$app = require_once 'bootstrap/app.php';
$kernel = $app->make(\Illuminate\Contracts\Console\Kernel::class);
$kernel->bootstrap();

try {
    \Illuminate\Support\Facades\DB::statement("DROP TABLE IF EXISTS frequently_bought_products");
    echo "Dropped successfully.\n";
} catch (\Exception $e) {
    echo "Error dropping: " . $e->getMessage() . "\n";
}

try {
    \Illuminate\Support\Facades\DB::statement("
        CREATE TABLE `frequently_bought_products` (
          `id` bigint(20) unsigned NOT NULL AUTO_INCREMENT,
          `product_id` bigint(20) NOT NULL,
          `frequently_bought_product_id` bigint(20) NOT NULL,
          `category_id` bigint(20) DEFAULT NULL,
          `source` varchar(255) DEFAULT 'manual',
          `affinity_score` double(8,2) DEFAULT '0.00',
          `created_at` timestamp NULL DEFAULT NULL,
          `updated_at` timestamp NULL DEFAULT NULL,
          PRIMARY KEY (`id`)
        ) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;
    ");
    echo "Created successfully.\n";
} catch (\Exception $e) {
    echo "Error creating: " . $e->getMessage() . "\n";
}
