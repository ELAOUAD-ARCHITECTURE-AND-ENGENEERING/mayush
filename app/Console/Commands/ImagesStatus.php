<?php

namespace App\Console\Commands;

use App\Models\ImageOptimizationState;
use App\Models\Upload;
use App\Services\ImageOptimizationService;
use App\Services\StorefrontImageReferenceService;
use Illuminate\Console\Command;
use Illuminate\Support\Facades\Queue;
use Illuminate\Support\Facades\Schema;
use Throwable;

class ImagesStatus extends Command
{
    protected $signature = 'images:status
                            {--fail-on-hero-missing : Return a failure when an active hero lacks a medium WebP}';

    protected $description = 'Report image optimization state and storefront hero readiness.';

    public function handle(ImageOptimizationService $optimizer, StorefrontImageReferenceService $references): int
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

        $missingHeroIds = Upload::query()
            ->whereIn('id', $references->heroUploadIds())
            ->get()
            ->filter(fn (Upload $upload) => ! $optimizer->exists($optimizer->derivativePath((string) $upload->file_name, 'medium')))
            ->pluck('id')
            ->values()
            ->all();

        $this->table(
            ['Optimized', 'Pending', 'Failed', 'Skipped', 'Missing', 'Queue depth', 'Heroes missing medium WebP'],
            [[
                (int) ($counts['optimized'] ?? 0),
                (int) ($counts['pending'] ?? 0),
                (int) ($counts['failed'] ?? 0),
                (int) ($counts['skipped'] ?? 0),
                (int) ($counts['missing'] ?? 0),
                $queueDepth,
                $missingHeroIds === [] ? 'none' : implode(', ', $missingHeroIds),
            ]]
        );

        if ($this->option('fail-on-hero-missing') && $missingHeroIds !== []) {
            $this->error('Storefront readiness failed: active hero derivatives are missing.');

            return self::FAILURE;
        }

        return self::SUCCESS;
    }
}
