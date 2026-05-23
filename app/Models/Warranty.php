<?php

namespace App\Models;

use App;
use App\Traits\PreventDemoModeChanges;
use Illuminate\Database\Eloquent\Model;

class Warranty extends Model
{
    use PreventDemoModeChanges;
    protected $with = ['warranty_translations'];
    
    public function getTranslation($field = '', $lang = false)
    {
        $lang = $lang ?: App::getLocale();
        $warranty_translation = $this->warranty_translations->where('lang', $lang)->first();
        if ($warranty_translation != null && $warranty_translation->$field !== null && $warranty_translation->$field !== $this->$field) {
            return in_array($field, ['name', 'title']) ? translate($warranty_translation->$field, $lang) : $warranty_translation->$field;
        }

        return $warranty_translation != null ? $warranty_translation->$field : $this->$field;
    }

    public function warranty_translations()
    {
        return $this->hasMany(WarrantyTranslation::class);
    }
}
