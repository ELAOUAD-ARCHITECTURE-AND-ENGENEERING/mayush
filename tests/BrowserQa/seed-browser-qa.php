<?php

require __DIR__ . '/../../vendor/autoload.php';

$app = require __DIR__ . '/../../bootstrap/app.php';
$app->make(Illuminate\Contracts\Console\Kernel::class)->bootstrap();

use App\Models\Address;
use App\Models\Brand;
use App\Models\BusinessSetting;
use App\Models\Category;
use App\Models\Language;
use App\Models\Order;
use App\Models\OrderDetail;
use App\Models\OrderTrackingHistory;
use App\Models\Page;
use App\Models\Product;
use App\Models\ProductCategory;
use App\Models\ProductStock;
use App\Models\Shop;
use App\Models\User;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Facades\Schema;

function browser_qa_set_attrs($model, array $attrs)
{
    foreach ($attrs as $key => $value) {
        if (Schema::hasColumn($model->getTable(), $key)) {
            $model->{$key} = $value;
        }
    }

    $model->save();

    return $model;
}

Language::updateOrCreate(['code' => 'en'], ['name' => 'English', 'app_lang_code' => 'en', 'rtl' => 0]);

foreach ([
    'site_name' => 'Mayush QA',
    'website_name' => 'Mayush QA',
    'system_default_currency' => 1,
    'language' => 'en',
    'homepage_select' => 'classic',
    'authentication_layout_select' => 'boxed',
    'classified_product' => '1',
    'google_recaptcha' => '0',
    'recaptcha_customer_register' => '0',
    'customer_registration_verify' => '0',
    'cloudflare_turnstile' => '0',
    'turnstile_customer_register' => '0',
    'facebook_login' => '0',
    'google_login' => '0',
    'twitter_login' => '0',
    'apple_login' => '0',
    'guest_checkout_activation' => '1',
    'vendor_system_activation' => '1',
    'conversation_system' => '1',
    'product_query_activation' => '1',
    'email_verification' => '0',
    'wallet_system' => '1',
    'pickup_point' => '0',
    'coupon_system' => '1',
] as $type => $value) {
    BusinessSetting::updateOrCreate(['type' => $type], ['value' => $value]);
}

$admin = browser_qa_set_attrs(User::firstOrNew(['email' => 'qa-admin@example.test']), [
    'name' => 'QA Admin',
    'password' => Hash::make('Password123!'),
    'user_type' => 'admin',
    'email_verified_at' => now(),
    'verification_status' => 1,
    'banned' => 0,
    'phone' => '+15550000000',
]);

$customer = browser_qa_set_attrs(User::firstOrNew(['email' => 'qa-customer@example.test']), [
    'name' => 'QA Customer',
    'password' => Hash::make('Password123!'),
    'user_type' => 'customer',
    'email_verified_at' => now(),
    'verification_status' => 1,
    'banned' => 0,
    'phone' => '+15550000001',
]);

$seller = browser_qa_set_attrs(User::firstOrNew(['email' => 'qa-seller@example.test']), [
    'name' => 'QA Seller',
    'password' => Hash::make('Password123!'),
    'user_type' => 'seller',
    'email_verified_at' => now(),
    'verification_status' => 1,
    'banned' => 0,
    'remaining_uploads' => 10,
    'phone' => '+15550000002',
]);

$shop = browser_qa_set_attrs(Shop::firstOrNew(['user_id' => $seller->id]), [
    'name' => 'QA Seller Shop',
    'slug' => 'qa-seller-shop',
    'address' => 'QA Seller Street',
    'phone' => '+15550000002',
    'registration_approval' => 1,
    'verification_status' => 1,
    'approval_status' => 'approved',
]);

$category = browser_qa_set_attrs(Category::firstOrNew(['slug' => 'qa-category']), [
    'name' => 'QA Category',
    'parent_id' => 0,
    'digital' => 0,
    'published' => 1,
    'order_level' => 1,
]);

$brand = browser_qa_set_attrs(Brand::firstOrNew(['slug' => 'qa-brand']), [
    'name' => 'QA Brand',
]);

$stocked = browser_qa_set_attrs(Product::firstOrNew(['slug' => 'qa-stocked-product']), [
    'name' => 'QA Stocked Product',
    'added_by' => 'seller',
    'user_id' => $seller->id,
    'category_id' => $category->id,
    'brand_id' => $brand->id,
    'unit' => 'pc',
    'min_qty' => 1,
    'current_stock' => 25,
    'unit_price' => 120,
    'purchase_price' => 80,
    'published' => 1,
    'approved' => 1,
    'digital' => 0,
    'auction_product' => 0,
    'wholesale_product' => 0,
    'description' => 'QA stocked product for browser automation',
    'tags' => 'qa,stocked',
    'thumbnail_img' => null,
    'photos' => null,
]);

browser_qa_set_attrs(ProductStock::firstOrNew(['product_id' => $stocked->id, 'variant' => '']), [
    'sku' => 'QA-STOCKED',
    'price' => 120,
    'qty' => 25,
]);

$out = browser_qa_set_attrs(Product::firstOrNew(['slug' => 'qa-out-of-stock-product']), [
    'name' => 'QA Out Of Stock Product',
    'added_by' => 'seller',
    'user_id' => $seller->id,
    'category_id' => $category->id,
    'brand_id' => $brand->id,
    'unit' => 'pc',
    'min_qty' => 1,
    'current_stock' => 0,
    'unit_price' => 80,
    'purchase_price' => 50,
    'published' => 1,
    'approved' => 1,
    'digital' => 0,
    'auction_product' => 0,
    'wholesale_product' => 0,
    'description' => 'QA out-of-stock product for stock alert automation',
    'tags' => 'qa,outofstock',
    'thumbnail_img' => null,
    'photos' => null,
]);

browser_qa_set_attrs(ProductStock::firstOrNew(['product_id' => $out->id, 'variant' => '']), [
    'sku' => 'QA-OOS',
    'price' => 80,
    'qty' => 0,
]);

if (class_exists(ProductCategory::class)) {
    $stockedCategory = ProductCategory::where('product_id', $stocked->id)->where('category_id', $category->id)->first() ?: new ProductCategory();
    browser_qa_set_attrs($stockedCategory, ['product_id' => $stocked->id, 'category_id' => $category->id]);
    $outCategory = ProductCategory::where('product_id', $out->id)->where('category_id', $category->id)->first() ?: new ProductCategory();
    browser_qa_set_attrs($outCategory, ['product_id' => $out->id, 'category_id' => $category->id]);
}

browser_qa_set_attrs(Address::firstOrNew(['user_id' => $customer->id, 'address' => 'QA Customer Street']), [
    'country_id' => 1,
    'city_id' => 1,
    'postal_code' => '10000',
    'phone' => '+15550000001',
    'set_default' => 1,
    'set_billing' => 1,
]);

$order = browser_qa_set_attrs(Order::firstOrNew(['code' => 'QA-ORDER-1001']), [
    'user_id' => $customer->id,
    'seller_id' => $seller->id,
    'shipping_address' => json_encode([
        'name' => 'QA Customer',
        'email' => 'qa-customer@example.test',
        'phone' => '+15550000001',
        'address' => 'QA Customer Street',
        'city' => 'QA City',
        'country' => 'US',
    ]),
    'payment_type' => 'cash_on_delivery',
    'payment_status' => 'unpaid',
    'delivery_status' => 'pending',
    'grand_total' => 120,
    'date' => now()->timestamp,
    'is_confirmed' => 1,
]);

browser_qa_set_attrs(OrderDetail::firstOrNew(['order_id' => $order->id, 'product_id' => $stocked->id]), [
    'seller_id' => $seller->id,
    'product_name' => 'QA Stocked Product',
    'price' => 120,
    'quantity' => 1,
    'payment_status' => 'unpaid',
    'delivery_status' => 'pending',
]);

if (class_exists(OrderTrackingHistory::class)) {
    browser_qa_set_attrs(OrderTrackingHistory::firstOrNew(['order_id' => $order->id, 'status' => 'created']), [
        'description' => 'QA tracking created',
        'location' => 'QA Warehouse',
        'carrier' => 'QA Carrier',
        'tracking_code' => 'QA-TRACK-1001',
    ]);
}

if (Schema::hasTable('pages')) {
    $contactPage = Page::where('slug', 'contact-us')->first() ?: new Page();
    browser_qa_set_attrs($contactPage, [
        'slug' => 'contact-us',
        'type' => 'contact_us_page',
        'title' => 'Contact Us',
        'content' => 'Browser QA contact page',
        'meta_title' => 'Contact Us',
    ]);
}

echo json_encode([
    'admin' => $admin->email,
    'customer' => $customer->email,
    'seller' => $seller->email,
    'shop' => $shop->slug,
    'product' => $stocked->slug,
    'out_of_stock' => $out->slug,
], JSON_PRETTY_PRINT) . PHP_EOL;
