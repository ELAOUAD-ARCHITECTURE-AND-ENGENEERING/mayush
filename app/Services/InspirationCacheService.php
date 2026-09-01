<?php

namespace App\Services;

use App\Models\Inspiration;
use Illuminate\Support\Facades\Cache;
use Illuminate\Support\Facades\Schema;

class InspirationCacheService
{
    public function featuredKey(string $language): string
    {
        return 'inspirations_featured_'.$this->language($language);
    }

    public function detailKey(string $slug, string $language): string
    {
        return 'inspiration_detail_'.$slug.'_'.$this->language($language);
    }

    public function invalidate(?Inspiration $inspiration = null, ?string $previousSlug = null): void
    {
        foreach (['fr', 'ar'] as $language) {
            Cache::forget($this->featuredKey($language));

            if ($inspiration) {
                Cache::forget($this->detailKey($inspiration->slug, $language));
            }

            if ($previousSlug && (!$inspiration || $previousSlug !== $inspiration->slug)) {
                Cache::forget($this->detailKey($previousSlug, $language));
            }
        }
    }

    public function invalidateForProduct(int $productId): void
    {
        if (!Schema::hasTable('inspirations') || !Schema::hasTable('inspiration_items')) {
            return;
        }

        $inspirations = Inspiration::withTrashed()
            ->whereHas('items', fn ($query) => $query->where('product_id', $productId))
            ->get(['id', 'slug', 'is_featured', 'show_on_home']);

        if ($inspirations->isEmpty()) {
            return;
        }

        $clearFeatured = $inspirations->contains(
            fn (Inspiration $inspiration) => $inspiration->is_featured && $inspiration->show_on_home
        );

        foreach (['fr', 'ar'] as $language) {
            if ($clearFeatured) {
                Cache::forget($this->featuredKey($language));
            }

            foreach ($inspirations as $inspiration) {
                Cache::forget($this->detailKey($inspiration->slug, $language));
            }
        }
    }

    public function language(string $language): string
    {
        return str_starts_with(strtolower($language), 'ar') ? 'ar' : 'fr';
    }
}
