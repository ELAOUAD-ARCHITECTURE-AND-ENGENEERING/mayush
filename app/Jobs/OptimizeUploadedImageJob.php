<?php

namespace App\Jobs;

use App\Models\Upload;
use App\Services\ImageOptimizationService;
use Illuminate\Bus\Queueable;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Foundation\Bus\Dispatchable;
use Illuminate\Queue\InteractsWithQueue;
use Illuminate\Queue\SerializesModels;

class OptimizeUploadedImageJob implements ShouldQueue
{
    use Dispatchable, InteractsWithQueue, Queueable, SerializesModels;

    public int $tries = 3;
    public int $timeout = 180;
    public array $backoff = [30, 120, 300];

    public function __construct(public int $uploadId)
    {
        $this->onQueue((string) config('image-optimization.queue', 'images'));
    }

    public function handle(ImageOptimizationService $optimizer): void
    {
        $upload = Upload::find($this->uploadId);

        if ($upload && str_contains((string) $upload->type, 'image')) {
            $optimizer->optimizeUpload($upload);
        }
    }
}
