<?php

require __DIR__.'/vendor/autoload.php';
$app = require_once __DIR__.'/bootstrap/app.php';
$app->make(Illuminate\Contracts\Console\Kernel::class)->bootstrap();

use App\Models\FlashDeal;
use Carbon\Carbon;

echo 'Current Time: ' . time() . PHP_EOL; 

foreach(FlashDeal::all() as $d) {
    echo 'Deal: ' . $d->slug . ' Status: ' . $d->status . ' Start: ' . $d->start_date . ' End: ' . $d->end_date . PHP_EOL;
}
