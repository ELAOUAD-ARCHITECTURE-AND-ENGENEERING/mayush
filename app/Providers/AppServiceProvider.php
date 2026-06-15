<?php

namespace App\Providers;

use Illuminate\Support\ServiceProvider;
use Illuminate\Support\Facades\Schema;
use Illuminate\Pagination\Paginator;
use App\Models\Order;
use Mayush\Shipping\Onessta\Observers\OrderObserver;
use App\Services\Payment\CmiConfigValidatorInterface;
use App\Services\Payment\CmiConfigValidator;

class AppServiceProvider extends ServiceProvider
{
  /**
   * Bootstrap any application services.
   *
   * @return void
   */
  public function boot()
  {
      Schema::defaultStringLength(191);
      Paginator::useBootstrap();
      
      // Robust dynamic protocol detection to eliminate Mixed Content errors
      if (env('FORCE_HTTPS') == 'On' 
          || str_starts_with(env('APP_URL', ''), 'https') 
          || request()->isSecure()
      ) {
          \URL::forceScheme('https');
      }
      \App\Models\Upload::observe(\App\Observers\UploadObserver::class);
      \App\Models\Product::observe(\App\Observers\ProductObserver::class);
      \App\Models\ProductStock::observe(\App\Observers\ProductStockObserver::class);
      \App\Models\BusinessSetting::observe(\App\Observers\StorefrontCacheObserver::class);
      \App\Models\Category::observe(\App\Observers\StorefrontCacheObserver::class);
      \App\Models\Blog::observe(\App\Observers\StorefrontCacheObserver::class);
      \App\Models\FlashDeal::observe(\App\Observers\StorefrontCacheObserver::class);
      \App\Models\FlashDealProduct::observe(\App\Observers\StorefrontCacheObserver::class);
      \App\Models\ProductCollection::observe(\App\Observers\StorefrontCacheObserver::class);
      \App\Models\TopBanner::observe(\App\Observers\StorefrontCacheObserver::class);
      \App\Models\TopBannerTranslation::observe(\App\Observers\StorefrontCacheObserver::class);
      \App\Models\DynamicPopup::observe(\App\Observers\StorefrontCacheObserver::class);
      \App\Models\CustomAlert::observe(\App\Observers\StorefrontCacheObserver::class);
      \App\Models\CustomSaleAlert::observe(\App\Observers\StorefrontCacheObserver::class);
      \App\Models\Language::observe(\App\Observers\StorefrontCacheObserver::class);
      \App\Models\Currency::observe(\App\Observers\StorefrontCacheObserver::class);
      \App\Models\CustomLabel::observe(\App\Observers\StorefrontCacheObserver::class);

      // SMS Rate Limiting Configuration
      \Illuminate\Support\Facades\RateLimiter::for('sms_sender', function ($job) {
          return \Illuminate\Cache\RateLimiting\Limit::perMinute(30);
      });

      // ONESSTA 3PL Shipping Integration
      if (config('onessta.enabled', false)) {
          Order::observe(OrderObserver::class);
      }
    }

  /**
   * Register any application services.
   *
   * @return void
   */
  public function register()
  {
    // Bind CMI Configuration Validator interface
    $this->app->bind(CmiConfigValidatorInterface::class, CmiConfigValidator::class);

    if (!$this->app->environment('production')) {
        if (env('DEBUGBAR_ENABLED', false) && class_exists('Barryvdh\\Debugbar\\ServiceProvider')) {
            $this->app->register('Barryvdh\\Debugbar\\ServiceProvider');
        }

        if (class_exists('Barryvdh\\LaravelIdeHelper\\IdeHelperServiceProvider')) {
            $this->app->register('Barryvdh\\LaravelIdeHelper\\IdeHelperServiceProvider');
        }

        if (class_exists('Spatie\\LaravelIgnition\\IgnitionServiceProvider')) {
            $this->app->register('Spatie\\LaravelIgnition\\IgnitionServiceProvider');
        }
    }
  }
}
