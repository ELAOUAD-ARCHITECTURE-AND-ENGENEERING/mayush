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
    public $tries = 3;
    public $timeout = 180;

    use Dispatchable, InteractsWithQueue, Queueable, SerializesModels;

    public array $backoff = [30, 120, 300];

    public function __construct(public string $path)
    {
        $this->onQueue('images');
        $this->onQueue((string) config('image-optimization.queue', 'images'));
    }

    public function handle(ImageOptimizationService $optimizer): void
    {
        $optimizer->optimizeStaticAsset($this->path);
    }
}
