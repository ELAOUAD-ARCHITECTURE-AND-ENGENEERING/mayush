<?php

namespace App\Console\Commands;

use App\Models\Product;
use App\Services\ProductMixedLanguageDetector;
use Illuminate\Console\Command;

class ExportMixedProductLanguages extends Command
{
    protected $signature = 'products:export-mixed-languages
                            {--output= : CSV path; defaults to storage/app/reports/}
                            {--lang= : Source language to inspect; defaults to product translation source language}
                            {--chunk=500 : Number of products loaded per batch}';

    protected $description = 'Export products whose source content contains both French and English signals.';

    public function handle(ProductMixedLanguageDetector $detector): int
    {
        $language = (string) ($this->option('lang') ?: config('product_translation.source_language', 'fr'));
        $chunkSize = max(50, min(2000, (int) $this->option('chunk')));
        $output = $this->outputPath((string) $this->option('output'));
        $directory = dirname($output);

        if (!is_dir($directory) && !mkdir($directory, 0755, true) && !is_dir($directory)) {
            $this->error('Unable to create the export directory.');
            return self::FAILURE;
        }

        $handle = fopen($output, 'wb');
        if ($handle === false) {
            $this->error('Unable to open the CSV output path.');
            return self::FAILURE;
        }

        fwrite($handle, "\xEF\xBB\xBF");
        fputcsv($handle, ['product_id', 'product_name', 'source_language', 'matched_fields', 'french_terms', 'english_terms', 'preview']);

        $count = 0;
        Product::query()
            ->without(['taxes', 'thumbnail'])
            ->select(['id', 'name', 'unit', 'description', 'meta_title', 'meta_description', 'meta_keywords'])
            ->where('draft', 0)
            ->with(['product_translations' => function ($query) use ($language) {
                $query->select(['id', 'product_id', 'lang', 'name', 'unit', 'description', 'meta_title', 'meta_description', 'meta_keywords'])
                    ->where('lang', $language);
            }])
            ->orderBy('id')
            ->chunkById($chunkSize, function ($products) use ($detector, $language, $handle, &$count): void {
                foreach ($products as $product) {
                    $match = $detector->analyze($product, $language);
                    if ($match === null) {
                        continue;
                    }

                    fputcsv($handle, [
                        $match['product_id'],
                        $match['product_name'],
                        $language,
                        implode(', ', $match['fields']),
                        implode(', ', $match['french_terms']),
                        implode(', ', $match['english_terms']),
                        $match['preview'],
                    ]);
                    $count++;
                }
            });

        fclose($handle);
        $this->info("Exported {$count} product(s) containing French and English signals.");
        $this->line($output);
        $this->warn('This is a review list based on conservative word signals; it does not change product data.');

        return self::SUCCESS;
    }

    private function outputPath(string $requested): string
    {
        if ($requested === '') {
            return storage_path('app/reports/mixed-french-english-products-'.now()->format('Ymd-His').'.csv');
        }

        return preg_match('/^(?:[A-Za-z]:[\\\\\/]|[\\\\\/])/', $requested) === 1
            ? $requested
            : base_path($requested);
    }
}
