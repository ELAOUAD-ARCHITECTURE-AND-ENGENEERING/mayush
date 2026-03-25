<?php

namespace App\Utility;

use Intervention\Image\Facades\Image;
use Illuminate\Support\Facades\Storage;
use Illuminate\Support\Facades\File;

class ImageUtility
{
    /**
     * Converts an image to WebP format.
     * 
     * @param string $path The relative path in the public directory
     * @param int $quality Compression quality (0-100)
     * @return string|bool The path to the new WebP file or false on failure
     */
    public static function convertToWebp($path, $quality = 80)
    {
        $fullPath = public_path($path);

        if (!File::exists($fullPath)) {
            return false;
        }

        $info = pathinfo($fullPath);
        
        // Skip if already webp or not an image
        if (strtolower($info['extension']) === 'webp') {
            return $fullPath;
        }

        $webpPath = $info['dirname'] . DIRECTORY_SEPARATOR . $info['filename'] . '.webp';

        try {
            Image::make($fullPath)
                ->encode('webp', $quality)
                ->save($webpPath);
            
            return $webpPath;
        } catch (\Exception $e) {
            \Log::error("WebP Conversion Failed for {$path}: " . $e->getMessage());
            return false;
        }
    }

    /**
     * Generates medium (600px) and thumb (300px) versions of an image.
     * 
     * @param string $savedPath The absolute path to the saved original image
     * @param string $extension The image extension to encode
     * @param int $quality Compression quality
     */
    public static function generateThumbnails($savedPath, $extension, $quality = 80)
    {
        if (!File::exists($savedPath)) {
            return;
        }

        try {
            $info = pathinfo($savedPath);
            $img = Image::make($savedPath);
            $width = $img->width();
            $height = $img->height();

            // Medium: 600px max
            if ($width > 600 || $height > 600) {
                $mediumPath = $info['dirname'] . DIRECTORY_SEPARATOR . $info['filename'] . '_medium.' . $info['extension'];
                Image::make($savedPath)->resize(600, 600, function ($constraint) {
                    $constraint->aspectRatio();
                    $constraint->upsize();
                })->encode($extension, $quality)->save($mediumPath);
            }

            // Thumb: 300px max
            if ($width > 300 || $height > 300) {
                $thumbPath = $info['dirname'] . DIRECTORY_SEPARATOR . $info['filename'] . '_thumb.' . $info['extension'];
                Image::make($savedPath)->resize(300, 300, function ($constraint) {
                    $constraint->aspectRatio();
                    $constraint->upsize();
                })->encode($extension, $quality)->save($thumbPath);
            }
        } catch (\Exception $e) {
            \Log::error("Thumbnail generation failed for {$savedPath}: " . $e->getMessage());
        }
    }
}
