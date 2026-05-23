<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use App\Traits\PreventDemoModeChanges;
use App;

class Element extends Model
{
    use PreventDemoModeChanges;

    protected $with = ['element_translations'];

    public function getTranslation($field = '', $lang = false)
    {
        $lang = $lang ?: App::getLocale();
        $element_translation = $this->element_translations->where('lang', $lang)->first();
        if ($element_translation != null && $element_translation->$field !== null && $element_translation->$field !== $this->$field) {
            return in_array($field, ['name', 'title']) ? translate($element_translation->$field, $lang) : $element_translation->$field;
        }

        return $element_translation != null ? $element_translation->$field : $this->$field;
    }

    public function element_translations()
    {
        return $this->hasMany(ElementTranslation::class);
    }

    public function element_types()
    {
        return $this->hasMany(ElementType::class);
    }
}
