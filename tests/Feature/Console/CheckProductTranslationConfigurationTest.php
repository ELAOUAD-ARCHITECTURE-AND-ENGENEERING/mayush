<?php

namespace Tests\Feature\Console;

use Tests\TestCase;

class CheckProductTranslationConfigurationTest extends TestCase
{
    public function test_it_passes_when_required_openrouter_configuration_is_available(): void
    {
        config([
            'services.openrouter.key' => 'test-key',
            'services.openrouter.model' => 'openrouter/free',
            'services.openrouter.api_base' => 'https://openrouter.ai/api/v1',
        ]);

        $this->artisan('product-translation:config-check')
            ->expectsOutput('OpenRouter translation configuration is available.')
            ->assertExitCode(0);
    }

    public function test_it_fails_without_required_openrouter_configuration(): void
    {
        config([
            'services.openrouter.key' => null,
            'services.openrouter.model' => null,
            'services.openrouter.api_base' => null,
        ]);

        $this->artisan('product-translation:config-check')
            ->expectsOutput('OpenRouter translation configuration is missing: key, model, api_base')
            ->assertExitCode(1);
    }

    public function test_it_fails_when_the_translation_queue_is_not_supervised(): void
    {
        config([
            'services.openrouter.key' => 'test-key',
            'services.openrouter.model' => 'openrouter/free',
            'services.openrouter.api_base' => 'https://openrouter.ai/api/v1',
            'product_translation.queue' => 'default',
            'product_translation.queue_connection' => 'sync',
            'queue.connections.redis_translations.retry_after' => 90,
            'product_translation.worker_timeout' => 480,
        ]);

        $this->artisan('product-translation:config-check')
            ->expectsOutput('OpenRouter translation configuration is missing: queue, queue_connection, queue_retry_after')
            ->assertExitCode(1);
    }
}
