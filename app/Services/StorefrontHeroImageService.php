<?php

namespace App\Services;

use App\Models\Upload;
use Illuminate\Support\Collection;

class StorefrontHeroImageService
{
    public function __construct(private readonly ImageOptimizationService $optimizer)
    {
    }

    /**
     * @return array<int>
     */
    public function configuredHeroIds(?string $lang = null): array
    {
        $value = get_setting('home_slider_images', null, $lang);
        $decoded = is_string($value) ? json_decode($value, true) : $value;

        return collect(is_array($decoded) ? $decoded : [])
            ->filter(fn ($id) => is_numeric($id) && (int) $id > 0)
            ->map(fn ($id) => (int) $id)
            ->values()
            ->all();
    }

    public function firstValidHero(?string $lang = null): ?Upload
    {
        return $this->validHeroUploads($lang)->first();
    }

    /**
     * Return valid uploads keyed by their original slider setting index.
     *
     * @return Collection<int, Upload>
     */
    public function validHeroUploads(?string $lang = null): Collection
    {
        return $this->validSliderUploads($this->configuredHeroIds($lang));
    }

    /**
     * Return valid uploads keyed by their original slider setting index.
     *
     * @param  array<mixed>  $ids
     * @return Collection<int, Upload>
     */
    public function validSliderUploads(array $ids): Collection
    {
        $idsByIndex = collect($ids)
            ->filter(fn ($id) => is_numeric($id) && (int) $id > 0)
            ->map(fn ($id) => (int) $id);

        if ($idsByIndex->isEmpty()) {
            return collect();
        }

        $uploads = Upload::query()
            ->whereIn('id', $idsByIndex->unique()->values()->all())
            ->get()
            ->keyBy('id');

        return $idsByIndex
            ->map(fn (int $id) => $uploads->get($id))
            ->filter(fn ($upload) => $upload instanceof Upload && $this->isUsable($upload));
    }

    /**
     * @return array<int>
     */
    public function staleHeroIds(?string $lang = null): array
    {
        $ids = $this->configuredHeroIds($lang);
        $validIds = $this->validSliderUploads($ids)
            ->pluck('id')
            ->map(fn ($id) => (int) $id)
            ->all();

        return collect($ids)
            ->reject(fn (int $id) => in_array($id, $validIds, true))
            ->unique()
            ->values()
            ->all();
    }

    /**
     * @return array<int>
     */
    public function validHeroIdsMissingDerivative(string $variant = 'medium', ?string $lang = null): array
    {
        return $this->validHeroUploads($lang)
            ->filter(fn (Upload $upload) => ! $upload->external_link)
            ->filter(fn (Upload $upload) => ! $this->optimizer->exists(
                $this->optimizer->derivativePath((string) $upload->file_name, $variant)
            ))
            ->pluck('id')
            ->map(fn ($id) => (int) $id)
            ->values()
            ->all();
    }

    public function isUsable(Upload $upload): bool
    {
        if ($upload->external_link) {
            return true;
        }

        if (! str_contains((string) $upload->type, 'image')) {
            return false;
        }

        $path = str_replace('\\', '/', (string) $upload->file_name);
        if ($this->optimizer->exists($path)) {
            return true;
        }

        $prefixedPath = 'uploads/all/'.basename($path);

        return $prefixedPath !== $path && $this->optimizer->exists($prefixedPath);
    }
}
