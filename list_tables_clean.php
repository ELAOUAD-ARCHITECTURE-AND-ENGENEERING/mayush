<?php
include 'vendor/autoload.php';
$app = include 'bootstrap/app.php';
$app->make('Illuminate\Contracts\Console\Kernel')->bootstrap();

$tables = DB::connection()->getSchemaBuilder()->getAllTables();
$names = [];
foreach ($tables as $table) {
    $names[] = array_values((array)$table)[0];
}
sort($names);
file_put_contents('clean_tables.txt', implode(PHP_EOL, $names));
echo "Saved " . count($names) . " tables to clean_tables.txt" . PHP_EOL;
