<?php

require __DIR__.'/vendor/autoload.php';
$app = require_once __DIR__.'/bootstrap/app.php';
$app->make(Illuminate\Contracts\Console\Kernel::class)->bootstrap();

use App\Models\FlashDeal;
use Illuminate\Support\Facades\DB;

echo 'DB: ' . DB::getDatabaseName() . PHP_EOL;
echo 'Table Count: ' . DB::table('flash_deals')->count() . PHP_EOL;

foreach(DB::table('flash_deals')->get() as $row) {
    echo 'Row: ' . $row->slug . ' - Status: ' . $row->status . ' - Start: ' . $row->start_date . ' - End: ' . $row->end_date . PHP_EOL;
}
