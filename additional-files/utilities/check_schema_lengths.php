<?php
require __DIR__ . '/vendor/autoload.php';
$app = require_once __DIR__ . '/bootstrap/app.php';
$kernel = $app->make(Illuminate\Contracts\Console\Kernel::class);
$kernel->bootstrap();

use Illuminate\Support\Facades\DB;

$columns = DB::select("SHOW COLUMNS FROM users WHERE Field IN ('phone', 'address', 'postal_code')");
$shopsColumns = DB::select("SHOW COLUMNS FROM shops WHERE Field IN ('bank_name', 'bank_info', 'business_info', 'verification_info')");

file_put_contents(__DIR__ . '/schema_out.json', json_encode(['users' => $columns, 'shops' => $shopsColumns], JSON_PRETTY_PRINT));
echo "Done.";
