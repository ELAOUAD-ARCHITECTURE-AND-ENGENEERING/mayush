<?php

/**
 * Utility: Dump column listing for the 'products' table.
 * Usage: php script.php
 */

require __DIR__ . '/../../vendor/autoload.php';

$app = require_once __DIR__ . '/../../bootstrap/app.php';
$kernel = $app->make(Illuminate\Contracts\Console\Kernel::class);
$kernel->bootstrap();

echo implode(',', Illuminate\Support\Facades\Schema::getColumnListing('products'));
echo PHP_EOL;
