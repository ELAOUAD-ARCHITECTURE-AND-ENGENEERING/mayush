<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use App\Traits\PreventDemoModeChanges;

class Shop extends Model
{
  use PreventDemoModeChanges;


  protected $with = ['user'];

  /**
   * The attributes that should be cast.
   *
   * @var array
   */
  protected $casts = [
      'bank_name'          => 'encrypted',
      'bank_info'          => 'encrypted',
      'business_info'      => 'encrypted',
      'verification_info'  => 'encrypted',
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
}
