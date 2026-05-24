<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use App\Traits\PreventDemoModeChanges;
use App;

class Role extends Model
{
  use PreventDemoModeChanges;

    protected $with = ['role_translations'];

    public function getTranslation($field = '', $lang = false){
        $lang = $lang ?: App::getLocale();
        $role_translation = $this->role_translations->where('lang', $lang)->first();
        if ($role_translation != null && $role_translation->$field !== null && $role_translation->$field !== $this->$field) {
            return in_array($field, ['name', 'title']) ? translate($role_translation->$field, $lang) : $role_translation->$field;
        }

        return $role_translation != null ? $role_translation->$field : $this->$field;
    }

    public function role_translations(){
      return $this->hasMany(RoleTranslation::class);
    }
}
