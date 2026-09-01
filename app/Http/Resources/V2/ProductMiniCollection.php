<?php

namespace App\Http\Resources\V2;

use Illuminate\Http\Resources\Json\ResourceCollection;

class ProductMiniCollection extends ResourceCollection
{
    public function toArray($request)
    {
        return [
            'data' => $this->collection->map(function ($data) {
                $wholesale_product =
                    ($data->wholesale_product == 1) ? true : false;
                return [
                    'id' => $data->id,
                    'slug' => $data->slug,
                    'name' => $data->getTranslation('name'),
                    'slug' => $data->slug,
                    'thumbnail_image' => self::resolveProductImage($data),
                    'base_price' => (float) home_base_price($data, false),
                    'base_discounted_price' => (float) home_discounted_base_price($data, false),
                    'has_discount' => home_base_price($data, false) != home_discounted_base_price($data, false),
                    'discount' => "-" . discount_in_percentage($data) . "%",
                    'stroked_price' => home_base_price($data),
                    'main_price' => home_discounted_base_price($data),
                    'rating' => (float) $data->rating,
                    'review_count' => $data->reviews ? $data->reviews->count() : 0,
                    'sales' => (int) $data->num_of_sale,
                    'is_wholesale' => $wholesale_product,
                    'links' => [
                        'details' => route('api.products.show', $data->id),
                    ]
                ];
            })
        ];
    }

    public function with($request)
    {
        return [
            'success' => true,
            'status' => 200
        ];
    }

    /**
     * Resolve a real image URL for a product.
     * Returns the thumbnail if the file exists on disk, otherwise tries gallery photos,
     * and returns null if no image is available (so the mobile app can show a branded placeholder).
     */
    private static function resolveProductImage($product): ?string
    {
        $url = uploaded_asset($product->thumbnail_img);
        if ($url && !str_contains($url, 'placeholder')) {
            return $url;
        }

        // Try gallery photos
        $photos = is_string($product->photos)
            ? json_decode($product->photos, true)
            : $product->photos;
        if (is_array($photos)) {
            foreach ($photos as $photoId) {
                if (!is_numeric($photoId)) continue;
                $photoUrl = uploaded_asset($photoId);
                if ($photoUrl && !str_contains($photoUrl, 'placeholder')) {
                    return $photoUrl;
                }
            }
        }

        return null;
    }
}
