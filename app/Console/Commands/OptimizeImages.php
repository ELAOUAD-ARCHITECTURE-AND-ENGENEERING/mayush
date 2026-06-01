<?php

namespace App\Console\Commands;

use Illuminate\Console\Command;

class OptimizeImages extends Command
{
    protected $signature = 'images:optimize
                            {--dry-run : Audit without queueing repairs}
                            {--limit= : Maximum upload records to inspect}
                            {--include-static : Include configured storefront static assets}';

    protected $description = 'Compatibility wrapper for the non-destructive image audit and repair pipeline.';

    public function handle(): int
    {
        $this->warn('images:optimize now preserves originals and queues WebP derivative repairs.');

        $arguments = [
            '--limit' => $this->option('limit') ?: config('image-optimization.audit_limit', 500),
            '--include-static' => (bool) $this->option('include-static'),
        ];
        if (!$this->option('dry-run')) {
            $arguments['--repair'] = true;
        }

        return $this->call('images:audit', $arguments);
    }
}
