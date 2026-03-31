<?php
use Illuminate\Support\Facades\DB;

require __DIR__.'/../vendor/autoload.php';
$app = require_once __DIR__.'/../bootstrap/app.php';
$kernel = $app->make(Illuminate\Contracts\Http\Kernel::class);
$app->boot();

$homepage_select = DB::table('business_settings')->where('type', 'homepage_select')->value('value');
$nav_activation = DB::table('business_settings')->where('type', 'flash_deals_navigation_activation')->value('value');

echo "Homepage Select: " . $homepage_select . "\n";
echo "Nav Activation: " . $nav_activation . "\n";

$deals = DB::table('flash_deals')->where('status', 1)->get();
echo "Active Deals count in DB: " . count($deals) . "\n";
foreach ($deals as $deal) {
    echo "ID: {$deal->id}, Title: {$deal->title}, Start: " . date('Y-m-d H:i:s', $deal->start_date) . ", End: " . date('Y-m-d H:i:s', $deal->end_date) . "\n";
}
echo "Current Time: " . date('Y-m-d H:i:s') . "\n";
?>
