<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use App\Traits\PreventDemoModeChanges;
use App;

class CustomerPackage extends Model
{
  use HasFactory, PreventDemoModeChanges;

    public function getTranslation($field = '', $lang = false){
      $lang = $lang ?: App::getLocale();
      $customer_package_translation = $this->customer_package_translations->where('lang', $lang)->first();
      if ($customer_package_translation != null && $customer_package_translation->$field !== null && $customer_package_translation->$field !== $this->$field) {
          return in_array($field, ['name', 'title']) ? translate($customer_package_translation->$field, $lang) : $customer_package_translation->$field;
      }

      return $customer_package_translation != null ? $customer_package_translation->$field : $this->$field;
    }

    public function customer_package_translations(){
      return $this->hasMany(CustomerPackageTranslation::class);
    }

    public function customer_package_payments()
    {
        return $this->hasMany(CustomerPackagePayment::class);
    
    }
}
