<?php

namespace Tests\Feature\ProductionReadiness;

use App\Models\Product;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Facades\Http;
use Tests\TestCase;

class SearchDisabledFeatureContractTest extends TestCase
{
    use RefreshDatabase;

    /** @test */
    public function ai_mode_falls_back_to_mysql_when_semantic_search_is_disabled(): void
    {
        config([
            'search.features.semantic' => false,
        ]);

        $product = Product::factory()->create([
            'name' => 'MySQL Fallback Chair',
            'slug' => 'mysql-fallback-chair',
            'tags' => 'mysql,fallback,chair',
            'added_by' => 'admin',
        ]);

        config(['services.openrouter.key' => 'test-key-that-must-not-be-used']);
        Http::fake();

        $response = $this->getJson(route('suggestion.search2', [
            'keyword' => 'mysql fallback',
            'mode' => 'ai',
        ]));

        $response->assertOk()
            ->assertJsonPath('success', true)
            ->assertJsonPath('total_product_count', 1);

        $this->assertStringContainsString($product->name, $response->json('product_html'));
        Http::assertNothingSent();
    }
}
