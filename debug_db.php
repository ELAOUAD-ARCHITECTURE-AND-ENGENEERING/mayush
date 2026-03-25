<?php
include 'vendor/autoload.php';
$app = include 'bootstrap/app.php';
$kernel = $app->make(Illuminate\Contracts\Console\Kernel::class);
$kernel->bootstrap();

use Illuminate\Support\Facades\DB;

$tables = DB::select('SHOW TABLES');
echo "TABLES STARTING WITH 'R':\n";
foreach ($tables as $table) {
    $name = array_values((array)$table)[0];
    if (strtolower($name[0]) === 'r') {
        try {
            $count = DB::table($name)->count();
            echo "- $name ($count rows)\n";
        } catch (\Exception $e) {
            echo "- $name (ERROR)\n";
        }
    }
}
