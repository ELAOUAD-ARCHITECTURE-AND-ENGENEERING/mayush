<?php

namespace App\Models;

use App;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class Faq extends Model
{
    use HasFactory;
    protected $with = ['faq_translations'];
    
    public function getTranslation($field = '', $lang = false)
    {
        $lang = $lang ?: App::getLocale();
        $faq_translation = $this->faq_translations->where('lang', $lang)->first();
        if ($faq_translation != null && $faq_translation->$field !== null && $faq_translation->$field !== $this->$field) {
            return in_array($field, ['name', 'title']) ? translate($faq_translation->$field, $lang) : $faq_translation->$field;
        }

        return $faq_translation != null ? $faq_translation->$field : $this->$field;
    }

    public function faq_translations()
    {
        return $this->hasMany(FaqTranslation::class);
    }
}
