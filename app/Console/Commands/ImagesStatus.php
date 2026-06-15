<?php

namespace App\Console\Commands;

use App\Models\ImageOptimizationState;
use App\Services\StorefrontHeroImageService;
use Illuminate\Console\Command;
use Illuminate\Support\Facades\Queue;
use Illuminate\Support\Facades\Schema;
use Throwable;

class ImagesStatus extends Command
{
    protected $signature = 'images:status
                            {--fail-on-hero-missing : Return a failure when an active hero lacks a medium WebP}';

    protected $description = 'Report image optimization state and storefront hero readiness.';

    public function handle(StorefrontHeroImageService $heroes): int
    {
        if (! Schema::hasTable('image_optimization_states')) {
            $this->error('Run migrations before checking image status: image_optimization_states is missing.');

            return self::FAILURE;
        }

        $counts = ImageOptimizationState::query()
            ->selectRaw('status, count(*) as aggregate')
            ->groupBy('status')
            ->pluck('aggregate', 'status');

        try {
            $queueDepth = Queue::size((string) config('image-optimization.queue', 'images'));
        } catch (Throwable) {
            $queueDepth = 'unavailable';
        }

        $validHeroIds = $heroes->validHeroUploads()->pluck('id')->map(fn ($id) => (int) $id)->values()->all();
        $staleHeroIds = $heroes->staleHeroIds();
        $missingHeroDerivativeIds = $heroes->validHeroIdsMissingDerivative('medium');

        $this->table(
            ['Optimized', 'Pending', 'Failed', 'Skipped', 'Missing', 'Queue depth', 'Valid heroes', 'Ignored stale heroes', 'Valid heroes missing medium WebP'],
            [[
                (int) ($counts['optimized'] ?? 0),
                (int) ($counts['pending'] ?? 0),
                (int) ($counts['failed'] ?? 0),
                (int) ($counts['skipped'] ?? 0),
                (int) ($counts['missing'] ?? 0),
                $queueDepth,
                $validHeroIds === [] ? 'none' : implode(', ', $validHeroIds),
                $staleHeroIds === [] ? 'none' : implode(', ', $staleHeroIds),
                $missingHeroDerivativeIds === [] ? 'none' : implode(', ', $missingHeroDerivativeIds),
            ]]
        );

        if (! $this->option('fail-on-hero-missing')) {
            return self::SUCCESS;
        }

        if ($validHeroIds === []) {
            $this->error('Storefront readiness failed: no valid active hero image exists.');

            return self::FAILURE;
        }

        if ($missingHeroDerivativeIds !== []) {
            $this->error('Storefront readiness failed: valid active hero derivatives are missing.');

            return self::FAILURE;
        }

        if ($staleHeroIds !== []) {
            $this->warn('Ignored stale hero IDs with missing upload records or source files: '.implode(', ', $staleHeroIds));
        }

        return self::SUCCESS;
    }
}
