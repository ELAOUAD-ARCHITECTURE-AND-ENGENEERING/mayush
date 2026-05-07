<?php

namespace App\Models;

use App;
use Illuminate\Database\Eloquent\Model;
use App\Traits\PreventDemoModeChanges;
use Illuminate\Database\Eloquent\SoftDeletes;
use Illuminate\Support\Str;

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

    public function shop()
    {
        return $this->belongsTo(Shop::class);
    }

    public function products()
    {
        return $this->belongsToMany(Product::class, 'blog_product')
            ->withPivot(['placement', 'sort_order'])
            ->withTimestamps()
            ->orderBy('blog_product.sort_order')
            ->orderBy('blog_product.id');
    }

    public function manualProducts()
    {
        return $this->products()->wherePivot('placement', 'manual');
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
        $blog_translation = $this->translations->where('lang', $lang)->first();

        if ($blog_translation != null && $blog_translation->{$field} !== null && $blog_translation->{$field} !== $this->{$field}) {
            return $blog_translation->{$field};
        }

        return $blog_translation != null ? $blog_translation->{$field} : $this->{$field};
    }

    public function scopePublished($query)
    {
        return $query->where('status', 1)
            ->where(function ($q) {
                $q->whereNull('published_at')->orWhere('published_at', '<=', now());
            });
    }

    public function scopeFeatured($query)
    {
        return $query->where('is_featured', 1);
    }

    public function scopeByCategory($query, $category)
    {
        if ($category === null || $category === '') {
            return $query;
        }

        if ($category instanceof BlogCategory) {
            return $query->where('category_id', $category->id);
        }

        if (is_numeric($category)) {
            return $query->where('category_id', $category);
        }

        return $query->whereHas('category', function ($categoryQuery) use ($category) {
            $categoryQuery->where('slug', $category);
        });
    }

    public function getReadTimeMinutesAttribute($value)
    {
        if ($value) {
            return (int) $value;
        }

        $words = str_word_count(strip_tags((string) $this->getTranslation('description')));
        return max(1, (int) ceil($words / 200));
    }

    public function getProductCountAttribute()
    {
        if ($this->relationLoaded('products')) {
            return $this->products->count();
        }

        return $this->products()->count();
    }

    public function getPublicUrlAttribute()
    {
        return route('blog.details', $this->slug);
    }

    public function getHeroImageUrlAttribute()
    {
        $image = $this->hero_image ?: $this->banner;
        return $image ? uploaded_asset($image) : null;
    }

    public function getMetaImageUrlAttribute()
    {
        $image = $this->meta_img ?: $this->hero_image ?: $this->banner;
        return $image ? uploaded_asset($image) : null;
    }

    public function getBadgeLabelAttribute()
    {
        if ($this->badge_type === 'custom') {
            return $this->custom_badge_text;
        }

        return $this->badge_type ? Str::headline($this->badge_type) : null;
    }
}
