<?php

namespace Tests\Integration\API;

use Tests\TestCase;
use App\Models\User;
use App\Models\Product;
use Illuminate\Foundation\Testing\RefreshDatabase;

class ApiV2ProductTest extends TestCase
{
    use RefreshDatabase;

    /** @test */
    public function it_can_list_products_via_api()
    {
        Product::factory()->count(5)->create(['published' => 1, 'approved' => 1]);

        $response = $this->getJson('/api/v2/products');

        $response->assertStatus(200)
                 ->assertJsonStructure([
                     'data' => [
                         '*' => ['id', 'name', 'thumbnail_image', 'base_price', 'rating']
                     ]
                 ]);
    }

    /** @test */
    public function it_can_get_product_details_via_api()
    {
        $product = Product::factory()->create(['published' => 1, 'approved' => 1]);

        $response = $this->getJson('/api/v2/products/' . $product->id);

        $response->assertStatus(200)
                 ->assertJsonStructure([
                     'data' => [
                         '*' => ['id', 'name', 'photos', 'rating', 'description']
                     ]
                 ]);
    }

    /** @test */
    public function it_can_search_products_via_api()
    {
        Product::factory()->create(['name' => 'Searchable Phone', 'published' => 1, 'approved' => 1]);

        $response = $this->getJson('/api/v2/products/search?name=Phone');

        $response->assertStatus(200)
                 ->assertJsonFragment(['name' => 'Searchable Phone']);
    }

    /** @test */
    public function api_multi_word_search_requires_each_meaningful_term(): void
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

        $response = $this->getJson('/api/v2/products/search?name=walnut%20dining');

        $response->assertOk()
            ->assertJsonFragment(['name' => 'Walnut Dining Chair'])
            ->assertJsonMissing(['name' => 'Dining Lamp']);
    }

    /** @test */
    public function it_returns_404_for_non_existent_product_api()
    {
        $response = $this->getJson('/api/v2/products/9999');

        $response->assertStatus(404);
    }
}
