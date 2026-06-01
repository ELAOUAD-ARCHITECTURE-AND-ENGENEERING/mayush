<?php

namespace App\Services;

use App\Models\LastViewedProduct;
use App\Models\Product;
use App\Models\ProductCollection;
use App\Utility\CategoryUtility;
use Illuminate\Database\Eloquent\Builder;

class ProductCollectionService
{
    public function query(ProductCollection $collection): Builder
    {
        $manualIds = $collection->products()->pluck('products.id')->all();

        if ($collection->mode === 'manual') {
            return filter_products(Product::query()->whereIn('products.id', $manualIds));
        }

        $query = Product::query();

        if ($collection->mode === 'hybrid' && $manualIds !== []) {
            $query->where(function (Builder $query) use ($collection, $manualIds) {
                $query->whereIn('products.id', $manualIds)
                    ->orWhere(function (Builder $query) use ($collection) {
                        $this->applyDynamicRules($query, $collection);
                    });
            });
        } else {
            $this->applyDynamicRules($query, $collection);
        }

        return filter_products($query);
    }

    public function applyRequestFilters(Builder $query, array $filters): Builder
    {
        if (!empty($filters['keyword'])) {
            $term = trim($filters['keyword']);
            $query->where(function (Builder $query) use ($term) {
                $query->where('name', 'like', '%' . $term . '%')
                    ->orWhere('tags', 'like', '%' . $term . '%');
            });
        }

        if (!empty($filters['brand'])) {
            $query->where('brand_id', (int) $filters['brand']);
        }

        if ($filters['min_price'] !== null && $filters['min_price'] !== '') {
            $query->where('unit_price', '>=', (float) $filters['min_price']);
        }

        if ($filters['max_price'] !== null && $filters['max_price'] !== '') {
            $query->where('unit_price', '<=', (float) $filters['max_price']);
        }

        return $query;
    }

    public function applySort(Builder $query, string $sort): Builder
    {
        return match ($sort) {
            'popular' => $query->orderByDesc('num_of_sale'),
            'price-asc' => $query->orderBy('unit_price'),
            'price-desc' => $query->orderByDesc('unit_price'),
            'oldest' => $query->orderBy('created_at'),
            default => $query->orderByDesc('created_at'),
        };
    }

    public function bestSelling(ProductCollection $collection, int $limit = 12)
    {
        return $this->query($collection)
            ->orderByDesc('num_of_sale')
            ->limit($limit)
            ->get();
    }

    public function recentlyViewed(ProductCollection $collection, ?int $userId, int $limit = 12)
    {
        if (!$userId) {
            return collect();
        }

        $viewedIds = LastViewedProduct::where('user_id', $userId)
            ->orderByDesc('updated_at')
            ->pluck('product_id');

        if ($viewedIds->isEmpty()) {
            return collect();
        }

        $products = $this->query($collection)
            ->whereIn('products.id', $viewedIds)
            ->get()
            ->keyBy('id');

        return $viewedIds->map(fn ($id) => $products->get($id))
            ->filter()
            ->take($limit)
            ->values();
    }

    private function applyDynamicRules(Builder $query, ProductCollection $collection): void
    {
        $categoryIds = collect($collection->category_ids ?: [])
            ->map(fn ($id) => (int) $id)
            ->filter()
            ->flatMap(fn ($id) => array_merge([$id], CategoryUtility::children_ids($id)))
            ->unique()
            ->values()
            ->all();

        if ($categoryIds !== []) {
            $query->where(function (Builder $query) use ($categoryIds) {
                $query->whereIn('category_id', $categoryIds)
                    ->orWhereHas('categories', fn (Builder $query) => $query->whereIn('categories.id', $categoryIds));
            });
        }

        if ($collection->brand_ids) {
            $query->whereIn('brand_id', $collection->brand_ids);
        }

        if ($collection->seller_ids) {
            $query->whereIn('user_id', $collection->seller_ids);
        }

        if ($collection->min_price !== null) {
            $query->where('unit_price', '>=', $collection->min_price);
        }

        if ($collection->max_price !== null) {
            $query->where('unit_price', '<=', $collection->max_price);
        }

        foreach (array_filter(array_map('trim', explode(',', (string) $collection->tags))) as $tag) {
            $query->where('tags', 'like', '%' . $tag . '%');
        }
    }
}
