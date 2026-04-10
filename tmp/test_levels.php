<?php
require __DIR__.'/../vendor/autoload.php';
$app = require_once __DIR__.'/../bootstrap/app.php';
$kernel = $app->make(Illuminate\Contracts\Console\Kernel::class);
$kernel->bootstrap();

$officeFurniture = \App\Models\Category::where('name', 'like', '%Office Furniture%')->first();
echo "Office Furniture level: " . $officeFurniture->level . "\n";
foreach($officeFurniture->childrenCategories as $child) {
    echo "- " . $child->name . " level: " . $child->level . "\n";
}
