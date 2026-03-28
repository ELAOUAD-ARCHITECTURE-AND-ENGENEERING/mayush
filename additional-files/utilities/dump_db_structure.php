<?php
require __DIR__.'/vendor/autoload.php';
$app = require_once __DIR__.'/bootstrap/app.php';
$kernel = $app->make(Illuminate\Contracts\Http\Kernel::class);
$app->make('db');

function dumpTable($table) {
    echo "--- Table: $table ---\n";
    $columns = DB::select("SHOW COLUMNS FROM $table");
    foreach ($columns as $column) {
        printf("%-20s %-15s %-5s %-5s %s\n", 
            $column->Field, 
            $column->Type, 
            $column->Null, 
            $column->Key, 
            $column->Default
        );
    }
    echo "\n";
}

dumpTable('flash_deals');
dumpTable('flash_deal_products');
dumpTable('flash_deal_translations');
