<?php

require __DIR__.'/vendor/autoload.php';
$app = require_once __DIR__.'/bootstrap/app.php';
$app->make(Illuminate\Contracts\Console\Kernel::class)->bootstrap();

use App\Models\FlashDeal;

echo 'Active Count: ' . FlashDeal::active()->get()->count() . PHP_EOL;

foreach(FlashDeal::active()->get() as $d) {
    echo 'Active Deal: ' . $d->slug . PHP_EOL;
}
