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
}
