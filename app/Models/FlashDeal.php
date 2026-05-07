<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use App\Traits\PreventDemoModeChanges;
use App;
use Carbon\Carbon;

class FlashDeal extends Model
{
    use HasFactory, PreventDemoModeChanges;

    protected $with = ['flash_deal_translations'];

    public function getTranslation($field = '', $lang = false){
        $lang = $lang ?: App::getLocale();
        $flash_deal_translation = $this->flash_deal_translations->where('lang', $lang)->first();
        if ($flash_deal_translation != null && $flash_deal_translation->$field !== null && $flash_deal_translation->$field !== $this->$field) {
            return $flash_deal_translation->$field;
        }

        if (in_array($field, ['title', 'name'])) {
            return translate($this->$field, $lang);
        }

        return $flash_deal_translation != null ? $flash_deal_translation->$field : $this->$field;
    }

    public function flash_deal_translations(){
      return $this->hasMany(FlashDealTranslation::class);
    }

    public function flash_deal_products()
    {
        return $this->hasMany(FlashDealProduct::class);
    }

    public function scopeActive($query)
    {
        return $query->where('status', 1)
            ->where('start_date', '<=', Carbon::now()->timestamp)
            ->where('end_date', '>=', Carbon::now()->timestamp);
    }

    public function scopeFeatured($query)
    {
        return $query->where('featured', 1);
    }

    public function getIsActiveAttribute()
    {
        $now = Carbon::now()->timestamp;
        return $this->status == 1 && $this->start_date <= $now && $this->end_date >= $now;
    }

    public function getUrlAttribute()
    {
        return route('flash-deal-details', $this->slug);
    }

    public function scopeIsActiveAndFeatured($query)
    {
        return $query->where('status', '1')
            ->where('featured', '1');
    }
}
