<?php

namespace App\Providers;

use Illuminate\Cache\RateLimiting\Limit;
use Illuminate\Support\Facades\Route;
use Illuminate\Foundation\Support\Providers\RouteServiceProvider as ServiceProvider;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\RateLimiter;

class RouteServiceProvider extends ServiceProvider
{
  /**
   * This namespace is applied to your controller routes.
   *
   * In addition, it is set as the URL generator's root namespace.
   *
   * @var string
   */
   protected $namespace = null;

  /**
   * Define your route model bindings, pattern filters, etc.
   *
   * @return void
   */
  public function boot()
  {
    //

    parent::boot();

    $this->configureRateLimiting();
  }

  /**
   * Define the routes for the application.
   *
   * @return void
   */
  public function map()
  {
      $this->mapApiRoutes();

      $this->mapApiSellerRoutes();

      $this->mapAdminRoutes();

      $this->mapSellerRoutes();

      $this->mapAffiliateRoutes();

      $this->mapRefundRoutes();

      $this->mapClubPointsRoutes();

      $this->mapOtpRoutes();

      $this->mapOfflinePaymentRoutes();

      $this->mapAfricanPaymentGatewayRoutes();

      $this->mapPaytmRoutes();

      $this->mapCmiRoutes();

      $this->mapPosRoutes();

      $this->mapSellerPackageRoutes();

      $this->mapDeliveryBoyRoutes();

      $this->mapAuctionRoutes();

      $this->mapWholesaleRoutes();

      $this->mapPreorderRoutes();

      $this->mapCybersourceRoutes();

      $this->mapGstRoutes();

      $this->mapShiprocketRoutes();

      $this->mapSteadfastRoutes();

      $this->mapPathaoRoutes();

      $this->mapKnetRoutes();

      $this->mapOnesstaRoutes();

      $this->mapWebRoutes();

      // $this->mapInstallRoutes();

      // $this->mapUpdateRoutes();
  }

  /**
   * Define the "b2b" routes for the application.
   *
   * These routes all receive session state, CSRF protection, etc.
   *
   * @return void
   */
  protected function mapWholesaleRoutes()
  {
    Route::middleware('web')
       ->namespace($this->namespace)
       ->group(base_path('routes/wholesale.php'));
  }

  /**
   * Define the "delivery boy" routes for the application.
   *
   * These routes all receive session state, CSRF protection, etc.
   *
   * @return void
   */
  protected function mapDeliveryBoyRoutes()
  {
    Route::middleware('web')
       ->namespace($this->namespace)
       ->group(base_path('routes/delivery_boy.php'));
  }

  /**
   * Define the "auction" routes for the application.
   *
   * These routes all receive session state, CSRF protection, etc.
   *
   * @return void
   */
  protected function mapAuctionRoutes()
  {
    Route::middleware('web')
       ->namespace($this->namespace)
       ->group(base_path('routes/auction.php'));
  }

  /**
   * Define the "seller package" routes for the application.
   *
   * These routes all receive session state, CSRF protection, etc.
   *
   * @return void
   */
  protected function mapSellerPackageRoutes()
  {
    Route::middleware('web')
       ->namespace($this->namespace)
       ->group(base_path('routes/seller_package.php'));
  }

  /**
   * Define the "affiliate" routes for the application.
   *
   * These routes all receive session state, CSRF protection, etc.
   *
   * @return void
   */
  protected function mapAffiliateRoutes()
  {
    Route::middleware('web')
       ->namespace($this->namespace)
       ->group(base_path('routes/affiliate.php'));
  }

  /**
   * Define the "offline payment" routes for the application.
   *
   * These routes all receive session state, CSRF protection, etc.
   *
   * @return void
   */
  protected function mapOfflinePaymentRoutes()
  {
    Route::middleware('web')
       ->namespace($this->namespace)
       ->group(base_path('routes/offline_payment.php'));
  }

  /**
   * Define the "Asian payment" routes for the application.
   *
   * These routes all receive session state, CSRF protection, etc.
   *
   * @return void
   */
  protected function mapPaytmRoutes()
  {
    Route::middleware('web')
       ->namespace($this->namespace)
       ->group(base_path('routes/paytm.php'));
  }

  /**
   * Define the "CMI Payment Gateway" routes for the application.
   *
   * These routes all receive session state, CSRF protection, etc.
   *
   * @return void
   */
  protected function mapCmiRoutes()
  {
    Route::middleware('web')
       ->namespace($this->namespace)
       ->group(base_path('routes/cmi.php'));
  }

  /**
   * Define the "African payment" routes for the application.
   *
   * These routes all receive session state, CSRF protection, etc.
   *
   * @return void
   */
  protected function mapAfricanPaymentGatewayRoutes()
  {
    Route::middleware('web')
       ->namespace($this->namespace)
       ->group(base_path('routes/african_pg.php'));
  }

  /**
   * Define the "refund" routes for the application.
   *
   * These routes all receive session state, CSRF protection, etc.
   *
   * @return void
   */
  protected function mapRefundRoutes()
  {
    Route::middleware('web')
       ->namespace($this->namespace)
       ->group(base_path('routes/refund_request.php'));
  }

  /**
   * Define the "club points" routes for the application.
   *
   * These routes all receive session state, CSRF protection, etc.
   *
   * @return void
   */
  protected function mapClubPointsRoutes()
  {
    Route::middleware('web')
       ->namespace($this->namespace)
       ->group(base_path('routes/club_points.php'));
  }

  /**
   * Define the "OTP System" routes for the application.
   *
   * These routes all receive session state, CSRF protection, etc.
   *
   * @return void
   */
  protected function mapOtpRoutes()
  {
    Route::middleware('web')
       ->namespace($this->namespace)
       ->group(base_path('routes/otp.php'));
  }

  /**
   * Define the "POS System" routes for the application.
   *
   * These routes all receive session state, CSRF protection, etc.
   *
   * @return void
   */
  protected function mapPosRoutes()
  {
    Route::middleware('web')
       ->namespace($this->namespace)
       ->group(base_path('routes/pos.php'));
  }

  /**
   * Define the "updating" routes for the application.
   *
   * These routes all receive session state, CSRF protection, etc.
   *
   * @return void
   */
  protected function mapUpdateRoutes()
  {
    Route::middleware('web')
       ->namespace($this->namespace)
       ->group(base_path('routes/update.php'));
  }

  /**
   * Define the "installation" routes for the application.
   *
   * These routes all receive session state, CSRF protection, etc.
   *
   * @return void
   */
  protected function mapInstallRoutes()
  {
    Route::middleware('web')
       ->namespace($this->namespace)
       ->group(base_path('routes/install.php'));
  }

  /**
   * Define the "web" routes for the application.
   *
   * These routes all receive session state, CSRF protection, etc.
   *
   * @return void
   */
  protected function mapWebRoutes()
  {
    Route::middleware('web')
       ->namespace($this->namespace)
       ->group(base_path('routes/web.php'));
  }

  /**
   * Define the "admin" routes for the application.
   *
   * These routes all receive session state, CSRF protection, etc.
   *
   * @return void
   */
  protected function mapAdminRoutes()
  {
    Route::middleware('web')
       ->namespace($this->namespace)
       ->group(base_path('routes/admin.php'));
  }

  /**
   * Define the "seller" routes for the application.
   *
   * These routes all receive session state, CSRF protection, etc.
   *
   * @return void
   */
  protected function mapSellerRoutes()
  {
    Route::middleware('web')
       ->namespace($this->namespace)
       ->group(base_path('routes/seller.php'));
  }

  /**
   * Define the "Pre Order" routes for the application.
   *
   * These routes all receive session state, CSRF protection, etc.
   *
   * @return void
   */
  protected function mapPreorderRoutes()
  {
    Route::middleware('web')
       ->namespace($this->namespace)
       ->group(base_path('routes/preorder.php'));
  }

  /**
   * Define the "api" routes for the application.
   *
   * These routes are typically stateless.
   *
   * @return void
   */
  protected function mapApiSellerRoutes()
  {
    Route::prefix('api')
       ->middleware('api')
       ->namespace($this->namespace)
       ->group(base_path('routes/api_seller.php'));
  }

  /**
   * Define the "api" routes for the application.
   *
   * These routes are typically stateless.
   *
   * @return void
   */
  protected function mapApiRoutes()
  {
    Route::prefix('api')
       ->middleware('api')
       ->namespace($this->namespace)
       ->group(base_path('routes/api.php'));
  }

  /**
   * Define the "b2b" routes for the application.
   *
   * These routes all receive session state, CSRF protection, etc.
   *
   * @return void
   */
  protected function mapCybersourceRoutes()
  {
    Route::middleware('web')
       ->namespace($this->namespace)
       ->group(base_path('routes/cybersource.php'));
  }

  /**
   * Configure the rate limiters for the application.
   *
   * @return void
   */
  protected function configureRateLimiting()
  {
    RateLimiter::for('auth-login', function (Request $request) {
        $key = $request->input('email') ?: $request->input('phone');
        return Limit::perMinute(5)->by($request->ip() . '|' . md5($key));
    });

    RateLimiter::for('auth-register', function (Request $request) {
        return Limit::perMinute(3)->by($request->ip());
    });

    RateLimiter::for('password-reset', function (Request $request) {
        return Limit::perMinute(3)->by($request->ip());
    });

    RateLimiter::for('admin-login', function (Request $request) {
        $key = $request->input('email') ?: $request->input('phone');
        return Limit::perMinute(3)->by($request->ip() . '|' . md5($key));
    });

    RateLimiter::for('seller-login', function (Request $request) {
        $key = $request->input('email') ?: $request->input('phone');
        return Limit::perMinute(5)->by($request->ip() . '|' . md5($key));
    });

    RateLimiter::for('checkout-submit', function (Request $request) {
        return Limit::perMinute(10)->by(optional($request->user())->id ?: $request->ip());
    });

    RateLimiter::for('express-buy', function (Request $request) {
        return Limit::perMinute(3)->by(optional($request->user())->id ?: $request->ip());
    });

    RateLimiter::for('cmi-webhook', function (Request $request) {
        return Limit::perMinute(30)->by($request->ip());
    });

    RateLimiter::for('onessta-webhook', function (Request $request) {
        return Limit::perMinute(30)->by($request->ip());
    });

    RateLimiter::for('search', function (Request $request) {
        return Limit::perMinute(60)->by(optional($request->user())->id ?: $request->ip());
    });

    RateLimiter::for('search-semantic', function (Request $request) {
        return Limit::perMinute(20)->by(optional($request->user())->id ?: $request->ip());
    });

    RateLimiter::for('blog-lead', function (Request $request) {
        return Limit::perMinute(3)->by($request->ip());
    });

    RateLimiter::for('contact-form', function (Request $request) {
        return Limit::perMinute(3)->by($request->ip());
    });

    RateLimiter::for('seller-application', function (Request $request) {
        return Limit::perMinute(2)->by($request->ip());
    });

    RateLimiter::for('file-upload', function (Request $request) {
        return Limit::perMinute(20)->by(optional($request->user())->id ?: $request->ip());
    });

    RateLimiter::for('api', function (Request $request) {
      return Limit::perMinute(60)->by(optional($request->user())->id ?: $request->ip());
    });

    RateLimiter::for('login', function (Request $request) {
      return Limit::perMinute(5)->by($request->ip());
    });

    RateLimiter::for('otp', function (Request $request) {
      return Limit::perMinute(3)->by($request->ip());
    });

    RateLimiter::for('payments', function (Request $request) {
      return Limit::perMinute(10)->by(optional($request->user())->id ?: $request->ip());
    });

    RateLimiter::for('uploads', function (Request $request) {
      return Limit::perMinute(20)->by(optional($request->user())->id ?: $request->ip());
    });

    RateLimiter::for('product-translation', function (Request $request) {
      return Limit::perMinute(10)->by(optional($request->user())->id ?: $request->ip());
    });
  }

  /**
   * Define the "GST System" routes for the application.
   *
   * These routes all receive session state, CSRF protection, etc.
   *
   * @return void
   */
  protected function mapGstRoutes()
  {
    Route::middleware('web')
       ->namespace($this->namespace)
       ->group(base_path('routes/gst.php'));
  }

  /**
   * Define the "Shiprocket System" routes for the application.
   *
   * These routes all receive session state, CSRF protection, etc.
   *
   * @return void
   */
  protected function mapShiprocketRoutes()
  {
    Route::middleware('web')
       ->namespace($this->namespace)
       ->group(base_path('routes/shiprocket.php'));
  }

  /**
   * Define the "Steadfast System" routes for the application.
   *
   * These routes all receive session state, CSRF protection, etc.
   *
   * @return void
   */
  protected function mapSteadfastRoutes()
  {
    Route::middleware('web')
       ->namespace($this->namespace)
       ->group(base_path('routes/steadfast.php'));
  }

  /**
   * Define the "Pathao System" routes for the application.
   *
   * These routes all receive session state, CSRF protection, etc.
   *
   * @return void
   */
  protected function mapPathaoRoutes()
  {
    Route::middleware('web')
       ->namespace($this->namespace)
       ->group(base_path('routes/pathao.php'));
  }

  /**
   * Define the "Knet Payment Gateway" routes for the application.
   *
   * These routes all receive session state, CSRF protection, etc.
   *
   * @return void
   */
  protected function mapKnetRoutes()
  {
    Route::middleware('web')
       ->namespace($this->namespace)
       ->group(base_path('routes/knet.php'));
  }

  /**
   * Define the "ONESSTA System" routes for the application.
   *
   * These routes all receive session state, CSRF protection, etc.
   *
   * @return void
   */
  protected function mapOnesstaRoutes()
  {
    Route::middleware('web')
       ->namespace($this->namespace)
       ->group(base_path('routes/onessta.php'));
  }


}
