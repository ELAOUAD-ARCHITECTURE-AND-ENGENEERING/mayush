<?php

namespace Tests\Integration\API;

use App\Models\Product;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class SearchSuggestionTest extends TestCase
{
    use RefreshDatabase;

    /** @test */
    public function product_suggestions_require_each_meaningful_term(): void
    {
        Product::factory()->create([
            'name' => 'Walnut Dining Chair',
            'tags' => 'walnut,dining,chair',
            'published' => 1,
            'approved' => 1,
        ]);

        Product::factory()->create([
            'name' => 'Dining Lamp',
            'tags' => 'dining,lighting',
            'published' => 1,
            'approved' => 1,
        ]);

        $response = $this->getJson('/api/v2/get-search-suggestions?type=product&query_key=walnut%20dining');

        $response->assertOk()
            ->assertJsonFragment(['query' => 'Walnut Dining Chair'])
            ->assertJsonMissing(['query' => 'Dining Lamp']);
    }
}
