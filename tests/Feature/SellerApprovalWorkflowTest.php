<?php

namespace Tests\Feature;

use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;
use App\Models\User;
use App\Models\Shop;

class SellerApprovalWorkflowTest extends TestCase
{
    use RefreshDatabase;

    public function test_new_seller_registration_defaults_to_pending()
    {
        $user = User::factory()->create([
            'user_type' => 'customer',
        ]);
        
        $this->actingAs($user);

        $email = 'seller_test_' . uniqid() . '@example.com';
        $response = $this->post(route('shops.store'), [
            'name' => 'John Doe',
            'email' => $email,
            'password' => 'password',
            'password_confirmation' => 'password',
            'shop_name' => 'My New Shop',
            'address' => '123 Fake Street',
            'phone' => '1234567890'
        ]);

        $response->assertRedirect();
        
        $newUser = User::where('email', $email)->first();
        $this->assertNotNull($newUser, 'User was not created');
        
        $shop = Shop::where('user_id', $newUser->id)->first();
        $this->assertNotNull($shop, 'Shop was not created');
        $this->assertEquals('pending', $shop->approval_status);
        $this->assertEquals(0, $shop->registration_approval);
    }
}
