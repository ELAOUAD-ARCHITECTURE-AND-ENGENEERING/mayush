<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use App\Traits\PreventDemoModeChanges;

class Shop extends Model
{
  use HasFactory, PreventDemoModeChanges;


  protected $with = ['user'];

  /**
   * The attributes that should be cast.
   *
   * @var array
   */
  protected $casts = [
      'bank_name'          => \App\Casts\SafeEncrypted::class,
      'bank_info'          => \App\Casts\SafeEncrypted::class,
      'business_info'      => \App\Casts\SafeEncrypted::class,
      'verification_info'  => \App\Casts\SafeEncrypted::class,
      'social_links'       => 'array',
      'gallery_json'       => 'array',
  ];

  public function user()
  {
    return $this->belongsTo(User::class);
  }
  
  public function seller_package(){
    return $this->belongsTo(SellerPackage::class);
  }
  public function followers(){
    return $this->hasMany(FollowSeller::class);
  }

  /**
   * Get the active Elite subscription for this shop.
   */
  public function activeEliteSubscription()
  {
    return $this->hasOne(\App\Models\EliteSubscription::class)
                ->where('status', 'active')
                ->where(function ($q) {
                    $q->whereNull('expires_at')
                      ->orWhere('expires_at', '>', now());
                });
  }

  /**
   * Determine if this shop currently has an active Elite subscription
   * AND the global Elite system is enabled.
   */
  public function isElite(): bool
  {
    if (get_setting('elite_system_active') != 1) {
        return false;
    }
    return $this->activeEliteSubscription()->exists();
  }
}
