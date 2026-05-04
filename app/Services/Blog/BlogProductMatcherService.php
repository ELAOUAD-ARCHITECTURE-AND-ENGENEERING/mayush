<?php

namespace App\Services\Blog;

use App\Models\Blog;
use App\Models\Product;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Support\Collection;

class BlogProductMatcherService
{
    public function productsFor(Blog $blog, string $placement = 'manual', int $count = 4): Collection
    {
        $placement = $placement ?: 'manual';
        $count = max(1, $count);

        $manualProducts = $this->safeManualProducts($blog, $placement, $count);
        if ($manualProducts->isNotEmpty()) {
            return $manualProducts;
        }

        $categoryProducts = $this->categoryFallbackProducts($blog, $count);
        if ($categoryProducts->isNotEmpty()) {
            return $categoryProducts;
        }

        $featuredProducts = $this->safeProductQuery()
            ->where('featured', 1)
            ->orderBy('created_at', 'desc')
            ->limit($count)
            ->get();

        if ($featuredProducts->isNotEmpty()) {
            return $featuredProducts;
        }

        return $this->safeProductQuery()
            ->orderBy('num_of_sale', 'desc')
            ->orderBy('created_at', 'desc')
            ->limit($count)
            ->get();
    }

    public function safeProductQuery(): Builder
    {
        return Product::query()
            ->with(['thumbnail', 'user.shop', 'stocks', 'taxes'])
            ->isApprovedPublished()
            ->where('digital', 0)
            ->where('auction_product', 0)
            ->where(function (Builder $query) {
                $query->whereColumn('current_stock', '>=', 'min_qty')
                    ->orWhereHas('stocks', function (Builder $stockQuery) {
                        $stockQuery->where('qty', '>', 0);
                    });
            });
    }

    private function safeManualProducts(Blog $blog, string $placement, int $count): Collection
    {
        if (!$blog->exists) {
            return collect();
        }

        return $blog->products()
            ->wherePivot('placement', $placement)
            ->whereIn('products.id', $this->safeProductQuery()->select('products.id'))
            ->limit($count)
            ->get();
    }

    private function categoryFallbackProducts(Blog $blog, int $count): Collection
    {
        if (!$blog->category_id) {
            return collect();
        }

        return $this->safeProductQuery()
            ->where(function (Builder $query) use ($blog) {
                $query->where('category_id', $blog->category_id)
                    ->orWhereHas('categories', function (Builder $categoryQuery) use ($blog) {
                        $categoryQuery->where('categories.id', $blog->category_id);
                    });
            })
            ->orderBy('created_at', 'desc')
            ->limit($count)
            ->get();
    }
}
