<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\SoftDeletes;

class Inspiration extends Model
{
    use SoftDeletes;

    protected $fillable = [
        'slug', 'title_fr', 'title_ar', 'subtitle_fr', 'subtitle_ar',
        'description_fr', 'description_ar', 'hero_image',
        'hero_image_width', 'hero_image_height',
        'status', 'is_featured', 'show_on_home', 'sort_order',
        'published_at', 'starts_at', 'ends_at', 'created_by',
    ];

    protected $casts = [
        'is_featured' => 'boolean',
        'show_on_home' => 'boolean',
        'published_at' => 'datetime',
        'starts_at' => 'datetime',
        'ends_at' => 'datetime',
    ];

    public function items()
    {
        return $this->hasMany(InspirationItem::class)->orderBy('display_order');
    }

    public function hotspots()
    {
        return $this->hasMany(InspirationHotspot::class)->orderBy('display_order');
    }

    public function creator()
    {
        return $this->belongsTo(User::class, 'created_by');
    }

    public function scopePublished($query)
    {
        return $query->where('status', 'published')
            ->where(function ($q) {
                $q->whereNull('starts_at')->orWhere('starts_at', '<=', now());
            })
            ->where(function ($q) {
                $q->whereNull('ends_at')->orWhere('ends_at', '>=', now());
            });
    }

    public function scopeFeatured($query)
    {
        return $query->where('is_featured', true)->where('show_on_home', true);
    }

    public function getTitle($lang = 'fr')
    {
        return $lang === 'ar' && $this->title_ar ? $this->title_ar : $this->title_fr;
    }

    public function getSubtitle($lang = 'fr')
    {
        return $lang === 'ar' && $this->subtitle_ar ? $this->subtitle_ar : $this->subtitle_fr;
    }

    public function getDescription($lang = 'fr')
    {
        return $lang === 'ar' && $this->description_ar ? $this->description_ar : $this->description_fr;
    }

    public function getHeroImageUrlAttribute()
    {
        if (!$this->hero_image) {
            return null;
        }

        if (str_starts_with($this->hero_image, 'http://') || str_starts_with($this->hero_image, 'https://') || str_starts_with($this->hero_image, '//')) {
            return $this->hero_image;
        }

        return my_asset('storage/' . ltrim($this->hero_image, '/'));
    }
}
