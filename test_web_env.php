<?php
require __DIR__.'/vendor/autoload.php';
$app = require_once __DIR__.'/bootstrap/app.php';
$kernel = $app->make(Illuminate\Contracts\Http\Kernel::class);
$request = Illuminate\Http\Request::capture();
$app->instance('request', $request);

echo "DB_DATABASE=" . env('DB_DATABASE') . "\n";
echo "DB_USERNAME=" . env('DB_USERNAME') . "\n";
echo "Config DB_DATABASE=" . config('database.connections.mysql.database') . "\n";
echo "Config DB_USERNAME=" . config('database.connections.mysql.username') . "\n";
