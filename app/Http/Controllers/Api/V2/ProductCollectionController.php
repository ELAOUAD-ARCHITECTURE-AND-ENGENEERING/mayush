<?php

namespace App\Http\Controllers\Api\V2;

use App\Models\Category;
use App\Models\ProductCollection;
use App\Services\ProductCollectionService;

class ProductCollectionController extends Controller
{
    /**
     * GET /api/v2/product-collections
     * Returns published collections with hero images and product counts for the mobile home screen or category landing.
     * Supports optional category_id or category_slug query parameters.
     * Falls back to the first linked category's cover_image or banner when hero_image is missing.
     */
    public function index(\Illuminate\Http\Request $request, ProductCollectionService $service)
    {
        $categoryId = $request->query('category_id');
        $categorySlug = $request->query('category_slug');

        if ($categorySlug && !$categoryId) {
            $matchedCat = Category::where('slug', $categorySlug)->first();
            if ($matchedCat) {
                $categoryId = $matchedCat->id;
            }
        }

        $collections = ProductCollection::published()
            ->orderBy('id')
            ->get();

        if ($categoryId) {
            $catIdInt = (int)$categoryId;
            $childCatIds = Category::where('parent_id', $catIdInt)->pluck('id')->toArray();
            $targetCatIds = array_merge([$catIdInt], $childCatIds);

            $collections = $collections->filter(function ($collection) use ($targetCatIds) {
                $rawCatIds = $collection->category_ids;
                if (is_string($rawCatIds)) {
                    $rawCatIds = json_decode($rawCatIds, true) ?: [];
                }
                $catIdsInt = array_map('intval', (array)$rawCatIds);
                return !empty(array_intersect($targetCatIds, $catIdsInt));
            });
        }

        $mapped = $collections->map(function ($collection) use ($service) {
            $image = uploaded_asset($collection->hero_image);
            $isPlaceholder = !$image || str_contains($image, 'placeholder');

            $rawCatIds = $collection->category_ids;
            if (is_string($rawCatIds)) {
                $rawCatIds = json_decode($rawCatIds, true) ?: [];
            }
            $catIdsArray = array_map('intval', (array)$rawCatIds);

            // Fall back to linked category cover_image, banner, or parent category image
            if ($isPlaceholder) {
                foreach ($catIdsArray as $catId) {
                    $image = self::resolveImageFromCategory(Category::find($catId));
                    if ($image) break;
                }
            }

            $productsCount = 0;
            try {
                $productsCount = $service->query($collection)->count();
            } catch (\Throwable) {
                $productsCount = 0;
            }

            return [
                'id'             => $collection->id,
                'name'           => $collection->name,
                'slug'           => $collection->slug,
                'description'    => $collection->description,
                'hero_image'     => $image ?: '',
                'products_count' => $productsCount,
                'category_ids'   => $catIdsArray,
            ];
        })->values();

        return response()->json(['data' => $mapped]);
    }

    private static function resolveImageFromCategory(?Category $cat): ?string
    {
        if (!$cat) return null;

        $cover = uploaded_asset($cat->cover_image);
        if ($cover && !str_contains($cover, 'placeholder')) return $cover;

        $banner = uploaded_asset($cat->banner);
        if ($banner && !str_contains($banner, 'placeholder')) return $banner;

        // Try parent category
        if ($cat->parent_id) {
            return self::resolveImageFromCategory(Category::find($cat->parent_id));
        }

        return null;
    }
}
