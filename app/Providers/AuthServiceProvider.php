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

    // Implicitly grant "Super Admin" role all permissions.
    // Blog admins also get the full editorial surface without changing user_type.
    Gate::before(function ($user, $ability) {
      if ($user->hasRole('Super Admin')) {
        return true;
      }

      $blogSuperAbilities = [
        'blog_super_admin',
        'manage_blog_authors',
        'view_blogs',
        'add_blog',
        'edit_blog',
        'delete_blog',
        'publish_blog',
        'review_blog',
      ];

      if (($user->user_type === 'admin' || $user->hasRole('blog_super_admin')) && in_array($ability, $blogSuperAbilities, true)) {
        return true;
      }

      return null;
    });

    Gate::define('viewPulse', function ($user = null) {
      return $user && in_array($user->user_type, ['admin', 'staff']);
    });
  }
}
