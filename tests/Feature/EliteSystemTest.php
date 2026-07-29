<?php

namespace Tests\Feature;

use App\Models\EliteSubscription;
use App\Models\Shop;
use App\Models\User;
use App\Models\BusinessSetting;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Foundation\Testing\WithFaker;
use Tests\TestCase;
use Carbon\Carbon;

class EliteSystemTest extends TestCase
{
    use RefreshDatabase;

    public function setUp(): void
    {
        parent::setUp();
        
        // Ensure necessary settings exist
        BusinessSetting::updateOrCreate(['type' => 'elite_system_active'], ['value' => '1']);
    }

    private function createShop($user)
    {
        $shop = new Shop();
        $shop->user_id = $user->id;
        $shop->name = 'Test Shop ' . uniqid();
        $shop->slug = 'test-shop-' . uniqid();
        $shop->verification_status = 1;
        $shop->approval_status = 'approved';
        $shop->save();
        return $shop;
    }

    /** @test */
    public function an_active_subscription_grants_elite_status()
    {
        $user = User::factory()->create(['user_type' => 'seller']);
        $shop = $this->createShop($user);

        EliteSubscription::create([
            'shop_id' => $shop->id,
            'status' => 'active',
            'amount_paid' => 19.99,
            'billing_cycle' => 'monthly',
            'expires_at' => Carbon::now()->addDays(30)
        ]);

        $this->assertTrue($shop->isElite());
    }

    /** @test */
    public function expired_subscriptions_do_not_grant_elite_status()
    {
        $user = User::factory()->create(['user_type' => 'seller']);
        $shop = $this->createShop($user);

        EliteSubscription::create([
            'shop_id' => $shop->id,
            'status' => 'expired',
            'amount_paid' => 19.99,
            'billing_cycle' => 'monthly',
            'expires_at' => Carbon::now()->subDays(1)
        ]);

        $this->assertFalse($shop->isElite());
    }

    /** @test */
    public function system_killswitch_disables_elite_status()
    {
        $user = User::factory()->create(['user_type' => 'seller']);
        $shop = $this->createShop($user);

        EliteSubscription::create([
            'shop_id' => $shop->id,
            'status' => 'active',
            'amount_paid' => 19.99,
            'billing_cycle' => 'monthly',
            'expires_at' => Carbon::now()->addDays(30)
        ]);

        // It is elite when system is active
        $this->assertTrue($shop->isElite());

        // Toggle killswitch
        BusinessSetting::updateOrCreate(['type' => 'elite_system_active'], ['value' => '0']);

        // Clear cache if needed (if settings are cached)
        \Cache::forget('business_settings');

        // Now should be false
        $this->assertFalse($shop->isElite());
    }

    /** @test */
    public function artisan_command_expires_past_subscriptions()
    {
        $user = User::factory()->create(['user_type' => 'seller']);
        $shop = $this->createShop($user);

        $sub = EliteSubscription::create([
            'shop_id' => $shop->id,
            'status' => 'active',
            'amount_paid' => 19.99,
            'billing_cycle' => 'monthly',
            'expires_at' => Carbon::now()->subDays(1) // Expired yesterday
        ]);

        $this->artisan('elite:expire')->assertExitCode(0);

        $sub->refresh();
        $this->assertEquals('expired', $sub->status);
    }

    /** @test */
    public function elite_payment_redirection_works_correctly()
    {
        $user = User::factory()->create(['user_type' => 'seller']);
        $shop = $this->createShop($user);
        
        // Mock session and auth
        $this->actingAs($user);
        
        // CMI credentials are read through config/cmi.php, not BusinessSetting rows.
        config([
            'cmi.merchant_id' => 'validmerchant123',
            'cmi.secret_key' => 'valid-secret-key-16chars',
            'cmi.gateway_url' => 'https://test-attijari.cmi.co.ma/fim/est3Dgate',
        ]);

        // 1. Initiate processPayment to set session
        $response = $this->post(route('seller.elite.process_payment'), [
            'billing_cycle' => 'monthly'
        ]);
        
        $sub = EliteSubscription::where('shop_id', $shop->id)->first();
        $this->assertNotNull($sub);
        
        // 2. Follow redirect to cmi.pay
        $response->assertRedirect(route('cmi.pay'));
        
        // 3. Test cmi.pay directly with session set
        $response = $this->withSession([
            'payment_type' => 'elite_payment',
            'payment_data' => [
                'subscription_id' => $sub->id,
                'amount' => $sub->amount_paid
            ],
            'elite_subscription_id' => $sub->id
        ])->get(route('cmi.pay'));

        // Assert it does NOT redirect to home, instead returns the CMI payment view (status 200)
        // Note: CmiController::pay() returns a view 'frontend.payment.cmi'
        $response->assertStatus(200);
        $response->assertViewIs('frontend.payment.cmi');
        $response->assertSee(translate('Redirecting to CMI...'));
    }
}
