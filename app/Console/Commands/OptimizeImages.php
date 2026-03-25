<?php

namespace App\Console\Commands;

use Illuminate\Console\Command;
use App\Models\Upload;
use Intervention\Image\Facades\Image;
use Illuminate\Support\Facades\File;

class OptimizeImages extends Command
{
    /**
     * The name and signature of the console command.
     *
     * @var string
     */
    protected $signature = 'images:optimize 
                            {--quality= : The quality to compress images to (0-100)} 
                            {--max-width= : The maximum width of the images}
                            {--format= : The format to convert to (webp, jpg, png)}
                            {--dry-run : Only show what would be done without doing it}';

    /**
     * The console command description.
     *
     * @var string
     */
    protected $description = 'Batch optimize all existing uploaded images based on current settings.';

    /**
     * Execute the console command.
     */
    public function handle()
    {
        $quality = $this->option('quality') ?? get_setting('image_quality', 80);
        $maxWidth = $this->option('max-width') ?? get_setting('image_max_width', 1500);
        $format = $this->option('format') ?? get_setting('uploaded_image_format', 'default');
        $dryRun = $this->option('dry-run');

        $this->info("Starting Image Optimization...");
        $this->info("Quality: {$quality}");
        $this->info("Max Width: {$maxWidth}px");
        $this->info("Format Target: " . ($format === 'default' ? 'Original' : $format));
        if ($dryRun) {
            $this->warn("DRY RUN ENABLED - No files will be modified.");
        }

        if (get_setting('disable_image_optimization') == 1 || get_setting('disable_image_optimization') == 'on') {
            $this->error("Image optimization is globally disabled in settings! Aborting.");
            return;
        }

        $uploads = Upload::where('type', 'image')->get();
        if ($uploads->count() === 0) {
            $this->info("No images found in the uploads table.");
            return;
        }

        $bar = $this->output->createProgressBar(count($uploads));
        $bar->start();

        $successCount = 0;
        $missingCount = 0;
        $skippedCount = 0;
        $errorCount = 0;

        foreach ($uploads as $upload) {
            $path = public_path($upload->file_name);
            
            if (!File::exists($path)) {
                $missingCount++;
                $bar->advance();
                continue;
            }

            // Skip SVG and GIF
            $ext = strtolower($upload->extension);
            if (in_array($ext, ['svg', 'gif'])) {
                $skippedCount++;
                $bar->advance();
                continue;
            }

            $targetFormat = ($format === 'default' || $format === '') ? $ext : $format;

            if ($dryRun) {
                $successCount++;
                $bar->advance();
                continue;
            }

            try {
                $img = Image::make($path);
                $width = $img->width();
                $height = $img->height();
                $oldSize = File::size($path);

                // Resize if needed
                if ($width > $maxWidth || $height > $maxWidth) {
                    $img->resize($maxWidth, $maxWidth, function ($constraint) {
                        $constraint->aspectRatio();
                        $constraint->upsize();
                    });
                }

                // Encode and save back to the same path (or new path if format changed)
                if ($ext !== $targetFormat) {
                    $newPath = preg_replace('/\.' . $ext . '$/i', '.' . $targetFormat, $path);
                    $newFileName = preg_replace('/\.' . $ext . '$/i', '.' . $targetFormat, $upload->file_name);
                    
                    $img->encode($targetFormat, $quality)->save($newPath);
                    
                    // Update DB record
                    $upload->extension = $targetFormat;
                    $upload->file_name = $newFileName;
                    $upload->file_size = File::size($newPath);
                    $upload->save();

                    // Optional: remove old file if name actually changed
                    if ($path !== $newPath && File::exists($path)) {
                        File::delete($path);
                    }
                    
                    // Thumbnails with new format
                    \App\Utility\ImageUtility::generateThumbnails($newPath, $targetFormat, $quality);
                    
                    // Also generate WebP if not already webp
                    if ($targetFormat !== 'webp') {
                         \App\Utility\ImageUtility::convertToWebp($upload->file_name, $quality);
                    }

                } else {
                    $img->encode($ext, $quality)->save($path);
                    $upload->file_size = File::size($path);
                    $upload->save();
                    
                    // Thumbnails
                    \App\Utility\ImageUtility::generateThumbnails($path, $ext, $quality);

                    // Also generate WebP if not already webp
                    if ($ext !== 'webp') {
                        \App\Utility\ImageUtility::convertToWebp($upload->file_name, $quality);
                    }
                }

                clearstatcache();
                $successCount++;
            } catch (\Exception $e) {
                \Log::error("Failed to optimize {$upload->file_name}: " . $e->getMessage());
                $errorCount++;
            }
            
            $bar->advance();
        }

        $bar->finish();
        $this->newLine(2);

        $this->info("Optimization Complete!");
        $this->line("Successfully Processed: <info>{$successCount}</info>");
        $this->line("Skipped (GIF/SVG): <comment>{$skippedCount}</comment>");
        $this->line("Missing Files: <comment>{$missingCount}</comment>");
        if ($errorCount > 0) {
            $this->line("Errors: <error>{$errorCount}</error> (Check laravel.log)");
        }
    }
}
