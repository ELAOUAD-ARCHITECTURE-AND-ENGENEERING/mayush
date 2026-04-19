<?php

namespace Tests\Integration;

use Tests\TestCase;
use App\Models\User;
use App\Models\Order;
use App\Models\OrderDetail;
use App\Models\Product;
use App\Models\CustomerPackage;
use App\Models\ClubPoint;
use App\Models\ClubPointDetail;
use App\Services\LoyaltyService;
use Illuminate\Foundation\Testing\RefreshDatabase;

class LoyaltyIntegrationTest extends TestCase
{
    use RefreshDatabase;

    protected function setUp(): void
    {
        parent::setUp();
        
        // Create Loyalty Tiers
        $this->silverTier = CustomerPackage::factory()->create([
            'name' => 'Silver',
            'min_spend' => 1000,
            'loyalty_multiplier' => 1.2,
            'tier_level' => 1,
            'is_loyalty_tier' => true
        ]);
        
        $this->goldTier = CustomerPackage::factory()->create([
            'name' => 'Gold',
            'min_spend' => 5000,
            'loyalty_multiplier' => 1.5,
            'tier_level' => 2,
            'is_loyalty_tier' => true
        ]);

        $this->user = User::factory()->create(['customer_package_id' => null, 'annual_spend' => 0, 'balance' => 0]);
        $this->loyaltyService = new LoyaltyService();
    }

    /** @test */
    public function user_upgrades_tier_after_reaching_spend_threshold()
    {
        // 1. Create a large paid order
        $order = Order::factory()->create([
            'user_id' => $this->user->id,
            'grand_total' => 1500,
            'payment_status' => 'paid',
            'created_at' => now()
        ]);

        // 2. Trigger tier recalculation
        $this->loyaltyService->recalculateTier($this->user->fresh());

        // 3. Verify user is now Silver
        $this->assertEquals($this->silverTier->id, $this->user->fresh()->customer_package_id);
    }

    /** @test */
    public function higher_tier_user_earns_more_points_per_product()
    {
        $product = Product::factory()->create(['earn_point' => 100]);

        // Basic User points
        $basicPoints = $this->loyaltyService->getPotentialPoints($product, $this->user);
        $this->assertEquals(100, $basicPoints);

        // Upgrade to Gold
        $this->user->customer_package_id = $this->goldTier->id;
        $this->user->save();

        // goldPoints = 100 * 1.5 = 150
        $goldPoints = $this->loyaltyService->getPotentialPoints($product, $this->user->fresh());
        $this->assertEquals(150, $goldPoints);
    }

    /** @test */
    public function user_can_convert_points_to_wallet_balance()
    {
        // 1. Give user some points
        $clubPoint = ClubPoint::create(['user_id' => $this->user->id, 'points' => 1000]);
        
        // 2. Mock conversion rate (10 points = 1 MAD)
        // Ensure the setting is present in the test DB
        \App\Models\BusinessSetting::create(['type' => 'club_point_convert_rate', 'value' => 10]);

        // 3. Request conversion via controller
        $this->actingAs($this->user)
             ->post(route('convert_point_into_wallet'), ['points' => 500]);

        // 4. Verify results
        // 1000 - 500 = 500 points left
        $this->assertEquals(500, $clubPoint->fresh()->points);
        // 500 / 10 = 50 MAD added to wallet
        $this->assertEquals(50, $this->user->fresh()->balance);
    }
}
