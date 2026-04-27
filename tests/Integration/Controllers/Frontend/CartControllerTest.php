<?php

namespace Tests\Integration\Controllers\Frontend;

use Tests\TestCase;
use App\Models\User;
use App\Models\Product;
use App\Models\Cart;
use App\Models\Language;
use App\Models\BusinessSetting;
use Illuminate\Foundation\Testing\RefreshDatabase;

class CartControllerTest extends TestCase
{
    use RefreshDatabase;

    protected function setUp(): void
    {
        parent::setUp();
        Language::factory()->create(['code' => 'en']);
        BusinessSetting::factory()->create(['type' => 'site_name', 'value' => 'Mayush']);
    }

    /** @test */
    public function guest_can_view_cart_page()
    {
        $response = $this->get(route('cart'));
        $response->assertStatus(200);
    }

    /** @test */
    public function authenticated_user_can_view_cart_page()
    {
        $user = User::factory()->create();
        $response = $this->actingAs($user)->get(route('cart'));
        $response->assertStatus(200);
    }

    /** @test */
    public function user_can_add_product_to_cart()
    {
        $product = Product::factory()->create(['current_stock' => 10]);
        $product->stocks()->create(['variant' => '', 'price' => $product->unit_price, 'qty' => 10]);
        
        $response = $this->post(route('cart.addToCart'), [
            'id' => $product->id,
            'quantity' => 1
        ]);

        $response->assertStatus(200);
        $this->assertDatabaseHas('carts', [
            'product_id' => $product->id,
            'quantity' => 1
        ]);
    }

    /** @test */
    public function user_can_remove_product_from_cart()
    {
        $user = User::factory()->create();
        $product = Product::factory()->create();
        $cart = Cart::factory()->create([
            'user_id' => $user->id,
            'product_id' => $product->id
        ]);

        $response = $this->actingAs($user)->post(route('cart.removeFromCart'), [
            'id' => $cart->id
        ]);

        $response->assertStatus(200);
        $this->assertDatabaseMissing('carts', ['id' => $cart->id]);
    }

    /** @test */
    public function user_cannot_add_more_than_stock_to_cart()
    {
        $product = Product::factory()->create(['current_stock' => 5]);
        $product->stocks()->create(['variant' => '', 'price' => $product->unit_price, 'qty' => 5]);
        
        $response = $this->post(route('cart.addToCart'), [
            'id' => $product->id,
            'quantity' => 10
        ]);

        // Depending on implementation, it might return a specific error message or status
        $response->assertStatus(200); // Usually AJAX returns 200 with JSON status
        $response->assertJson(['status' => 0]); 
    }
}
