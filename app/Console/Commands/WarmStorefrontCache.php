<?php

namespace App\Console\Commands;

use App\Services\HomeLayoutService;
use App\Services\StorefrontCacheService;
use App\Services\StorefrontDataService;
use Illuminate\Console\Command;

class WarmStorefrontCache extends Command
{
    protected $signature = 'storefront:cache-warm {--with-sections : Warm deferred homepage section caches too}';

    protected $description = 'Warm session-independent storefront homepage data caches.';

    public function handle(HomeLayoutService $layouts, StorefrontCacheService $cache, StorefrontDataService $storefront): int
    {
        $layouts->getHomepageData();

        if ($this->option('with-sections')) {
            $layouts->getTodaysDealProducts();
            $layouts->getNewestProducts(12);
            $layouts->getPreorderFeaturedProducts();
            $layouts->getRecentBestSellers();
            $layouts->getEliteArtisans();

            $storefront->featuredProducts();
            $storefront->bestSellingProducts(20);
            $storefront->activeFlashDeals();

            $flashDeal = $storefront->featuredFlashDeal();
            if ($flashDeal) {
                $storefront->flashDealProducts((int) $flashDeal->id);
            }

            $homeCategories = json_decode(get_setting('home_categories'), true) ?: [];
            if ($homeCategories !== []) {
                $storefront->categories($homeCategories);

                foreach ($homeCategories as $categoryId) {
                    $storefront->categoryProducts((int) $categoryId, 5);
                }
            }

            $promotedCategoryId = (int) get_setting('promoted_category_id');
            if ($promotedCategoryId > 0) {
                $promotedCategory = $storefront->categories([$promotedCategoryId])->first();
                $categoryIds = $promotedCategory
                    ? array_merge([$promotedCategory->id], $promotedCategory->categories->pluck('id')->toArray())
                    : [$promotedCategoryId];

                $storefront->promotedCategoryProducts($categoryIds);
            }
        }

        $this->info('Storefront cache warmed at revision '.$cache->revision().'.');

        return self::SUCCESS;
    }
}
