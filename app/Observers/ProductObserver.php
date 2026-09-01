<?php

namespace App\Observers;

use App\Models\Product;
use App\Jobs\SyncSemanticEmbeddingJob;
use App\Models\SemanticEmbedding;
use App\Services\StorefrontCacheService;
use App\Services\InspirationCacheService;

class ProductObserver
{
    /**
     * Handle the Product "saved" event (covers creation and updating).
     */
    public function saved(Product $product)
    {
        app(StorefrontCacheService::class)->bump();
        app(InspirationCacheService::class)->invalidateForProduct((int) $product->id);

        // Offload the heavy OpenRouter AI generation to the Horizon background worker.
        // It won't stall the user's web request.
        SyncSemanticEmbeddingJob::dispatch($product);
    }

    /**
     * Handle the Product "deleted" event.
     */
    public function deleted(Product $product)
    {
        app(StorefrontCacheService::class)->bump();
        app(InspirationCacheService::class)->invalidateForProduct((int) $product->id);

        // Removing the parent row inherently means the AI shouldn't be recommending it.
        SemanticEmbedding::where('embeddable_type', Product::class)
            ->where('embeddable_id', $product->id)
            ->delete();
    }
}
