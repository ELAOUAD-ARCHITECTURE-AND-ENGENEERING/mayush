<?php

namespace Tests\Feature\Affiliate;

use App\Http\Controllers\AffiliateController;
use App\Models\AffiliateLog;
use App\Models\AffiliateStats;
use App\Models\AffiliateUser;
use App\Models\AffiliateWithdrawRequest;
use App\Models\Order;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class AffiliateFlowTest extends TestCase
{
    use RefreshDatabase;

    public function test_user_dashboard_generates_referral_code_and_link(): void
    {
        \App\Models\Addon::updateOrCreate(['unique_identifier' => 'affiliate_system'], ['name' => 'Affiliate System', 'activated' => 1]);
        $setting = \App\Models\BusinessSetting::firstOrNew(['type' => 'affiliate_system_activation']);
        $setting->value = 1;
        $setting->save();

        $user = User::factory()->create(['referral_code' => null]);
        $this->affiliateUser($user, ['status' => 1]);

        $response = $this->actingAs($user)->get(route('affiliate.user.index'));

        $response->assertOk();
        $user->refresh();

        $this->assertNotNull($user->referral_code);
        $response->assertSee(route('home', ['referral_code' => $user->referral_code]), false);
    }

    public function test_user_can_apply_once_for_affiliate_program(): void
    {
        $user = User::factory()->create();

        $this->actingAs($user)->post(route('affiliate.apply'))->assertRedirect();
        $this->actingAs($user)->post(route('affiliate.apply'))->assertRedirect();

        $this->assertSame(1, AffiliateUser::where('user_id', $user->id)->count());
        $this->assertDatabaseHas('affiliate_users', [
            'user_id' => $user->id,
            'status' => 0,
        ]);
    }

    public function test_approved_affiliate_can_create_withdrawal_request(): void
    {
        $user = User::factory()->create();
        $this->affiliateUser($user, ['status' => 1, 'balance' => 100]);

        $this->actingAs($user)
            ->post(route('affiliate.withdraw_request.store'), ['amount' => 40])
            ->assertRedirect();

        $this->assertDatabaseHas('affiliate_withdraw_requests', [
            'user_id' => $user->id,
            'amount' => 40,
            'status' => 0,
        ]);
    }

    public function test_withdrawal_amount_cannot_exceed_affiliate_balance(): void
    {
        $user = User::factory()->create();
        $this->affiliateUser($user, ['status' => 1, 'balance' => 25]);

        $this->actingAs($user)
            ->from(route('affiliate.user.index'))
            ->post(route('affiliate.withdraw_request.store'), ['amount' => 40])
            ->assertRedirect(route('affiliate.user.index'))
            ->assertSessionHasErrors('amount');

        $this->assertDatabaseMissing('affiliate_withdraw_requests', [
            'user_id' => $user->id,
            'amount' => 40,
        ]);
    }

    public function test_affiliate_payment_history_is_scoped_to_current_user(): void
    {
        $user = User::factory()->create();
        $other = User::factory()->create();

        AffiliateLog::forceCreate([
            'user_id' => $user->id,
            'order_id' => 11,
            'amount' => 15,
            'status' => 1,
        ]);
        AffiliateLog::forceCreate([
            'user_id' => $other->id,
            'order_id' => 22,
            'amount' => 99,
            'status' => 1,
        ]);

        $response = $this->actingAs($user)->get(route('affiliate.user.payment_history'));

        $response->assertOk();
        $response->assertViewHas('affiliate_logs', function ($logs) use ($user, $other) {
            return $logs->contains('user_id', $user->id)
                && !$logs->contains('user_id', $other->id);
        });
    }

    public function test_withdrawal_history_is_scoped_to_current_user(): void
    {
        $user = User::factory()->create();
        $other = User::factory()->create();

        AffiliateWithdrawRequest::create(['user_id' => $user->id, 'amount' => 30, 'status' => 0]);
        AffiliateWithdrawRequest::create(['user_id' => $other->id, 'amount' => 88, 'status' => 0]);

        $response = $this->actingAs($user)->get(route('affiliate.user.withdraw_request_history'));

        $response->assertOk();
        $response->assertViewHas('withdraw_requests', function ($requests) use ($user, $other) {
            return $requests->contains('user_id', $user->id)
                && !$requests->contains('user_id', $other->id);
        });
    }

    public function test_referral_link_visit_stores_cookie_and_tracks_click(): void
    {
        $referrer = User::factory()->create(['referral_code' => 'ref-abc']);

        $response = $this->get(route('home', ['referral_code' => $referrer->referral_code]));

        $response->assertCookie('referral_code', $referrer->referral_code);
        $this->assertDatabaseHas('affiliate_stats', [
            'user_id' => $referrer->id,
            'no_of_click' => 1,
        ]);
    }

    public function test_affiliate_commission_is_awarded_once_per_order(): void
    {
        $referrer = User::factory()->create();
        $buyer = User::factory()->create(['referred_by' => $referrer->id]);
        $this->affiliateUser($referrer, ['status' => 1, 'balance' => 0]);
        $order = Order::factory()->create([
            'user_id' => $buyer->id,
            'grand_total' => 200,
        ]);

        $controller = app(AffiliateController::class);
        $controller->processAffiliatePoints($order);
        $controller->processAffiliatePoints($order);

        $this->assertSame(1, AffiliateLog::where('user_id', $referrer->id)->where('order_id', $order->id)->count());
        $this->assertEquals(10, AffiliateUser::where('user_id', $referrer->id)->first()->balance);
    }

    public function test_admin_approves_affiliate_with_post_only_route(): void
    {
        $admin = User::factory()->admin()->create();
        $applicant = User::factory()->create();
        $affiliateUser = $this->affiliateUser($applicant, ['status' => 0]);

        $this->actingAs($admin)
            ->get(route('admin.affiliate.users.approve', $affiliateUser->id))
            ->assertNotFound();

        $this->assertDatabaseHas('affiliate_users', [
            'id' => $affiliateUser->id,
            'status' => 0,
        ]);

        $this->actingAs($admin)
            ->post(route('admin.affiliate.users.approve', $affiliateUser->id))
            ->assertRedirect();

        $this->assertDatabaseHas('affiliate_users', [
            'id' => $affiliateUser->id,
            'status' => 1,
        ]);
    }

    public function test_admin_affiliate_users_view_uses_post_approval_form(): void
    {
        $view = file_get_contents(resource_path('views/backend/marketing/affiliate/users.blade.php'));

        $this->assertStringContainsString('method="POST"', $view);
        $this->assertStringContainsString("@csrf", $view);
        $this->assertStringContainsString("admin.affiliate.users.approve", $view);
    }

    private function affiliateUser(User $user, array $attributes = []): AffiliateUser
    {
        return AffiliateUser::forceCreate(array_merge([
            'user_id' => $user->id,
            'status' => 1,
            'balance' => 0,
        ], $attributes));
    }
}
