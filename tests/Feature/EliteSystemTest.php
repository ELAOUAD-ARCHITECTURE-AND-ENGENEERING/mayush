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
        \Cache::forget('elite_system_active');

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
}
