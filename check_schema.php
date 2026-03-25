<?php

require __DIR__.'/vendor/autoload.php';
$app = require_once __DIR__.'/bootstrap/app.php';
$kernel = $app->make(Illuminate\Contracts\Http\Kernel::class);
$app->make('db');

use Illuminate\Support\Facades\DB;

$schema = DB::select("SHOW COLUMNS FROM flash_deals");
echo json_encode($schema, JSON_PRETTY_PRINT);
