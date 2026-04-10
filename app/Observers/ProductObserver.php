<?php

namespace App\Observers;

use App\Models\Product;
use App\Jobs\SyncSemanticEmbeddingJob;
use App\Models\SemanticEmbedding;

class ProductObserver
{
    /**
     * Handle the Product "saved" event (covers creation and updating).
     */
    public function saved(Product $product)
    {
        // Offload the heavy Gemini API generation to the Horizon background worker.
        // It won't stall the user's web request.
        SyncSemanticEmbeddingJob::dispatch($product);
    }

    /**
     * Handle the Product "deleted" event.
     */
    public function deleted(Product $product)
    {
        // Removing the parent row inherently means the AI shouldn't be recommending it.
        SemanticEmbedding::where('embeddable_type', Product::class)
            ->where('embeddable_id', $product->id)
            ->delete();
    }
}
