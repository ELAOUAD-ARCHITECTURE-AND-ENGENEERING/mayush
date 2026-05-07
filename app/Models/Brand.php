<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use App\Traits\PreventDemoModeChanges;

use App;

class Brand extends Model
{
    use HasFactory, PreventDemoModeChanges;

    protected $with = ['brand_translations'];
    protected $fillable = ['name', 'logo', 'slug', 'meta_title', 'meta_description'];
    
    public function getTranslation($field = '', $lang = false)
    {
        $lang = $lang ?: App::getLocale();
        $brand_translation = $this->brand_translations->where('lang', $lang)->first();
        if ($brand_translation != null && $brand_translation->$field !== null && $brand_translation->$field !== $this->$field) {
            return $brand_translation->$field;
        }

        if (in_array($field, ['name', 'title'])) {
            return translate($this->$field, $lang);
        }

        return $brand_translation != null ? $brand_translation->$field : $this->$field;
    }

    public function brand_translations()
    {
        return $this->hasMany(BrandTranslation::class);
    }

    public function products()
    {
        return $this->hasMany(Product::class);
    }
}
