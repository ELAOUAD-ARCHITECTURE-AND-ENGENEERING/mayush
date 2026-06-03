<?php

namespace Tests\Feature\Seller;

use App\Models\Shop;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;
use Tests\Traits\SeedsAppConfigs;

class AccountModeSwitchTest extends TestCase
{
    use RefreshDatabase, SeedsAppConfigs;

    protected function setUp(): void
    {
        parent::setUp();

        $this->seedConfigs();
    }

    public function test_verified_seller_sees_switch_to_buyer_in_seller_mode(): void
    {
        $seller = $this->approvedSeller();

        $this->actingAs($seller)
            ->get(route('seller.dashboard'))
            ->assertOk()
            ->assertSee('Switch to Buyer');
    }

    public function test_buyer_only_account_does_not_see_switcher(): void
    {
        $customer = User::factory()->customer()->create();

        $this->actingAs($customer)
            ->get(route('dashboard'))
            ->assertOk()
            ->assertDontSee('Switch to Buyer')
            ->assertDontSee('Switch to Seller')
            ->assertDontSee('data-account-mode-switcher', false);
    }

    public function test_unapproved_seller_account_cannot_use_switcher(): void
    {
        $seller = User::factory()->seller()->create();
        Shop::factory()->create([
            'user_id' => $seller->id,
            'registration_approval' => 0,
            'approval_status' => 'pending',
            'verification_status' => 0,
        ]);

        $this->actingAs($seller)
            ->get(route('seller.dashboard'))
            ->assertOk()
            ->assertDontSee('Switch to Buyer')
            ->assertDontSee('data-account-mode-switcher', false);

        $this->actingAs($seller)
            ->post(route('account-mode.switch'), ['mode' => 'buyer'])
            ->assertForbidden();
    }

    public function test_seller_can_switch_to_buyer_mode_for_current_session(): void
    {
        $seller = $this->approvedSeller();

        $this->actingAs($seller)
            ->post(route('account-mode.switch'), ['mode' => 'buyer'])
            ->assertRedirect(route('dashboard'))
            ->assertSessionHas('account_mode', 'buyer');

        $this->actingAs($seller)
            ->withSession(['account_mode' => 'buyer'])
            ->get(route('dashboard'))
            ->assertOk()
            ->assertSee('Switch to Seller')
            ->assertDontSee('Switch to Buyer');
    }

    public function test_seller_can_switch_back_to_seller_mode(): void
    {
        $seller = $this->approvedSeller();

        $this->actingAs($seller)
            ->withSession(['account_mode' => 'buyer'])
            ->post(route('account-mode.switch'), ['mode' => 'seller'])
            ->assertRedirect(route('seller.dashboard'))
            ->assertSessionHas('account_mode', 'seller');
    }

    public function test_buyer_only_account_cannot_switch_modes_or_access_seller_dashboard(): void
    {
        $customer = User::factory()->customer()->create();

        $this->actingAs($customer)
            ->post(route('account-mode.switch'), ['mode' => 'seller'])
            ->assertForbidden();

        $this->actingAs($customer)
            ->get(route('seller.dashboard'))
            ->assertStatus(404);
    }

    public function test_active_mode_controls_buyer_and_seller_route_access(): void
    {
        $seller = $this->approvedSeller();

        $this->actingAs($seller)
            ->withSession(['account_mode' => 'buyer'])
            ->get(route('seller.dashboard'))
            ->assertRedirect(route('dashboard'));

        $this->actingAs($seller)
            ->withSession(['account_mode' => 'buyer'])
            ->get(route('purchase_history.index'))
            ->assertOk();

        $this->actingAs($seller)
            ->withSession(['account_mode' => 'seller'])
            ->get(route('purchase_history.index'))
            ->assertRedirect(route('user.login'));
    }

    public function test_invalid_mode_is_rejected_without_changing_session(): void
    {
        $seller = $this->approvedSeller();

        $this->actingAs($seller)
            ->from(route('dashboard'))
            ->post(route('account-mode.switch'), ['mode' => 'admin'])
            ->assertSessionHasErrors('mode')
            ->assertSessionMissing('account_mode');
    }

    private function approvedSeller(): User
    {
        $seller = User::factory()->seller()->create();

        Shop::factory()->create([
            'user_id' => $seller->id,
            'registration_approval' => 1,
            'approval_status' => 'approved',
            'verification_status' => 1,
        ]);

        return $seller;
    }
}
