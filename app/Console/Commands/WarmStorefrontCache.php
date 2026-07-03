<?php

namespace App\Console\Commands;

use App\Services\HomeLayoutService;
use App\Services\StorefrontCacheService;
use Illuminate\Console\Command;

class WarmStorefrontCache extends Command
{
    protected $signature = 'storefront:cache-warm';

    protected $description = 'Warm session-independent storefront homepage data caches.';

    public function handle(HomeLayoutService $layouts, StorefrontCacheService $cache): int
    {
        $layouts->getHomepageData();

        $this->info('Storefront cache warmed at revision '.$cache->revision().'.');

        return self::SUCCESS;
    }
}
