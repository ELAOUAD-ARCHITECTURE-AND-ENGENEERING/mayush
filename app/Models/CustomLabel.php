<?php

namespace App\Models;

use App;
use App\Traits\PreventDemoModeChanges;
use Illuminate\Database\Eloquent\Model;

class CustomLabel extends Model
{
    use PreventDemoModeChanges;
    protected $table = 'custom_labels';

    protected $with = ['custom_label_translations'];

    public function user()
    {
        return $this->belongsTo(User::class);
    }

    public function getTranslation($field = '', $lang = false)
    {
        $lang = $lang ?: App::getLocale();
        $custom_label_translation = $this->custom_label_translations->where('lang', $lang)->first();
        if ($custom_label_translation != null && $custom_label_translation->$field !== null && $custom_label_translation->$field !== $this->$field) {
            return in_array($field, ['name', 'title']) ? translate($custom_label_translation->$field, $lang) : $custom_label_translation->$field;
        }

        return $custom_label_translation != null ? $custom_label_translation->$field : $this->$field;
    }

    public function custom_label_translations()
    {
        return $this->hasMany(CustomLabelTranslation::class);
    }
}
