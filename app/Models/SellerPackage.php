<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use App\Traits\PreventDemoModeChanges;
use App;

class SellerPackage extends Model
{
    use PreventDemoModeChanges;

    protected $guarded = [];

    public function getTranslation($field = '', $lang = false){
        $lang = $lang ?: App::getLocale();
        $seller_package_translation = $this->seller_package_translations->where('lang', $lang)->first();
        if ($seller_package_translation != null && $seller_package_translation->$field !== null && $seller_package_translation->$field !== $this->$field) {
            return in_array($field, ['name', 'title']) ? translate($seller_package_translation->$field, $lang) : $seller_package_translation->$field;
        }

        return $seller_package_translation != null ? $seller_package_translation->$field : $this->$field;
    }

    public function seller_package_translations(){
      return $this->hasMany(SellerPackageTranslation::class);
    }

    public function seller_package_payments()
    {
        return $this->hasMany(SelllerPackagePayment::class);
    }

    public function shop()
    {
        return $this->hasOne(Shop::class);
    }

}
