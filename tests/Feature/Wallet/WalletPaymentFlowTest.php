<?php

namespace Tests\Feature\Wallet;

use App\Http\Controllers\WalletController;
use App\Models\User;
use App\Models\Wallet;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Http\Request;
use Tests\TestCase;

class WalletPaymentFlowTest extends TestCase
{
    use RefreshDatabase;

    public function test_wallet_page_shows_recharge_action(): void
    {
        $view = file_get_contents(resource_path('views/frontend/user/wallet/index.blade.php'));
        $modal = file_get_contents(resource_path('views/frontend/partials/wallet_modal.blade.php'));

        $this->assertStringContainsString('Recharge Wallet', $view);
        $this->assertStringContainsString("show_wallet_modal()", $view);
        $this->assertStringContainsString("route('wallet.recharge')", $modal);
        $this->assertStringContainsString('name="amount"', $modal);
    }

    public function test_wallet_recharge_initiation_stores_payment_session_without_crediting_immediately(): void
    {
        $user = User::factory()->create(['balance' => 0]);

        $this->actingAs($user)
            ->from(route('wallet.index'))
            ->post(route('wallet.recharge'), [
                'amount' => 50,
                'payment_option' => 'unavailable_gateway',
            ])
            ->assertRedirect(route('wallet.index'))
            ->assertSessionHas('payment_type', 'wallet_payment')
            ->assertSessionHas('payment_data.amount', '50')
            ->assertSessionHas('payment_data.payment_method', 'unavailable_gateway');

        $this->assertSame(0, Wallet::where('user_id', $user->id)->count());
        $this->assertEquals(0, $user->fresh()->balance);
    }

    public function test_invalid_wallet_recharge_amount_fails_validation(): void
    {
        $user = User::factory()->create(['balance' => 0]);

        $this->actingAs($user)
            ->from(route('wallet.index'))
            ->post(route('wallet.recharge'), [
                'amount' => 0,
                'payment_option' => 'wallet',
            ])
            ->assertRedirect(route('wallet.index'))
            ->assertSessionHasErrors('amount');

        $this->assertSame(0, Wallet::where('user_id', $user->id)->count());
    }

    public function test_failed_wallet_payment_does_not_credit_wallet(): void
    {
        $user = User::factory()->create(['balance' => 0]);

        $this->actingAs($user)
            ->withSession([
                'payment_type' => 'wallet_payment',
                'payment_data' => [
                    'amount' => 60,
                    'payment_method' => 'Iyzico',
                ],
            ])
            ->get(route('payment.failed'))
            ->assertOk()
            ->assertSee(route('wallet.index'), false);

        $this->assertSame(0, Wallet::where('user_id', $user->id)->count());
        $this->assertEquals(0, $user->fresh()->balance);
    }

    public function test_web_wallet_success_callback_is_idempotent(): void
    {
        $user = User::factory()->create(['balance' => 0]);
        $paymentData = [
            'amount' => 25,
            'payment_method' => 'Iyzico',
        ];
        $paymentDetails = json_encode(['payment_id' => 'iyzico-wallet-1']);

        $this->actingAs($user);

        app(WalletController::class)->wallet_payment_done($paymentData, $paymentDetails);
        app(WalletController::class)->wallet_payment_done($paymentData, $paymentDetails);

        $this->assertSame(1, Wallet::where('user_id', $user->id)->count());
        $this->assertEquals(25, $user->fresh()->balance);
        $this->assertDatabaseHas('wallets', [
            'user_id' => $user->id,
            'amount' => 25,
            'payment_reference' => 'iyzico:iyzico-wallet-1',
        ]);
    }

    public function test_global_wallet_payment_helper_is_idempotent_for_api_callbacks(): void
    {
        $user = User::factory()->create(['balance' => 0]);
        $paymentDetails = json_encode(['transaction_id' => 'flutterwave-wallet-1']);

        wallet_payment_done($user->id, 30, 'Flutterwave', $paymentDetails);
        wallet_payment_done($user->id, 30, 'Flutterwave', $paymentDetails);

        $this->assertSame(1, Wallet::where('user_id', $user->id)->count());
        $this->assertEquals(30, $user->fresh()->balance);
        $this->assertDatabaseHas('wallets', [
            'user_id' => $user->id,
            'amount' => 30,
            'payment_reference' => 'flutterwave:flutterwave-wallet-1',
        ]);
    }

    public function test_offline_wallet_approval_changes_balance_once_per_state_change(): void
    {
        $user = User::factory()->create(['balance' => 0]);
        $wallet = Wallet::create([
            'user_id' => $user->id,
            'amount' => 70,
            'payment_method' => 'bank_transfer',
            'payment_details' => 'TRX-1',
            'approval' => 0,
            'offline_payment' => 1,
        ]);
        $controller = app(WalletController::class);

        $controller->updateApproved(Request::create('/offline-wallet-recharge/approved', 'POST', [
            'id' => $wallet->id,
            'status' => 1,
        ]));
        $controller->updateApproved(Request::create('/offline-wallet-recharge/approved', 'POST', [
            'id' => $wallet->id,
            'status' => 1,
        ]));

        $this->assertEquals(70, $user->fresh()->balance);

        $controller->updateApproved(Request::create('/offline-wallet-recharge/approved', 'POST', [
            'id' => $wallet->id,
            'status' => 2,
        ]));

        $this->assertEquals(0, $user->fresh()->balance);
    }
}
