<?php

namespace App\Console\Commands;

use Illuminate\Console\Command;

class CheckProductTranslationConfiguration extends Command
{
    protected $signature = 'product-translation:config-check';

    protected $description = 'Verify that Azure Translator configuration is available to this Laravel process.';

    public function handle(): int
    {
        $required = [
            'key' => config('services.azure_translator.key'),
            'endpoint' => config('services.azure_translator.endpoint'),
            'api_version' => config('services.azure_translator.api_version'),
        ];
        $missing = collect($required)
            ->filter(fn ($value) => blank($value))
            ->keys()
            ->all();

        if ($missing !== []) {
            $this->error('Azure Translator configuration is missing: '.implode(', ', $missing));
            return self::FAILURE;
        }

        $this->info('Azure Translator configuration is available.');
        return self::SUCCESS;
    }
}
