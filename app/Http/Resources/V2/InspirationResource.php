<?php

namespace App\Http\Resources\V2;

use Illuminate\Http\Resources\Json\JsonResource;

class InspirationResource extends JsonResource
{
    public function toArray($request): array
    {
        $lang = $this->language($request);
        $items = $this->relationLoaded('items')
            ? $this->items->filter(fn ($item) => $item->is_visible && $item->product)
            : collect();

        return [
            'id' => $this->id,
            'slug' => $this->slug,
            'title' => $this->getTitle($lang),
            'subtitle' => $this->getSubtitle($lang),
            'image' => $this->hero_image_url,
            'products_count' => $items->count(),
            'preview_products' => $items->take(4)->map(function ($item) use ($lang) {
                $product = $item->product;

                return [
                    'id' => $product->id,
                    'slug' => $product->slug,
                    'name' => $this->itemTitle($item, $lang),
                    'image' => uploaded_asset($product->thumbnail_img),
                    'price' => home_discounted_base_price($product),
                    'available' => $product->isAvailable(),
                    'rating' => (float) $product->rating,
                    'review_count' => (int) ($product->reviews_count ?? 0),
                    'sales' => (int) $product->num_of_sale,
                    'links' => [
                        'details' => route('api.products.show', $product->id),
                    ],
                ];
            })->values()->all(),
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

    private function itemTitle($item, string $lang): string
    {
        $custom = $lang === 'ar' ? $item->custom_title_ar : $item->custom_title_fr;

        return $custom ?: $item->product->getTranslation('name', $lang);
    }
}
