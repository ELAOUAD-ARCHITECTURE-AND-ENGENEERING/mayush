<?php

namespace App\Services;

use App\Models\Category;
use App\Models\CustomAlert;
use App\Models\CustomSaleAlert;
use App\Models\DynamicPopup;
use App\Models\FlashDeal;
use App\Models\FlashDealProduct;
use App\Models\Product;
use App\Models\ProductCollection;
use App\Models\Shop;
use App\Models\TopBanner;
use App\Models\Upload;
use Illuminate\Support\Facades\App;
use Illuminate\Support\Facades\Cache;
use Illuminate\Support\Facades\Schema;
use Illuminate\Support\Facades\Session;

class StorefrontDataService
{
    public function __construct(private readonly StorefrontCacheService $cache)
    {
    }

    public function topBanners()
    {
        return $this->remember('top-banners', 900, fn () =>
            TopBanner::where('status', 1)->orderBy('id', 'desc')->get()
        );
    }

    public function customAlerts(string $order)
    {
        return $this->remember('custom-alerts', 900, fn () =>
            CustomAlert::where('status', 1)->orderBy('id', $order)->get(),
            [$order]
        );
    }

    public function dynamicPopups()
    {
        return $this->remember('dynamic-popups', 900, fn () =>
            DynamicPopup::where('status', 1)->orderBy('id', 'asc')->get()
        );
    }

    public function saleAlertProducts()
    {
        return $this->remember('sale-alert-products', 900, function () {
            return CustomSaleAlert::with('product')->get()->map(function ($alert) {
                if (! $alert->product || ! $alert->product->isPubliclyVisible()) {
                    return null;
                }

                return [
                    'id' => $alert->product->id,
                    'title' => $alert->product->getTranslation('name'),
                    'image' => uploaded_asset($alert->product->thumbnail_img, 'small'),
                    'url' => route('product', $alert->product->slug),
                ];
            })->filter()->values();
        });
    }

    public function activeFlashDeals()
    {
        return $this->remember('active-flash-deals', 300, fn () =>
            FlashDeal::active()
                ->has('flash_deal_products')
                ->withCount('flash_deal_products')
                ->get()
        );
    }

    public function featuredFlashDeal()
    {
        return $this->remember('featured-flash-deal', 300, function () {
            $now = strtotime(date('Y-m-d H:i:s'));

            return FlashDeal::query()
                ->isActiveAndFeatured()
                ->where('start_date', '<=', $now)
                ->where('end_date', '>=', $now)
                ->first();
        });
    }

    public function flashDealProducts(int $flashDealId, int $limit = 10)
    {
        return $this->remember('flash-deal-products', 300, fn () =>
            FlashDealProduct::with(['product' => fn ($query) => $this->withProductCardRelations($query)])
                ->where('flash_deal_id', $flashDealId)
                ->orderBy('id', 'desc')
                ->get(),
            [$flashDealId, $limit]
        )->filter(fn ($flashDealProduct) => $flashDealProduct->product?->isPubliclyVisible())->take($limit)->values();
    }

    public function featuredProducts(int $limit = 12)
    {
        return $this->remember('featured-products', 900, fn () =>
            $this->withProductCardRelations(filter_products(Product::query()->where('featured', '1')))
                ->latest()
                ->limit($limit)
                ->get(),
            [$limit]
        );
    }

    public function bestSellingProducts(int $limit = 20, ?int $userId = null)
    {
        return $this->remember('best-selling-products', 900, function () use ($limit, $userId) {
            $query = Product::query();

            if ($userId) {
                $query->where('user_id', $userId);
            }

            return $this->withProductCardRelations(filter_products($query->orderBy('num_of_sale', 'desc')))
                ->limit($limit)
                ->get();
        }, [$limit, $userId]);
    }

    public function todaysDealProducts(int $limit = 20, ?int $userId = null)
    {
        return $this->remember('todays-deal-products', 900, function () use ($limit, $userId) {
            $query = Product::query()->where('todays_deal', '1');

            if ($userId) {
                $query->where('user_id', $userId);
            }

            return $this->withProductCardRelations(filter_products($query))
                ->orderBy('id', 'desc')
                ->limit($limit)
                ->get();
        }, [$limit, $userId]);
    }

    public function newestProducts(int $limit = 12)
    {
        return $this->remember('newest-products', 900, fn () =>
            $this->withProductCardRelations(filter_products(Product::query()->latest()))
                ->take($limit)
                ->get(),
            [$limit]
        );
    }

    public function productCollection(?int $id): ?ProductCollection
    {
        if (! $id) {
            return null;
        }

        return $this->remember('product-collection', 900, fn () =>
            ProductCollection::published()->find($id),
            [$id]
        );
    }

    public function sliderImages(array $ids)
    {
        $ids = array_values(array_filter(array_map('intval', $ids)));

        if ($ids === []) {
            return collect();
        }

        return $this->remember('slider-images', 900, function () use ($ids) {
            $sliders = Upload::query()->whereIn('id', $ids);

            foreach ($ids as $id) {
                $sliders->orderByRaw('id!=?', [$id]);
            }

            return $sliders->get();
        }, $ids);
    }

    public function categories(array $ids)
    {
        $ids = array_values(array_filter(array_map('intval', $ids)));

        if ($ids === []) {
            return collect();
        }

        return $this->remember('categories', 900, fn () =>
            Category::query()
                ->with(['coverImage', 'bannerImage', 'catIcon', 'categories'])
                ->whereIn('id', $ids)
                ->get(),
            $ids
        );
    }

    public function levelZeroCategories()
    {
        return $this->remember('level-zero-categories', 900, fn () =>
            Category::query()
                ->with(['coverImage', 'catIcon', 'childrenCategories', 'childrenCategories.childrenCategories'])
                ->where('level', 0)
                ->orderBy('order_level', 'desc')
                ->get()
        );
    }

    public function categoryProducts(int $categoryId, int $limit = 5)
    {
        return $this->remember('category-products', 900, fn () =>
            $this->withProductCardRelations(filter_products(Product::where('category_id', $categoryId)))
                ->latest()
                ->take($limit)
                ->get(),
            [$categoryId, $limit]
        );
    }

    public function promotedCategoryProducts(array $categoryIds, int $limit = 12)
    {
        $categoryIds = array_values(array_filter(array_map('intval', $categoryIds)));

        if ($categoryIds === []) {
            return collect();
        }

        return $this->remember('promoted-category-products', 900, function () use ($categoryIds, $limit) {
            $discounted = $this->withProductCardRelations(filter_products(Product::whereIn('category_id', $categoryIds)))
                ->where('discount', '>', 0)
                ->latest()
                ->limit($limit)
                ->get();

            if ($discounted->isNotEmpty()) {
                return $discounted;
            }

            return $this->withProductCardRelations(filter_products(Product::whereIn('category_id', $categoryIds)))
                ->latest()
                ->limit($limit)
                ->get();
        }, array_merge($categoryIds, [$limit]));
    }

    public function eliteArtisans()
    {
        return $this->remember('elite-artisans', 900, fn () =>
            Shop::publiclyVisible()
                ->whereHas('activeEliteSubscription')
                ->get()
        );
    }

    public function recentBestSellers()
    {
        return $this->remember('recent-best-sellers', 300, fn () =>
            Shop::publiclyVisible()
                ->whereIn('user_id', function ($query) {
                    $query->select('seller_id')
                        ->from('order_details')
                        ->where('created_at', '>=', now()->subDays(3))
                        ->groupBy('seller_id')
                        ->havingRaw('COUNT(*) > 10');
                })
                ->orderBy('num_of_sale', 'desc')
                ->take(20)
                ->get()
        );
    }

    private function remember(string $name, int $ttlSeconds, callable $callback, array $parts = [])
    {
        return Cache::remember($this->key($name, $parts), $ttlSeconds, $callback);
    }

    private function withProductCardRelations($query)
    {
        $relations = ['stocks'];

        static $hasAuctionBidsTable = null;
        $hasAuctionBidsTable ??= Schema::hasTable('auction_product_bids');

        if ($hasAuctionBidsTable) {
            $relations['bids'] = fn ($bidQuery) => $bidQuery->select('id', 'product_id', 'amount');
        }

        return $query->with($relations);
    }

    private function key(string $name, array $parts = []): string
    {
        $locale = App::getLocale();
        $currency = Session::get('currency_code', 'default');
        $revision = $this->cache->revision();
        $suffix = $parts === [] ? '' : ':'.md5(json_encode($parts));

        return "storefront:v{$revision}:{$locale}:{$currency}:{$name}{$suffix}";
    }
}
