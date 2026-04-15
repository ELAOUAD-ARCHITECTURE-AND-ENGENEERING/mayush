<?php
require dirname(__DIR__).'/vendor/autoload.php';
$app = require_once dirname(__DIR__).'/bootstrap/app.php';
$app->make(Illuminate\Contracts\Console\Kernel::class)->bootstrap();

echo "Vendor System Activation: " . get_setting('vendor_system_activation') . "\n";
echo "Verified Sellers ID Count: " . count(verified_sellers_id()) . "\n";
echo "Verified Sellers IDs: " . implode(',', verified_sellers_id()) . "\n";

$cat_id = 341;
$products = \App\Models\Product::where(function($q) use ($cat_id) {
    $q->where('category_id', $cat_id)
      ->orWhereHas('categories', function($query) use ($cat_id) {
          $query->where('categories.id', $cat_id);
      });
});

echo "Total products in Category 341 (no filters): " . $products->count() . "\n";

$p1 = clone $products;
echo "Published/Approved/Auction filters: " . $p1->where('published', 1)->where('approved', 1)->where('auction_product', 0)->count() . "\n";

$p2 = clone $products;
$p2 = filter_products($p2);
echo "Full filter_products() count: " . $p2->count() . "\n";

// Let's check why they are filtered out
$all_in_cat = $products->get();
$reasons = [
    'not_published' => 0,
    'not_approved' => 0,
    'auction' => 0,
    'not_admin_and_not_verified' => 0
];

$verified = verified_sellers_id();
$vendor_system = get_setting('vendor_system_activation');

foreach ($all_in_cat as $p) {
    if ($p->published != 1) $reasons['not_published']++;
    if ($p->approved != 1) $reasons['not_approved']++;
    if ($p->auction_product != 0) $reasons['auction']++;
    
    $is_admin = ($p->added_by == 'admin');
    $is_verified = in_array($p->user_id, $verified);
    
    if ($vendor_system == 1) {
        if (!$is_admin && !$is_verified) $reasons['not_admin_and_not_verified']++;
    } else {
        if (!$is_admin) $reasons['not_admin_and_not_verified']++;
    }
}

print_r($reasons);
