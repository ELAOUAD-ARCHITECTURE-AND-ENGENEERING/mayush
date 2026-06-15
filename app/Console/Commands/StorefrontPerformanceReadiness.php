<?php

namespace App\Console\Commands;

use Illuminate\Console\Command;

class StorefrontPerformanceReadiness extends Command
{
    protected $signature = 'storefront:performance-readiness';

    protected $description = 'Fail when production-critical storefront performance prerequisites are not ready.';

    public function handle(): int
    {
        $this->info('Checking storefront backend cache readiness...');

        $cacheStore = config('cache.default');
        $this->line('Cache store: '.$cacheStore);

        if (! in_array($cacheStore, ['redis', 'file'], true)) {
            $this->warn('Use Redis or file cache for storefront section caches in production.');
        }

        if (PHP_SAPI !== 'cli' && function_exists('opcache_get_status') && ! opcache_get_status(false)) {
            $this->warn('PHP OPcache is not enabled for this runtime.');
        }

        $this->call('storefront:cache-warm', ['--with-sections' => true]);

        $this->info('Checking storefront image readiness...');

        if ($this->call('images:status', ['--fail-on-hero-missing' => true]) !== self::SUCCESS) {
            return self::FAILURE;
        }

        $this->info('Storefront performance prerequisites are ready.');

        return self::SUCCESS;
    }
}
