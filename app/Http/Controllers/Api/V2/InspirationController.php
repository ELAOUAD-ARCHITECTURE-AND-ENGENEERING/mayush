<?php

namespace App\Http\Controllers\Api\V2;

use App\Models\Inspiration;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Cache;

class InspirationController extends Controller
{
    public function index(Request $request)
    {
        $lang = $request->header('Accept-Language', 'fr');

        $inspirations = Inspiration::published()
            ->withCount('items')
            ->with(['items' => function ($q) {
                $q->where('is_visible', true)
                    ->orderBy('display_order')
                    ->limit(4)
                    ->with('product');
            }])
            ->orderBy('sort_order')
            ->get();

        $data = $inspirations->map(function ($insp) use ($lang) {
            return $this->formatPreview($insp, $lang);
        })->values();

        return response()->json(['data' => $data]);
    }

    public function featured(Request $request)
    {
        $lang = $request->header('Accept-Language', 'fr');
        $cacheKey = "inspirations_featured_{$lang}";

        $data = Cache::remember($cacheKey, 900, function () use ($lang) {
            $inspirations = Inspiration::published()
                ->featured()
                ->withCount('items')
                ->with(['items' => function ($q) {
                    $q->where('is_visible', true)
                        ->orderBy('display_order')
                        ->limit(4)
                        ->with('product');
                }])
                ->orderBy('sort_order')
                ->limit(3)
                ->get();

            return $inspirations->map(function ($insp) use ($lang) {
                return $this->formatPreview($insp, $lang);
            })->values();
        });

        return response()->json(['data' => $data]);
    }

    public function show(Request $request, $slug)
    {
        $lang = $request->header('Accept-Language', 'fr');
        $cacheKey = "inspiration_detail_{$slug}_{$lang}";

        $data = Cache::remember($cacheKey, 300, function () use ($slug, $lang) {
            $inspiration = Inspiration::published()
                ->where('slug', $slug)
                ->with(['items' => function ($q) {
                    $q->where('is_visible', true)
                        ->orderBy('display_order')
                        ->with(['product', 'hotspot']);
                }])
                ->firstOrFail();

            return $this->formatDetail($inspiration, $lang);
        });

        return response()->json(['data' => $data]);
    }

    private function formatPreview(Inspiration $insp, string $lang): array
    {
        return [
            'id' => $insp->id,
            'slug' => $insp->slug,
            'title' => $insp->getTitle($lang),
            'subtitle' => $insp->getSubtitle($lang),
            'image' => $insp->hero_image_url,
            'products_count' => $insp->items_count ?? 0,
            'preview_products' => $insp->items
                ->filter(fn ($item) => $item->product !== null)
                ->take(4)
                ->map(fn ($item) => [
                    'id' => $item->product->id,
                    'name' => $item->product->getTranslation('name', $lang),
                    'image' => uploaded_asset($item->product->thumbnail_img),
                    'price' => format_price(convert_price($item->product->unit_price)),
                    'available' => (bool) ($item->product->published && $item->product->approved),
                ])->values()->all(),
        ];
    }

    private function formatDetail(Inspiration $inspiration, string $lang): array
    {
        return [
            'id' => $inspiration->id,
            'slug' => $inspiration->slug,
            'title' => $inspiration->getTitle($lang),
            'subtitle' => $inspiration->getSubtitle($lang),
            'description' => $inspiration->getDescription($lang),
            'image' => [
                'url' => $inspiration->hero_image_url,
                'width' => $inspiration->hero_image_width,
                'height' => $inspiration->hero_image_height,
            ],
            'items' => $inspiration->items
                ->filter(fn ($item) => $item->product !== null)
                ->values()
                ->map(fn ($item) => [
                    'id' => $item->id,
                    'display_order' => $item->display_order,
                    'hotspot' => $item->hotspot ? [
                        'x' => (float) $item->hotspot->x,
                        'y' => (float) $item->hotspot->y,
                    ] : null,
                    'product' => [
                        'id' => $item->product->id,
                        'name' => $item->product->getTranslation('name', $lang),
                        'slug' => $item->product->slug,
                        'price' => format_price(convert_price($item->product->unit_price)),
                        'discount_price' => $item->product->discount > 0
                            ? format_price(convert_price($item->product->unit_price - ($item->product->discount_type === 'percent'
                                ? ($item->product->unit_price * $item->product->discount / 100)
                                : $item->product->discount)))
                            : null,
                        'image' => uploaded_asset($item->product->thumbnail_img),
                        'available' => (bool) ($item->product->published && $item->product->approved),
                        'stock_status' => $item->product->current_stock > 0 ? 'in_stock' : 'out_of_stock',
                    ],
                ])->all(),
        ];
    }
}
