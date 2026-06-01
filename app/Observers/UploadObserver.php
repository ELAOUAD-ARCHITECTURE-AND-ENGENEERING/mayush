<?php

namespace App\Observers;

use App\Jobs\OptimizeUploadedImageJob;
use App\Models\Upload;

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
        if (str_contains((string) $upload->type, 'image')) {
            OptimizeUploadedImageJob::dispatch($upload->id)->afterCommit();
        }
    }
}
