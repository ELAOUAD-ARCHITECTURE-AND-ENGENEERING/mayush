<?php

namespace App\Console\Commands;

use Illuminate\Console\Command;

class CheckProductTranslationConfiguration extends Command
{
    protected $signature = 'product-translation:config-check';

    protected $description = 'Verify that OpenRouter translation configuration is available to this Laravel process.';

    public function handle(): int
    {
        $required = [
            'key' => config('services.openrouter.key'),
            'model' => config('services.openrouter.model'),
            'api_base' => config('services.openrouter.api_base'),
            'queue' => config('product_translation.queue') === 'translations' ? 'translations' : null,
            'queue_connection' => config('product_translation.queue_connection') === 'redis_translations' ? 'redis_translations' : null,
            'queue_retry_after' => (int) config('queue.connections.redis_translations.retry_after') >= ((int) config('product_translation.worker_timeout', 480) + 60) ? 'valid' : null,
        ];
        $missing = collect($required)
            ->filter(fn ($value) => blank($value))
            ->keys()
            ->all();

        if ($missing !== []) {
            $this->error('OpenRouter translation configuration is missing: '.implode(', ', $missing));
            return self::FAILURE;
        }

        $this->info('OpenRouter translation configuration is available.');
        return self::SUCCESS;
    }
}
