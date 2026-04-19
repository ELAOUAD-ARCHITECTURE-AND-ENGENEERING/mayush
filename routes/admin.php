<?php

use App\Http\Controllers\AddonController;
use App\Http\Controllers\AdminController;
use App\Http\Controllers\AizUploadController;
use App\Http\Controllers\AttributeController;
use App\Http\Controllers\BlogCategoryController;
use App\Http\Controllers\BlogController;
use App\Http\Controllers\BrandController;
use App\Http\Controllers\BrandBulkUploadController;
use App\Http\Controllers\BusinessSettingsController;
use App\Http\Controllers\CarrierController;
use App\Http\Controllers\CategoryController;
use App\Http\Controllers\CityController;
use App\Http\Controllers\CommissionController;
use App\Http\Controllers\ConversationController;
use App\Http\Controllers\CountryController;
use App\Http\Controllers\CouponController;
use App\Http\Controllers\CurrencyController;
use App\Http\Controllers\CustomerController;
use App\Http\Controllers\CustomerPackageController;
use App\Http\Controllers\CustomerProductController;
use App\Http\Controllers\DigitalProductController;
use App\Http\Controllers\FlashDealController;
use App\Http\Controllers\LanguageController;
use App\Http\Controllers\NewsletterController;
use App\Http\Controllers\NotificationController;
use App\Http\Controllers\OrderController;
use App\Http\Controllers\PageController;
use App\Http\Controllers\PaymentController;
use App\Http\Controllers\PickupPointController;
use App\Http\Controllers\PickupController;
use App\Http\Controllers\ShippingBoxSizeController;
use App\Http\Controllers\ProductBulkUploadController;
use App\Http\Controllers\ProductController;
use App\Http\Controllers\ProductQueryController;
use App\Http\Controllers\ProfileController;
use App\Http\Controllers\ReportController;
use App\Http\Controllers\ReviewController;
use App\Http\Controllers\RoleController;
use App\Http\Controllers\SellerController;
use App\Http\Controllers\SellerWithdrawRequestController;
use App\Http\Controllers\Backend\EliteSubscriptionController;
use App\Http\Controllers\StaffController;
use App\Http\Controllers\StateController;
use App\Http\Controllers\SubscriberController;
use App\Http\Controllers\SupportTicketController;
use App\Http\Controllers\TaxController;
use App\Http\Controllers\UpdateController;
use App\Http\Controllers\WebsiteController;
use App\Http\Controllers\ZoneController;
use App\Http\Controllers\SizeChartController;
use App\Http\Controllers\MeasurementPointsController;
use App\Http\Controllers\CustomSaleAlertController;
use App\Http\Controllers\WarrantyController;
use App\Http\Controllers\DynamicPopupController;
use App\Http\Controllers\CustomAlertController;
use App\Http\Controllers\ContactController;
use App\Http\Controllers\Admin\Report\EarningReportController;
use App\Http\Controllers\NoteController;
use App\Http\Controllers\NotificationTypeController;
use App\Http\Controllers\AreaController;
use App\Http\Controllers\ElementController;
use App\Http\Controllers\CustomLabelController;
use App\Http\Controllers\ShippingSystemController;
use App\Http\Controllers\EmailTemplateController;
use App\Http\Controllers\TopBannerController;
use App\Http\Controllers\PromotionalCategoryController;
use App\Http\Controllers\PosController;

/*
  |--------------------------------------------------------------------------
  | Admin Routes
  |--------------------------------------------------------------------------
  |
  | Here is where you can register admin routes for your application. These
  | routes are loaded by the RouteServiceProvider within a group which
  | contains the "web" middleware group. Now create something great!
  |
 */
//Update Routes
Route::controller(UpdateController::class)->group(function () {
    Route::post('/update', 'step0')->name('update');
    Route::get('/update/step1', 'step1')->name('update.step1');
    Route::get('/update/step2', 'step2')->name('update.step2');
});

Route::get('/admin', [AdminController::class, 'admin_dashboard'])->name('admin.dashboard')->middleware(['auth', 'admin']);

// Technical Analytics Dashboard
Route::get('/admin/technical-analytics', function () {
    return view('backend.analytics.index');
})->name('admin.technical_analytics')->middleware(['auth', 'admin']);

Route::group(['prefix' => 'admin', 'middleware' => ['auth', 'admin', 'prevent-back-history']], function() {
    
    // Technical Analytics Data
    Route::prefix('analytics')->group(function () {
        Route::get('visitor-stats', [App\Http\Controllers\Api\AnalyticsController::class, 'getVisitorStats']);
        Route::get('health-stats', [App\Http\Controllers\Api\AnalyticsController::class, 'getHealthStats']);
        Route::get('cart-stats', [App\Http\Controllers\Api\AnalyticsController::class, 'getCartStats']);
        Route::get('live-locations', [App\Http\Controllers\Api\AnalyticsController::class, 'getLiveLocations']);
        Route::get('visitor-flow', [App\Http\Controllers\Api\AnalyticsController::class, 'getVisitorFlow']);
        Route::get('traffic-sources', [App\Http\Controllers\Api\AnalyticsController::class, 'getTrafficSources']);
        Route::get('page-performance', [App\Http\Controllers\Api\AnalyticsController::class, 'getPagePerformance']);
        Route::get('behavior-flow', [App\Http\Controllers\Api\AnalyticsController::class, 'getBehaviorFlow']);
        Route::get('interaction-heatmap', [App\Http\Controllers\Api\AnalyticsController::class, 'getInteractionHeatmap']);
        Route::get('export-csv', [App\Http\Controllers\Api\AnalyticsController::class, 'exportToCsv']);
        Route::get('automated-insights', [App\Http\Controllers\Api\AnalyticsController::class, 'getAutomatedInsights']);
        Route::get('forecasting', [App\Http\Controllers\Api\AnalyticsController::class, 'getForecastingData']);
        Route::get('top-vendors', [App\Http\Controllers\Api\AnalyticsController::class, 'getTopVendors']);
        Route::get('system-health', [App\Http\Controllers\Api\AnalyticsController::class, 'getSystemStatus']);
        Route::get('currency-config', [App\Http\Controllers\Api\AnalyticsController::class, 'getCurrencyConfig']);
        Route::get('vendor-analytics', [App\Http\Controllers\Api\AnalyticsController::class, 'getVendorAnalytics']);
        Route::get('finance-analytics', [App\Http\Controllers\Api\AnalyticsController::class, 'getFinanceAnalytics']);
        Route::get('marketing-analytics', [App\Http\Controllers\Api\AnalyticsController::class, 'getMarketingAnalytics']);
        Route::get('security-metrics', [App\Http\Controllers\Api\AnalyticsController::class, 'getSecurityMetrics'])->name('api.analytics.security_metrics');
    });

    // Security Dashboard
    Route::get('/security-dashboard', function () {
        return view('backend.security.index');
    })->name('admin.security_dashboard');

    // Dashboard
    Route::controller(AdminController::class)->group(function () {
        Route::post('/dashboard/top-category-products-section', 'top_category_products_section')->name('dashboard.top_category_products_section');
        Route::post('/dashboard/inhouse-top-brands', 'inhouse_top_brands')->name('dashboard.inhouse_top_brands');
        Route::post('/dashboard/inhouse-top-categories', 'inhouse_top_categories')->name('dashboard.inhouse_top_categories');
        Route::post('/dashboard/top-sellers-products-section', 'top_sellers_products_section')->name('dashboard.top_sellers_products_section');
        Route::post('/dashboard/top-brands-products-section', 'top_brands_products_section')->name('dashboard.top_brands_products_section');
    });

    // Task Dashboard
    Route::get('/task-dashboard', [App\Http\Controllers\Admin\TaskDashboardController::class, 'index'])->name('admin.task_dashboard');

    // category
    Route::resource('categories', CategoryController::class)->except('destroy');
    Route::controller(CategoryController::class)->group(function () {
        // Route::get('/categories/edit/{id}', 'edit')->name('categories.edit');
        Route::get('/categories/destroy/{id}', 'destroy')->name('categories.destroy');
        Route::post('/categories/featured', 'updateFeatured')->name('categories.featured');
        Route::get('/categories_wise_product_discount', 'categoriesWiseProductDiscount')->name('categories_wise_product_discount');
        Route::get('/categories_wise_commission', 'categoriesWiseCommission')->name('categories_wise_commission');
        Route::post('/categories_wise_commission_update', 'categoriesWiseCommissionUpdate')->name('categories_wise_commission.update');
        Route::post('/bulk-categories-delete', 'bulk_category_delete')->name('bulk-categories-delete');
        Route::post('/bulk-categories-featured', 'bulk_categories_featured')->name('bulk-categories-featured');
        Route::post('/bulk-categories-hot', 'bulk_categories_hot')->name('bulk-categories-hot');
        Route::get('/categories-filter', 'categories_filter')->name('categories.filter');
        Route::get('/categories-details', 'categories_details')->name('categories.details');
        Route::get('/categories-by-type', 'categories_by_type')->name('categories.categories-by-type');
        Route::get('/categories/hot', 'hot')->name('categories.hot');
    });
    
    // Brand
    Route::resource('brands', BrandController::class)->except('destroy');
    Route::controller(BrandController::class)->group(function () {
        // Route::get('/brands/edit/{id}', 'edit')->name('brands.edit');
        Route::get('/brands/destroy/{id}', 'destroy')->name('brands.destroy');
        Route::get('/brands-filter', 'get_brands_by_filter')->name('brands.filter');
        Route::post('/bulk-brands-delete', 'bulk_brands_delete')->name('bulk-brands-delete');
        Route::get('/brand-category-show', 'brand_category_show')->name('brand_category.show');
    });

    Route::controller(BrandBulkUploadController::class)->group(function () {
        Route::get('/brand-bulk-upload', 'index')->name('brand_bulk_upload.index');
        Route::post('/brand-bulk-upload/upload', 'bulk_upload')->name('brand_bulk_upload.upload');
    });

    // Products
    Route::controller(ProductController::class)->group(function () {
        Route::get('/products/admin', 'admin_products')->name('products.admin');
        Route::get('/products/seller', 'seller_products')->name('products.seller');
        Route::get('/products/all', 'all_products')->name('products.all');
        Route::get('/products/create', 'create')->name('products.create');
        Route::post('/products/store/', 'store')->name('products.store');
        Route::get('/products/admin/{id}/edit', 'admin_product_edit')->name('products.admin.edit');
        Route::get('/products/seller/{id}/edit', 'seller_product_edit')->name('products.seller.edit');
        Route::post('/products/update/{product}', 'update')->name('products.update');
        Route::post('/products/todays_deal', 'updateTodaysDeal')->name('products.todays_deal');
        Route::post('/products/featured', 'updateFeatured')->name('products.featured');
        Route::post('/products/published', 'updatePublished')->name('products.published');
        Route::post('/products/approved', 'updateProductApproval')->name('products.approved');
        Route::post('/products/get_products_by_subcategory', 'get_products_by_subcategory')->name('products.get_products_by_subcategory');
        Route::get('/products/duplicate/{id}', 'duplicate')->name('products.duplicate');
        Route::get('/products/destroy/{id}', 'destroy')->name('products.destroy');
        Route::post('/bulk-product-delete', 'bulk_product_delete')->name('bulk-product-delete');
    
        Route::post('/products/sku_combination', 'sku_combination')->name('products.sku_combination');
        Route::post('/products/sku_combination_edit', 'sku_combination_edit')->name('products.sku_combination_edit');
        Route::post('/products/add-more-choice-option', 'add_more_choice_option')->name('products.add-more-choice-option');
        Route::post('/products/store-attribute-value-ajax', 'store_attribute_value_ajax')->name('products.store-attribute-value-ajax');
        Route::post('/set_product_discount', 'setProductDiscount')->name('set_product_discount');
        Route::get('/products/smart-bar', 'smartBar')->name('smart.bar');
        
        Route::get('/products-filter', 'get_filter_products')->name('products.filter');
        Route::get('/products-search', 'search')->name('products.search');
        Route::post('/products-store-as-draft', 'store_as_draft')->name('products.store_as_draft');
        Route::post('/bulk-product-publish', 'bulk_product_publish')->name('bulk-product-publish');
        Route::post('/bulk-product-featured', 'bulk_product_featured')->name('bulk-product-featured');
        Route::post('/bulk-product-todays-deal', 'bulk_product_todays_deal')->name('bulk-product-todays-deal');
        Route::get('/get-selected-products', 'get_selected_products')->name('get-selected-products');
        Route::post('/bulk-product-stock-update', 'bulk_product_stock_update')->name('bulk-product-stock-update');
        Route::get('/get-custom-review-product-by-category', 'get_custom_review_product_by_category')->name('get-custom-review-product-by-category');
        Route::get('/products-reviews', 'reviews')->name('products.reviews');
    });

    // Digital Product
    Route::resource('digitalproducts', DigitalProductController::class)->except('destroy');
    Route::controller(DigitalProductController::class)->group(function () {
        // Route::get('/digitalproducts/edit/{id}', 'edit')->name('digitalproducts.edit');
        Route::get('/digitalproducts/destroy/{id}', 'destroy')->name('digitalproducts.destroy');
        Route::get('/digitalproducts/download/{id}', 'download')->name('digitalproducts.download');
    });

    Route::controller(ProductBulkUploadController::class)->group(function () {
        //Product Export
        Route::get('/product-bulk-export', 'export')->name('product_bulk_export.index');
        
        //Product Bulk Upload
        Route::get('/product-bulk-upload/index', 'index')->name('product_bulk_upload.index');
        Route::post('/bulk-product-upload', 'bulk_upload')->name('bulk_product_upload');
        Route::get('/product-csv-download/{type}', 'import_product')->name('product_csv.download');
        Route::get('/vendor-product-csv-download/{id}', 'import_vendor_product')->name('import_vendor_product.download');
        Route::group(['prefix' => 'bulk-upload/download'], function() {
            Route::get('/category', 'pdf_download_category')->name('pdf.download_category');
            Route::get('/brand', 'pdf_download_brand')->name('pdf.download_brand');
            Route::get('/seller', 'pdf_download_seller')->name('pdf.download_seller');
        });
    });
    
    // Seller
    Route::controller(SellerController::class)->group(function () {
        Route::get('sellers_ban/{id}', 'ban')->name('sellers.ban');
        Route::get('/sellers/destroy/{id}', 'destroy')->name('sellers.destroy');
        Route::post('/bulk-seller-delete', 'bulk_seller_delete')->name('bulk-seller-delete');
        Route::get('/sellers/view/{id}/verification', 'show_verification_request')->name('sellers.show_verification_request');
        Route::get('/sellers/approve/{id}', 'approve_seller')->name('sellers.approve');
        Route::get('/sellers/reject/{id}', 'reject_seller')->name('sellers.reject');
        Route::get('/sellers/login/{id}', 'login')->name('sellers.login');
        Route::post('/sellers/payment_modal', 'payment_modal')->name('sellers.payment_modal');
        Route::post('/sellers/profile_modal', 'profile_modal')->name('sellers.profile_modal');
        Route::post('/sellers/approved', 'updateApproved')->name('sellers.approved');
        Route::get('/sellers/registration-pending', 'pendingSellers')->name('sellers.registration_pending');
        Route::post('/sellers/registration-approved', 'UpdateSellerRegistration')->name('sellers.registration.approved');
        Route::get('/seller_based_commission', 'sellerBasedCommission')->name('seller_based_commission');
        Route::post('/set_seller_commission', 'setSellerBasedCommission')->name('set_seller_commission');
        Route::get('/edit_Seller_custom_followers', 'editSellerCustomFollowers')->name('edit_Seller_custom_followers');
        Route::get('/sellers/profile/{id}', 'sellerProfile')->name('sellers.profile');
        Route::get('/sellers/profile-tab/{shop}', 'getSellerProfileTab')->name('sellers.profile.tab');
        Route::post('/sellers/verification-info-modal', 'verification_info_modal')->name('sellers.verification_info_modal');
        Route::get('/sellers/rating-followers', 'index')->name('sellers.rating_followers');
        Route::get('/sellers/resend-verification/{id}', 'resendVerification')->name('sellers.email_verification_resend');
    });
    Route::resource('sellers', SellerController::class)->except('destroy');

    // Elite Artisans
    Route::controller(EliteSubscriptionController::class)->group(function () {
        Route::get('elite-subscriptions', 'index')->name('elite.index');
        Route::post('elite-subscriptions/{id}/approve', 'approve')->name('elite.approve');
        Route::post('elite-subscriptions/{id}/reject', 'reject')->name('elite.reject');
        Route::post('elite-subscriptions/{id}/revoke', 'revoke')->name('elite.revoke');
        Route::post('elite-settings', 'updateSettings')->name('elite.settings');
    });

    // Seller Payment
    Route::controller(PaymentController::class)->group(function () {
        Route::get('/seller/payments', 'payment_histories')->name('sellers.payment_histories');
        Route::get('/seller/payments/show/{id}', 'show')->name('sellers.payment_history');
    });

    // Seller Withdraw Request
    Route::resource('/withdraw_requests', SellerWithdrawRequestController::class);
    Route::controller(SellerWithdrawRequestController::class)->group(function () {
        Route::get('/withdraw_requests_all', 'index')->name('withdraw_requests_all');
        Route::post('/withdraw_request/payment_modal', 'payment_modal')->name('withdraw_request.payment_modal');
        Route::post('/withdraw_request/message_modal', 'message_modal')->name('withdraw_request.message_modal');
        Route::post('/withdraw_request/pay', 'update')->name('withdraw_request.pay');
    });

    // Customer
    Route::controller(CustomerController::class)->group(function () {
        Route::get('customers_ban/{customer}', 'ban')->name('customers.ban');
        Route::get('/customers/login/{id}', 'login')->name('customers.login');
        Route::get('/customers/destroy/{id}', 'destroy')->name('customers.destroy');
        Route::post('/bulk-customer-delete', 'bulk_customer_delete')->name('bulk-customer-delete');
        Route::get('/customers/unverified', 'unverifiedCustomers')->name('customers.unverified.index');
        Route::get('/customers/suspicious/{id}', 'suspicious')->name('customers.suspicious');
    });
    Route::resource('customers', CustomerController::class)->except('destroy');

    // Newsletter
    Route::controller(NewsletterController::class)->group(function () {
        Route::get('/newsletter', 'index')->name('newsletters.index');
        Route::post('/newsletter/send', 'send')->name('newsletters.send');
        Route::post('/newsletter/test/smtp', 'testEmail')->name('test.smtp');
    });

    // Email Templates
    Route::controller(EmailTemplateController::class)->group(function () {
        Route::get('/email-templates/{emailReceiver}', 'index')->name('email-templates.index');
        Route::get('/email-templates/edit/{id}', 'edit')->name('email-templates.edit');
        Route::patch('/email-templates/update/{id}', 'update')->name('email-templates.update');
        Route::post('/email-templates/update-status', 'updateStatus')->name('email-template.update-status');
    });

    Route::resource('profile', ProfileController::class);

    // Business Settings
    Route::controller(BusinessSettingsController::class)->group(function () {
        Route::post('/business-settings/update', 'update')->name('business_settings.update');
        Route::post('/business-settings/update/activation', 'updateActivationSettings')->name('business_settings.update.activation');
        Route::get('/general-setting', 'general_setting')->name('general_setting.index');
        Route::get('/activation', 'activation')->name('activation.index');
        Route::get('/payment-method', 'payment_method')->name('payment_method.index');
        Route::get('/file_system', 'file_system')->name('file_system.index');
        Route::get('/social-login', 'social_login')->name('social_login.index');
        Route::get('/smtp-settings', 'smtp_settings')->name('smtp_settings.index');
        Route::get('/google-analytics', 'google_analytics')->name('google_analytics.index');
        Route::get('/google-recaptcha', 'google_recaptcha')->name('google_recaptcha.index');
        Route::get('/cloudflare-turnstile', 'cloudflare_turnstile')->name('cloudflare_turnstile.index');
        Route::get('/google-map', 'google_map')->name('google-map.index');
        Route::get('/google-firebase', 'google_firebase')->name('google-firebase.index');

        //Facebook Settings
        Route::get('/facebook-chat', 'facebook_chat')->name('facebook_chat.index');
        Route::post('/facebook_chat', 'facebook_chat_update')->name('facebook_chat.update');
        Route::get('/facebook-comment', 'facebook_comment')->name('facebook-comment');
        Route::post('/facebook-comment', 'facebook_comment_update')->name('facebook-comment.update');
        Route::post('/facebook_pixel', 'facebook_pixel_update')->name('facebook_pixel.update');

        Route::post('/env_key_update', 'env_key_update')->name('env_key_update.update');
        Route::post('/payment_method_update', 'payment_method_update')->name('payment_method.update');
        Route::post('/google_analytics', 'google_analytics_update')->name('google_analytics.update');
        Route::post('/google_recaptcha', 'google_recaptcha_update')->name('google_recaptcha.update');
        Route::post('/cloudflare_turnstile', 'cloudflare_turnstile_update')->name('cloudflare_turnstile.update');
        Route::post('/google-map', 'google_map_update')->name('google-map.update');
        Route::post('/google-firebase', 'google_firebase_update')->name('google-firebase.update');

        Route::get('/whatsapp-chat', 'whatsappChat')->name('whatsapp_chat.index');
        Route::post('/whatsapp-chat-update', 'whatsappChatUpdate')->name('whatsapp_chat.update');
        Route::post('/smart-bar-status', 'smart_bar_status')->name('business_settings.smart_bar_status');

        Route::get('/verification/form', 'seller_verification_form')->name('seller_verification_form.index');
        Route::post('/verification/form', 'seller_verification_form_update')->name('seller_verification_form.update');
        Route::get('/vendor_commission', 'vendor_commission')->name('business_settings.vendor_commission');
        Route::post('/vendor_commission_update', 'vendor_commission_update')->name('business_settings.vendor_commission.update');

        //Shipping Configuration
        Route::get('/shipping_configuration', 'shipping_configuration')->name('shipping_configuration.index');
        Route::get('/shipping_method', 'shipping_method')->name('shipping_configuration.shipping_method');
        Route::post('/shipping_configuration/update', 'shipping_configuration_update')->name('shipping_configuration.update');

        // Order Configuration
        Route::get('/order-configuration', 'order_configuration')->name('order_configuration.index');

        // Business Settings
        Route::get('/business-settings/index', 'business_settings')->name('business_settings.index');
        Route::get('/business-settings/select-font-family', 'select_font_family')->name('website.select-font-family');
        Route::post('/business-settings/business-info-update', 'business_info_update')->name('business_info.update');
        Route::get('/business-settings/custom-product-visitors', 'customProductVisitorsUpdate')->name('custom_product_visitors');
        Route::post('/business-settings/custom-product-visitors-update', 'customProductVisitorsUpdate')->name('custom_product_visitors.update');
    });


    //Currency
    Route::controller(CurrencyController::class)->group(function () {
        Route::get('/currency', 'currency')->name('currency.index');
        Route::post('/currency/update', 'updateCurrency')->name('currency.update');
        Route::post('/your-currency/update', 'updateYourCurrency')->name('your_currency.update');
        Route::get('/currency/create', 'create')->name('currency.create');
        Route::post('/currency/store', 'store')->name('currency.store');
        Route::post('/currency/currency_edit', 'edit')->name('currency.edit');
        Route::post('/currency/update_status', 'update_status')->name('currency.update_status');
    });
    
    //Tax
    Route::resource('tax', TaxController::class)->except('destroy');
    Route::controller(TaxController::class)->group(function () {
        // Route::get('/tax/edit/{id}', 'edit')->name('tax.edit');
        Route::get('/tax/destroy/{id}', 'destroy')->name('tax.destroy');
        Route::post('tax-status', 'change_tax_status')->name('taxes.tax-status');
    });
    
    // Language
    Route::resource('/languages', LanguageController::class)->except('destroy');
    Route::controller(LanguageController::class)->group(function () {
        // Route::post('/languages/{id}/update', 'update')->name('languages.update');
        Route::get('/languages/destroy/{id}', 'destroy')->name('languages.destroy');
        Route::post('/languages/update_rtl_status', 'update_rtl_status')->name('languages.update_rtl_status');
        Route::post('/languages/update-status', 'update_status')->name('languages.update-status');
        Route::post('/languages/key_value_store', 'key_value_store')->name('languages.key_value_store');

        //App Trasnlation
        Route::post('/languages/app-translations/import', 'importEnglishFile')->name('app-translations.import');
        Route::get('/languages/app-translations/show/{id}', 'showAppTranlsationView')->name('app-translations.show');
        Route::post('/languages/app-translations/key_value_store', 'storeAppTranlsation')->name('app-translations.store');
        Route::get('/languages/app-translations/export/{id}', 'exportARBFile')->name('app-translations.export');
    });
    

    // website setting
    Route::group(['prefix' => 'website'], function() {
        Route::controller(WebsiteController::class)->group(function () {
            Route::get('/footer', 'footer')->name('website.footer');
            Route::get('/header', 'header')->name('website.header');
            Route::get('/appearance', 'appearance')->name('website.appearance');
            Route::get('/pages', 'pages')->name('website.pages');
            Route::get('/select-homepage', 'select_homepage')->name('website.select-homepage');
            Route::get('/select-header', 'select_header')->name('website.select-header');
            Route::get('/portfolio-header', 'portfolio_header')->name('website.portfolioheader');
            Route::get('/authentication-layout-settings', 'authentication_layout_settings')->name('website.authentication-layout-settings');
        });

        // Custom Page
        Route::resource('custom-pages', PageController::class)->parameters(['custom-pages' => 'id'])->except('destroy');
        Route::controller(PageController::class)->group(function () {
            // Route::get('/custom-pages/edit/{id}', 'edit')->name('custom-pages.edit');
            Route::get('/custom-pages/destroy/{id}', 'destroy')->name('custom-pages.destroy');
        });
    });

    // Staff Roles
    Route::resource('roles', RoleController::class)->parameters(['roles' => 'id'])->except('destroy');
    Route::controller(RoleController::class)->group(function () {
        // Route::get('/roles/edit/{id}', 'edit')->name('roles.edit');
        Route::get('/roles/destroy/{id}', 'destroy')->name('roles.destroy');

        // Add Permissiom
        Route::post('/roles/add_permission', 'add_permission')->name('roles.permission');
    });
    
    // Staff
    Route::resource('staffs', StaffController::class)->parameters(['staffs' => 'id'])->except('destroy');
    Route::get('/staffs/destroy/{id}', [StaffController::class, 'destroy'])->name('staffs.destroy');

    // Flash Deal
    Route::resource('flash_deals', FlashDealController::class)->except('destroy');
    Route::controller(FlashDealController::class)->group(function () {
        // Route::get('/flash_deals/edit/{id}', 'edit')->name('flash_deals.edit');
        Route::get('/flash_deals/destroy/{id}', 'destroy')->name('flash_deals.destroy');
        Route::post('/flash_deals/update_status', 'update_status')->name('flash_deals.update_status');
        Route::post('/flash_deals/update_featured', 'update_featured')->name('flash_deals.update_featured');
        Route::post('/flash_deals/product_discount', 'product_discount')->name('flash_deals.product_discount');
        Route::post('/flash_deals/product_discount_edit', 'product_discount_edit')->name('flash_deals.product_discount_edit');
    });
    
    //Subscribers
    Route::controller(SubscriberController::class)->group(function () {
        Route::get('/subscribers', 'index')->name('subscribers.index');
        Route::get('/subscribers/destroy/{id}', 'destroy')->name('subscriber.destroy');
    });
    
    // Order
    Route::resource('orders', OrderController::class)->except('destroy');
    Route::controller(OrderController::class)->group(function () {
        // All Orders
        Route::get('/all_orders', 'all_orders')->name('all_orders.index');
        Route::get('/inhouse-orders', 'all_orders')->name('inhouse_orders.index');
        Route::get('/seller_orders', 'all_orders')->name('seller_orders.index');
        Route::get('orders_by_pickup_point', 'all_orders')->name('pick_up_point.index');
        
        Route::get('/orders/{id}/show', 'show')->name('all_orders.show');
        Route::get('/inhouse-orders/{id}/show', 'show')->name('inhouse_orders.show');
        Route::get('/seller_orders/{id}/show', 'show')->name('seller_orders.show');
        Route::get('/orders_by_pickup_point/{id}/show', 'show')->name('pick_up_point.order_show');

        Route::post('/bulk-order-status', 'bulk_order_status')->name('bulk-order-status');

        Route::get('/orders/destroy/{id}', 'destroy')->name('orders.destroy');
        Route::post('/bulk-order-delete', 'bulk_order_delete')->name('bulk-order-delete');

        Route::post('/orders/details', 'order_details')->name('orders.details');
        Route::post('/orders/update_delivery_status', 'update_delivery_status')->name('orders.update_delivery_status');
        Route::post('/orders/update_payment_status', 'update_payment_status')->name('orders.update_payment_status');
        Route::post('/orders/update_tracking_code', 'update_tracking_code')->name('orders.update_tracking_code');

        //Delivery Boy Assign
        Route::post('/orders/delivery-boy-assign', 'assign_delivery_boy')->name('orders.delivery-boy-assign');

        Route::get('/unpaid-orders', 'all_orders')->name('unpaid_orders.index');
        Route::post('/unpaid-order-payment-notification', 'unpaid_order_payment_notification_send')->name('unpaid_order_payment_notification');
        Route::get('/order-bulk-export', 'orderBulkExport')->name('order-bulk-export');
    });
    
    Route::post('/pay_to_seller', [CommissionController::class, 'pay_to_seller'])->name('commissions.pay_to_seller');

    //Reports
    Route::controller(ReportController::class)->group(function () {
        Route::get('/in_house_sale_report', 'in_house_sale_report')->name('in_house_sale_report.index');
        Route::get('/seller_sale_report', 'seller_sale_report')->name('seller_sale_report.index');
        Route::get('/stock_report', 'stock_report')->name('stock_report.index');
        Route::get('/wish_report', 'wish_report')->name('wish_report.index');
        Route::get('/user_search_report', 'user_search_report')->name('user_search_report.index');
        Route::get('/commission-log', 'commission_history')->name('commission-log.index');
        Route::get('/wallet-history', 'wallet_transaction_history')->name('wallet-history.index');
    });

    //Blog Section
    //Blog cateory
    Route::resource('blog-category', BlogCategoryController::class);
    // Route::get('/blog-category/destroy/{id}', [BlogCategoryController::class, 'destroy'])->name('blog-category.destroy');

    // Blog
    Route::resource('blog', BlogController::class);
    Route::controller(BlogController::class)->group(function () {
        // Route::get('/blog/destroy/{id}', 'destroy')->name('blog.destroy');
        Route::post('/blog/change-status', 'change_status')->name('blog.change-status');
        Route::post('/blog/generate-slug', 'generateSlug')->name('generate.slug');
    });

    //Coupons
    Route::resource('coupon', CouponController::class);
    Route::controller(CouponController::class)->group(function () {
        // Route::get('/coupon/destroy/{id}', 'destroy')->name('coupon.destroy');
    
        //Coupon Form
        Route::post('/coupon/get_form', 'get_coupon_form')->name('coupon.get_coupon_form');
        Route::post('/coupon/get_form_edit', 'get_coupon_form_edit')->name('coupon.get_coupon_form_edit');
        Route::post('/coupon/update-status', 'update_status')->name('coupon.update_status');
    });

    // Reviews
    Route::resource('reviews', ReviewController::class, ['as' => 'admin']);
    Route::controller(ReviewController::class)->group(function () {
        Route::post('/reviews/published', 'updatePublished')->name('reviews.published');
    });

    //Support_Ticket
    Route::controller(SupportTicketController::class)->group(function () {
        Route::get('support_ticket/', 'admin_index')->name('support_ticket.admin_index');
        Route::get('support_ticket/{id}/show', 'admin_show')->name('support_ticket.admin_show');
        Route::post('support_ticket/reply', 'admin_store')->name('support_ticket.admin_store');
    });

    //Pickup_Points
    Route::resource('pick_up_points', PickupPointController::class);
    
    //Pickup Address
    Route::resource('pickup_address', PickupController::class);
    Route::post('/pickup_addresses/status', [PickupController::class, 'updateStatus'])->name('pickup_addresses.status');
    Route::post('/pickup_addresses/filter', [PickupController::class, 'filter'])->name('pickup_addresses.filter');
    Route::post('/pickup_addresses/bulk-delete', [PickupController::class, 'bulkDelete'])->name('bulk-pickup-addresses-delete');

    //Shipping Box Size
    Route::resource('shipping_box_size', ShippingBoxSizeController::class);
    Route::post('/shipping_box_sizes/filter', [ShippingBoxSizeController::class, 'filter'])->name('shipping_box_sizes.filter');
    Route::post('/shipping_box_sizes/bulk-delete', [ShippingBoxSizeController::class, 'bulkDelete'])->name('bulk-shipping-box-sizes-delete');

    Route::controller(PickupPointController::class)->group(function () {
        // Route::get('/pick_up_points/edit/{id}', 'edit')->name('pick_up_points.edit');
        // Route::get('/pick_up_points/destroy/{id}', 'destroy')->name('pick_up_points.destroy');
    });

    //conversation of seller customer
    Route::controller(ConversationController::class)->group(function () {
        Route::get('conversations', 'admin_index')->name('conversations.admin_index');
        Route::get('conversations/{id}/show','admin_show')->name('conversations.admin_show');
    });

    // product Queries show on Admin panel
    Route::controller(ProductQueryController::class)->group(function () {
        Route::get('/product-queries', 'index')->name('product_query.index');
        Route::get('/product-queries/{id}', 'show')->name('product_query.show');
        Route::put('/product-queries/{id}', 'reply')->name('product_query.reply');
    });

    // Product Attribute
    Route::resource('attributes', AttributeController::class);
    Route::controller(AttributeController::class)->group(function () {

        //Attribute Value
        Route::post('/store-attribute-value', 'store_attribute_value')->name('store-attribute-value');
        Route::get('/edit-attribute-value/{id}', 'edit_attribute_value')->name('edit-attribute-value');
        Route::post('/update-attribute-value/{id}', 'update_attribute_value')->name('update-attribute-value');
        Route::get('/destroy-attribute-value/{id}', 'destroy_attribute_value')->name('destroy-attribute-value');
    
        //Colors
        Route::get('/colors', 'colors')->name('colors');
        Route::get('/colors/create', 'colors_create')->name('colors.create');
        Route::post('/colors/store', 'store_color')->name('colors.store');
        Route::get('/colors/edit/{id}', 'edit_color')->name('colors.edit');
        Route::post('/colors/update/{id}', 'update_color')->name('colors.update');
        Route::get('/colors/destroy/{id}', 'destroy_color')->name('colors.destroy');
    });

    // Addon
    Route::resource('addons', AddonController::class);
    Route::post('/addons/activation', [AddonController::class, 'activation'])->name('addons.activation');

    //Customer Package
    Route::resource('customer_packages', CustomerPackageController::class);
    Route::controller(CustomerPackageController::class)->group(function () {
        // Route::get('/customer_packages/edit/{id}', 'edit')->name('customer_packages.edit');
        // Route::get('/customer_packages/destroy/{id}', 'destroy')->name('customer_packages.destroy');
    });

    // Phase 4: Loyalty Configuration
    Route::controller(\App\Http\Controllers\LoyaltyController::class)->group(function () {
        Route::get('/loyalty-config', 'adminConfig')->name('admin.loyalty.config');
        Route::post('/loyalty-config/update', 'adminConfigUpdate')->name('admin.loyalty.config.update');
    });

    // Phase 5: Loyalty Points Management
    Route::controller(\App\Http\Controllers\Backend\PointManagementController::class)->group(function () {
        Route::get('/loyalty-points', 'dashboard')->name('admin.loyalty.points.dashboard');
        Route::post('/loyalty-points/bulk', 'bulkAssign')->name('admin.loyalty.points.bulk');
        
        Route::get('/loyalty-points/templates', 'templates')->name('admin.loyalty.points.templates');
        Route::post('/loyalty-points/templates', 'storeTemplate')->name('admin.loyalty.points.templates.store');
        Route::get('/loyalty-points/templates/delete/{id}', 'destroyTemplate')->name('admin.loyalty.points.templates.destroy');
        
        Route::get('/loyalty-points/history', 'history')->name('admin.loyalty.points.history');
        Route::post('/loyalty-points/rollback/{id}', 'rollback')->name('admin.loyalty.points.rollback');
        
        Route::get('/loyalty-points/export', 'csvExport')->name('admin.loyalty.points.export');
        Route::post('/loyalty-points/import', 'csvImport')->name('admin.loyalty.points.import');
    });

    //Classified Products
    Route::controller(CustomerProductController::class)->group(function () {
        Route::get('/classified_products', 'customer_product_index')->name('classified_products');
        Route::post('/classified_products/published', 'updatePublished')->name('classified_products.published');
        Route::get('/classified_products/destroy/{id}', 'destroy_by_admin')->name('classified_products.destroy');
    });

    //Promotions
    Route::controller(App\Http\Controllers\PromotionController::class)->group(function () {
        Route::get('/promotions', 'index')->name('promotions.index');
        Route::post('/promotions/update_status', 'update_status')->name('promotions.update_status');
    });

    // Affiliate
    Route::controller(\App\Http\Controllers\AffiliateController::class)->group(function () {
        Route::get('/affiliate-config', 'configuration')->name('admin.affiliate.configuration');
        Route::post('/affiliate-config/update', 'updateSettings')->name('affiliate.config.update');
        Route::get('/affiliate-users', 'users')->name('admin.affiliate.users');
        Route::get('/affiliate-users/approve/{id}', 'approve')->name('admin.affiliate.users.approve');
    });



    // Countries
    Route::resource('countries', CountryController::class);
    Route::post('/countries/status', [CountryController::class, 'updateStatus'])->name('countries.status');

    // States
    Route::resource('states', StateController::class);
	Route::post('/states/status', [StateController::class, 'updateStatus'])->name('states.status');

    // Carriers
    Route::resource('carriers', CarrierController::class);
    Route::controller(CarrierController::class)->group(function () {
        // Route::get('/carriers/destroy/{id}', 'destroy')->name('carriers.destroy');
        Route::post('/carriers/update_status', 'updateStatus')->name('carriers.update_status');
    });


    // Zones
    Route::resource('zones', ZoneController::class);
    // Route::get('/zones/destroy/{id}', [ZoneController::class, 'destroy'])->name('zones.destroy');

    Route::resource('cities', CityController::class);
    Route::controller(CityController::class)->group(function () {
        // Route::get('/cities/edit/{id}', 'edit')->name('cities.edit');
        // Route::get('/cities/destroy/{id}', 'destroy')->name('cities.destroy');
        Route::post('/cities/status', 'updateStatus')->name('cities.status');
    });

    Route::view('/system/update', 'backend.system.update')->name('system_update');
    Route::view('/system/server-status', 'backend.system.server_status')->name('system_server');

    // uploaded files
    Route::resource('/uploaded-files', AizUploadController::class);
    Route::controller(AizUploadController::class)->group(function () {
        Route::any('/uploaded-files/file-info', 'file_info')->name('uploaded-files.info');
        Route::post('/bulk-uploaded-files-delete', 'bulk_uploaded_files_delete')->name('bulk-uploaded-files-delete');
        // Route::get('/uploaded-files/destroy/{id}', 'destroy')->name('uploaded-files.destroy');
        Route::get('/all-file', 'all_file');
    });
    
    Route::get('/all-notification', [NotificationController::class, 'index'])->name('admin.all-notification');
    Route::get('/all-notifications', [NotificationController::class, 'adminIndex'])->name('admin.all-notifications');
    Route::get('/notification-settings', [NotificationController::class, 'notificationSettings'])->name('notification.settings');
    Route::get('/custom-notification', [NotificationController::class, 'customNotification'])->name('custom_notification');
    Route::post('/custom-notification-send', [NotificationController::class, 'sendCustomNotification'])->name('send_custom_notification');

    Route::get('/earning-payout-report', [EarningReportController::class, 'index'])->name('earning_payout_report.index');

    Route::resource('size-charts', SizeChartController::class);
    Route::resource('measurement-points', MeasurementPointsController::class);
    Route::resource('warranties', WarrantyController::class);
    Route::resource('dynamic-popups', DynamicPopupController::class);
    Route::controller(DynamicPopupController::class)->group(function () {
        Route::post('/dynamic-popups/update-status', 'update_status')->name('dynamic-popups.update-status');
        Route::post('/dynamic-popups/bulk-delete', 'bulk_dynamic_popup_delete')->name('bulk-dynamic-popup-delete');
    });

    Route::resource('top_banner', TopBannerController::class);
    Route::controller(TopBannerController::class)->group(function () {
        Route::get('/top-banner/setting', 'setting')->name('top_banner.setting');
        Route::get('/top-banner/delete/{id}', 'destroy')->name('top_banner.delete');
        Route::post('/top-banner/update-status', 'update_status')->name('top-banner.update-status');
    });

    // Removed duplicate: custom-sale-alerts resource was duplicating custom-alerts resource below
    Route::resource('note', NoteController::class);
    Route::controller(NoteController::class)->group(function () {
        Route::post('/note/get-single-note', 'get_single_note')->name('get-single-note');
        Route::post('/note/delete', 'destroy')->name('note.delete');
        Route::post('/note/update-seller-access', 'update_seller_access')->name('note.update-seller-access');
        Route::get('/note/get-notes', 'get_notes')->name('get_notes');
    });

    Route::resource('contacts', ContactController::class);
    Route::controller(ContactController::class)->group(function () {
        Route::get('/contact', 'index')->name('contact');
        Route::get('/contact/query-modal', 'query_modal')->name('contact.query_modal');
        Route::get('/contact/reply-modal', 'reply_modal')->name('contact.reply_modal');
        Route::post('/contact/reply', 'reply')->name('contact.reply');
    });

    Route::resource('notification-type', NotificationTypeController::class);
    Route::controller(NotificationTypeController::class)->group(function () {
        Route::post('/notification-type/get-default-text', 'get_default_text')->name('notification_type.get_default_text');
        Route::post('/notification-type/update-status', 'update_status')->name('notification-type.update-status');
        Route::post('/notification-type/bulk-delete', 'bulk_delete')->name('notifications-type.bulk_delete');
    });
    
    Route::resource('custom-alerts', CustomAlertController::class);
    Route::controller(CustomAlertController::class)->group(function () {
        Route::post('/custom-alerts/update-status', 'update_status')->name('custom-alerts.update-status');
        Route::post('/bulk-custom-alerts-delete', 'bulk_custom_alerts_delete')->name('bulk-custom-alerts-delete');
        Route::get('/custom-sale-alert/edit', 'sale_alert_edit')->name('custom-sale-alert.edit');
    });

    Route::resource('custom-sale-alerts', CustomSaleAlertController::class);
    Route::controller(App\Http\Controllers\CustomSaleAlertController::class)->group(function () {
        Route::get('/custom-sale-alerts/products', 'products')->name('custom_sale_alerts.products');
        Route::post('/custom-sale-alerts/product-update', 'product_update')->name('custom-sale-alerts.product_update');
    });

    Route::resource('areas', AreaController::class);
    Route::resource('elements', ElementController::class);
    
    Route::resource('custom_label', CustomLabelController::class);
    Route::controller(CustomLabelController::class)->group(function () {
        Route::get('/custom-label/products', 'custom_label_products')->name('custom_label.products');
        Route::post('/custom-label/update-status', 'update_status')->name('custom-label.update-status');
        Route::get('/custom-label/delete/{id}', 'destroy')->name('custom_label.delete');
    });

    // Shipping System
    Route::controller(ShippingSystemController::class)->group(function () {
        Route::get('/shiprocket-configuration', 'shiprocket_configuration')->name('shiprocket_configuration');
        Route::get('/steadfast-configuration', 'steadfast_configuration')->name('steadfast_configuration');
        Route::get('/pathao-configuration', 'pathao_configuration')->name('pathao_configuration');
    });
    
    Route::get('/notification/read-and-redirect/{id}', [NotificationController::class, 'readAndRedirect'])->name('notification.read-and-redirect');
    Route::get('/admin/notification/read-and-redirect/{id}', [NotificationController::class, 'readAndRedirect'])->name('admin.notification.read-and-redirect');
    Route::post('/non-linkable-notification-read', [NotificationController::class, 'markAsRead'])->name('non-linkable-notification-read');
    Route::get('/custom-notification-history', [NotificationController::class, 'customNotificationHistory'])->name('custom_notification.history');
    Route::post('/custom-notification-send-action', [NotificationController::class, 'sendCustomNotification'])->name('custom_notification.send');

    Route::get('/clear-cache', [AdminController::class, 'clearCache'])->name('cache.clear');
    
    // Sitemap
    Route::controller(AdminController::class)->group(function () {
        Route::get('/sitemap/generator', 'SitemapGenerator')->name('sitemap_generator');
        Route::post('/sitemap/download', 'DoSitemapGenerate')->name('generate_sitemap');
        Route::post('/sitemap/delete', 'DeleteSitemapFile')->name('delete_sitemap');
        Route::post('/sitemap/download-old', 'DownloadSingleSitemapFile')->name('download_old_sitemap');
    });

    Route::post('/custom-notifications/bulk-delete', [NotificationController::class, 'customNotificationBulkDelete'])->name('custom-notifications.bulk_delete');
    Route::get('/custom-notifications/delete/{id}', [NotificationController::class, 'customNotificationSingleDelete'])->name('custom-notifications.delete');
    Route::post('/admin/notifications/bulk-delete', [NotificationController::class, 'bulkDeleteAdmin'])->name('admin.notifications.bulk_delete');
    Route::post('/custom-notified-customers-list', [NotificationController::class, 'customNotifiedCustomersList'])->name('custom_notified_customers_list');

    Route::get('/admin-permissions', [RoleController::class, 'create_admin_permissions']);

    // Promotional Category
    Route::controller(PromotionalCategoryController::class)->group(function () {
        Route::post('/promotional-category/products', 'getProducts')->name('promotional_category.products');
        Route::post('/promotional-category/update-discounts', 'updateDiscounts')->name('promotional_category.update_discounts');
    });
});
