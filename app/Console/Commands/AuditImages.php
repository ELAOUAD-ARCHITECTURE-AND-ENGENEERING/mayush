<?php

namespace App\Console\Commands;

use App\Jobs\OptimizeStaticImageJob;
use App\Jobs\OptimizeUploadedImageJob;
use App\Models\Upload;
use App\Services\ImageOptimizationService;
use App\Services\StorefrontImageReferenceService;
use Illuminate\Console\Command;
use Illuminate\Support\Facades\Cache;
use Illuminate\Support\Facades\Schema;

class AuditImages extends Command
{
    protected $signature = 'images:audit
                            {--repair : Queue repairs for missing or stale derivatives}
                            {--include-static : Include configured storefront static assets}
                            {--priority-storefront : Inspect currently visible storefront images before the catalog cursor}
                            {--limit= : Maximum upload records to inspect}
                            {--id= : Inspect one upload ID}
                            {--reset-cursor : Restart the resumable upload scan}';

    protected $description = 'Audit uploaded images and queue non-destructive WebP derivative repairs.';

    public function handle(ImageOptimizationService $optimizer, StorefrontImageReferenceService $references): int
    {
        if (! Schema::hasTable('image_optimization_states')) {
            $this->error('Run migrations before auditing images: image_optimization_states is missing.');

            return self::FAILURE;
        }

        $limit = max(1, (int) ($this->option('limit') ?: config('image-optimization.audit_limit', 500)));
        $repair = (bool) $this->option('repair');
        $cursorKey = 'image-optimization:audit-cursor';

        if ($this->option('reset-cursor')) {
            Cache::forget($cursorKey);
        }

        $priorityIds = $this->option('priority-storefront') && !$this->option('id') ? $references->priorityUploadIds() : [];
        $priorityUploads = Upload::query()
            ->where('type', 'like', 'image%')
            ->whereIn('id', $priorityIds)
            ->limit($limit)
            ->get();

        $remaining = max(0, $limit - $priorityUploads->count());
        $query = Upload::query()
            ->where('type', 'like', 'image%')
            ->whereNotIn('id', $priorityUploads->pluck('id'))
            ->orderBy('id');
        if ($this->option('id')) {
            $query->whereKey((int) $this->option('id'));
        } else {
            $query->where('id', '>', (int) Cache::get($cursorKey, 0));
        }

        $cursorUploads = $query->limit($remaining)->get();
        if (!$this->option('id') && $cursorUploads->isEmpty() && Cache::get($cursorKey, 0)) {
            Cache::forget($cursorKey);
            $cursorUploads = Upload::query()
                ->where('type', 'like', 'image%')
                ->whereNotIn('id', $priorityUploads->pluck('id'))
                ->orderBy('id')
                ->limit($remaining)
                ->get();
        }
        $uploads = $priorityUploads->concat($cursorUploads)->unique('id')->values();

        $counts = ['optimized' => 0, 'pending' => 0, 'missing' => 0, 'skipped' => 0, 'queued' => 0];
        foreach ($uploads as $upload) {
            $inspection = $optimizer->inspectUpload($upload);
            $status = $inspection['status'];
            $counts[$status] = ($counts[$status] ?? 0) + 1;

            if ($repair && ($inspection['needs_optimization'] ?? false)) {
                OptimizeUploadedImageJob::dispatch($upload->id);
                $counts['queued']++;
            }
        }

        if (!$this->option('id') && $cursorUploads->isNotEmpty()) {
            Cache::forever($cursorKey, $cursorUploads->last()->id);
        }

        if ($this->option('include-static')) {
            foreach ((array) config('image-optimization.static_assets', []) as $path) {
                $inspection = $optimizer->inspectStaticAsset($path);
                $status = $inspection['status'];
                $counts[$status] = ($counts[$status] ?? 0) + 1;

                if ($repair && ($inspection['needs_optimization'] ?? false)) {
                    OptimizeStaticImageJob::dispatch($path);
                    $counts['queued']++;
                }
            }
        }

        $this->table(
            ['Inspected', 'Optimized', 'Needs repair', 'Missing', 'Skipped', 'Queued'],
            [[
                $uploads->count() + ($this->option('include-static') ? count((array) config('image-optimization.static_assets', [])) : 0),
                $counts['optimized'],
                $counts['pending'],
                $counts['missing'],
                $counts['skipped'],
                $counts['queued'],
            ]]
        );

        return self::SUCCESS;
    }
}
