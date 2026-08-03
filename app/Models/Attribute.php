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

      if ($attribute_translation != null && $attribute_translation->$field !== null && trim((string)$attribute_translation->$field) !== '') {
          $translated = $attribute_translation->$field;
          if ($translated !== $this->$field) {
              return in_array($field, ['name', 'title']) ? translate($translated, $lang) : $translated;
          }
      }

      $base_value = $this->$field;
      if (in_array($field, ['name', 'title']) && $base_value !== null && trim((string)$base_value) !== '') {
          return translate($base_value, $lang);
      }

      return $base_value;
    }

    public function attribute_translations(){
      return $this->hasMany(AttributeTranslation::class);
    }

    public function attribute_values() {
        return $this->hasMany(AttributeValue::class);
    }

}
