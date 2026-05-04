<?php

namespace App\Models;

use App;
use Illuminate\Database\Eloquent\Model;
use App\Traits\PreventDemoModeChanges;
use Illuminate\Database\Eloquent\SoftDeletes;

class Blog extends Model
{
    use PreventDemoModeChanges;

    use SoftDeletes;

    protected $guarded = [];
    
    public function category() {
        return $this->belongsTo(BlogCategory::class, 'category_id');
    }

    public function author()
    {
        return $this->belongsTo(User::class, 'user_id');
    }

    public function tags()
    {
        return $this->belongsToMany(Tag::class, 'blog_tag')->withTimestamps();
    }

    public function translations()
    {
        return $this->hasMany(BlogTranslation::class);
    }

    public function blog_translations()
    {
        return $this->translations();
    }

    public function getTranslation($field = '', $lang = false)
    {
        $lang = $lang ?: App::getLocale();
        $translation = $this->translations->where('lang', $lang)->first();

        return $translation && $translation->{$field} !== null ? $translation->{$field} : $this->{$field};
    }

    public function scopePublished($query)
    {
        return $query->where('status', 1)
            ->where(function ($q) {
                $q->whereNull('published_at')->orWhere('published_at', '<=', now());
            });
    }
}
