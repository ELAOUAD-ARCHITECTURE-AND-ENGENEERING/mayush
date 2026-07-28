<?php

namespace Tests\Feature\ProductionReadiness;

use Tests\TestCase;
use App\Models\Product;
use App\Models\SemanticEmbedding;
use App\Utility\SemanticUtility;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Facades\Http;

class SearchFallbackTest extends TestCase
{
    use RefreshDatabase;

    /** @test */
    public function normal_keyword_search_returns_products(): void
    {
        $product = Product::factory()->create([
            'name' => 'Test Product',
            'published' => 1,
            'approved' => 1
        ]);

        $response = $this->get('/search?q=Test');

        $response->assertStatus(200);
    }

    /** @test */
    public function semantic_search_failure_does_not_crash_search_page(): void
    {
        config(['services.openrouter.key' => '']);

        $response = $this->get('/search?q=test');

        $response->assertStatus(200);
    }

    /** @test */
    public function empty_search_query_is_handled_safely(): void
    {
        $response = $this->get('/search?q=');

        $this->assertContains($response->status(), [200, 302]);
    }

    /** @test */
    public function search_route_returns_valid_response_when_openrouter_api_is_unavailable(): void
    {
        Http::fake([
            'openrouter.ai/*' => Http::response(['error' => 'API unavailable'], 500)
        ]);

        $response = $this->get('/search?q=test');

        $response->assertStatus(200);
    }

    /** @test */
    public function opt_in_semantic_results_exclude_unpublished_products(): void
    {
        config([
            'search.features.semantic' => true,
            'services.openrouter.key' => '',
        ]);

        $visible = Product::factory()->create([
            'name' => 'Visible Semantic Product',
            'published' => 1,
            'approved' => 1,
            'added_by' => 'admin',
        ]);

        $hidden = Product::factory()->create([
            'name' => 'Hidden Semantic Product',
            'published' => 0,
            'approved' => 1,
            'added_by' => 'admin',
        ]);

        $vector = SemanticUtility::generateEmbedding('semantic visibility query');

        foreach ([$visible, $hidden] as $product) {
            SemanticEmbedding::create([
                'embeddable_type' => Product::class,
                'embeddable_id' => $product->id,
                'vector' => json_encode($vector),
                'content' => $product->name,
                'content_hash' => hash('sha256', $product->name),
                'metadata' => json_encode([]),
            ]);
        }

        $response = $this->getJson(route('suggestion.search2', [
            'keyword' => 'semantic visibility query',
            'mode' => 'ai',
        ]));

        $response->assertOk()
            ->assertJsonPath('success', true)
            ->assertJsonPath('total_product_count', 1);

        $html = $response->json('product_html');
        $this->assertStringContainsString('Visible Semantic Product', $html);
        $this->assertStringNotContainsString('Hidden Semantic Product', $html);
    }
}
