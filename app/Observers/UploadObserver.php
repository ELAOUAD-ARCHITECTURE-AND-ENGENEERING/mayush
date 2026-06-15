<?php

namespace App\Observers;

use App\Jobs\OptimizeUploadedImageJob;
use App\Models\Upload;
use App\Services\StorefrontCacheService;

class UploadObserver
{
    /**
     * Handle the Upload "created" event.
     *
     * @param  \App\Models\Upload  $upload
     * @return void
     */
    public function created(Upload $upload)
    {
        app(StorefrontCacheService::class)->bump();

        if (str_contains((string) $upload->type, 'image')) {
            OptimizeUploadedImageJob::dispatch($upload->id)->afterCommit();
        }
    }

    public function updated(): void
    {
        app(StorefrontCacheService::class)->bump();
    }

    public function deleted(): void
    {
        app(StorefrontCacheService::class)->bump();
    }
}
