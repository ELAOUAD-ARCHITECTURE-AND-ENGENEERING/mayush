<?php

namespace App\Models;

use App;
use Illuminate\Database\Eloquent\Model;
use App\Traits\PreventDemoModeChanges;

class Note extends Model
{
    use PreventDemoModeChanges;
    protected $with = ['note_translations'];

    public function getTranslation($field = '', $lang = false)
    {
        $lang = $lang ?: App::getLocale();
        $note_translation = $this->note_translations->where('lang', $lang)->first();
        if ($note_translation != null && $note_translation->$field !== null && $note_translation->$field !== $this->$field) {
            return $note_translation->$field;
        }

        if (in_array($field, ['name', 'title'])) {
            return translate($this->$field, $lang);
        }

        return $note_translation != null ? $note_translation->$field : $this->$field;
    }
    
    public function note_translations()
    {
        return $this->hasMany(NoteTranslation::class);
    }

    public function user()
    {
        return $this->belongsTo(User::class);
    }
}
