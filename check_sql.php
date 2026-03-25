<?php

require __DIR__.'/vendor/autoload.php';
$app = require_once __DIR__.'/bootstrap/app.php';
$app->make(Illuminate\Contracts\Console\Kernel::class)->bootstrap();

use App\Models\FlashDeal;
use Illuminate\Support\Facades\DB;

DB::listen(function($query) {
    echo $query->sql . PHP_EOL;
    print_r($query->bindings);
});

echo 'Executing FlashDeal::active()->get()...' . PHP_EOL;
$deals = FlashDeal::active()->get();
echo 'Results: ' . $deals->count() . PHP_EOL;
foreach($deals as $d) {
    echo ' - Deal: ' . $d->slug . PHP_EOL;
}
