<?php

namespace Tests\Integration\Controllers\Seller;

use Tests\TestCase;
use App\Models\User;
use App\Models\Product;
use App\Models\Category;
use App\Models\Shop;
use App\Models\Language;
use App\Models\BusinessSetting;
use Illuminate\Foundation\Testing\RefreshDatabase;

class SellerProductControllerTest extends TestCase
{
    use RefreshDatabase;

    protected function setUp(): void
    {
        parent::setUp();
        Language::factory()->create(['code' => 'en']);
        BusinessSetting::factory()->create(['type' => 'site_name', 'value' => 'Mayush']);
        
        $this->seller = User::factory()->create(['user_type' => 'seller']);
        $this->shop = Shop::factory()->create(['user_id' => $this->seller->id]);
    }

    /** @test */
    public function seller_can_view_their_products_list()
    {
        $response = $this->actingAs($this->seller)->get(route('seller.products'));
        $response->assertStatus(200);
        $response->assertViewIs('seller.product.products.index');
    }

    /** @test */
    public function seller_can_view_create_product_page()
    {
        $response = $this->actingAs($this->seller)->get(route('seller.products.create'));
        $response->assertStatus(200);
    }

    /** @test */
    public function seller_can_store_product()
    {
        $category = Category::factory()->create();
        
        $productData = [
            'name' => 'Seller Test Product',
            'category_ids' => [$category->id],
            'unit' => 'pcs',
            'min_qty' => 1,
            'tags' => [json_encode([['value' => 'seller-test']])],
            'unit_price' => 50,
            'current_stock' => 20,
            'description' => 'Seller test description'
        ];

        $response = $this->actingAs($this->seller)->post(route('seller.products.store'), $productData);

        $response->assertRedirect(route('seller.products'));
        $this->assertDatabaseHas('products', [
            'name' => 'Seller Test Product',
            'user_id' => $this->seller->id,
            'added_by' => 'seller'
        ]);
    }

    /** @test */
    public function seller_cannot_edit_another_sellers_product()
    {
        $otherSeller = User::factory()->create(['user_type' => 'seller']);
        $product = Product::factory()->create(['user_id' => $otherSeller->id]);

        $response = $this->actingAs($this->seller)->get(route('seller.products.edit', $product->id));

        $response->assertStatus(302); // Redirect back with warning
    }

    /** @test */
    public function seller_can_delete_their_own_product()
    {
        $product = Product::factory()->create(['user_id' => $this->seller->id]);

        $response = $this->actingAs($this->seller)->get(route('seller.products.destroy', $product->id));

        $this->assertDatabaseMissing('products', ['id' => $product->id]);
    }
}
