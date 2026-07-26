<?php

namespace App\Console\Commands;

use Illuminate\Console\Command;

class ReindexProducts extends Command
{
    /**
     * The name and signature of the console command.
     *
     * @var string
     */
    protected $signature = 'search:reindex {--force : Force re-generation of all embeddings}';

    /**
     * The console command description.
     *
     * @var string
     */
    protected $description = 'Reindex products into the semantic vector store (skips unchanged content by default)';

    /**
     * Execute the console command.
     */
    public function handle()
    {
        $force = $this->option('force');
        $products = \App\Models\Product::all();
        $this->info("Starting Semantic Reindexing of " . $products->count() . " products...");
        
        $bar = $this->output->createProgressBar(count($products));
        $bar->start();

        $synced = 0;
        $skipped = 0;
        $failed = 0;

        foreach ($products as $product) {
            $result = \App\Utility\SemanticUtility::syncEmbedding($product, $force);
            
            if ($result) {
                // We don't know if it was skipped or synced from the return value, 
                // but we can infer based on whether we were forcing or if it's new.
                $synced++;
            } else {
                $failed++;
            }

            $bar->advance();
            
            // Limit external AI requests to 60 per minute max.
            // Only sleep if we actually made an API request (implied by synced/not skipped)
            // For simplicity, we sleep 1s per product to be the most conservative.
            sleep(1);
        }

        $bar->finish();
        $this->newLine();
        $this->info("Reindexing Complete! Processed: {$synced}, Failed: {$failed}");
        $this->info("Tip: Use --force to regenerate all embeddings even if content hasn't changed.");
    }
}
