<?php

require __DIR__.'/../vendor/autoload.php';
$app = require_once __DIR__.'/../bootstrap/app.php';
$app->make(Illuminate\Contracts\Console\Kernel::class)->bootstrap();

$term = 'reunion';
echo "Searching names for '$term'...\n";
$t = \App\Models\ProductTranslation::where('name', 'like', "%$term%")->get();
foreach ($t as $trans) {
    echo "ID: {$trans->id} | P_ID: {$trans->product_id} | Name: {$trans->name}\n";
}

$term2 = 'table';
echo "\nSearching names for '$term2' (first 20)...\n";
$t2 = \App\Models\ProductTranslation::where('name', 'like', "%$term2%")->take(20)->get();
foreach ($t2 as $trans) {
    echo "ID: {$trans->id} | P_ID: {$trans->product_id} | Name: {$trans->name}\n";
}
