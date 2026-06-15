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
          return in_array($field, ['name', 'title']) ? translate($page_translation->$field, $lang) : $page_translation->$field;
      }

      return $page_translation != null ? $page_translation->$field : $this->$field;
  }

  public function page_translations(){
    return $this->hasMany(PageTranslation::class);
  }
}
