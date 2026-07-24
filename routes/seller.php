<?php

use App\Http\Controllers\AizUploadController;
use App\Http\Controllers\Seller\AnalyticsDashboardController;
use App\Http\Controllers\Seller\AddressController;
use App\Http\Controllers\Seller\CommissionHistoryController;
use App\Http\Controllers\Seller\ConversationController;
use App\Http\Controllers\Seller\CouponController;
use App\Http\Controllers\Seller\CustomLabelController;
use App\Http\Controllers\Seller\DashboardController;
use App\Http\Controllers\Seller\DigitalProductController;
use App\Http\Controllers\Seller\GSTController;
use App\Http\Controllers\Seller\InvoiceController;
use App\Http\Controllers\Seller\NoteController;
use App\Http\Controllers\Seller\NotificationController;
use App\Http\Controllers\Seller\OrderController;
use App\Http\Controllers\Seller\PaymentController;
use App\Http\Controllers\Seller\ProductBulkUploadController;
use App\Http\Controllers\Seller\ProductController;
use App\Http\Controllers\Seller\ProductQueryController;
use App\Http\Controllers\Seller\ProfileController;
use App\Http\Controllers\Seller\ReviewController;
use App\Http\Controllers\Seller\SellerWithdrawRequestController;
use App\Http\Controllers\Seller\ShopController;
use App\Http\Controllers\Seller\SupportTicketController;
use App\Http\Controllers\SellerEliteController;
use App\Http\Controllers\ProductTranslationController;

//Upload
Route::group(['prefix' => 'seller', 'middleware' => ['seller', 'verified', 'user', 'prevent-back-history'], 'as' => 'seller.'], function () {
    Route::controller(AizUploadController::class)->group(function () {
        Route::any('/uploads', 'index')->name('uploaded-files.index');
        Route::any('/uploads/create', 'create')->name('uploads.create');
        Route::any('/uploads/trash', 'trash')->name('uploaded-files.trash');
        Route::any('/uploads/file-info', 'file_info')->name('my_uploads.info');
        Route::delete('/uploads/destroy/{id}', 'destroy')->name('my_uploads.destroy');
        Route::post('/uploads/restore', 'restore')->name('uploaded-files.restore');
        Route::post('/uploads/bulk-force-delete', 'bulk_force_delete')->name('uploaded-files.bulk-force-delete');
        Route::post('/bulk-uploaded-files-delete', 'bulk_uploaded_files_delete')->name('bulk-uploaded-files-delete');
    });
});

Route::group(['prefix' => 'seller', 'middleware' => ['seller', 'verified', 'user', 'prevent-back-history'], 'as' => 'seller.'], function () {
    Route::controller(DashboardController::class)->group(function () {
        Route::get('/dashboard', 'index')->name('dashboard');
        Route::get('/clear-cache', 'clearCache')->name('cache.clear');
    });

    // Onboarding
    Route::controller(\App\Http\Controllers\Seller\OnboardingController::class)->group(function () {
        Route::get('/onboarding', 'index')->name('onboarding.index');
        Route::post('/onboarding/upload', 'upload')->name('onboarding.upload');
        Route::get('/onboarding/contract', 'downloadContract')->name('onboarding.contract');
        Route::get('/onboarding/documents/{document}/download', 'downloadDocument')->name('onboarding.document.download');
        Route::post('/onboarding/resubmit', 'resubmit')->name('onboarding.resubmit');
    });

    Route::prefix('analytics')->name('analytics.')->group(function () {
        Route::get('/', [AnalyticsDashboardController::class, 'index'])->name('index');
        Route::get('/stats', [AnalyticsDashboardController::class, 'stats'])->name('stats');
        Route::get('/funnel', [AnalyticsDashboardController::class, 'funnel'])->name('funnel');
        Route::get('/top-products', [AnalyticsDashboardController::class, 'topProducts'])->name('top_products');
        Route::get('/revenue-trend', [AnalyticsDashboardController::class, 'revenueTrend'])->name('revenue_trend');
        Route::get('/financial', [AnalyticsDashboardController::class, 'financialStats'])->name('financial');
        Route::get('/geo', [AnalyticsDashboardController::class, 'geoStats'])->name('geo');
        Route::get('/projected', [AnalyticsDashboardController::class, 'projectedStats'])->name('projected');
    });

    // Product
    Route::middleware(['seller.approved'])->group(function () {
        Route::controller(ProductController::class)->group(function () {
            Route::get('/products', 'index')->name('products');
            Route::get('/product/create', 'create')->name('products.create');
            Route::post('/products/store', 'store')->name('products.store');
            Route::post('/products/translate-to-arabic', [ProductTranslationController::class, 'translate'])
                ->middleware('throttle:product-translation')
                ->name('products.translate_to_arabic');
            Route::get('/product/{id}/edit', 'edit')->name('products.edit');
            Route::post('/products/update/{product}', 'update')->name('products.update');
            Route::get('/products/duplicate/{id}', 'duplicate')->name('products.duplicate');
            Route::post('/products/sku_combination', 'sku_combination')->name('products.sku_combination');
            Route::post('/products/sku_combination_edit', 'sku_combination_edit')->name('products.sku_combination_edit');
            Route::post('/products/add-more-choice-option', 'add_more_choice_option')->name('products.add-more-choice-option');
            Route::post('/products/seller/featured', 'updateFeatured')->name('products.featured');
            Route::post('/products/published', 'updatePublished')->name('products.published');
            Route::delete('/products/destroy/{id}', 'destroy')->name('products.destroy');
            Route::post('/products/bulk-delete', 'bulk_product_delete')->name('products.bulk-delete');
            Route::post('/product-search', 'product_search')->name('product.search');
            Route::post('/get-selected-products', 'get_selected_products')->name('get-selected-products');

            // category-wise discount set
            Route::get('/categories-wise-product-discount', 'categoriesWiseProductDiscount')->name('categories_wise_product_discount');
            Route::post('/set-product-discount', 'setProductDiscount')->name('set_product_discount');
        });

        // Promoted Products (Classifieds)
        Route::get('/promoted-products', [\App\Http\Controllers\CustomerProductController::class, 'index'])->name('promoted_products');



        // Product Bulk Upload
        Route::controller(ProductBulkUploadController::class)->group(function () {
            Route::get('/product-bulk-upload/index', 'index')->name('product_bulk_upload.index');
            Route::post('/product-bulk-upload/store', 'bulk_upload')->name('bulk_product_upload');
            Route::group(['prefix' => 'bulk-upload/download'], function() {
                Route::get('/category', 'pdf_download_category')->name('pdf.download_category');
                Route::get('/brand', 'pdf_download_brand')->name('pdf.download_brand');
            });
        });

        // Digital Product
        Route::controller(DigitalProductController::class)->group(function () {
            Route::get('/digitalproducts', 'index')->name('digitalproducts');
            Route::get('/digitalproducts/create', 'create')->name('digitalproducts.create');
            Route::post('/digitalproducts/store', 'store')->name('digitalproducts.store');
            Route::get('/digitalproducts/{id}/edit', 'edit')->name('digitalproducts.edit');
            Route::post('/digitalproducts/update/{product}', 'update')->name('digitalproducts.update');
            Route::delete('/digitalproducts/destroy/{id}', 'destroy')->name('digitalproducts.destroy');
            Route::get('/digitalproducts/download/{id}', 'download')->name('digitalproducts.download');
        });

        // custom label
        Route::controller(CustomLabelController::class)->group(function () {
            Route::get('/custom-label-list', 'index')->name('custom_label.index');
            Route::get('/custom-label-create', 'create')->name('custom_label.create');
            Route::post('/custom-label-store', 'store')->name('custom_label.store');
            Route::get('/custom-label-edit/{id}', 'edit')->name('custom_label.edit');
            Route::post('/custom-label-update/{id}', 'update')->name('custom_label.update');
            Route::delete('/custom-label-delete/{id}', 'destroy')->name('custom_label.delete');
            Route::post('/custom-label/products', 'products')->name('custom_label.products');
        });
    });
    
    // Note
    Route::resource('note', NoteController::class);
    Route::resource('coupon', CouponController::class);
    Route::controller(CouponController::class)->group(function () {
        Route::post('/coupon/get_form', 'get_coupon_form')->name('coupon.get_coupon_form');
        Route::post('/coupon/get_form_edit', 'get_coupon_form_edit')->name('coupon.get_coupon_form_edit');
        // Route::get('/coupon/destroy/{id}', 'destroy')->name('coupon.destroy');
    });

    //Order
    Route::resource('orders', OrderController::class)->only(['index', 'create', 'store', 'show', 'edit', 'update', 'destroy']);
    Route::controller(OrderController::class)->group(function () {
        Route::post('/orders/update_delivery_status', 'update_delivery_status')->name('orders.update_delivery_status');
        Route::post('/orders/update_payment_status', 'update_payment_status')->name('orders.update_payment_status');

        // Order bulk export
        Route::get('/order-bulk-export', 'orderBulkExport')->name('order-bulk-export');
    });

    Route::controller(InvoiceController::class)->group(function () {
        Route::get('/invoice/{order_id}', 'invoice_download')->name('invoice.download');
    });
    
    //Review
    Route::controller(ReviewController::class)->group(function () {
        Route::get('/product-reviews', 'index')->name('product-reviews');
        Route::get('/product/detail-reviews/{id}', 'detailReviews')->name('detail-reviews');
        
    });

    //Shop
    Route::controller(ShopController::class)->group(function () {
        Route::get('/shop', 'index')->name('shop.index');
        Route::post('/shop/update', 'update')->name('shop.update');
        Route::post('/shop/banner-update', 'bannerUpdate')->name('shop.banner.update');
        Route::get('/shop/apply-for-verification', 'verify_form')->name('shop.verify');
        Route::post('/shop/verification_info_store', 'verify_form_store')->name('shop.verify.store');
        Route::get('/category-wise-commission', 'categoriesWiseCommission')->name('categories-wise-commission');
    });

    //Payments
    Route::resource('payments', PaymentController::class)->only(['index', 'create', 'store', 'show', 'edit', 'update', 'destroy']);

    // Profile Settings
    Route::controller(ProfileController::class)->group(function () {
        Route::get('/profile', 'index')->name('profile.index');
        Route::post('/profile/update/{id}', 'update')->name('profile.update');
    });

    // Artisan Elite Profile
    Route::controller(SellerEliteController::class)->group(function () {
        Route::get('/elite-profile', 'index')->name('elite.index');
        Route::get('/elite/pricing', 'pricing')->name('elite.pricing');
        Route::post('/elite/recap', 'recap')->name('elite.recap');
        Route::post('/elite/process-payment', 'processPayment')->name('elite.process_payment');
        Route::get('/elite/payment-success', 'paymentSuccess')->name('elite.payment.success');
        Route::get('/elite/payment-failed', 'paymentFail')->name('elite.payment.fail');
        Route::post('/elite/cancel', 'cancel')->name('elite.cancel');
        Route::post('/elite/update', 'updateProfile')->name('elite.update_profile');
    });

    // Address
    Route::resource('addresses', AddressController::class);
    Route::controller(AddressController::class)->group(function () {
        Route::post('/get-states', 'getStates')->name('get-state');
        Route::post('/get-cities', 'getCities')->name('get-city');
        // Route::post('/address/update/{id}', 'update')->name('addresses.update');
        // Route::get('/addresses/destroy/{id}', 'destroy')->name('addresses.destroy');
        Route::post('/addresses/set_default/{id}', 'set_default')->name('addresses.set_default');
    });

    // Money Withdraw Requests
    Route::controller(SellerWithdrawRequestController::class)->group(function () {
        Route::get('/money-withdraw-requests', 'index')->name('money_withdraw_requests.index');
        Route::post('/money-withdraw-request/store', 'store')->name('money_withdraw_request.store');
    });

    // Commission History
    Route::controller(CommissionHistoryController::class)->group(function () {
        Route::get('/commission-history', 'index')->name('commission-history.index');
    });

    //Conversations
    Route::controller(ConversationController::class)->group(function () {
        Route::get('/conversations', 'index')->name('conversations.index');
        Route::get('/conversations/show/{id}', 'show')->name('conversations.show');
        Route::post('conversations/refresh', 'refresh')->name('conversations.refresh');
        Route::post('conversations/message/store', 'message_store')->name('conversations.message_store');
    });


    // product query (comments) show on seller panel
    Route::controller(ProductQueryController::class)->group(function () {
        Route::get('/product-queries', 'index')->name('product_query.index');
        Route::get('/product-queries/{id}', 'show')->name('product_query.show');
        Route::put('/product-queries/{id}', 'reply')->name('product_query.reply');
    });

    // Support Ticket
    Route::controller(SupportTicketController::class)->group(function () {
        Route::get('/support_ticket', 'index')->name('support_ticket.index');
        Route::post('/support_ticket/store', 'store')->name('support_ticket.store');
        Route::get('/support_ticket/show/{id}', 'show')->name('support_ticket.show');
        Route::post('/support_ticket/reply', 'ticket_reply_store')->name('support_ticket.reply_store');
    });

    // Notifications
    Route::controller(NotificationController::class)->group(function () {
        Route::get('/all-notification', 'index')->name('all-notification');
        Route::post('/notifications/bulk-delete', 'bulkDelete')->name('notifications.bulk_delete');
        Route::get('/notification/read-and-redirect/{id}', 'readAndRedirect')->name('notification.read-and-redirect');

    });

    Route::controller(GSTController::class)->group(function () {
        Route::get('/gst-configuration', 'configure_index')->name('gst.configconfiguration');
        Route::post('/gst-docs-update', 'update_documents')->name('update_gst_docs');
        Route::get('/product-hsn-gst-assign', 'hsn_gst_assign')->name('products.hsn-gst.assigns');
        Route::get('/wholesale-product-hsn-gst-assign', 'wholesale_hsn_gst_assign')->name('products.wholesale-hsn-gst.assigns');
        Route::get('/auction-product-hsn-gst-assign', 'auction_hsn_gst_assign')->name('products.auction-hsn-gst.assigns');
        Route::get('/preorder-product-hsn-gst-assign', 'preorder_hsn_gst_assign')->name('products.preorder-hsn-gst.assigns');
        Route::post('/products-hsn-gst-single-update', 'updateHsnGstRate')->name('products.single-hsn-gst.update');
        Route::post('/bulk-product-gst-assign', 'updateBulkHsnGstRate')->name('products.bulk-product-gst-assign');
        Route::get('/products/gst/products/{type}', 'get_filter_products')->name('products.gst.filter');
    });

});

