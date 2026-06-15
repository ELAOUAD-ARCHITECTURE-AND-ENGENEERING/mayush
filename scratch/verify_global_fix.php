<?php
require __DIR__ . '/../vendor/autoload.php';
$app = require_once __DIR__ . '/../bootstrap/app.php';
$app->make(Illuminate\Contracts\Console\Kernel::class)->bootstrap();

use App\Models\Category;
use App\Models\CategoryTranslation;

// Verify category 341
$c = Category::find(341);
$t = CategoryTranslation::where('category_id', 341)->where('lang', 'fr')->first();

echo "Category ID: " . $c->id . "\n";
echo "Base Name: " . $c->name . "\n";
echo "DB Translation (fr): " . ($t ? $t->name : 'N/A') . "\n";

// Set locale to French
App::setLocale('fr');
echo "Current Locale: " . App::getLocale() . "\n";
echo "getTranslation('name') result: " . $c->getTranslation('name') . "\n";

if ($c->getTranslation('name') == 'Mobilier de bureau') {
    echo "SUCCESS: Translation fallback worked!\n";
} else {
    echo "FAILURE: Translation fallback did not work.\n";
}

// Test with a field that shouldn't fall back (e.g. description)
echo "getTranslation('description') result: " . $c->getTranslation('description') . "\n";
