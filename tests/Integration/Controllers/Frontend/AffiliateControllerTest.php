<?php

namespace Tests\Integration\Controllers\Frontend;

use App\Models\User;
use App\Models\AffiliateUser;
use App\Models\AffiliateLog;
use App\Models\AffiliateWithdrawRequest;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class AffiliateControllerTest extends TestCase
{
    use RefreshDatabase;

    /** @test */
    public function an_authenticated_user_can_view_affiliate_dashboard()
    {
        $user = User::factory()->create();

        $response = $this->actingAs($user)->get(route('affiliate.user.index'));

        $response->assertStatus(200);
        $response->assertViewIs('frontend.user.affiliate.index');
    }

    /** @test */
    public function an_authenticated_user_can_apply_for_affiliate_program()
    {
        $user = User::factory()->create();

        $response = $this->actingAs($user)->post(route('affiliate.apply'));

        $response->assertRedirect();
        $this->assertDatabaseHas('affiliate_users', [
            'user_id' => $user->id,
            'status' => 0
        ]);
    }

    /** @test */
    public function duplicate_affiliate_application_does_not_create_second_record()
    {
        $user = User::factory()->create();

        // First application
        $this->actingAs($user)->post(route('affiliate.apply'));

        // Second application
        $response = $this->actingAs($user)->post(route('affiliate.apply'));

        $response->assertRedirect();
        $this->assertCount(1, AffiliateUser::where('user_id', $user->id)->get());
    }

    /** @test */
    public function affiliate_user_can_view_payment_history()
    {
        $user = User::factory()->create();

        $affiliateUser = new AffiliateUser();
        $affiliateUser->user_id = $user->id;
        $affiliateUser->status = 1;
        $affiliateUser->balance = 100;
        $affiliateUser->save();

        $log = new AffiliateLog();
        $log->user_id = $user->id;
        $log->order_id = 1;
        $log->amount = 50;
        $log->status = 1;
        $log->save();

        $response = $this->actingAs($user)->get(route('affiliate.user.payment_history'));

        $response->assertStatus(200);
        $response->assertViewIs('frontend.user.affiliate.payment_history');
    }

    /** @test */
    public function affiliate_user_can_view_withdraw_request_history()
    {
        $user = User::factory()->create();

        $affiliateUser = new AffiliateUser();
        $affiliateUser->user_id = $user->id;
        $affiliateUser->status = 1;
        $affiliateUser->balance = 100;
        $affiliateUser->save();

        $withdraw = new AffiliateWithdrawRequest();
        $withdraw->user_id = $user->id;
        $withdraw->amount = 50;
        $withdraw->status = 0;
        $withdraw->save();

        $response = $this->actingAs($user)->get(route('affiliate.user.withdraw_request_history'));

        $response->assertStatus(200);
        $response->assertViewIs('frontend.user.affiliate.withdraw_request_history');
    }

    /** @test */
    public function unauthenticated_user_cannot_access_affiliate_dashboard()
    {
        $response = $this->get(route('affiliate.user.index'));

        $response->assertRedirect('/login');
    }
}
