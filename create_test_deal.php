<?php

use App\Models\FlashDeal;
use App\Models\Product;
use Carbon\Carbon;

require __DIR__.'/vendor/autoload.php';
$app = require_once __DIR__.'/bootstrap/app.php';
$kernel = $app->make(Illuminate\Contracts\Http\Kernel::class);
$kernel->handle(Illuminate\Http\Request::capture());

$deal = FlashDeal::updateOrCreate(
    ['slug' => 'test-modern-deal'],
    [
        'title' => 'Test Modern Deal',
        'start_date' => Carbon::now()->subDay()->timestamp,
        'end_date' => Carbon::now()->addDays(2)->timestamp,
        'status' => 1,
        'featured' => 1,
        'background_color' => '#ffffff',
        'text_color' => '#000000',
        'banner' => 1, // Placeholder ID
    ]
);

$product = Product::published()->first();
if ($product) {
    \App\Models\FlashDealProduct::updateOrCreate(
        ['flash_deal_id' => $deal->id, 'product_id' => $product->id]
    );
    echo "Created Test Deal with Product: " . $product->name . "\n";
} else {
    echo "No products found to add to deal.\n";
}
