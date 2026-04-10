<?php

namespace App\Services;

use App\Models\Product;
use App\Models\PreorderProduct;
use App\Models\Shop;
use Cache;

class HomeLayoutService
{
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
