<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use App\Traits\PreventDemoModeChanges;
use App;

class Attribute extends Model
{
  use PreventDemoModeChanges;

    protected $with = ['attribute_translations'];

    public function getTranslation($field = '', $lang = false){
      $lang = $lang ?: App::getLocale();
      $attribute_translation = $this->attribute_translations->where('lang', $lang)->first();
      if ($attribute_translation != null && $attribute_translation->$field !== null && $attribute_translation->$field !== $this->$field) {
          return $attribute_translation->$field;
      }

      if (in_array($field, ['name', 'title'])) {
          return translate($this->$field, $lang);
      }

      return $attribute_translation != null ? $attribute_translation->$field : $this->$field;
    }

    public function attribute_translations(){
      return $this->hasMany(AttributeTranslation::class);
    }

    public function attribute_values() {
        return $this->hasMany(AttributeValue::class);
    }

}
