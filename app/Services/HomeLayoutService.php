<?php

namespace App\Services;

use App\Models\Blog;
use App\Models\Category;
use App\Models\Product;
use App\Models\PreorderProduct;
use Cache;
use Illuminate\Support\Facades\Schema;

class HomeLayoutService
{
    public function __construct(private readonly StorefrontDataService $storefrontData)
    {
    }

    /**
     * Assemble data required by the homepage shell.
     */
    public function getHomepageData(): array
    {
        $lang = get_system_language() ? get_system_language()->code : null;
        $revision = app(StorefrontCacheService::class)->revision();

        return [
            'featured_categories' => Cache::remember("storefront:v{$revision}:featured-categories", 900, function () {
                return Category::with([
                    'bannerImage',
                    'coverImage',
                    'childrenCategories',
                ])->where('featured', 1)->get();
            }),
            'hot_categories' => Cache::remember("storefront:v{$revision}:hot-categories", 900, function () {
                return Category::with(['bannerImage', 'coverImage', 'catIcon'])->where('hot_category', '1')->get();
            }),
            'latest_blogs' => Cache::remember('home_latest_blogs', 900, function () {
                return Blog::published()
                    ->with(['category', 'translations'])
                    ->orderBy('published_at', 'desc')
                    ->orderBy('created_at', 'desc')
                    ->take(3)
                    ->get();
            }),
            'inspiration_blogs' => $this->getHomepageInspirationBlogs(),
            'lang' => $lang,
        ];
    }

    public function getHomepageInspirationBlogs()
    {
        if (get_setting('home_inspiration_section_status', '1') != '1') {
            return collect();
        }

        $selectedIds = json_decode(get_setting('home_inspiration_blog_ids') ?: '[]', true) ?: [];
        $selectedIds = array_values(array_filter(array_map('intval', $selectedIds)));

        if ($selectedIds !== []) {
            return Blog::published()
                ->with(['category', 'translations'])
                ->whereIn('id', $selectedIds)
                ->get()
                ->sortBy(function ($blog) use ($selectedIds) {
                    return array_search((int) $blog->id, $selectedIds, true);
                })
                ->take(6)
                ->values();
        }

        return Cache::remember('home_inspiration_blogs', 900, function () {
            return Blog::published()
                ->with(['category', 'translations'])
                ->orderBy('published_at', 'desc')
                ->orderBy('created_at', 'desc')
                ->take(6)
                ->get();
        });
    }

    /**
     * Get active portfolio posts for the portfolio landing fallback.
     */
    public function getPortfolioGoingOns()
    {
        return Blog::where('status', 1)->where('going_on', 1)->latest()->get();
    }

    /**
     * Get Today's Deal products with optimized querying.
     */
    public function getTodaysDealProducts()
    {
        return $this->storefrontData->todaysDealProducts(20);
    }

    /**
     * Get newest products, with optional pagination for infinite scrolling.
     */
    public function getNewestProducts($limit = 12, $page = null)
    {
        if ($page !== null && is_numeric($page)) {
            $limit = 18;
            $pageNum = max(1, (int)$page);
            $offset = ($pageNum - 1) * $limit;

            return filter_products(Product::latest())
                ->skip($offset)
                ->take($limit)
                ->get();
        }

        return $this->storefrontData->newestProducts($limit);
    }

    /**
     * Get featured preorder products with shop verification requirements.
     */
    public function getPreorderFeaturedProducts()
    {
        if (
            ! Schema::hasTable('preorder_products')
            || ! Schema::hasColumn('preorder_products', 'is_published')
            || ! Schema::hasColumn('preorder_products', 'is_featured')
        ) {
            return collect();
        }

        return Cache::remember('home_preorder_featured_products', 300, fn () => PreorderProduct::publiclyVisible()
                ->where('is_featured', 1)
            ->latest()
            ->limit(12)
            ->get());
    }

    /**
     * Get elite artisan shops for the homepage.
     */
    public function getEliteArtisans()
    {
        return $this->storefrontData->eliteArtisans();
    }

    public function getRecentBestSellers()
    {
        return $this->storefrontData->recentBestSellers();
    }
}
