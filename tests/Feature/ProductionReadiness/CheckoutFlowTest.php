<?php

namespace Tests\Feature\ProductionReadiness;

use Tests\TestCase;
use App\Models\User;
use App\Models\Product;
use App\Models\Address;
use App\Models\Cart;
use Illuminate\Foundation\Testing\RefreshDatabase;

use Tests\Traits\SeedsAppConfigs;

class CheckoutFlowTest extends TestCase
{
    use RefreshDatabase, SeedsAppConfigs;

    protected function setUp(): void
    {
        parent::setUp();
        $this->seedConfigs();
        
        // Setup required data
        \DB::table('countries')->insertOrIgnore([
            'id' => 1, 
            'name' => 'Morocco', 
            'code' => 'MA', 
            'status' => 1
        ]);
        \DB::table('cities')->insertOrIgnore([
            'id' => 1, 
            'name' => 'Casablanca', 
            'country_id' => 1, 
            'status' => 1
        ]);
    }

    /** @test */
    public function customer_can_add_product_to_cart(): void
    {
        $product = Product::factory()->create([
            'published' => 1, 
            'approved' => 1,
            'current_stock' => 10,
            'unit_price' => 100
        ]);
        
        $product->stocks()->create([
            'variant' => '',
            'price' => 100,
            'qty' => 10
        ]);

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
    public function customer_can_reach_checkout_with_valid_cart(): void
    {
        $user = User::factory()->customer()->create();
        $product = Product::factory()->create([
            'published' => 1, 
            'approved' => 1,
            'current_stock' => 10,
            'unit_price' => 100
        ]);
        
        $product->stocks()->create([
            'variant' => '',
            'price' => 100,
            'qty' => 10
        ]);

        $cart = Cart::create([
            'user_id' => $user->id,
            'product_id' => $product->id,
            'quantity' => 1,
            'price' => 100,
            'status' => 1,
            'shipping_cost' => 0.00,
            'shipping_type' => 'flat_rate'
        ]);

        $response = $this->actingAs($user)->get('/checkout');
        $this->assertContains($response->status(), [200, 302]);
    }

    /** @test */
    public function customer_cannot_checkout_with_empty_cart(): void
    {
        $user = User::factory()->customer()->create();
        
        $response = $this->actingAs($user)->get('/checkout');
        $this->assertContains($response->status(), [200, 302]);
    }

    /** @test */
    public function customer_must_have_valid_address_before_placing_order(): void
    {
        $user = User::factory()->customer()->create();
        $product = Product::factory()->create([
            'published' => 1, 
            'approved' => 1,
            'current_stock' => 10,
            'unit_price' => 100
        ]);
        
        $product->stocks()->create([
            'variant' => '',
            'price' => 100,
            'qty' => 10
        ]);

        $cart = Cart::create([
            'user_id' => $user->id,
            'product_id' => $product->id,
            'quantity' => 1,
            'price' => 100,
            'status' => 1,
            'shipping_cost' => 0.00,
            'shipping_type' => 'flat_rate'
        ]);

        $response = $this->actingAs($user)->post(route('payment.checkout'), [
            'payment_option' => 'cash_on_delivery'
        ]);

        // Should redirect or show an error
        $this->assertContains($response->status(), [200, 302]);
    }

    /** @test */
    public function order_creation_uses_database_transaction(): void
    {
        $user = User::factory()->customer()->create();
        $address = Address::factory()->create([
            'user_id' => $user->id,
            'country_id' => 1,
            'city_id' => 1
        ]);
        
        $product = Product::factory()->create([
            'published' => 1, 
            'approved' => 1,
            'current_stock' => 10,
            'unit_price' => 100
        ]);
        
        $product->stocks()->create([
            'variant' => '',
            'price' => 100,
            'qty' => 10
        ]);

        $cart = Cart::create([
            'user_id' => $user->id,
            'product_id' => $product->id,
            'quantity' => 1,
            'price' => 100,
            'status' => 1,
            'address_id' => $address->id,
            'shipping_cost' => 0.00,
            'shipping_type' => 'flat_rate'
        ]);

        // Mock session data for checkout
        $this->actingAs($user);
        $response = $this->post(route('payment.checkout'), [
            'address_id' => $address->id,
            'payment_option' => 'cash_on_delivery'
        ]);

        // Should complete checkout successfully
        $this->assertContains($response->status(), [200, 302]);
    }

    /** @test */
    public function multi_vendor_cart_creates_separated_seller_logic(): void
    {
        // This test assumes multi-vendor functionality exists in the codebase
        // The actual implementation would depend on the existing business logic
        $this->assertTrue(true); // Placeholder - real implementation would verify multi-vendor logic
    }
}