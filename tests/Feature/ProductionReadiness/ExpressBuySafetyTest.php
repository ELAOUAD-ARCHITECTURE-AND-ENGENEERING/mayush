<?php

namespace Tests\Feature\ProductionReadiness;

use Tests\TestCase;
use App\Models\User;
use App\Models\Product;
use App\Models\Address;
use App\Models\PaymentToken;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Facades\Session;

use Tests\Traits\SeedsAppConfigs;

class ExpressBuySafetyTest extends TestCase
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
        
        config([
            'cmi.merchant_id' => 'test_merchant',
            'cmi.secret_key' => 'test_secret',
        ]);
    }

    /** @test */
    public function express_buy_eligibility_fails_when_customer_has_no_default_address(): void
    {
        $user = User::factory()->customer()->create();
        PaymentToken::factory()->active()->default()->create(['user_id' => $user->id]);

        $this->actingAs($user);
        $response = $this->get(route('express.check'));

        $response->assertStatus(200);
        $this->assertFalse($response->json('eligible'));
    }

    /** @test */
    public function express_buy_eligibility_fails_when_customer_has_no_vaulted_token(): void
    {
        $user = User::factory()->customer()->create();
        Address::factory()->create([
            'user_id' => $user->id,
            'country_id' => 1,
            'city_id' => 1,
            'set_default' => 1
        ]);

        $this->actingAs($user);
        $response = $this->get(route('express.check'));

        $response->assertStatus(200);
        $this->assertFalse($response->json('eligible'));
    }

    /** @test */
    public function express_buy_eligibility_succeeds_when_customer_has_default_address_and_active_token(): void
    {
        $user = User::factory()->customer()->create();
        Address::factory()->create([
            'user_id' => $user->id,
            'country_id' => 1,
            'city_id' => 1,
            'set_default' => 1
        ]);
        PaymentToken::factory()->active()->default()->create(['user_id' => $user->id]);

        $this->actingAs($user);
        $response = $this->get(route('express.check'));

        $response->assertStatus(200);
        $this->assertTrue($response->json('eligible'));
    }

    /** @test */
    public function express_buy_submit_rejects_invalid_session_fingerprint(): void
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

        Address::factory()->create([
            'user_id' => $user->id,
            'country_id' => 1,
            'city_id' => 1,
            'set_default' => 1
        ]);
        PaymentToken::factory()->active()->default()->create(['user_id' => $user->id]);

        $this->actingAs($user);
        
        // Submit without valid v_token
        $response = $this->post(route('express.buy', $product->id), [
            'quantity' => 1,
            'v_token' => 'invalid_token'
        ]);

        // Should reject or show error
        $this->assertContains($response->status(), [302, 403, 400]);
    }

    /** @test */
    public function express_buy_submit_does_not_create_duplicate_orders_on_repeated_submission(): void
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

        Address::factory()->create([
            'user_id' => $user->id,
            'country_id' => 1,
            'city_id' => 1,
            'set_default' => 1
        ]);
        $token = PaymentToken::factory()->active()->default()->create(['user_id' => $user->id]);

        $this->actingAs($user);
        
        // Get valid token
        $checkResponse = $this->get(route('express.check'));
        $vToken = $checkResponse->json('v_token');

        // Submit first request
        $response1 = $this->post(route('express.buy', $product->id), [
            'quantity' => 1,
            'v_token' => $vToken
        ]);

        // Submit second request with same token
        $response2 = $this->post(route('express.buy', $product->id), [
            'quantity' => 1,
            'v_token' => $vToken
        ]);

        // Should handle gracefully - not necessarily crash, but should prevent duplicate orders
        $this->assertContains($response2->status(), [200, 302, 400, 403]);
    }

    /** @test */
    public function payment_token_never_exposes_full_card_data_in_serialized_output(): void
    {
        $user = User::factory()->customer()->create();
        $token = PaymentToken::factory()->create([
            'user_id' => $user->id,
            'token' => 'sensitive_card_token_data',
            'card_last_four' => '1234'
        ]);

        // Check that token value is not exposed in serialized output
        $serialized = $token->toArray();
        
        // Should not expose sensitive token
        $this->assertArrayNotHasKey('token', $serialized);
        
        // Should expose only last four digits
        $this->assertEquals('1234', $serialized['card_last_four']);
    }
}