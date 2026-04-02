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
      
      // Force HTTPS if explicitly enabled OR if APP_URL is already https
      if (env('FORCE_HTTPS') == 'On' || str_starts_with(env('APP_URL', ''), 'https')) {
          \URL::forceScheme('https');
      }
      // \App\Models\Upload::observe(\App\Observers\UploadObserver::class);
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
