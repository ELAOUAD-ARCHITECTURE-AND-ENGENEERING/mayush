<?php
require __DIR__ . '/../vendor/autoload.php';
$app = require_once __DIR__ . '/../bootstrap/app.php';
$app->make(Illuminate\Contracts\Console\Kernel::class)->bootstrap();

use App\Models\CategoryTranslation;

$t = CategoryTranslation::where('category_id', 341)->where('lang', 'fr')->first();
if ($t) {
    $t->name = 'Office Furniture'; // Reverting to English to simulate missing/incorrect translation
    $t->save();
    echo "Reverted translation to: " . $t->name . "\n";
} else {
    echo "Translation not found\n";
}
