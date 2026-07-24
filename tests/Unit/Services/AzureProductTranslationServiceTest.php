<?php

namespace Tests\Unit\Services;

use App\Services\AzureProductTranslationService;
use Illuminate\Http\Client\ConnectionException;
use Illuminate\Http\Client\Request as HttpRequest;
use Illuminate\Support\Facades\Http;
use Tests\TestCase;

class AzureProductTranslationServiceTest extends TestCase
{
    private function configureAzure(?string $region = null): void
    {
        config([
            'services.azure_translator.key' => 'test-key',
            'services.azure_translator.region' => $region,
            'services.azure_translator.endpoint' => 'https://translator.test',
            'services.azure_translator.api_version' => '3.0',
            'services.azure_translator.timeout' => 15,
            'services.azure_translator.connect_timeout' => 5,
        ]);
    }

    public function test_it_sends_azure_payload_with_stable_local_mapping_and_no_region_for_global_resources(): void
    {
        $this->configureAzure();
        Http::fake(function (HttpRequest $request) {
            $this->assertStringContainsString('/translate?api-version=3.0&from=fr&to=ar', $request->url());
            $this->assertArrayNotHasKey('Ocp-Apim-Subscription-Region', $request->headers());
            $this->assertSame([
                ['Text' => 'Bureau mural'],
                ['Text' => 'mobilier compact'],
            ], $request->data());

            return Http::response([
                ['translations' => [['text' => 'مكتب جداري']]],
                ['translations' => [['text' => 'أثاث مدمج']]],
            ], 200);
        });

        $result = (new AzureProductTranslationService())->translateFields([
            'name' => 'Bureau mural',
            'tags' => ['mobilier compact'],
            'unit_price' => '125',
            'sku' => 'SKU-123',
        ]);

        $this->assertTrue($result['success']);
        $this->assertSame('مكتب جداري', $result['fields']['name']);
        $this->assertSame(['أثاث مدمج'], $result['fields']['tags']);
        $this->assertSame('125', $result['fields']['unit_price']);
        $this->assertSame('SKU-123', $result['fields']['sku']);
    }

    public function test_it_sends_regional_header_and_html_mode_for_rich_text(): void
    {
        $this->configureAzure('westeurope');
        Http::fake(function (HttpRequest $request) {
            $this->assertStringContainsString('textType=html', $request->url());
            $this->assertSame(['westeurope'], $request->header('Ocp-Apim-Subscription-Region'));
            $this->assertSame('<p>Bureau</p>', $request->data()[0]['Text']);

            return Http::response([
                ['translations' => [['text' => '<p>مكتب</p>']]],
            ], 200);
        });

        $result = (new AzureProductTranslationService())->translateFields([
            'description' => '<p>Bureau</p>',
        ]);

        $this->assertSame('<p>مكتب</p>', $result['fields']['description']);
        $this->assertSame([], $result['failed_fields']);
    }

    public function test_dimension_choice_options_are_preserved_and_not_sent_to_azure(): void
    {
        $this->configureAzure();
        Http::fake();

        $result = (new AzureProductTranslationService())->translateFields([
            'choice_options_35' => ['1-100 cm'],
            'name' => 'Bureau',
        ]);

        $this->assertSame(['1-100 cm'], $result['fields']['choice_options_35']);
        Http::assertSentCount(1);
        Http::assertSent(function (HttpRequest $request) {
            return $request->data() === [['Text' => 'Bureau']];
        });
    }

    public function test_missing_configuration_preserves_values(): void
    {
        config(['services.azure_translator.key' => null]);

        $result = (new AzureProductTranslationService())->translateFields(['name' => 'Bureau']);

        $this->assertFalse($result['success']);
        $this->assertSame('configuration', $result['error_code']);
        $this->assertSame(['name' => 'Bureau'], $result['fields']);
    }

    public function test_incomplete_response_preserves_original_values(): void
    {
        $this->configureAzure();
        Http::fake([
            'https://translator.test/*' => Http::response([
                ['translations' => [['text' => 'مكتب']]],
            ], 200),
        ]);

        $result = (new AzureProductTranslationService())->translateFields([
            'name' => 'Bureau',
            'description' => 'Description',
        ]);

        $this->assertFalse($result['success']);
        $this->assertCount(2, $result['failed_fields']);
        $this->assertSame('Bureau', $result['fields']['name']);
        $this->assertSame('Description', $result['fields']['description']);
    }

    public function test_timeout_preserves_original_values(): void
    {
        $this->configureAzure();
        Http::fake(function () {
            throw new ConnectionException('translator timeout');
        });

        $result = (new AzureProductTranslationService())->translateFields(['name' => 'Bureau']);

        $this->assertFalse($result['success']);
        $this->assertSame('timeout', $result['errors']['name']);
        $this->assertSame('Bureau', $result['fields']['name']);
    }

    public function test_rate_limiting_is_returned_as_structured_failure(): void
    {
        $this->configureAzure();
        Http::fake([
            'https://translator.test/*' => Http::response([], 429),
        ]);

        $result = (new AzureProductTranslationService())->translateFields(['name' => 'Bureau']);

        $this->assertFalse($result['success']);
        $this->assertSame('rate_limit', $result['error_code']);
        $this->assertSame('Bureau', $result['fields']['name']);
    }

    public function test_invalid_credentials_are_returned_without_clearing_values(): void
    {
        $this->configureAzure();
        Http::fake([
            'https://translator.test/*' => Http::response([], 401),
        ]);

        $result = (new AzureProductTranslationService())->translateFields(['name' => 'Bureau']);

        $this->assertFalse($result['success']);
        $this->assertSame('credentials', $result['error_code']);
        $this->assertSame('Bureau', $result['fields']['name']);
    }
}
