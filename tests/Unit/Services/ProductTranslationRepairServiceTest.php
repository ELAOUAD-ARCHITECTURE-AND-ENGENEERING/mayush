<?php

namespace Tests\Unit\Services;

use App\Models\Product;
use App\Models\ProductTranslation;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Http\Client\Request as HttpRequest;
use Illuminate\Support\Facades\Cache;
use Illuminate\Support\Facades\Http;
use Tests\TestCase;

class ProductTranslationRepairServiceTest extends TestCase
{
    use RefreshDatabase;

    public function test_it_completes_a_product_when_openrouter_preserves_a_standard_pc_unit(): void
    {
        config([
            'app.locale' => 'fr',
            'services.openrouter.key' => 'test-key',
            'services.openrouter.model' => 'openrouter/free',
            'services.openrouter.api_base' => 'https://openrouter.ai/api/v1',
            'services.openrouter.max_retries' => 0,
            'product_translation.fields' => ['unit'],
            'product_translation.required_fields' => [],
        ]);
        Cache::flush();

        $product = Product::factory()->create([
            'name' => 'Bureau mural',
            'unit' => 'Pc',
            'description' => 'Description franÃ§aise',
        ]);
        ProductTranslation::create([
            'product_id' => $product->id,
            'lang' => 'ma',
            'name' => 'Ù…ÙƒØªØ¨ Ø¬Ø¯Ø§Ø±ÙŠ',
            'unit' => 'Pc',
            'description' => 'ÙˆØµÙ Ø¹Ø±Ø¨ÙŠ',
        ]);

        Http::fake(function () {
            return Http::response([
                'model' => 'openrouter/free',
                'choices' => [[
                    'message' => [
                        'content' => json_encode(['fields' => ['unit' => 'Pc']], JSON_UNESCAPED_UNICODE),
                    ],
                ]],
            ], 200);
        });

        $result = app(\App\Services\ProductTranslationRepairService::class)->repair($product);

        $this->assertSame('success', $result['status']);
        $this->assertSame(['unit'], $result['translated_fields']);
        $this->assertSame(json_decode('"\\u0642\\u0637\\u0639\\u0629"'), $product->fresh()->product_translations()->where('lang', 'ma')->value('unit'));
        Http::assertSent(fn (HttpRequest $request) => $request->url() === 'https://openrouter.ai/api/v1/chat/completions');
    }
}
