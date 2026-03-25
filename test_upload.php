<?php
require __DIR__.'/vendor/autoload.php';
$app = require_once __DIR__.'/bootstrap/app.php';
$kernel = $app->make(Illuminate\Contracts\Console\Kernel::class);
$kernel->bootstrap();

$upload = \App\Models\Upload::latest()->first();
echo json_encode($upload->toArray(), JSON_PRETTY_PRINT);
