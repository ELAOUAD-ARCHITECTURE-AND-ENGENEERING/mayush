<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use App\Traits\PreventDemoModeChanges;
use App;

class City extends Model
{
    use PreventDemoModeChanges;

    protected $guarded = [];

    public function getTranslation($field = '', $lang = false){
        $lang = $lang ?: App::getLocale();
        $city_translation = $this->city_translations->where('lang', $lang)->first();
        if ($city_translation != null && $city_translation->$field !== null && $city_translation->$field !== $this->$field) {
            return in_array($field, ['name', 'title']) ? translate($city_translation->$field, $lang) : $city_translation->$field;
        }

        return $city_translation != null ? $city_translation->$field : $this->$field;
    }

    public function city_translations(){
       return $this->hasMany(CityTranslation::class);
    }

    public function areas(){
        return $this->hasMany(Area::class);
    }

    public function country()
    {
        return $this->belongsTo(Country::class);
    }

    public function state()
    {
        return $this->belongsTo(State::class);
    }
}
