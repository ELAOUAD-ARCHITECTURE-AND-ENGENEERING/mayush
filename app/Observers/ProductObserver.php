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

        // Only dispatch the expensive OpenRouter embedding job when
        // content that affects the embedding has actually changed.
        $embeddingFields = ['name', 'description', 'tags', 'category_id', 'brand_id'];
        if ($product->wasRecentlyCreated || $product->wasChanged($embeddingFields)) {
            SyncSemanticEmbeddingJob::dispatch($product);
        }
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
