<?php

namespace App\Http\Resources\V2;

use Illuminate\Http\Resources\Json\JsonResource;

class InspirationDetailResource extends JsonResource
{
    public function toArray($request): array
    {
        $lang = $this->language($request);

        return [
            'id' => $this->id,
            'slug' => $this->slug,
            'title' => $this->getTitle($lang),
            'subtitle' => $this->getSubtitle($lang),
            'description' => $this->getDescription($lang),
            'image' => [
                'url' => $this->hero_image_url,
                'width' => $this->hero_image_width,
                'height' => $this->hero_image_height,
            ],
            'items' => $this->items
                ->filter(fn ($item) => $item->is_visible && $item->product)
                ->values()
                ->map(function ($item) use ($lang) {
                    $product = $item->product;
                    $basePrice = (float) home_base_price($product, false);
                    $discountedPrice = (float) home_discounted_base_price($product, false);
                    $custom = $lang === 'ar' ? $item->custom_title_ar : $item->custom_title_fr;

                    return [
                        'id' => $item->id,
                        'display_order' => $item->display_order,
                        'hotspot' => $item->hotspot ? [
                            'x' => (float) $item->hotspot->x,
                            'y' => (float) $item->hotspot->y,
                        ] : null,
                        'product' => [
                            'id' => $product->id,
                            'name' => $custom ?: $product->getTranslation('name', $lang),
                            'slug' => $product->slug,
                            'price' => format_price($basePrice),
                            'discount_price' => $discountedPrice < $basePrice
                                ? format_price($discountedPrice)
                                : null,
                            'image' => uploaded_asset($product->thumbnail_img),
                            'available' => $product->isAvailable(),
                            'stock_status' => $product->stockStatus(),
                            'rating' => (float) $product->rating,
                            'review_count' => (int) ($product->reviews_count ?? 0),
                            'sales' => (int) $product->num_of_sale,
                            'links' => [
                                'details' => route('api.products.show', $product->id),
                            ],
                        ],
                    ];
                })->all(),
        ];
    }

    private function language($request): string
    {
        $header = strtolower((string) $request->header(
            'App-Language',
            $request->header('Accept-Language', 'fr')
        ));

        return str_starts_with($header, 'ar') ? 'ar' : 'fr';
    }
}
