<?php

namespace App\Observers;

use App\Services\InspirationCacheService;
use Illuminate\Database\Eloquent\Model;

class InspirationProductDataObserver
{
    public function saved(Model $model): void
    {
        $this->invalidate($model);
    }

    public function deleted(Model $model): void
    {
        $this->invalidate($model);
    }

    private function invalidate(Model $model): void
    {
        $productId = (int) $model->getAttribute('product_id');
        if ($productId > 0) {
            app(InspirationCacheService::class)->invalidateForProduct($productId);
        }
    }
}
