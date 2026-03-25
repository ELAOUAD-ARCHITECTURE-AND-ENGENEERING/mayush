<?php
include 'vendor/autoload.php';
$app = include 'bootstrap/app.php';
$app->make('Illuminate\Contracts\Console\Kernel')->bootstrap();

echo "DB_DATABASE: " . DB::connection()->getDatabaseName() . PHP_EOL;

$tables = DB::connection()->getSchemaBuilder()->getAllTables();
foreach ($tables as $table) {
    $tableName = array_values((array)$table)[0];
    echo "Table: [" . $tableName . "]" . PHP_EOL;
}
