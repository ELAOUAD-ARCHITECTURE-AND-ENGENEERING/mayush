<?php

namespace App\Models;

use App;
use Illuminate\Database\Eloquent\Model;
use App\Traits\PreventDemoModeChanges;

class TopBanner extends Model
{
    use PreventDemoModeChanges;
    protected $table = 'top_banners';
    protected $with = ['top_banner_translations'];

    public function getTranslation($field = '', $lang = false)
    {
        $lang = $lang ?: App::getLocale();
        $top_banner_translation = $this->top_banner_translations->where('lang', $lang)->first();
        if ($top_banner_translation != null && $top_banner_translation->$field !== null && $top_banner_translation->$field !== $this->$field) {
            return in_array($field, ['name', 'title']) ? translate($top_banner_translation->$field, $lang) : $top_banner_translation->$field;
        }

        return $top_banner_translation != null ? $top_banner_translation->$field : $this->$field;
    }
    
    public function top_banner_translations()
    {
        return $this->hasMany(TopBannerTranslation::class);
    }
}
