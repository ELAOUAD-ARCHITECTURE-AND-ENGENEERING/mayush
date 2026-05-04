<?php

namespace App\Services\Blog;

use App\Services\SeoService;
use Illuminate\Support\Collection;

class BlogSchemaService
{
    public function productSchemas(Collection $products): array
    {
        return $products
            ->filter()
            ->map(function ($product) {
                $product->loadMissing(['brand', 'reviews', 'user.shop']);

                return SeoService::productSchema($product, 'InStock');
            })
            ->values()
            ->all();
    }
}
