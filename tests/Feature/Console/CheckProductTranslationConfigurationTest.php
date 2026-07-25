<?php

namespace Tests\Feature\Console;

use Tests\TestCase;

class CheckProductTranslationConfigurationTest extends TestCase
{
    public function test_it_passes_when_required_azure_configuration_is_available(): void
    {
        config([
            'services.azure_translator.key' => 'test-key',
            'services.azure_translator.endpoint' => 'https://translator.test',
            'services.azure_translator.api_version' => '3.0',
        ]);

        $this->artisan('product-translation:config-check')
            ->expectsOutput('Azure Translator configuration is available.')
            ->assertExitCode(0);
    }

    public function test_it_fails_without_required_azure_configuration(): void
    {
        config([
            'services.azure_translator.key' => null,
            'services.azure_translator.endpoint' => null,
            'services.azure_translator.api_version' => null,
        ]);

        $this->artisan('product-translation:config-check')
            ->expectsOutput('Azure Translator configuration is missing: key, endpoint, api_version')
            ->assertExitCode(1);
    }
}
