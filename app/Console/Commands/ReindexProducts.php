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
    protected $signature = 'search:reindex';

    /**
     * The console command description.
     *
     * @var string
     */
    protected $description = 'Reindex all products into the semantic vector store';

    /**
     * Execute the console command.
     */
    public function handle()
    {
        $products = \App\Models\Product::all();
        $this->info("Starting Semantic Reindexing of " . $products->count() . " products...");
        
        $bar = $this->output->createProgressBar(count($products));
        $bar->start();

        foreach ($products as $product) {
            \App\Utility\SemanticUtility::syncEmbedding($product);
            $bar->advance();
        }

        $bar->finish();
        $this->newLine();
        $this->info("Reindexing Complete!");
    }
}
