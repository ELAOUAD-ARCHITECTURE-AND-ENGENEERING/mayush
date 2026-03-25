<?php

namespace App\Observers;

use App\Models\Upload;
use App\Utility\ImageUtility;

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
        if (strpos($upload->type, 'image') !== false) {
            $path = 'uploads/' . $upload->file_name;
            ImageUtility::convertToWebp($path);
        }
    }
}
