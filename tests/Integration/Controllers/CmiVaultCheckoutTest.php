<?php

namespace Tests\Integration\Controllers;

use Tests\TestCase;
use App\Models\User;
use App\Models\Product;
use App\Models\Address;
use App\Models\Cart;
use App\Models\CombinedOrder;
use App\Models\PaymentToken;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Facades\Session;
use Auth;

class CmiVaultCheckoutTest extends TestCase
{
    use RefreshDatabase;

    protected $user;
    protected $product;
    protected $address;

    protected function setUp(): void
    {
        parent::setUp();
        
        $this->user = User::factory()->create();
        $this->product = Product::factory()->create(['unit_price' => 100, 'min_qty' => 1]);
        
        // Ensure Country and City exist
        $country = \App\Models\Country::create(['name' => 'Morocco', 'code' => 'MA', 'status' => 1]);
        $city = \App\Models\City::create(['name' => 'Casablanca', 'country_id' => $country->id, 'status' => 1]);

        $this->address = Address::create([
            'user_id' => $this->user->id,
            'address' => 'Test Address',
            'country_id' => $country->id,
            'city_id' => $city->id,
            'phone' => '1234567890',
            'set_default' => 1
        ]);
        
        Auth::login($this->user);

        // Create Product Stock
        \App\Models\ProductStock::create([
            'product_id' => $this->product->id,
            'variant' => '',
            'price' => 100,
            'sku' => 'TEST-SKU',
            'qty' => 100
        ]);
    }

    /** @test */
    public function it_can_initiate_checkout_using_a_vaulted_token()
    {
        // 1. Setup a valid token
        $token = PaymentToken::create([
            'user_id' => $this->user->id,
            'gateway' => 'cmi',
            'token' => 'VALID-VAULT-TOKEN-123',
            'card_expiry_month' => 12,
            'card_expiry_year' => 2030,
            'is_active' => true,
            'is_default' => true
        ]);

        // 2. Add product to cart
        Cart::create([
            'user_id' => $this->user->id,
            'product_id' => $this->product->id,
            'quantity' => 1,
            'price' => 100,
            'address_id' => $this->address->id,
            'billing_address' => $this->address->id,
            'status' => 1,
            'shipping_cost' => 0
        ]);

        // 3. Mock checkout session
        $combinedOrder = CombinedOrder::create([
            'user_id' => $this->user->id,
            'shipping_address' => json_encode($this->address),
            'grand_total' => 100
        ]);
        Session::put('combined_order_id', $combinedOrder->id);
        Session::put('payment_type', 'cart_payment');

        // 4. Hit the payment.checkout route with cmi_vault
        $response = $this->post(route('payment.checkout'), [
            'payment_option' => 'cmi_vault',
            'payment_token_id' => $token->id
        ]);

        // 5. Assert redirect to CMI (through CmiVaultController -> CmiController::expressCharge)
        // Express charge returns a view 'frontend.payment.cmi' which we check
        $response->assertStatus(200);
        $response->assertViewIs('frontend.payment.cmi');
        $response->assertViewHas('data');
        
        $viewData = $response->viewData('data');
        $this->assertEquals('VALID-VAULT-TOKEN-123', $viewData['recurringTrxnRef']);
        $this->assertEquals('true', $viewData['isRecurring']);
        $this->assertEquals(100, $viewData['amount']);
    }

    /** @test */
    public function it_fails_if_invalid_token_is_provided()
    {
        // Setup cart/session as above
        Cart::create([
            'user_id' => $this->user->id,
            'product_id' => $this->product->id,
            'quantity' => 1,
            'price' => 100,
            'address_id' => $this->address->id,
            'billing_address' => $this->address->id,
            'status' => 1,
            'shipping_cost' => 0
        ]);
        
        $combinedOrder = CombinedOrder::create([
            'user_id' => $this->user->id,
            'shipping_address' => json_encode($this->address),
            'grand_total' => 100
        ]);
        Session::put('combined_order_id', $combinedOrder->id);
        Session::put('payment_type', 'cart_payment');

        // Hit with non-existent token ID
        $response = $this->post(route('payment.checkout'), [
            'payment_option' => 'cmi_vault',
            'payment_token_id' => 999
        ]);

        $response->assertRedirect(route('checkout.payment_info'));
        $response->assertSessionHas('flash_notification.0.message', 'Invalid payment token selected.');
    }

    /** @test */
    public function it_fails_if_token_belongs_to_another_user()
    {
        $otherUser = User::factory()->create();
        $token = PaymentToken::create([
            'user_id' => $otherUser->id,
            'gateway' => 'cmi',
            'token' => 'OTHER-USER-TOKEN',
            'is_active' => true
        ]);

        Cart::create([
            'user_id' => $this->user->id,
            'product_id' => $this->product->id,
            'quantity' => 1,
            'price' => 100,
            'address_id' => $this->address->id,
            'billing_address' => $this->address->id,
            'status' => 1,
            'shipping_cost' => 0
        ]);
        
        $combinedOrder = CombinedOrder::create([
            'user_id' => $this->user->id,
            'shipping_address' => json_encode($this->address),
            'grand_total' => 100
        ]);
        Session::put('combined_order_id', $combinedOrder->id);
        Session::put('payment_type', 'cart_payment');

        $response = $this->post(route('payment.checkout'), [
            'payment_option' => 'cmi_vault',
            'payment_token_id' => $token->id
        ]);

        $response->assertRedirect(route('checkout.payment_info'));
    }
}
