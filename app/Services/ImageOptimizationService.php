<?php

namespace App\Services;

use App\Jobs\OptimizeUploadedImageJob;
use App\Models\ImageOptimizationState;
use App\Models\Upload;
use Illuminate\Support\Facades\Cache;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Facades\Schema;
use Illuminate\Support\Facades\Storage;
use Intervention\Image\Facades\Image;
use Throwable;

class ImageOptimizationService
{
    private static ?bool $stateTableAvailable = null;
    private static array $existsCache = [];

    public function diskName(string $sourceKind = 'upload'): string
    {
        if ($sourceKind === 'static') {
            return (string) config('image-optimization.static_disk', 'local');
        }

        return (string) config('image-optimization.disk', config('filesystems.default'));
    }

    public function optimizeUpload(Upload $upload): ImageOptimizationState
    {
        if ($upload->external_link) {
            return $this->recordSkipped($upload->file_name ?: (string) $upload->external_link, 'upload', $upload, 'External image URL');
        }

        return $this->optimizePath((string) $upload->file_name, 'upload', $upload);
    }

    public function optimizeStaticAsset(string $path): ImageOptimizationState
    {
        return $this->optimizePath($path, 'static');
    }

    public function inspectUpload(Upload $upload): array
    {
        if ($upload->external_link) {
            return ['status' => 'skipped', 'reason' => 'External image URL', 'needs_optimization' => false];
        }

        return $this->inspectPath((string) $upload->file_name, 'upload', $upload);
    }

    public function inspectStaticAsset(string $path): array
    {
        return $this->inspectPath($path, 'static');
    }

    public function derivativePath(string $path, ?string $variant = null): string
    {
        $info = pathinfo($path);
        $directory = ($info['dirname'] ?? '.') === '.' ? '' : $info['dirname'].'/';
        $suffix = $variant ? '_'.$variant : '';

        return str_replace('\\', '/', $directory.($info['filename'] ?? $path).$suffix.'.webp');
    }

    public function resolveDerivative(string $path, ?string $variant = null): string
    {
        $candidate = $this->derivativePath($path, $variant);

        return $this->exists($candidate) ? $candidate : $path;
    }

    public function resolveUploadDerivative(Upload $upload, ?string $variant = null): string
    {
        $path = (string) $upload->file_name;
        $candidate = $this->derivativePath($path, $variant);

        if ($this->exists($candidate)) {
            return $candidate;
        }

        $this->requestUploadRepair($upload);

        return $path;
    }

    /**
     * Return real derivatives only. Repeating the original URL at invented
     * widths causes browsers to download an oversized source image.
     *
     * @return array<string, string>
     */
    public function existingUploadDerivatives(Upload $upload, array $variants = []): array
    {
        $path = (string) $upload->file_name;
        $configuredVariants = (array) config('image-optimization.variants', []);
        $variants = $variants ?: array_keys($configuredVariants);
        $derivatives = [];

        foreach ($variants as $variant) {
            if (! array_key_exists($variant, $configuredVariants)) {
                continue;
            }

            $candidate = $this->derivativePath($path, $variant);

            if ($this->exists($candidate)) {
                $derivatives[$variant] = $candidate;
            }
        }

        if ($derivatives === []) {
            $this->requestUploadRepair($upload);
        }

        return $derivatives;
    }

    public function requestUploadRepair(Upload $upload): void
    {
        $path = (string) $upload->file_name;
        $extension = strtolower(pathinfo($path, PATHINFO_EXTENSION));

        if ($path === ''
            || $upload->external_link
            || in_array($extension, ['svg', 'gif'], true)
            || (config('queue.default') === 'sync' && ! app()->runningUnitTests())
            || ! $this->stateTableAvailable()) {
            return;
        }

        if (! Cache::add($this->repairCacheKey($upload->getKey()), true, now()->addMinutes(10))) {
            return;
        }

        OptimizeUploadedImageJob::dispatch($upload->getKey());
    }

    public function repairCacheKey(int $uploadId): string
    {
        return 'image-optimization:repair-upload:'.$uploadId;
    }

    public function clearRepairLock(int $uploadId): void
    {
        Cache::forget($this->repairCacheKey($uploadId));
    }

    private function stateTableAvailable(): bool
    {
        return self::$stateTableAvailable ??= Schema::hasTable('image_optimization_states');
    }

    public function exists(string $path, string $sourceKind = 'upload'): bool
    {
        if ($path === '') {
            return false;
        }

        $diskName = $this->diskName($sourceKind);
        $cacheKey = $diskName.':'.$path;

        if (array_key_exists($cacheKey, self::$existsCache)) {
            return true;
        }

        if (Storage::disk($diskName)->exists($path)) {
            self::$existsCache[$cacheKey] = true;

            return true;
        }

        return false;
    }

    private function inspectPath(string $path, string $sourceKind, ?Upload $upload = null): array
    {
        $extension = strtolower(pathinfo($path, PATHINFO_EXTENSION));
        if ($path === '' || in_array($extension, ['svg', 'gif'], true)) {
            return ['status' => 'skipped', 'reason' => strtoupper($extension ?: 'unknown').' is not optimized', 'needs_optimization' => false];
        }

        $disk = Storage::disk($this->diskName($sourceKind));
        if (!$disk->exists($path)) {
            return ['status' => 'missing', 'reason' => 'Source file is missing', 'needs_optimization' => false];
        }

        $fingerprint = $this->fingerprint($path, $sourceKind);
        $state = ImageOptimizationState::query()
            ->where('source_kind', $sourceKind)
            ->where('disk', $this->diskName($sourceKind))
            ->where('source_path', $path)
            ->first();
        $derivatives = array_merge([null], array_keys((array) config('image-optimization.variants', [])));
        $missingDerivative = collect($derivatives)->contains(fn ($variant) => !$disk->exists($this->derivativePath($path, $variant)));
        $needsOptimization = !$state
            || $state->status !== 'optimized'
            || $state->source_fingerprint !== $fingerprint
            || $state->recipe_version !== (string) config('image-optimization.recipe_version')
            || $missingDerivative;

        return [
            'status' => $needsOptimization ? 'pending' : 'optimized',
            'reason' => $needsOptimization ? 'Derivative generation required' : null,
            'needs_optimization' => $needsOptimization,
            'fingerprint' => $fingerprint,
            'upload_id' => $upload?->id,
        ];
    }

    private function optimizePath(string $path, string $sourceKind, ?Upload $upload = null): ImageOptimizationState
    {
        $inspection = $this->inspectPath($path, $sourceKind, $upload);
        if ($inspection['status'] === 'skipped') {
            return $this->recordSkipped($path, $sourceKind, $upload, $inspection['reason']);
        }

        $state = $this->state($path, $sourceKind, $upload);
        $state->fill([
            'upload_id' => $upload?->id,
            'source_fingerprint' => $inspection['fingerprint'] ?? null,
            'recipe_version' => (string) config('image-optimization.recipe_version'),
            'status' => $inspection['status'],
            'last_error' => $inspection['reason'] ?? null,
            'last_checked_at' => now(),
        ])->save();

        if (!$inspection['needs_optimization']) {
            return $state;
        }

        try {
            $disk = Storage::disk($this->diskName($sourceKind));
            $source = $disk->get($path);
            $this->storeDerivative($path, null, $source, (int) config('image-optimization.max_width', 1500), $sourceKind);

            foreach ((array) config('image-optimization.variants', []) as $variant => $maxWidth) {
                $this->storeDerivative($path, (string) $variant, $source, (int) $maxWidth, $sourceKind);
            }

            $state->fill([
                'status' => 'optimized',
                'last_error' => null,
                'optimized_at' => now(),
            ])->save();
        } catch (Throwable $e) {
            $state->fill([
                'status' => 'failed',
                'last_error' => $e->getMessage(),
            ])->save();
            Log::error('Image derivative generation failed.', [
                'path' => $path,
                'disk' => $this->diskName($sourceKind),
                'error' => $e->getMessage(),
            ]);
            throw $e;
        }

        return $state;
    }

    private function storeDerivative(string $path, ?string $variant, string $source, int $maxWidth, string $sourceKind): void
    {
        $derivativePath = $this->derivativePath($path, $variant);
        if ($derivativePath === $path) {
            return;
        }

        $image = Image::make($source);
        $image->resize($maxWidth, $maxWidth, function ($constraint) {
            $constraint->aspectRatio();
            $constraint->upsize();
        });

        Storage::disk($this->diskName($sourceKind))->put(
            $derivativePath,
            (string) $image->encode('webp', (int) config('image-optimization.quality', 80)),
            [
                'visibility' => 'public',
                'ContentType' => 'image/webp',
                'CacheControl' => 'public, max-age=31536000, immutable',
            ]
        );
    }

    private function fingerprint(string $path, string $sourceKind): string
    {
        $disk = Storage::disk($this->diskName($sourceKind));
        $modified = method_exists($disk, 'lastModified') ? $disk->lastModified($path) : 0;

        return $disk->size($path).':'.$modified;
    }

    private function state(string $path, string $sourceKind, ?Upload $upload = null): ImageOptimizationState
    {
        return ImageOptimizationState::firstOrNew([
            'source_kind' => $sourceKind,
            'disk' => $this->diskName($sourceKind),
            'source_path' => $path,
        ], [
            'upload_id' => $upload?->id,
        ]);
    }

    private function recordSkipped(string $path, string $sourceKind, ?Upload $upload, string $reason): ImageOptimizationState
    {
        $state = $this->state($path, $sourceKind, $upload);
        $state->fill([
            'upload_id' => $upload?->id,
            'recipe_version' => (string) config('image-optimization.recipe_version'),
            'status' => 'skipped',
            'last_error' => $reason,
            'last_checked_at' => now(),
        ])->save();

        return $state;
    }
}
