<?php

namespace App\Providers;

use Illuminate\Support\ServiceProvider;
use Illuminate\Support\Facades\Schema;
use Illuminate\Pagination\Paginator;

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
      // \App\Models\Upload::observe(\App\Observers\UploadObserver::class);
      \App\Models\Product::observe(\App\Observers\ProductObserver::class);
  }

  /**
   * Register any application services.
   *
   * @return void
   */
  public function register()
  {
    //
  }
}
