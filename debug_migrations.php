<?php
require 'vendor/autoload.php';
$app = require_once 'bootstrap/app.php';
$kernel = $app->make(Illuminate\Contracts\Console\Kernel::class);

// Create the sqlite file if doesn't exist
$dbPath = 'database/test_db.sqlite';
if (!file_exists($dbPath)) {
    touch($dbPath);
}

// Set env for this process
putenv('DB_CONNECTION=sqlite');
putenv('DB_DATABASE=' . $dbPath);
$_ENV['DB_CONNECTION'] = 'sqlite';
$_ENV['DB_DATABASE'] = $dbPath;

// Also override config directly to be sure
config(['database.default' => 'sqlite']);
config(['database.connections.sqlite.database' => $dbPath]);

try {
    echo "Running migrations...\n";
    $exitCode = $kernel->call('migrate', ['--force' => true]);
    echo "Migration exit code: $exitCode\n";
    echo $kernel->output();
} catch (Exception $e) {
    echo "Migration failed: " . $e->getMessage() . "\n";
    echo $e->getTraceAsString();
}
