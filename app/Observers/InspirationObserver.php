<?php

namespace App\Observers;

use App\Models\Inspiration;
use App\Services\InspirationCacheService;
use Illuminate\Support\Facades\Storage;

class InspirationObserver
{
    public function saved(Inspiration $inspiration): void
    {
        app(InspirationCacheService::class)->invalidate(
            $inspiration,
            $inspiration->getOriginal('slug')
        );

        if ($inspiration->wasChanged('hero_image')) {
            $this->deleteImage($inspiration->getOriginal('hero_image'));
        }
    }

    public function deleted(Inspiration $inspiration): void
    {
        app(InspirationCacheService::class)->invalidate($inspiration);

        if ($inspiration->isForceDeleting()) {
            $this->deleteImage($inspiration->hero_image);
        }
    }

    public function restored(Inspiration $inspiration): void
    {
        app(InspirationCacheService::class)->invalidate($inspiration);
    }

    private function deleteImage(?string $path): void
    {
        if ($path && Storage::disk('public')->exists($path)) {
            Storage::disk('public')->delete($path);
        }
    }
}
