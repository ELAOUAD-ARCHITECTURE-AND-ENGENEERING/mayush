<?php
require __DIR__ . '/../vendor/autoload.php';
$app = require_once __DIR__ . '/../bootstrap/app.php';
$app->make(Illuminate\Contracts\Console\Kernel::class)->bootstrap();

use App\Models\Category;
use Illuminate\Support\Facades\App;

App::setLocale('fr');
$c = Category::find(341);
if ($c) {
    echo "Locale: " . App::getLocale() . "\n";
    echo "Translation: " . $c->getTranslation('name') . "\n";
} else {
    echo "Category 341 not found\n";
}
