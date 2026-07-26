<?php

namespace Tests\Unit\Services;

use App\Services\OpenRouterProductTranslationService;
use App\Contracts\ProductTranslationService;
use Illuminate\Http\Client\Request as HttpRequest;
use Illuminate\Support\Facades\Http;
use Tests\TestCase;

class OpenRouterProductTranslationServiceTest extends TestCase
{
    private function configureOpenRouter(int $maxRetries = 0): void
    {
        config([
            'services.openrouter.key' => 'test-key',
            'services.openrouter.model' => 'openrouter/free',
            'services.openrouter.api_base' => 'https://openrouter.ai/api/v1',
            'services.openrouter.site_url' => 'https://mayushdesign.com',
            'services.openrouter.app_name' => 'MAYUSH',
            'services.openrouter.timeout' => 90,
            'services.openrouter.max_retries' => $maxRetries,
            'services.openrouter.retry_after' => 1,
            'services.openrouter.temperature' => 0.1,
            'services.openrouter.translation_max_payload' => 100000,
        ]);
    }

    private function response(array $fields, int $status = 200)
    {
        return Http::response([
            'model' => 'openrouter/free',
            'choices' => [[
                'finish_reason' => 'stop',
                'message' => ['role' => 'assistant', 'content' => json_encode(['fields' => $fields], JSON_UNESCAPED_UNICODE | JSON_UNESCAPED_SLASHES)],
            ]],
        ], $status);
    }

    public function test_it_sends_openrouter_structured_json_and_preserves_technical_values_and_html(): void
    {
        $this->configureOpenRouter();
        Http::fake(function () {
            return $this->response([
                'name' => 'مكتب جداري',
                'description' => '[MAYUSH_HTML_0]حسّن [MAYUSH_HTML_1]المساحة[MAYUSH_HTML_2][MAYUSH_HTML_3]',
                'tags' => ['أثاث مدمج', 'تصميم'],
                'unit_price' => '[MAYUSH_VALUE_4]',
                'sku' => '[MAYUSH_VALUE_5]',
                'choice_options_35' => ['[MAYUSH_VALUE_6]'],
            ]);
        });

        $result = (new OpenRouterProductTranslationService(app(\App\Services\OpenRouterProductTranslationPrompt::class)))->translateFields([
            'name' => 'Bureau mural',
            'description' => '<p>Optimisez <strong>l’espace</strong></p>',
            'tags' => ['mobilier compact', 'design'],
            'unit_price' => '125',
            'sku' => 'SKU-123',
            'choice_options_35' => ['1-100 cm'],
        ]);

        $this->assertTrue($result['success']);
        $this->assertSame('مكتب جداري', $result['fields']['name']);
        $this->assertSame('<p>حسّن <strong>المساحة</strong></p>', $result['fields']['description']);
        $this->assertSame(['أثاث مدمج', 'تصميم'], $result['fields']['tags']);
        $this->assertSame('125', $result['fields']['unit_price']);
        $this->assertSame('SKU-123', $result['fields']['sku']);
        $this->assertSame(['1-100 cm'], $result['fields']['choice_options_35']);
        $this->assertSame(4, $result['translated_count']);
        Http::assertSent(function (HttpRequest $request) {
            return $request->url() === 'https://openrouter.ai/api/v1/chat/completions'
                && $request->hasHeader('Content-Type', 'application/json')
                && $request->hasHeader('Authorization', 'Bearer test-key')
                && $request->hasHeader('HTTP-Referer', 'https://mayushdesign.com')
                && $request->hasHeader('X-OpenRouter-Title', 'MAYUSH')
                && $request->data()['model'] === 'openrouter/free'
                && $request->data()['stream'] === false
                && $request->data()['response_format']['type'] === 'json_schema'
                && $request->data()['provider']['require_parameters'] === true
                && str_contains($request->data()['messages'][1]['content'], 'Bureau mural');
        });
    }

    public function test_the_provider_neutral_contract_resolves_to_openrouter_only(): void
    {
        $this->assertInstanceOf(OpenRouterProductTranslationService::class, app(ProductTranslationService::class));
    }

    public function test_it_accepts_defensive_markdown_wrapped_json(): void
    {
        $this->configureOpenRouter();
        Http::fake(function () {
            $fields = ['name' => 'مكتب جداري'];
            $json = json_encode(['fields' => $fields], JSON_UNESCAPED_UNICODE);
            return Http::response([
                'choices' => [['message' => ['content' => "```json\n{$json}\n```"]]],
            ], 200);
        });

        $result = app(OpenRouterProductTranslationService::class)->translateFields(['name' => 'Bureau mural']);

        $this->assertTrue($result['success']);
        $this->assertSame('مكتب جداري', $result['fields']['name']);
    }

    public function test_it_rejects_unknown_or_missing_fields_without_clearing_original_values(): void
    {
        $this->configureOpenRouter();
        Http::fake(function () {
            return $this->response(['name' => 'مكتب جداري', 'unexpected' => 'غير مسموح']);
        });

        $result = app(OpenRouterProductTranslationService::class)->translateFields([
            'name' => 'Bureau mural',
            'description' => 'Description complète',
        ]);

        $this->assertFalse($result['success']);
        $this->assertSame('incomplete_response', $result['error_code']);
        $this->assertSame(['name' => 'Bureau mural', 'description' => 'Description complète'], $result['fields']);
    }

    public function test_it_rejects_html_structure_changes(): void
    {
        $this->configureOpenRouter();
        Http::fake(function () {
            return $this->response(['description' => '[MAYUSH_HTML_0]Texte[MAYUSH_HTML_3]']);
        });

        $result = app(OpenRouterProductTranslationService::class)->translateFields(['description' => '<p><strong>Texte</strong></p>']);

        $this->assertFalse($result['success']);
        $this->assertSame('malformed_response', $result['error_code']);
    }

    public function test_it_returns_rate_limit_without_repeating_a_quota_exhausting_request(): void
    {
        $this->configureOpenRouter(2);
        Http::fake(['https://openrouter.ai/*' => Http::response([], 429)]);

        $result = app(OpenRouterProductTranslationService::class)->translateFields(['name' => 'Bureau mural']);

        $this->assertFalse($result['success']);
        $this->assertSame('rate_limit', $result['error_code']);
        $this->assertSame('Bureau mural', $result['fields']['name']);
        Http::assertSentCount(1);
    }

    public function test_it_honors_openrouter_retry_delay_information(): void
    {
        $this->configureOpenRouter();
        Http::fake(['https://openrouter.ai/*' => Http::response([
            'error' => [
                'message' => 'rate limit exceeded',
            ],
        ], 429, ['Retry-After' => '7'])]);

        $result = app(OpenRouterProductTranslationService::class)->translateFields(['name' => 'Bureau mural']);

        $this->assertSame('rate_limit', $result['error_code']);
        $this->assertSame(7, $result['retry_after']);
    }

    public function test_it_normalizes_a_rate_limit_error_envelope_inside_a_success_response(): void
    {
        $this->configureOpenRouter();
        Http::fake(['https://openrouter.ai/*' => Http::response([
            'error' => [
                'message' => 'provider rate limit',
                'metadata' => ['error_type' => 'rate_limit'],
            ],
        ], 200, ['Retry-After' => '4'])]);

        $result = app(OpenRouterProductTranslationService::class)->translateFields(['name' => 'Bureau mural']);

        $this->assertFalse($result['success']);
        $this->assertSame('rate_limit', $result['error_code']);
        $this->assertSame(4, $result['retry_after']);
    }

    public function test_it_normalizes_a_choice_error_envelope_inside_a_success_response(): void
    {
        $this->configureOpenRouter();
        Http::fake(['https://openrouter.ai/*' => Http::response([
            'choices' => [[
                'error' => [
                    'code' => 503,
                    'message' => 'provider overloaded',
                    'metadata' => ['error_type' => 'provider_overloaded'],
                ],
            ]],
        ], 200)]);

        $result = app(OpenRouterProductTranslationService::class)->translateFields(['name' => 'Bureau mural']);

        $this->assertFalse($result['success']);
        $this->assertSame('temporary_failure', $result['error_code']);
    }

    public function test_it_distinguishes_guardrail_403_from_credentials(): void
    {
        $this->configureOpenRouter();
        Http::fake(['https://openrouter.ai/*' => Http::response([
            'error' => [
                'message' => 'content policy guardrail',
                'metadata' => ['error_type' => 'moderation'],
            ],
        ], 403)]);

        $result = app(OpenRouterProductTranslationService::class)->translateFields(['name' => 'Bureau mural']);

        $this->assertFalse($result['success']);
        $this->assertSame('safety_blocked', $result['error_code']);
    }

    public function test_it_rejects_length_terminated_output(): void
    {
        $this->configureOpenRouter();
        Http::fake(['https://openrouter.ai/*' => Http::response([
            'choices' => [['finish_reason' => 'length', 'message' => ['content' => '{"fields":{"name":"قطع"}}']]],
        ], 200)]);

        $result = app(OpenRouterProductTranslationService::class)->translateFields(['name' => 'Bureau mural']);

        $this->assertFalse($result['success']);
        $this->assertSame('incomplete_response', $result['error_code']);
    }

    public function test_it_falls_back_once_when_structured_json_schema_is_not_supported(): void
    {
        $this->configureOpenRouter();
        Http::fakeSequence()
            ->push(['error' => ['message' => 'response_format json_schema unsupported']], 400)
            ->push([
                'choices' => [[
                    'message' => [
                        'content' => json_encode(['fields' => ['name' => 'Ù…ÙƒØªØ¨ Ø¬Ø¯Ø§Ø±ÙŠ']], JSON_UNESCAPED_UNICODE),
                    ],
                ]],
            ], 200);

        $result = app(OpenRouterProductTranslationService::class)->translateFields(['name' => 'Bureau mural']);

        $this->assertTrue($result['success']);
        Http::assertSentCount(2);
        Http::assertSent(function (HttpRequest $request) {
            return $request->data()['response_format'] === ['type' => 'json_object']
                && !array_key_exists('provider', $request->data());
        });
    }

    public function test_it_rejects_safety_blocked_and_empty_responses(): void
    {
        $this->configureOpenRouter();
        Http::fakeSequence()
            ->push(['choices' => [['finish_reason' => 'SAFETY', 'message' => ['content' => '']]]], 200)
            ->push(['choices' => [['message' => ['content' => '']]]], 200);

        $blocked = app(OpenRouterProductTranslationService::class)->translateFields(['name' => 'Bureau mural']);
        $empty = app(OpenRouterProductTranslationService::class)->translateFields(['name' => 'Bureau mural']);

        $this->assertSame('safety_blocked', $blocked['error_code']);
        $this->assertSame('empty_response', $empty['error_code']);
    }

    public function test_it_handles_invalid_credentials_without_exposing_provider_response(): void
    {
        $this->configureOpenRouter();
        Http::fake(['https://openrouter.ai/*' => Http::response(['error' => ['message' => 'secret details']], 401)]);

        $result = app(OpenRouterProductTranslationService::class)->translateFields(['name' => 'Bureau mural']);

        $this->assertFalse($result['success']);
        $this->assertSame('credentials', $result['error_code']);
        $this->assertArrayNotHasKey('secret details', $result);
    }

    public function test_it_reports_missing_configuration(): void
    {
        config(['services.openrouter.key' => null, 'services.openrouter.model' => null]);

        $result = app(OpenRouterProductTranslationService::class)->translateFields(['name' => 'Bureau mural']);

        $this->assertFalse($result['success']);
        $this->assertSame('configuration', $result['error_code']);
        $this->assertSame('Bureau mural', $result['fields']['name']);
    }
}
