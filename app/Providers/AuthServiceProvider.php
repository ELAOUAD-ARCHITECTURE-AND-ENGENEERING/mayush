<?php

namespace App\Providers;

use Illuminate\Support\Facades\Gate;
use Illuminate\Foundation\Support\Providers\AuthServiceProvider as ServiceProvider;

class AuthServiceProvider extends ServiceProvider
{
  /**
   * The policy mappings for the application.
   *
   * @var array
   */
  protected $policies = [
    \App\Models\Order::class => \App\Policies\OrderPolicy::class,
    \App\Models\Shop::class => \App\Policies\ShopPolicy::class,
    \App\Models\SellerWithdrawRequest::class => \App\Policies\SellerWithdrawRequestPolicy::class,
  ];

  /**
   * Register any authentication / authorization services.
   *
   * @return void
   */
  public function boot()
  {
    $this->registerPolicies();

    // Implicitly grant "Super Admin" role all permissions
    Gate::before(function ($user, $ability) {
      return $user->hasRole('Super Admin') ? true : null;
    });
  }
}
