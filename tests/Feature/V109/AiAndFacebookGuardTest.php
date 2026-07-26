<?php

namespace Tests\Feature\V109;

use App\Models\AiPrompt;
use App\Models\BusinessSetting;
use App\Models\User;
use App\Services\AiService;
use App\Services\FacebookConversionService;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Facades\Http;
use Tests\TestCase;
use Tests\Traits\SeedsAppConfigs;

class AiAndFacebookGuardTest extends TestCase
{
    use RefreshDatabase, SeedsAppConfigs;

    protected function setUp(): void
    {
        parent::setUp();
        $this->seedConfigs();
    }

    public function test_ai_generation_is_guarded_when_disabled(): void
    {
        Http::fake();
        BusinessSetting::updateOrCreate(['type' => 'ai_activation'], ['value' => '0']);

        $response = app(AiService::class)->productGenerateWithAI([
            'product_name' => 'Test Product',
            'section' => 'basic-information',
        ]);

        $this->assertSame(403, $response->getStatusCode());
        Http::assertNothingSent();
    }

    public function test_ai_generation_uses_fake_http_when_enabled(): void
    {
        $this->actingAs(User::factory()->admin()->create());
        BusinessSetting::updateOrCreate(['type' => 'ai_activation'], ['value' => '1']);
        BusinessSetting::updateOrCreate(['type' => 'openrouter_model'], ['value' => 'openrouter/free']);
        AiPrompt::updateOrCreate(['identifier' => 'product_add_edit_prompt'], [
            'type' => 'product',
            'prompt' => 'Generate {prompt_fields} for {product_name} in {language}',
        ]);
        config(['services.openrouter.key' => 'fake-key', 'services.openrouter.model' => 'openrouter/free', 'services.openrouter.api_base' => 'https://openrouter.ai/api/v1']);

        Http::fake([
            'openrouter.ai/*' => Http::response([
                'choices' => [['message' => ['content' => '{"name":"Generated Product"}']]],
                'usage' => [
                    'prompt_tokens' => 2,
                    'completion_tokens' => 3,
                    'total_tokens' => 5,
                ],
            ]),
        ]);

        $response = app(AiService::class)->productGenerateWithAI([
            'product_name' => 'Test Product',
            'section' => 'basic-information',
            'lang' => 'en',
        ]);

        $this->assertSame(200, $response->getStatusCode());
        $this->assertDatabaseHas('ai_usage_logs', ['total_tokens' => 5, 'model' => 'openrouter/free']);
        Http::assertSentCount(1);
    }

    public function test_facebook_conversion_api_is_disabled_by_default(): void
    {
        Http::fake();
        BusinessSetting::updateOrCreate(['type' => 'facebook_pixel_capi'], ['value' => '0']);

        $sent = app(FacebookConversionService::class)->sendAddToWishlist(123);

        $this->assertFalse($sent);
        Http::assertNothingSent();
    }
}
