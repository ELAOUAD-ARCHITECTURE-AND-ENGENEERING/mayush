<?php
require __DIR__.'/vendor/autoload.php';
$app = require_once __DIR__.'/bootstrap/app.php';
$kernel = $app->make(Illuminate\Contracts\Console\Kernel::class);
$kernel->bootstrap();

try {
    DB::connection()->getPdo();
    echo "Connected successfully to " . DB::connection()->getDatabaseName();
} catch (\Exception $e) {
    die("Could not connect to the database.  Please check your configuration. error:" . $e->getMessage());
}
