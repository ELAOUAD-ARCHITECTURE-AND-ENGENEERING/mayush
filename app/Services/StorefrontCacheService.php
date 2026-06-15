<?php

namespace App\Services;

use Illuminate\Support\Facades\Cache;

class StorefrontCacheService
{
    private const REVISION_KEY = 'storefront:revision';

    /**
     * @var array<string>
     */
    private const HOME_CACHE_KEYS = [
        'business_settings',
        'featured_categories',
        'hot_categories',
        'home_latest_blogs',
        'home_inspiration_blogs',
        'featured_products',
        'best_selling_products',
        'newest_products',
        'todays_deal_products',
        'best_selers',
        'home_preorder_featured_products',
    ];

    public function revision(): int
    {
        return (int) Cache::get(self::REVISION_KEY, 1);
    }

    public function bump(): int
    {
        foreach (self::HOME_CACHE_KEYS as $key) {
            Cache::forget($key);
        }

        if (! Cache::has(self::REVISION_KEY)) {
            Cache::forever(self::REVISION_KEY, 1);
        }

        return (int) Cache::increment(self::REVISION_KEY);
    }
}
