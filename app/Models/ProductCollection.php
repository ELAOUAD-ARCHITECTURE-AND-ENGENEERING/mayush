<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\Model;

class ProductCollection extends Model
{
    protected $fillable = [
        'name',
        'slug',
        'description',
        'mode',
        'category_ids',
        'brand_ids',
        'seller_ids',
        'tags',
        'min_price',
        'max_price',
        'default_sort',
        'hero_image',
        'meta_title',
        'meta_description',
        'meta_image',
        'show_best_selling',
        'show_recently_viewed',
        'status',
        'starts_at',
        'ends_at',
    ];

    protected $casts = [
        'category_ids' => 'array',
        'brand_ids' => 'array',
        'seller_ids' => 'array',
        'show_best_selling' => 'boolean',
        'show_recently_viewed' => 'boolean',
        'status' => 'boolean',
        'starts_at' => 'datetime',
        'ends_at' => 'datetime',
    ];

    public function products()
    {
        return $this->belongsToMany(Product::class, 'product_collection_product')
            ->withPivot('sort_order')
            ->withTimestamps()
            ->orderByPivot('sort_order');
    }

    public function scopePublished(Builder $query): Builder
    {
        return $query->where('status', true)
            ->where(function (Builder $query) {
                $query->whereNull('starts_at')->orWhere('starts_at', '<=', now());
            })
            ->where(function (Builder $query) {
                $query->whereNull('ends_at')->orWhere('ends_at', '>=', now());
            });
    }
}
