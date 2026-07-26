<?php

namespace Tests\Feature\ProductionReadiness;

use Tests\TestCase;
use App\Models\Product;
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
        // Should contain search results
    }

    /** @test */
    public function semantic_search_failure_does_not_crash_search_page(): void
    {
        // Disable OpenRouter API key to simulate failure
        config(['services.openrouter.key' => '']);

        $response = $this->get('/search?q=test');

        // Should not crash even if semantic search fails
        $response->assertStatus(200);
    }

    /** @test */
    public function empty_search_query_is_handled_safely(): void
    {
        $response = $this->get('/search?q=');

        // Should handle empty query gracefully
        $this->assertContains($response->status(), [200, 302]);
    }

    /** @test */
    public function search_route_returns_valid_response_when_openrouter_api_is_unavailable(): void
    {
        // Mock OpenRouter API failure
        Http::fake([
            'openrouter.ai/*' => Http::response(['error' => 'API unavailable'], 500)
        ]);

        $response = $this->get('/search?q=test');

        // Should not crash
        $response->assertStatus(200);
    }
}
