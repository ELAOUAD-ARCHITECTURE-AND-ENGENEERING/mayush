<?php

namespace Tests\Feature\Frontend;

use App\Models\Brand;
use App\Models\Category;
use App\Models\Product;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class SearchFilterRegressionTest extends TestCase
{
    use RefreshDatabase;

    public function test_ajax_listing_matches_all_search_words_not_only_the_last_term(): void
    {
        $this->withoutExceptionHandling();

        Product::factory()->create([
            'name' => 'Walnut Dining Chair',
            'slug' => 'walnut-dining-chair',
            'tags' => 'walnut,dining,chair',
            'added_by' => 'admin',
            'unit_price' => 180,
        ]);

        Product::factory()->create([
            'name' => 'Dining Lamp',
            'slug' => 'dining-lamp',
            'tags' => 'dining,lighting',
            'added_by' => 'admin',
            'unit_price' => 80,
        ]);

        $response = $this->getJson(route('suggestion.search2', [
            'keyword' => 'walnut dining',
            'mode' => 'standard',
        ]));

        $response->assertOk()
            ->assertJsonPath('success', true)
            ->assertJsonPath('total_product_count', 1);

        $this->assertStringContainsString('Walnut Dining Chair', $response->json('product_html'));
        $this->assertStringNotContainsString('Dining Lamp', $response->json('product_html'));
    }

    public function test_ajax_listing_applies_sidebar_filter_contract(): void
    {
        $category = Category::factory()->create(['name' => 'Dining Room', 'slug' => 'dining-room']);
        $otherCategory = Category::factory()->create(['name' => 'Outdoor', 'slug' => 'outdoor']);
        $brand = Brand::factory()->create(['name' => 'Mayush Atelier', 'slug' => 'mayush-atelier']);
        $otherBrand = Brand::factory()->create(['name' => 'Other Brand', 'slug' => 'other-brand']);
        $seller = User::factory()->create();
        $otherSeller = User::factory()->create();

        $matchingProduct = Product::factory()->create([
            'name' => 'Filtered Walnut Table',
            'slug' => 'filtered-walnut-table',
            'tags' => 'walnut,table',
            'category_id' => $category->id,
            'brand_id' => $brand->id,
            'user_id' => $seller->id,
            'added_by' => 'admin',
            'unit_price' => 150,
            'colors' => json_encode(['#112233']),
        ]);

        Product::factory()->create([
            'name' => 'Wrong Brand Walnut Table',
            'slug' => 'wrong-brand-walnut-table',
            'category_id' => $category->id,
            'brand_id' => $otherBrand->id,
            'user_id' => $seller->id,
            'added_by' => 'admin',
            'unit_price' => 150,
            'colors' => json_encode(['#112233']),
        ]);

        Product::factory()->create([
            'name' => 'Wrong Category Walnut Table',
            'slug' => 'wrong-category-walnut-table',
            'category_id' => $otherCategory->id,
            'brand_id' => $brand->id,
            'user_id' => $seller->id,
            'added_by' => 'admin',
            'unit_price' => 150,
            'colors' => json_encode(['#112233']),
        ]);

        Product::factory()->create([
            'name' => 'Wrong Seller Walnut Table',
            'slug' => 'wrong-seller-walnut-table',
            'category_id' => $category->id,
            'brand_id' => $brand->id,
            'user_id' => $otherSeller->id,
            'added_by' => 'admin',
            'unit_price' => 150,
            'colors' => json_encode(['#112233']),
        ]);

        Product::factory()->create([
            'name' => 'Wrong Price Walnut Table',
            'slug' => 'wrong-price-walnut-table',
            'category_id' => $category->id,
            'brand_id' => $brand->id,
            'user_id' => $seller->id,
            'added_by' => 'admin',
            'unit_price' => 450,
            'colors' => json_encode(['#112233']),
        ]);

        $response = $this->getJson(route('suggestion.search2', [
            'categories' => ['generel_' . $category->id],
            'brand_id' => $brand->id,
            'seller_id' => $seller->id,
            'min_price' => 100,
            'max_price' => 200,
            'colors' => ['#112233'],
            'sort_by' => 'price-asc',
            'mode' => 'standard',
        ]));

        $response->assertOk()
            ->assertJsonPath('success', true)
            ->assertJsonPath('total_product_count', 1);

        $html = $response->json('product_html');
        $this->assertStringContainsString($matchingProduct->name, $html);
        $this->assertStringNotContainsString('Wrong Brand Walnut Table', $html);
        $this->assertStringNotContainsString('Wrong Category Walnut Table', $html);
        $this->assertStringNotContainsString('Wrong Seller Walnut Table', $html);
        $this->assertStringNotContainsString('Wrong Price Walnut Table', $html);
    }

    public function test_ajax_listing_handles_special_character_query_and_renders_empty_state(): void
    {
        $this->withoutExceptionHandling();

        $response = $this->getJson(route('suggestion.search2', [
            'keyword' => '@@@',
            'mode' => 'standard',
        ]));

        $response->assertOk()
            ->assertJsonPath('success', true)
            ->assertJsonPath('total_product_count', 0);

        $this->assertStringContainsString('No products found', $response->json('product_html'));
    }

    public function test_autocomplete_search_uses_sqlite_safe_multi_word_matching(): void
    {
        Product::factory()->create([
            'name' => 'Walnut Dining Bench',
            'slug' => 'walnut-dining-bench',
            'tags' => 'walnut,dining,bench',
            'added_by' => 'admin',
        ]);

        Product::factory()->create([
            'name' => 'Dining Pendant',
            'slug' => 'dining-pendant',
            'tags' => 'dining,lighting',
            'added_by' => 'admin',
        ]);

        $response = $this->post(route('search.ajax'), [
            'search' => 'walnut dining',
            'mode' => 'standard',
        ]);

        $response->assertOk();
        $response->assertSee('Walnut Dining Bench');
        $response->assertDontSee('Dining Pendant');
    }
}
