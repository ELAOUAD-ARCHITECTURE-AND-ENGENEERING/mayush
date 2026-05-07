<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use App\Traits\PreventDemoModeChanges;
use App;

class Page extends Model
{
  use PreventDemoModeChanges;

  public function getTranslation($field = '', $lang = false){
      $lang = $lang ?: App::getLocale();
      $page_translation = $this->page_translations->where('lang', $lang)->first();
      if ($page_translation != null && $page_translation->$field !== null && $page_translation->$field !== $this->$field) {
          return $page_translation->$field;
      }

      if (in_array($field, ['title', 'name'])) {
          return translate($this->$field, $lang);
      }

      return $page_translation != null ? $page_translation->$field : $this->$field;
  }

  public function page_translations(){
    return $this->hasMany(PageTranslation::class);
  }
}
