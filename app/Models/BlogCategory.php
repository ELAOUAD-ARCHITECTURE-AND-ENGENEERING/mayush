<?php

namespace App\Models;

use App;
use Illuminate\Database\Eloquent\Model;
use App\Traits\PreventDemoModeChanges;
use Illuminate\Database\Eloquent\SoftDeletes;

class BlogCategory extends Model
{
    use PreventDemoModeChanges;

    use SoftDeletes;

    protected $guarded = [];

    public function getTranslation($field = 'category_name', $lang = false)
    {
        $lang = $lang ?: App::getLocale();
        $value = $this->{$field};

        if ($value === null || $value === '') {
            return $value;
        }

        return $field === 'category_name' ? translate($value, $lang) : $value;
    }
    
    public function posts()
    {
        return $this->hasMany(Blog::class, 'category_id');
    }
}
