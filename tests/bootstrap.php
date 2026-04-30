<?php

// Ensure stale caches are cleared before tests start
echo "Clearing application cache...\n";
exec('php artisan optimize:clear');

// Load the Composer autoloader
require __DIR__.'/../vendor/autoload.php';
