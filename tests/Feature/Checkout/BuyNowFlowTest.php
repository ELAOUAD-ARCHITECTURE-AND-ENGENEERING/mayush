<?php

namespace Tests\Feature\Checkout;

use App\Models\Cart;
use App\Models\Product;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class BuyNowFlowTest extends TestCase
{
    use RefreshDatabase;

    public function test_customer_buy_now_creates_single_cart_item_and_redirects_to_checkout(): void
    {
        $customer = User::factory()->customer()->create();
        $oldProduct = Product::factory()->create();
        Cart::create([
            'user_id' => $customer->id,
            'product_id' => $oldProduct->id,
            'owner_id' => $oldProduct->user_id,
            'quantity' => 1,
            'price' => 10,
            'tax' => 0,
            'status' => 1,
        ]);

        $product = Product::factory()->create(['name' => 'Buy Now Simple']);
        $product->stocks()->create([
            'variant' => '',
            'sku' => 'BUY-NOW-SIMPLE',
            'price' => 150,
            'qty' => 8,
        ]);

        $this->actingAs($customer)
            ->post(route('cart.buy_now'), [
                'id' => $product->id,
                'quantity' => 2,
            ])
            ->assertRedirect(route('checkout.shipping_info'));

        $this->assertSame(1, Cart::where('user_id', $customer->id)->count());
        $this->assertDatabaseHas('carts', [
            'user_id' => $customer->id,
            'product_id' => $product->id,
            'quantity' => 2,
            'price' => 150,
            'status' => 1,
        ]);
        $this->assertDatabaseMissing('carts', [
            'user_id' => $customer->id,
            'product_id' => $oldProduct->id,
        ]);
    }

    public function test_customer_buy_now_uses_selected_variant_price(): void
    {
        $customer = User::factory()->customer()->create();
        $product = Product::factory()->create([
            'name' => 'Variant Buy Now',
            'choice_options' => json_encode([
                ['attribute_id' => 1, 'values' => ['Large', 'Small']],
            ]),
        ]);
        $product->stocks()->create([
            'variant' => 'Large',
            'sku' => 'VARIANT-L',
            'price' => 210,
            'qty' => 4,
        ]);
        $product->stocks()->create([
            'variant' => 'Small',
            'sku' => 'VARIANT-S',
            'price' => 90,
            'qty' => 4,
        ]);

        $this->actingAs($customer)
            ->post(route('cart.buy_now'), [
                'id' => $product->id,
                'quantity' => 1,
                'attribute_id_1' => 'Large',
            ])
            ->assertRedirect(route('checkout.shipping_info'));

        $this->assertDatabaseHas('carts', [
            'user_id' => $customer->id,
            'product_id' => $product->id,
            'variation' => 'Large',
            'price' => 210,
        ]);
    }

    public function test_out_of_stock_buy_now_does_not_clear_existing_cart(): void
    {
        $customer = User::factory()->customer()->create();
        $existing = Product::factory()->create();
        Cart::create([
            'user_id' => $customer->id,
            'product_id' => $existing->id,
            'owner_id' => $existing->user_id,
            'quantity' => 1,
            'price' => 25,
            'tax' => 0,
            'status' => 1,
        ]);

        $product = Product::factory()->create(['name' => 'Out Of Stock Buy Now']);
        $product->stocks()->create([
            'variant' => '',
            'sku' => 'OUT-BUY-NOW',
            'price' => 100,
            'qty' => 0,
        ]);

        $this->actingAs($customer)
            ->from(route('cart'))
            ->post(route('cart.buy_now'), [
                'id' => $product->id,
                'quantity' => 1,
            ])
            ->assertRedirect(route('cart'))
            ->assertSessionHas('flash_notification');

        $this->assertDatabaseHas('carts', [
            'user_id' => $customer->id,
            'product_id' => $existing->id,
            'quantity' => 1,
        ]);
        $this->assertDatabaseMissing('carts', [
            'user_id' => $customer->id,
            'product_id' => $product->id,
        ]);
    }

    public function test_guest_buy_now_creates_temporary_cart_and_redirects_to_checkout_gate(): void
    {
        $product = Product::factory()->create(['name' => 'Guest Buy Now']);
        $product->stocks()->create([
            'variant' => '',
            'sku' => 'GUEST-BUY-NOW',
            'price' => 80,
            'qty' => 3,
        ]);

        $response = $this->post(route('cart.buy_now'), [
            'id' => $product->id,
            'quantity' => 1,
        ]);

        $response->assertRedirect(route('checkout.shipping_info'));
        $this->assertNotNull(session('temp_user_id'));
        $this->assertDatabaseHas('carts', [
            'temp_user_id' => session('temp_user_id'),
            'product_id' => $product->id,
            'quantity' => 1,
        ]);
    }
}
