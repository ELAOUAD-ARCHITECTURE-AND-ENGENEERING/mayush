<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use App\Traits\PreventDemoModeChanges;
use App;

class Area extends Model
{
    use PreventDemoModeChanges;

    public function getTranslation($field = '', $lang = false){
        $lang = $lang ?: App::getLocale();
        $area_translation = $this->area_translations->where('lang', $lang)->first();
        if ($area_translation != null && $area_translation->$field !== null && $area_translation->$field !== $this->$field) {
            return $area_translation->$field;
        }

        return $area_translation != null ? $area_translation->$field : $this->$field;
    }

    public function area_translations(){
       return $this->hasMany(AreaTranslation::class);
    }

    public function city()
    {
        return $this->belongsTo(City::class);
    }

    public function state()
    {
        return $this->belongsTo(State::class);
    }

    public function country()
    {
        return $this->belongsTo(Country::class);
    }
}
