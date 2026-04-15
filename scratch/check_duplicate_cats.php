<?php
require dirname(__DIR__).'/vendor/autoload.php';
$app = require_once dirname(__DIR__).'/bootstrap/app.php';
$app->make(Illuminate\Contracts\Console\Kernel::class)->bootstrap();

$cats = App\Models\Category::where('name', 'like', '%Office Furniture%')->get();
foreach ($cats as $cat) {
    echo "ID: {$cat->id}, Name: {$cat->name}\n";
}
