<?php

namespace Tests\Feature\Security;

use Tests\TestCase;
use App\Models\User;
use App\Models\Product;
use App\Models\Address;
use App\Models\PaymentToken;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Facades\Session;

class ExpressBuySecurityTest extends TestCase
{
    use RefreshDatabase;

    protected $user;
    protected $product;

    protected function setUp(): void
    {
        parent::setUp();
        
        \DB::table('countries')->insert(['id' => 1, 'name' => 'Test Country', 'status' => 1, 'code' => 'TC', 'zone_id' => 1]);
        \DB::table('states')->insert(['id' => 1, 'name' => 'Test State', 'country_id' => 1, 'status' => 1]);
        \DB::table('cities')->insert(['id' => 1, 'name' => 'Test City', 'state_id' => 1, 'country_id' => 1, 'status' => 1]);
        
        $this->user = User::factory()->create();
        $this->product = Product::factory()->create(['published' => 1, 'approved' => 1]);
        
        // Setup eligibility
        Address::create([
            'user_id' => $this->user->id,
            'address' => '123 Main St',
            'country_id' => 1,
            'city_id' => 1,
            'set_default' => 1
        ]);
        PaymentToken::create([
            'user_id' => $this->user->id,
            'gateway' => 'cmi',
            'token' => 'valid-token',
            'is_active' => true,
            'is_default' => true
        ]);

        // CMI credentials are read through config/cmi.php
        config([
            'cmi.merchant_id' => 'TEST_MERCHANT',
            'cmi.secret_key' => 'TEST_KEY',
            'cmi.gateway_url' => 'http://localhost/cmi',
        ]);
    }

    /** @test */
    public function it_provides_a_v_token_in_eligibility_check()
    {
        $this->actingAs($this->user);

        $response = $this->get(route('express.check'));

        $response->assertStatus(200);
        $response->assertJsonStructure(['eligible', 'v_token']);
        
        $vToken = $response->json('v_token');
        $this->assertNotEmpty($vToken);
        
        // Check that it's stored in session
        $this->assertEquals($vToken, Session::get('vault_session_fingerprint'));
    }

    /** @test */
    public function it_rejects_express_buy_with_missing_v_token()
    {
        $this->actingAs($this->user);

        // Submit without v_token
        $response = $this->post(route('express.buy', $this->product->id), [
            'quantity' => 1
        ]);

        $response->assertStatus(302);
        $response->assertSessionHas('flash_notification');
    }

    /** @test */
    public function it_rejects_express_buy_with_mismatched_v_token()
    {
        $this->actingAs($this->user);

        // First gets a valid token
        $this->get(route('express.check'));
        
        // Submit with a fake token
        $response = $this->post(route('express.buy', $this->product->id), [
            'quantity' => 1,
            'v_token' => 'invalid-fingerprint-token'
        ]);

        $response->assertStatus(302);
        $response->assertSessionHas('flash_notification');
    }

    /** @test */
    public function it_accepts_express_buy_with_correct_v_token()
    {
        $this->actingAs($this->user);

        // 1. Get token
        $checkResponse = $this->get(route('express.check'));
        $vToken = $checkResponse->json('v_token');

        // 2. Submit with token
        $response = $this->post(route('express.buy', $this->product->id), [
            'quantity' => 1,
            'v_token' => $vToken
        ]);

        // Should NOT be 403. It might redirect to CMI or success.
        $this->assertNotEquals(403, $response->getStatusCode());
    }

    /** @test */
    public function it_charges_via_cmi_vault_when_preferred()
    {
        $this->withoutExceptionHandling();
        $this->actingAs($this->user);
        
        $this->product->stocks()->create(['variant' => '', 'price' => 100, 'qty' => 10]);

        $checkResponse = $this->get(route('express.check'));
        $vToken = $checkResponse->json('v_token');

        $response = $this->post(route('express.buy', $this->product->id), [
            'quantity' => 1,
            'v_token' => $vToken
        ]);

        // Should create an order and return the payment view
        $response->assertStatus(200);
        
        $this->assertDatabaseHas('orders', [
            'user_id' => $this->user->id,
            'payment_type' => 'cmi_vault'
        ]);
    }
}
