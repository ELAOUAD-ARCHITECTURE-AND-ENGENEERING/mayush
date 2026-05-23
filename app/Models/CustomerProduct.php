<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use App\Traits\PreventDemoModeChanges;
use App;

class CustomerProduct extends Model
{
    use PreventDemoModeChanges;

    protected $with = ['customer_product_translations'];

    public function getTranslation($field = '', $lang = false)
    {
        $lang = $lang ?: App::getLocale();
        $customer_product_translation = $this->customer_product_translations->where('lang', $lang)->first();
        if ($customer_product_translation != null && $customer_product_translation->$field !== null && $customer_product_translation->$field !== $this->$field) {
            return in_array($field, ['name', 'title']) ? translate($customer_product_translation->$field, $lang) : $customer_product_translation->$field;
        }

        return $customer_product_translation != null ? $customer_product_translation->$field : $this->$field;
    }

    public function scopeIsActiveAndApproval($query)
    {
        return $query->where('status', '1')
            ->where('published', '1');
    }

    public function category()
    {
        return $this->belongsTo(Category::class);
    }

    public function subcategory()
    {
        return $this->belongsTo(SubCategory::class);
    }

    public function subsubcategory()
    {
        return $this->belongsTo(SubSubCategory::class);
    }

    public function brand()
    {
        return $this->belongsTo(Brand::class);
    }

    public function user()
    {
        return $this->belongsTo(User::class);
    }

    public function state()
    {
        return $this->belongsTo(State::class);
    }

    public function city()
    {
        return $this->belongsTo(City::class);
    }

    public function customer_product_translations()
    {
        return $this->hasMany(CustomerProductTranslation::class);
    }

    public function thumbnail()
    {
        return $this->belongsTo(Upload::class, 'thumbnail_img');
    }

    public function promotion()
    {
        return $this->hasOne(Promotion::class, 'product_id')
            ->where('status', 'approved')
            ->where('start_date', '<=', \Carbon\Carbon::now())
            ->where('end_date', '>=', \Carbon\Carbon::now());
    }
}
