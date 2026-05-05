<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Models\Blog;
use App\Models\BlogCategory;
use App\Services\Blog\BlogProductMatcherService;
use App\Services\Blog\BlogSettingsService;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Cache;

class BlogApiController extends Controller
{
    public function products(
        Request $request,
        BlogProductMatcherService $productMatcher,
        BlogSettingsService $settings
    ) {
        if (!$settings->boolean('blog_enable_product_embeds')) {
            return response()->json(['success' => true, 'data' => []]);
        }

        $validated = $request->validate([
            'blog_id' => ['nullable', 'integer', 'exists:blogs,id'],
            'category' => ['nullable', 'string', 'max:150'],
            'count' => ['nullable', 'integer', 'min:1', 'max:12'],
            'placement' => ['nullable', 'string', 'max:30'],
        ]);

        $blog = Blog::published()->with(['products'])->find($validated['blog_id'] ?? null);
        $category = empty($validated['category'])
            ? null
            : BlogCategory::where('slug', $validated['category'])->first();

        if (!$blog && !$category) {
            return response()->json(['success' => true, 'data' => []]);
        }

        $count = (int) ($validated['count'] ?? $settings->integer('blog_products_per_embed'));
        $count = min(12, max(1, $count));
        $placement = $validated['placement'] ?? 'manual';
        $cacheMinutes = max(1, $settings->integer('blog_product_embed_cache_minutes') ?: 15);
        $cacheKey = 'blog_api_products:' . implode(':', [optional($blog)->id ?: 'category-' . optional($category)->id, $placement, $count]);

        $data = Cache::remember($cacheKey, now()->addMinutes($cacheMinutes), function () use ($productMatcher, $blog, $category, $placement, $count) {
            $products = $blog
                ? $productMatcher->productsFor($blog, $placement, $count)
                : $productMatcher->productsForCategory($category->id, $count);

            return $products
                ->map(fn ($product) => $this->serializeProduct($product))
                ->values()
                ->all();
        });

        return response()->json(['success' => true, 'data' => $data]);
    }

    private function serializeProduct($product): array
    {
        $price = strip_tags((string) home_discounted_base_price($product));
        $vendorName = optional(optional($product->user)->shop)->name ?: optional($product->user)->name;

        return [
            'id' => $product->id,
            'name' => $product->getTranslation('name'),
            'price' => html_entity_decode(trim($price), ENT_QUOTES, 'UTF-8'),
            'sale_price' => null,
            'vendor_name' => $vendorName,
            'url' => route('product', $product->slug),
            'thumbnail' => uploaded_asset($product->thumbnail_img),
            'badge' => translate('Available on Mayush'),
        ];
    }
}
