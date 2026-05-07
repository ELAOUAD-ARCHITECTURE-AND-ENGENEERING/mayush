<?php

namespace App\Services;

use App\Models\Blog;
use App\Models\Category;
use App\Models\Product;
use App\Models\PreorderProduct;
use App\Models\Shop;
use Cache;
use Carbon\Carbon;

class HomeLayoutService
{
    /**
     * Assemble data required by the homepage shell.
     */
    public function getHomepageData(): array
    {
        $lang = get_system_language() ? get_system_language()->code : null;

        return [
            'featured_categories' => Cache::rememberForever('featured_categories', function () {
                return Category::with('bannerImage')->where('featured', 1)->get();
            }),
            'hot_categories' => Cache::rememberForever('hot_categories', function () {
                return Category::with('bannerImage')->where('hot_category', '1')->get();
            }),
            'latest_blogs' => Cache::remember('home_latest_blogs', 900, function () {
                return Blog::published()
                    ->with(['category', 'translations'])
                    ->orderBy('published_at', 'desc')
                    ->orderBy('created_at', 'desc')
                    ->take(3)
                    ->get();
            }),
            'lang' => $lang,
        ];
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
        return filter_products(Product::where('todays_deal', '1'))->orderBy('id', 'desc')->get();
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

        return Cache::remember('newest_products', 3600, function () use ($limit) {
            return filter_products(Product::latest())->take($limit)->get();
        });
    }

    /**
     * Get featured preorder products with shop verification requirements.
     */
    public function getPreorderFeaturedProducts()
    {
        return PreorderProduct::where('is_published', 1)->where('is_featured', 1)
            ->where(function ($query) {
                $query->whereHas('user', function ($q) {
                    $q->where('user_type', 'admin');
                })->orWhereHas('user.shop', function ($q) {
                    $q->where('verification_status', 1);
                });
            })
            ->latest()
            ->limit(12)
            ->get();
    }

    /**
     * Get elite artisan shops for the homepage.
     */
    public function getEliteArtisans()
    {
        return Shop::whereHas('activeEliteSubscription')
            ->where('verification_status', 1)
            ->get();
    }
}
