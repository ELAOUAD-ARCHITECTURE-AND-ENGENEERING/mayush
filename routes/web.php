<?php

use App\Http\Controllers\AddressController;
use App\Http\Controllers\AizUploadController;
use App\Http\Controllers\Auth\LoginController;
use App\Http\Controllers\Auth\VerificationController;
use App\Http\Controllers\BlogController;
use App\Http\Controllers\CartController;
use App\Http\Controllers\CheckoutController;
use App\Http\Controllers\CompareController;
use App\Http\Controllers\ConversationController;
use App\Http\Controllers\CurrencyController;
use App\Http\Controllers\CustomerPackageController;
use App\Http\Controllers\CustomerProductController;
use App\Http\Controllers\DemoController;
use App\Http\Controllers\DigitalProductController;
use App\Http\Controllers\HomeController;
use App\Http\Controllers\ExpressBuyController;
use App\Http\Controllers\InvoiceController;
use App\Http\Controllers\LanguageController;
use App\Http\Controllers\MessageController;
use App\Http\Controllers\NotificationController;
use App\Http\Controllers\PageController;
use App\Http\Controllers\PurchaseHistoryController;
use App\Http\Controllers\ReviewController;
use App\Http\Controllers\SearchController;
use App\Http\Controllers\VisualSearchController;
use App\Http\Controllers\SubscriberController;
use App\Http\Controllers\SupportTicketController;
use App\Http\Controllers\WalletController;
use App\Http\Controllers\ProductDetailsController;
use App\Http\Controllers\FrontendShopController;
use App\Http\Controllers\OTPVerificationController;

use App\Http\Controllers\Payment\AamarpayController;
use App\Http\Controllers\Payment\AuthorizenetController;
use App\Http\Controllers\Payment\BkashController;
use App\Http\Controllers\Payment\InstamojoController;
use App\Http\Controllers\Payment\MercadopagoController;
use App\Http\Controllers\Payment\NgeniusController;
use App\Http\Controllers\Payment\PayhereController;
use App\Http\Controllers\Payment\PaypalController;
use App\Http\Controllers\Payment\PaystackController;
use App\Http\Controllers\Payment\SslcommerzController;
use App\Http\Controllers\Payment\RazorpayController;
use App\Http\Controllers\Payment\StripeController;
use App\Http\Controllers\Payment\VoguepayController;
use App\Http\Controllers\Payment\IyzicoController;
use App\Http\Controllers\Payment\NagadController;
use App\Http\Controllers\Payment\PaykuController;
use App\Http\Controllers\ProductQueryController;
use App\Http\Controllers\ShopController;
use App\Http\Controllers\WishlistController;
use App\Http\Controllers\FollowSellerController;

/*
  |--------------------------------------------------------------------------
  | Web Routes
  |--------------------------------------------------------------------------
  |
  | Here is where you can register web routes for your application. These
  | routes are loaded by the RouteServiceProvider within a group which
  | contains the "web" middleware group. Now create something great!
  |
 */

Route::controller(DemoController::class)->group(function () {
    Route::get('/demo/cron_1', 'cron_1');
    Route::get('/demo/cron_2', 'cron_2');
    Route::get('/convert_assets', 'convert_assets');
    Route::get('/convert_category', 'convert_category');
    Route::get('/convert_tax', 'convertTaxes');
    Route::get('/insert_product_variant_forcefully', 'insert_product_variant_forcefully');
    Route::get('/update_seller_id_in_orders/{id_min}/{id_max}', 'update_seller_id_in_orders');
    Route::get('/migrate_attribute_values', 'migrate_attribute_values');
});

// AIZ Uploader
Route::controller(AizUploadController::class)->middleware(['throttle:uploads'])->group(function () {
    Route::post('/aiz-uploader', 'show_uploader');
    Route::post('/aiz-uploader/upload', 'upload');
    Route::get('/aiz-uploader/get-uploaded-files', 'get_uploaded_files');
    Route::post('/aiz-uploader/get_file_by_ids', 'get_preview_files');
    Route::get('/aiz-uploader/download/{id}', 'attachment_download')->name('download_attachment');
    Route::post('/aiz-uploader/bulk-delete', 'bulk_uploaded_files_delete')->name('aiz-uploader.bulk-delete');
    Route::delete('/aiz-uploader/destroy/{id}', 'destroy')->name('aiz-uploader.destroy');
});

Route::middleware(['throttle:login'])->group(function () {
    Auth::routes(['verify' => true]);
});

// Login
Route::controller(LoginController::class)->group(function () {
    Route::get('/logout', 'logout');
    Route::get('/social-login/redirect/{provider}', 'redirectToProvider')->name('social.login');
    Route::get('/social-login/{provider}/callback', 'handleProviderCallback')->name('social.callback');
    //Apple Callback
    Route::post('/apple-callback', 'handleAppleCallback');
    Route::get('/account-deletion', 'account_deletion')->name('account_delete');
});

Route::controller(VerificationController::class)->group(function () {
    Route::post('/email/resend', 'resend')->name('verification.resend');
    Route::get('/email/waiting', 'waiting')->name('verification.waiting');
    Route::get('/email/check-verification', 'check_status')->name('verification.check');
    Route::get('/verification-confirmation/{code}', 'verification_confirmation')->name('email.verification.confirmation');
});



Route::controller(\App\Http\Controllers\Auth\CustomAuthController::class)->group(function () {
    Route::get('/email_change/callback', 'email_change_callback')->name('email_change.callback');
    Route::post('/password/reset/email/submit', 'reset_password_with_code')->name('password.update.email');
    Route::get('/users/login', 'login')->middleware(['throttle:login'])->name('user.login');
    Route::get('/seller/login', 'login')->middleware(['throttle:login'])->name('seller.login');
    Route::get('/deliveryboy/login', 'login')->middleware(['throttle:login'])->name('deliveryboy.login');
    Route::get('/users/registration', 'registration')->name('user.registration');
    Route::post('/users/login/cart', 'cart_login')->middleware(['throttle:login'])->name('cart.login.submit');
    // Route::get('/new-page', 'new_page')->name('new_page');

    Route::post('/customer-reg/verification-code-send', 'sendRegVerificationCode')->name('customer-reg.verification_code_send');
    Route::get('/customer-reg/verify-code/{id}', 'regVerifyCode')->name('customer-reg.verify_code');
    Route::post('/customer-reg/verify-code-confirmation', 'regVerifyCodeConfirmation')->name('customer-reg.verify_code_confirmation');
});

Route::controller(HomeController::class)->group(function () {


    // Visual Search
    Route::post('/search/visual', [VisualSearchController::class, 'visualSearch'])->name('search.visual');

    //Home Page
    Route::get('/', 'index')->name('home');

    Route::get('/home/section/featured', 'load_featured_section')->name('home.section.featured');
    Route::get('/home/section/best_selling', 'load_best_selling_section')->name('home.section.best_selling');
    Route::get('/home/section/home_categories', 'load_home_categories_section')->name('home.section.home_categories');
    Route::get('/home/section/best_sellers', 'load_best_sellers_section')->name('home.section.best_sellers');
    Route::get('/home/section/todays_deal', 'load_todays_deal_section')->name('home.section.todays_deal');
    Route::get('/home/section/newest_products', 'load_newest_product_section')->name('home.section.newest_products');
    Route::get('/home/section/promoted_category', 'load_promoted_category_section')->name('home.section.promoted_category');
    Route::get('/home/section/preorder_products', 'load_preorder_featured_products_section')->name('home.section.preorder_products');
    Route::get('/home/section/load-elite-artisans-section', 'load_elite_artisans_section')->name('load-elite-artisans-section');

    //category dropdown menu ajax call
    Route::post('/category/nav-element-list', 'get_category_items')->name('category.elements');

    //Flash Deal Details Page
    Route::controller(ProductDetailsController::class)->group(function () {
        Route::get('/flash-deals', 'all_flash_deals')->name('flash-deals');
        Route::get('/flash-deals-grid', 'flash_deals_grid')->name('flash-deals-grid'); // AJAX only – returns product grid HTML
        Route::get('/flash-deal/{slug}', 'flash_deal_details')->name('flash-deal-details');
        Route::get('/flash-deal-details-grid/{slug}', 'flash_deal_details_grid')->name('flash-deal-details-grid'); // AJAX only

        Route::get('/product/{slug}', 'product')->name('product');
        Route::post('/product/variant_price', 'variant_price')->name('products.variant_price');
        Route::get('/todays-deal', 'todays_deal')->name('todays-deal');
        Route::get('/best-selling', 'best_selling')->name('best-selling');
        Route::get('/featured-products', 'featured_products')->name('featured-products');
        Route::get('/track-your-order', 'trackOrder')->name('orders.track');
        Route::get('/product-reviews', 'product_reviews')->name('product.reviews');
    });

    Route::controller(FrontendShopController::class)->group(function () {
        Route::get('/shop/{slug}', 'shop')->name('shop.visit');
        Route::get('/shop/{slug}/{type}', 'filter_shop')->name('shop.visit.type');
        Route::get('/sellers', 'all_seller')->name('sellers');
    });

    Route::get('/customer-packages', 'premium_package_index')->name('customer_packages_list_show');

    Route::get('/brands', 'all_brands')->name('brands.all');
    Route::get('/categories', 'all_categories')->name('categories.all');
    Route::get('/coupons', 'all_coupons')->name('coupons.all');
    Route::get('/inhouse', 'inhouse_products')->name('inhouse.all');


});

Route::controller(\App\Http\Controllers\Frontend\PolicyController::class)->group(function () {
    // Policies
    Route::get('/seller-policy', 'sellerpolicy')->name('sellerpolicy');
    Route::get('/return-policy', 'returnpolicy')->name('returnpolicy');
    Route::get('/support-policy', 'supportpolicy')->name('supportpolicy');
    Route::get('/terms', 'terms')->name('terms');
    Route::get('/privacy-policy', 'privacypolicy')->name('privacypolicy');
});

// Health check route for deploy script
Route::get('/up', function () {
    return response('OK', 200);
});

// Language Switch
Route::post('/language', [LanguageController::class, 'changeLanguage'])->name('language.change');

// Currency Switch
Route::post('/currency', [CurrencyController::class, 'changeCurrency'])->name('currency.change');

// Address Helper Routes (Public)
Route::controller(AddressController::class)->group(function () {
    Route::post('/get-states', 'getStates')->name('get-state');
    Route::post('/get-cities', 'getCities')->name('get-city');
    Route::post('/get-areas', 'getAreas')->name('get-area');
    Route::post('/get-cities-by-state', 'getStates')->name('get-cities-by-state');
    Route::post('/get-cities-by-country', 'getCitiesByCountry')->name('get-city-by-country');
});


Route::get('/sitemap.xml', function() {
    return base_path('sitemap.xml');
});

// Classified Product
Route::controller(CustomerProductController::class)->group(function () {
    Route::get('/customer-products', 'customer_products_listing')->name('customer.products');
    Route::get('/customer-products?category={category_slug}', 'search')->name('customer_products.category');
    Route::get('/customer-products?city={city_id}', 'search')->name('customer_products.city');
    Route::get('/customer-products?q={search}', 'search')->name('customer_products.search');
    Route::get('/customer-product/{slug}', 'customer_product')->name('customer.product');
});

// Search
Route::controller(SearchController::class)->group(function () {
    Route::get('/search', 'index')->name('search');
    Route::get('/search?keyword={search}', 'index')->name('suggestion.search');
    Route::get('/search-v2', 'index2')->name('suggestion.search2');
    Route::post('/ajax-search', 'ajax_search')->name('search.ajax');
    Route::get('/category/{category_slug}', 'listingByCategory')->name('products.category');
    Route::get('/brand/{brand_slug}', 'listingByBrand')->name('products.brand');
});

// Cart
Route::controller(CartController::class)->group(function () {
    Route::get('/cart', 'index')->name('cart');
    Route::post('/cart/show-cart-modal', 'showCartModal')->name('cart.showCartModal');
    Route::post('/cart/select-variant-canvas', 'selectVariantCanvas')->name('cart.selectVariantCanvas');
    Route::post('/cart/addtocart', 'addToCart')->name('cart.addToCart');
    Route::post('/cart/removeFromCart', 'removeFromCart')->name('cart.removeFromCart');
    Route::post('/cart/updateQuantity', 'updateQuantity')->name('cart.updateQuantity');
    Route::post('/cart/update-status', 'updateCartStatus')->name('cart.updateCartStatus');
    Route::post('/cart/show-cart-modal-auction', 'showCartModalAuction')->name('cart.showCartModalAuction');
    Route::post('/cart/buy-now', 'buyNow')->name('cart.buy_now');
});

Route::middleware(['throttle:payments'])->group(function () {
    //Paypal START
    Route::controller(PaypalController::class)->group(function () {
        Route::get('/paypal/payment/done', 'getDone')->name('payment.done');
        Route::get('/paypal/payment/cancel', 'getCancel')->name('payment.cancel');
    });
    //Mercadopago START
    Route::controller(MercadopagoController::class)->group(function () {
        Route::any('/mercadopago/payment/done', 'paymentstatus')->name('mercadopago.done');
        Route::any('/mercadopago/payment/cancel', 'callback')->name('mercadopago.cancel');
    });
    //Mercadopago 

    // SSLCOMMERZ Start
    Route::controller(SslcommerzController::class)->group(function () {
        Route::get('/sslcommerz/pay', 'index');
        Route::POST('/sslcommerz/success', 'success');
        Route::POST('/sslcommerz/fail', 'fail');
        Route::POST('/sslcommerz/cancel', 'cancel');
        Route::POST('/sslcommerz/ipn', 'ipn');
    });
    //SSLCOMMERZ END

    //Stipe Start
    Route::controller(StripeController::class)->group(function () {
        Route::get('stripe', 'stripe');
        Route::post('/stripe/create-checkout-session', 'create_checkout_session')->name('stripe.get_token');
        Route::any('/stripe/payment/callback', 'callback')->name('stripe.callback');
        Route::get('/stripe/success', 'success')->name('stripe.success');
        Route::get('/stripe/cancel', 'cancel')->name('stripe.cancel');
    });
    //Stripe END
});

// Compare
Route::controller(CompareController::class)->group(function () {
    Route::get('/compare', 'index')->name('compare');
    Route::get('/compare/reset', 'reset')->name('compare.reset');
    Route::post('/compare/addToCompare', 'addToCompare')->name('compare.addToCompare');
});

// Subscribe (public form)
Route::post('subscribers', [SubscriberController::class, 'store'])->name('subscribers.store');
Route::resource('subscribers', SubscriberController::class, ['as' => 'public'])->except(['store']);

// Missing payment/order routes referenced by CmiController
Route::get('/payment/failed/{combined_order_id?}', [CheckoutController::class, 'payment_failed'])->name('payment.failed');

Route::group(['middleware' => ['user', 'verified', 'unbanned']], function() {

    Route::controller(HomeController::class)->group(function () {
        Route::get('/dashboard', 'dashboard')->name('dashboard');
        Route::get('/profile', 'profile')->name('profile');
        Route::post('/user/update-profile', 'userProfileUpdate')->name('user.profile.update');
    });

    Route::controller(\App\Http\Controllers\Auth\CustomAuthController::class)->group(function () {
        Route::post('/new-user-verification', 'new_verify')->name('user.new.verify');
        Route::post('/new-user-email', 'update_email')->name('user.change.email');
    });
    
    Route::get('/all-notifications', [NotificationController::class, 'index'])->name('all-notifications');

});

Route::group(['middleware' => ['customer', 'verified', 'unbanned']], function() {

    Route::get('/checkout-test', [CheckoutController::class, 'index']);
    Route::get('/checkout', [CheckoutController::class, 'index'])->name('checkout.shipping_info');
    // Checkout Routes
    Route::group(['prefix' => 'checkout', 'middleware' => ['throttle:payments']], function() {
        Route::controller(CheckoutController::class)->group(function () {
            Route::any('/delivery_info', 'store_shipping_info')->name('checkout.store_shipping_infostore');
            Route::post('/payment_select', 'store_delivery_info')->name('checkout.store_delivery_info');
            Route::post('/update-delivery-info', 'updateDeliveryInfo')->name('checkout.updateDeliveryInfo');
            Route::get('/order-confirmed', 'order_confirmed')->name('order_confirmed');
            Route::get('/order-confirmed/{combined_order_id}', 'order_confirmed')->name('order_confirmed_with_id');
            Route::post('/payment', 'checkout')->name('payment.checkout');
            Route::get('/deliveryinfo', 'get_delivery_info')->name('checkout.get_delivery_info');
            Route::post('/get_pick_up_points', 'get_pick_up_points')->name('shipping_info.get_pick_up_points');
            Route::get('/payment-select', 'get_payment_info')->name('checkout.payment_info');
            Route::post('/apply_coupon_code', 'apply_coupon_code')->name('checkout.apply_coupon_code');
            Route::post('/remove_coupon_code', 'remove_coupon_code')->name('checkout.remove_coupon_code');
            //Club point
            Route::post('/apply-club-point', 'apply_club_point')->name('checkout.apply_club_point');
            Route::post('/remove-club-point', 'remove_club_point')->name('checkout.remove_club_point'); 
            Route::post('/update-delivery-address', 'updateDeliveryAddress')->name('checkout.updateDeliveryAddress');
            Route::post('/update-billing-address', 'updateBillingAddress')->name('checkout.updateBillingAddress');
            Route::post('/fast-purchase', 'fast_purchase')->name('checkout.fast_purchase');
        });
    });

    // Purchase History
    Route::resource('purchase_history', PurchaseHistoryController::class);
    Route::controller(PurchaseHistoryController::class)->group(function () {
        Route::get('/purchase_history/details/{id}', 'purchase_history_details')->name('purchase_history.details');
        // Route::get('/purchase_history/destroy/{id}', 'order_cancel')->name('purchase_history.destroy');
        Route::get('digital-purchase-history', 'digital_index')->name('digital_purchase_history.index');
        Route::get('/digital-products/download/{id}', 'download')->name('digital-products.download');
    });

    // Wishlist
    Route::resource('wishlists', WishlistController::class);
    Route::post('/wishlists/remove', [WishlistController::class, 'remove'])->name('wishlists.remove');

    // Wallet
    Route::controller(WalletController::class)->group(function () {
        Route::get('/wallet', 'index')->name('wallet.index');
        Route::post('/recharge', 'recharge')->name('wallet.recharge');
    });

    // Support Ticket
    Route::resource('support_ticket', SupportTicketController::class);
    Route::post('support_ticket/reply', [SupportTicketController::class, 'seller_store'])->name('support_ticket.seller_store');

    // Customer Package
    Route::post('/customer_packages/purchase',[CustomerPackageController::class, 'purchase_package'])->name('customer_packages.purchase');

    // Customer Product
    Route::resource('customer_products', CustomerProductController::class)->except(['store', 'edit', 'update', 'destroy']);
    Route::controller(CustomerProductController::class)->group(function () {
        Route::post('/customer_products', 'store')->name('customer_products.store')->withoutMiddleware(['customer']);
        Route::get('/customer_products/{id}/edit', 'edit')->name('customer_products.edit')->withoutMiddleware(['customer'])->middleware(['user']);
        Route::post('/customer_products/update/{id}', 'update')->name('customer_products.update')->withoutMiddleware(['customer'])->middleware(['user']);
        Route::get('/customer_products/destroy/{id}', 'destroy')->name('customer_products.destroy')->withoutMiddleware(['customer'])->middleware(['user']);
        Route::post('/customer_products/published', 'updatePublished')->name('customer_products.published');
        Route::post('/customer_products/status', 'updateStatus')->name('customer_products.update.status');
        Route::post('/customer_products/promote', 'store_promotion')->name('customer_products.promote')->withoutMiddleware(['customer']);
    });

    // Product Review
    Route::post('/product_review_modal', [ReviewController::class, 'product_review_modal'])->name('product_review_modal');
    Route::controller(FollowSellerController::class)->group(function () {
        Route::get('/followed-seller', 'index')->name('followed_seller');
        Route::post('/followed-seller-store', 'store')->name('followed_seller.store');
        Route::get('/followed-seller-remove', 'remove')->name('followed_seller.remove');
    });
});

Route::group(['middleware' => ['auth']], function() {
    
    Route::get('invoice/{order_id}', [InvoiceController::class, 'invoice_download'])->name('invoice.download');

    Route::get('/express-buy/check', [ExpressBuyController::class, 'eligibility'])->name('express.check');
    Route::post('/express-buy/{product_id}', [ExpressBuyController::class, 'submit'])->middleware('throttle:5,1')->name('express.buy');

    // Stock Alert
    Route::post('/stock-alert/subscribe', [App\Http\Controllers\StockAlertController::class, 'subscribe'])->name('stock.alert.subscribe');

    // Reviews
    Route::resource('/reviews', ReviewController::class);
    
    // Product Conversation
    Route::resource('conversations', ConversationController::class);
    Route::controller(ConversationController::class)->group(function () {
        // Route::get('/conversations/destroy/{id}', 'destroy')->name('conversations.destroy');
        Route::post('conversations/refresh', 'refresh')->name('conversations.refresh');
    });
    
    // Product Query
    Route::resource('product-queries', ProductQueryController::class);

    Route::resource('messages', MessageController::class);

    //Address
    Route::resource('addresses', AddressController::class);
    Route::controller(AddressController::class)->group(function () {
        // Helper routes moved to public section
        // Route::post('/addresses/update/{id}', 'update')->name('addresses.update');
        // Route::get('/addresses/destroy/{id}', 'destroy')->name('addresses.destroy');
        Route::get('/addresses/set_default/{id}', 'set_default')->name('addresses.set_default');
        Route::get('/addresses/set_billing/{id}', 'set_billing')->name('addresses.set_billing');
    });

    // Payment Tokens (Vault)
    Route::controller(\App\Http\Controllers\PaymentTokenController::class)->group(function () {
        Route::get('/payment-methods', 'index')->name('payment.tokens');
        Route::get('/payment-methods/{token}/default', 'setDefault')->name('payment.token.default');
        Route::get('/payment-methods/{token}/remove', 'destroy')->name('payment.token.remove');
    });

    // Phase 4: Customer Loyalty Hub
    Route::get('/loyalty', [\App\Http\Controllers\LoyaltyController::class, 'hub'])->name('loyalty.hub');

    // Phase 5: Affiliate
    Route::get('/affiliate', [\App\Http\Controllers\AffiliateController::class, 'index'])->name('affiliate.user.index');
    Route::post('/affiliate/apply', [\App\Http\Controllers\AffiliateController::class, 'apply'])->name('affiliate.apply');

    // Advanced Live Tracking (RBAC Unified)
    Route::get('/dashboard/tracking/{id}', [\App\Http\Controllers\OrderTrackingController::class, 'show'])->name('orders.tracking.show');
    Route::get('/dashboard/tracking/{id}/sync', [\App\Http\Controllers\OrderTrackingController::class, 'syncTracking'])->name('orders.tracking.sync');

});

Route::resource('shops', ShopController::class);
Route::controller(ShopController::class)->group(function () {
    Route::get('/shop-reg/verification', 'verifyRegEmailorPhone')->name('shop-reg.verification');
    Route::post('/shop-reg/verification-code-send', 'sendRegVerificationCode')->name('shop-reg.verification_code_send');
    Route::get('/shop-reg/verify-code/{id}', 'regVerifyCode')->name('shop-reg.verify_code');
    Route::post('/shop-reg/verify-code-confirmation', 'regVerifyCodeConfirmation')->name('shop-reg.verify_code_confirmation');
});
Route::middleware(['throttle:payments'])->group(function () {
    Route::get('/instamojo/payment/pay-success', [InstamojoController::class, 'success'])->name('instamojo.success');

    Route::post('rozer/payment/pay-success', [RazorpayController::class, 'payment'])->name('payment.rozer');

    Route::get('/paystack/payment/callback', [PaystackController::class, 'handleGatewayCallback']);
    Route::get('/paystack/new-callback', [PaystackController::class, 'paystackNewCallback']);

    Route::controller(VoguepayController::class)->group(function () {
        Route::get('/vogue-pay', 'pay');
        Route::get('/vogue-pay/success/{id}', 'paymentSuccess');
        Route::get('/vogue-pay/failure/{id}', 'paymentFailure');
    });


    //Iyzico
    Route::any('/iyzico/payment/callback/{payment_type}/{amount?}/{payment_method?}/{combined_order_id?}/{customer_package_id?}/{seller_package_id?}', [IyzicoController::class, 'callback'])->name('iyzico.callback');

    Route::get('/customer-products/admin', [IyzicoController::class, 'initPayment'])->name('iyzico.init_payment_admin');

    //payhere below
    Route::controller(PayhereController::class)->group(function () {
        Route::get('/payhere/checkout/testing', 'checkout_testing')->name('payhere.checkout.testing');
        Route::get('/payhere/wallet/testing', 'wallet_testing')->name('payhere.wallet.testing');
        Route::get('/payhere/customer_package/testing', 'customer_package_testing')->name('payhere.customer_package.testing');

        Route::any('/payhere/checkout/notify', 'checkout_notify')->name('payhere.checkout.notify');
        Route::any('/payhere/checkout/return', 'checkout_return')->name('payhere.checkout.return');
        Route::any('/payhere/checkout/cancel', 'chekout_cancel')->name('payhere.checkout.cancel');

        Route::any('/payhere/wallet/notify', 'wallet_notify')->name('payhere.wallet.notify');
        Route::any('/payhere/wallet/return', 'wallet_return')->name('payhere.wallet.return');
        Route::any('/payhere/wallet/cancel', 'wallet_cancel')->name('payhere.wallet.cancel');

        Route::any('/payhere/seller_package_payment/notify', 'seller_package_notify')->name('payhere.seller_package_payment.notify');
        Route::any('/payhere/seller_package_payment/return', 'seller_package_payment_return')->name('payhere.seller_package_payment.return');
        Route::any('/payhere/seller_package_payment/cancel', 'seller_package_payment_cancel')->name('payhere.seller_package_payment.cancel');

        Route::any('/payhere/customer_package_payment/notify', 'customer_package_notify')->name('payhere.customer_package_payment.notify');
        Route::any('/payhere/customer_package_payment/return', 'customer_package_return')->name('payhere.customer_package_payment.return');
        Route::any('/payhere/customer_package_payment/cancel', 'customer_package_cancel')->name('payhere.customer_package_payment.cancel');
    });


    //N-genius
    Route::controller(NgeniusController::class)->group(function () {
        Route::any('ngenius/cart_payment_callback', 'cart_payment_callback')->name('ngenius.cart_payment_callback');
        Route::any('ngenius/wallet_payment_callback', 'wallet_payment_callback')->name('ngenius.wallet_payment_callback');
        Route::any('ngenius/customer_package_payment_callback', 'customer_package_payment_callback')->name('ngenius.customer_package_payment_callback');
        Route::any('ngenius/seller_package_payment_callback', 'seller_package_payment_callback')->name('ngenius.seller_package_payment_callback');
    });

    //bKash
    Route::controller(BkashController::class)->group(function () {
        Route::post('/bkash/createpayment', 'checkout')->name('bkash.checkout');
        Route::post('/bkash/executepayment', 'excecute')->name('bkash.excecute');
        Route::get('/bkash/success', 'success')->name('bkash.success');
    });

    Route::get('/checkout-payment-detail', [StripeController::class, 'checkout_payment_detail']);

    //Nagad
    Route::get('/nagad/callback', [NagadController::class, 'verify'])->name('nagad.callback');

    //aamarpay
    Route::controller(AamarpayController::class)->group(function () {
        Route::post('/aamarpay/success','success')->name('aamarpay.success');
        Route::post('/aamarpay/fail','fail')->name('aamarpay.fail');
    });

    //Authorize-Net-Payment
    Route::post('/dopay/online', [AuthorizenetController::class, 'handleonlinepay'])->name('dopay.online');

    //payku
    Route::get('/payku/callback/{id}', [PaykuController::class, 'callback'])->name('payku.result');
});


//Blog Section
Route::controller(BlogController::class)->group(function () {
    Route::get('/blog', 'all_blog')->name('blog');
    Route::get('/blog/{slug}', 'blog_details')->name('blog.details');
});

Route::controller(PageController::class)->group(function () {
    //mobile app balnk page for webview
    Route::get('/mobile-page/{slug}', 'mobile_custom_page')->name('mobile.custom-pages');

    //Custom page
    Route::get('/{slug}', 'show_custom_page')->name('custom-pages.show_custom_page')->where('slug', '.+');
});
