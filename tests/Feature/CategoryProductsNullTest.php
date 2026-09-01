<?php

namespace Tests\Feature;

use Tests\TestCase;
use App\Models\Category;
use App\Models\Brand;
use App\Models\Product;
use Illuminate\Foundation\Testing\RefreshDatabase;

class CategoryProductsNullTest extends TestCase
{
    use RefreshDatabase;

    public function test_category_products_returns_empty_collection_for_non_existent_slug()
    {
        $response = $this->getJson('/api/v2/products/category/non-existent-category-slug-123');

        $response->assertStatus(200);
        $response->assertJson([
            'data' => [],
            'success' => true,
            'status' => 200,
        ]);
    }

    public function test_brand_products_returns_empty_collection_for_non_existent_slug()
    {
        $response = $this->getJson('/api/v2/products/brand/non-existent-brand-slug-123');

        $response->assertStatus(200);
        $response->assertJson([
            'data' => [],
            'success' => true,
            'status' => 200,
        ]);
    }

    public function test_category_products_returns_products_when_valid_slug()
    {
        $user = \App\Models\User::create([
            'name' => 'Test User',
            'email' => 'test@example.com',
            'password' => bcrypt('password'),
            'user_type' => 'customer',
        ]);

        $category = Category::create([
            'name' => 'Test Category',
            'slug' => 'test-category',
        ]);

        $product = Product::create([
            'name' => 'Test Product',
            'slug' => 'test-product',
            'user_id' => $user->id,
            'category_id' => $category->id,
            'unit_price' => 100,
            'published' => 1,
            'approved' => 1,
        ]);

        $response = $this->getJson('/api/v2/products/category/test-category');

        $response->assertStatus(200);
        $response->assertJsonFragment([
            'id' => $product->id,
            'slug' => 'test-product',
        ]);
    }
}
