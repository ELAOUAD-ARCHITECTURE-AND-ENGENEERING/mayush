<?php
require 'vendor/autoload.php';
$app = require_once 'bootstrap/app.php';
$app->make('Illuminate\Contracts\Console\Kernel')->bootstrap();
$deals = \App\Models\FlashDeal::all();
if($deals->isEmpty()) {
    echo "NO_DEALS_FOUND\n";
} else {
    foreach($deals as $d) {
        $active = ($d->status == 1 && $d->start_date <= time() && $d->end_date >= time()) ? "ACTIVE" : "INACTIVE";
        echo "ID: {$d->id}, Title: {$d->title}, Status: {$d->status}, End: ".date('Y-m-d H:i:s', $d->end_date).", Logic: $active\n";
    }
}
