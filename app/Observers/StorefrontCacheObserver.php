<?php

namespace App\Observers;

use App\Services\StorefrontCacheService;

class StorefrontCacheObserver
{
    public function saved(): void
    {
        app(StorefrontCacheService::class)->bump();
    }

    public function deleted(): void
    {
        app(StorefrontCacheService::class)->bump();
    }

    public function restored(): void
    {
        app(StorefrontCacheService::class)->bump();
    }
}
