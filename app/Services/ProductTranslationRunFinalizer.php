<?php

namespace App\Services;

use App\Models\ProductTranslationRun;
use App\Services\Notifications\NotificationDispatcher;

class ProductTranslationRunFinalizer
{
    public function finish(ProductTranslationRun $run): ProductTranslationRun
    {
        $run->refresh();
        if ($run->finished_at !== null && $run->active_key === null) {
            return $run;
        }
        if ($run->items()->whereIn('status', ['pending', 'processing'])->exists()) {
            return $run;
        }

        $status = $run->failed_count > 0 ? 'completed_with_errors' : 'completed';

        $run->forceFill([
            'status' => $status,
            'active_key' => null,
            'processing_product_id' => null,
            'finished_at' => now(),
            'next_retry_at' => null,
            'last_progress_at' => now(),
        ])->save();

        if ($run->user_id && config('notifications_v2.enabled')) {
            app(NotificationDispatcher::class)->dispatch(
                'product.translation_completed',
                'product_translation_run',
                $run->id,
                $status,
                [$run->user_id],
                [
                    'title' => 'Correction des traductions terminée',
                    'message' => sprintf('%d produits corrigés, %d ignorés et %d en erreur.', $run->success_count, $run->skipped_count, $run->failed_count),
                    'action_url' => route('admin.product_translation_diagnostics', [], false),
                ]
            );
        }

        return $run;
    }
}
