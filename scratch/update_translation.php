<?php
require __DIR__ . '/../vendor/autoload.php';
$app = require_once __DIR__ . '/../bootstrap/app.php';
$app->make(Illuminate\Contracts\Console\Kernel::class)->bootstrap();

use App\Models\CategoryTranslation;

$t = CategoryTranslation::where('category_id', 341)->where('lang', 'fr')->first();
if ($t) {
    $t->name = 'Mobilier de bureau';
    $t->save();
    echo "Updated translation to: " . $t->name . "\n";
} else {
    echo "Translation not found\n";
}
