<?php

namespace App\Observers;

use App\Models\Inspiration;
use App\Models\InspirationHotspot;
use App\Services\InspirationCacheService;

class InspirationHotspotObserver
{
    public function saved(InspirationHotspot $hotspot): void
    {
        $this->invalidate($hotspot);
    }

    public function deleted(InspirationHotspot $hotspot): void
    {
        $this->invalidate($hotspot);
    }

    private function invalidate(InspirationHotspot $hotspot): void
    {
        $inspiration = $hotspot->inspiration
            ?? Inspiration::withTrashed()->find($hotspot->inspiration_id);

        if ($inspiration) {
            app(InspirationCacheService::class)->invalidate($inspiration);
        }
    }
}
