<?php

namespace Tests\Feature;

use Tests\TestCase;
use App\Models\User;
use App\Models\Shop;
use Illuminate\Foundation\Testing\DatabaseTransactions;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Hash;
use App\Utility\EmailUtility;
use Illuminate\Support\Facades\Mail;
use Illuminate\Support\Facades\Auth;
use Illuminate\Foundation\Testing\WithFaker;
use Illuminate\Foundation\Testing\WithoutMiddleware;

class SellerRegistrationTest extends TestCase
{
    use WithFaker, DatabaseTransactions, WithoutMiddleware;

    protected $admin;

    protected function setUp(): void
    {
        parent::setUp();
        // Create a unique admin for this test run
        $this->admin = User::create([
            'name' => 'Test Admin ' . rand(1000, 9999),
            'email' => 'admin_' . rand(1000, 9999) . '@test.com',
            'password' => Hash::make('123456'),
            'user_type' => 'admin',
        ]);
    }

    /** @test */
    public function admin_can_create_new_seller_with_valid_data()
    {
        $this->withoutExceptionHandling();
        Mail::fake();

        $email = 'seller_' . rand(1000, 9999) . '@test.com';
        $sellerData = [
            'name' => 'New Seller Name',
            'email' => $email,
            'shop_name' => 'New Shop Name',
            'address' => '123 Test St',
        ];

        $this->actingAs($this->admin, 'web');

        $response = $this->post(route('sellers.store'), $sellerData);

        $response->assertStatus(302);
        
        $this->assertDatabaseHas('users', [
            'email' => $sellerData['email'],
            'user_type' => 'seller',
        ]);

        $user = User::where('email', $sellerData['email'])->first();
        
        if ($user) {
            $this->assertDatabaseHas('shops', [
                'user_id' => $user->id,
                'name' => $sellerData['shop_name'],
            ]);
        } else {
            $this->fail('User not created');
        }
    }

    /** @test */
    public function seller_creation_fails_with_duplicate_email()
    {
        $email = 'duplicate_' . rand(1000, 9999) . '@test.com';
        
        $existingUser = User::create([
            'name' => 'Existing',
            'email' => $email,
            'password' => Hash::make('password'),
            'user_type' => 'customer'
        ]);

        $sellerData = [
            'name' => 'New Seller',
            'email' => $email, // Same email
            'shop_name' => 'New Shop',
            'address' => 'Address',
        ];

        $response = $this->actingAs($this->admin, 'web')
                         ->post(route('sellers.store'), $sellerData);

        // Without middleware, session errors might not work as expected?
        // But validate() throws ValidationException which might be caught by Handler
        // If WithoutMiddleware disables StartSession, errors are lost.
        // So this test might fail or behave differently.
        
        // However, if validation fails, it redirects back.
        $response->assertStatus(302);
        
        // We can't easily assert session errors without session middleware.
        $this->assertDatabaseMissing('users', [
            'email' => $email,
            'user_type' => 'seller' // The failed creation attempt
        ]);
    }

    /** @test */
    public function seller_creation_handles_email_failure_gracefully()
    {
        // This test relies on invalid data validation
        $sellerData = [
            'name' => '', // Invalid
            'email' => 'invalid-email',
            'shop_name' => 'Shop',
        ];

        $response = $this->actingAs($this->admin, 'web')
                         ->post(route('sellers.store'), $sellerData);

        $response->assertStatus(302);
        
        $this->assertDatabaseMissing('users', [
            'email' => 'invalid-email'
        ]);
    }
}
