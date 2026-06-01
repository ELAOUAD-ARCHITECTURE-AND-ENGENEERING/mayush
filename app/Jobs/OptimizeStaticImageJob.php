<?php

namespace App\Jobs;

use App\Services\ImageOptimizationService;
use Illuminate\Bus\Queueable;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Foundation\Bus\Dispatchable;
use Illuminate\Queue\InteractsWithQueue;
use Illuminate\Queue\SerializesModels;

class OptimizeStaticImageJob implements ShouldQueue
{
    use Dispatchable, InteractsWithQueue, Queueable, SerializesModels;

    public int $tries = 3;
    public int $timeout = 180;
    public array $backoff = [30, 120, 300];

    public function __construct(public string $path)
    {
        $this->onQueue((string) config('image-optimization.queue', 'images'));
    }

    public function handle(ImageOptimizationService $optimizer): void
    {
        $optimizer->optimizeStaticAsset($this->path);
    }
}
