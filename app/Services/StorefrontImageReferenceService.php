<?php

namespace App\Services;

use App\Models\BusinessSetting;
use App\Models\Category;
use App\Models\Product;
use Illuminate\Support\Collection;

class StorefrontImageReferenceService
{
    /**
     * @return array<int>
     */
    public function priorityUploadIds(): array
    {
        $ids = collect($this->heroUploadIds());

        $ids = $ids
            ->merge($this->settingUploadIds([
                'header_logo',
                'footer_logo',
                'site_icon',
                'meta_image',
                'home_banner1_images',
                'home_banner2_images',
                'home_banner3_images',
                'home_banner4_images',
            ]))
            ->merge(Category::query()
                ->where(fn ($query) => $query->where('featured', 1)->orWhere('hot_category', 1))
                ->limit(48)
                ->get(['cover_image', 'banner', 'icon'])
                ->flatMap(fn (Category $category) => [$category->cover_image, $category->banner, $category->icon]))
            ->merge(Product::query()
                ->where('published', 1)
                ->where(fn ($query) => $query->where('featured', 1)->orWhere('todays_deal', 1))
                ->latest('id')
                ->limit(64)
                ->pluck('thumbnail_img'));

        return $this->normalizeIds($ids);
    }

    /**
     * @return array<int>
     */
    public function heroUploadIds(): array
    {
        return $this->settingUploadIds(['home_slider_images']);
    }

    /**
     * @param  array<string>  $settingTypes
     * @return array<int>
     */
    private function settingUploadIds(array $settingTypes): array
    {
        $values = BusinessSetting::query()
            ->whereIn('type', $settingTypes)
            ->pluck('value');

        return $this->normalizeIds($values->flatMap(fn ($value) => $this->extractIds($value)));
    }

    /**
     * @return array<int|string>
     */
    private function extractIds(mixed $value): array
    {
        if (is_array($value)) {
            return collect($value)->flatMap(fn ($nested) => $this->extractIds($nested))->all();
        }

        if (is_string($value)) {
            $decoded = json_decode($value, true);

            if (json_last_error() === JSON_ERROR_NONE) {
                return $this->extractIds($decoded);
            }
        }

        return is_numeric($value) ? [(int) $value] : [];
    }

    /**
     * @param  Collection<int, mixed>|array<mixed>  $ids
     * @return array<int>
     */
    private function normalizeIds(Collection|array $ids): array
    {
        return collect($ids)
            ->filter(fn ($id) => is_numeric($id) && (int) $id > 0)
            ->map(fn ($id) => (int) $id)
            ->unique()
            ->values()
            ->all();
    }
}
