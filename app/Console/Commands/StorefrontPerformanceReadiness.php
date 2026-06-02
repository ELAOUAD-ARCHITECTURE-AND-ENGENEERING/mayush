<?php

namespace App\Console\Commands;

use Illuminate\Console\Command;

class StorefrontPerformanceReadiness extends Command
{
    protected $signature = 'storefront:performance-readiness';

    protected $description = 'Fail when production-critical storefront performance prerequisites are not ready.';

    public function handle(): int
    {
        $this->info('Checking storefront image readiness...');

        if ($this->call('images:status', ['--fail-on-hero-missing' => true]) !== self::SUCCESS) {
            return self::FAILURE;
        }

        $this->info('Storefront performance prerequisites are ready.');

        return self::SUCCESS;
    }
}
