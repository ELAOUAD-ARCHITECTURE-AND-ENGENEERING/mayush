<?php

namespace App\Observers;

use App\Models\Inspiration;
use App\Models\InspirationItem;
use App\Services\InspirationCacheService;

class InspirationItemObserver
{
    public function saved(InspirationItem $item): void
    {
        $this->invalidate($item);
    }

    public function deleted(InspirationItem $item): void
    {
        $this->invalidate($item);
    }

    private function invalidate(InspirationItem $item): void
    {
        $inspiration = $item->inspiration
            ?? Inspiration::withTrashed()->find($item->inspiration_id);

        if ($inspiration) {
            app(InspirationCacheService::class)->invalidate($inspiration);
        }
    }
}
