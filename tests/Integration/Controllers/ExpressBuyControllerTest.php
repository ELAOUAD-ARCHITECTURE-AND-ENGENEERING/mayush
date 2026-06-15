<?php

namespace Tests\Integration\Controllers;

use Tests\TestCase;
use App\Models\User;
use App\Models\Address;
use App\Models\Product;
use App\Models\Cart;
use App\Models\Order;
use App\Models\CombinedOrder;
use App\Models\PaymentToken;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Facades\Session;

class ExpressBuyControllerTest extends TestCase
{
    use RefreshDatabase;

    /** @test */
    public function it_returns_false_eligibility_when_user_has_no_address()
    {
        $user = User::factory()->create();
        $this->actingAs($user);

        $response = $this->get('/express-buy/check');
        
        $response->assertStatus(200);
        $this->assertFalse($response->json('eligible'));
    }

    /** @test */
    public function it_returns_false_eligibility_when_user_has_no_vaulted_token()
    {
        $user = User::factory()->create();
        $this->actingAs($user);
        
        // Add default address
        Address::create([
            'user_id' => $user->id,
            'address' => '123 Test St',
            'country_id' => 1,
            'city_id' => 1,
            'phone' => '1234567890',
            'set_default' => 1
        ]);

        $response = $this->get('/express-buy/check');
        
        $response->assertStatus(200);
        $this->assertFalse($response->json('eligible'));
    }

    /** @test */
    public function it_returns_true_eligibility_when_user_has_address_and_token()
    {
        $user = User::factory()->create();
        $this->actingAs($user);
        
        // Add default address
        Address::create([
            'user_id' => $user->id,
            'address' => '123 Test St',
            'country_id' => 1,
            'city_id' => 1,
            'phone' => '1234567890',
            'set_default' => 1
        ]);

        // Add vaulted token
        PaymentToken::create([
            'user_id' => $user->id,
            'gateway' => 'cmi',
            'token' => 'TRANSID_123456789',
            'is_active' => true,
            'is_default' => true
        ]);

        $response = $this->get('/express-buy/check');
        
        $response->assertStatus(200);
        $this->assertTrue($response->json('eligible'));
    }
}
