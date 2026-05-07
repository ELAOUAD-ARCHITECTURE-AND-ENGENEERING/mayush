<?php

namespace Tests\Feature\Payment;

use App\Http\Controllers\Payment\CmiController;
use App\Models\Address;
use App\Models\PaymentToken;
use App\Models\User;
use App\Models\Wallet;
use App\Services\PaymentVaultService;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Facades\Cache;
use Tests\TestCase;

class PaymentVaultRegressionTest extends TestCase
{
    use RefreshDatabase;

    public function test_payment_vault_eligibility_requires_default_address_and_active_token(): void
    {
        $user = User::factory()->customer()->create();
        $this->actingAs($user);

        $this->assertFalse(PaymentVaultService::isEligible());

        Address::factory()->create([
            'user_id' => $user->id,
            'set_default' => 1,
        ]);

        $this->assertFalse(PaymentVaultService::isEligible());

        PaymentToken::create([
            'user_id' => $user->id,
            'gateway' => 'cmi',
            'token' => 'vault-token',
            'is_active' => true,
            'is_default' => true,
            'card_expiry_month' => now()->month,
            'card_expiry_year' => now()->year + 1,
        ]);

        $this->assertTrue(PaymentVaultService::isEligible());
    }

    public function test_store_token_deduplicates_cards_without_querying_encrypted_card_fields(): void
    {
        $user = User::factory()->customer()->create();

        $first = PaymentVaultService::storeToken($user->id, [
            'TransId' => 'trans-1',
            'MaskedPan' => '411111******1111',
            'ExpDate' => now()->addYear()->format('ym'),
        ]);
        $second = PaymentVaultService::storeToken($user->id, [
            'TransId' => 'trans-2',
            'MaskedPan' => '411111******1111',
            'ExpDate' => now()->addYear()->format('ym'),
        ]);

        $this->assertSame($first->id, $second->id);
        $this->assertSame(1, PaymentToken::where('user_id', $user->id)->where('is_active', true)->count());
        $this->assertSame('trans-2', $second->fresh()->token);
        $this->assertNotNull($second->fresh()->card_fingerprint);
    }

    public function test_user_can_set_default_and_delete_only_own_tokens_with_safe_methods(): void
    {
        $user = User::factory()->customer()->create();
        $otherUser = User::factory()->customer()->create();
        $ownToken = PaymentToken::create([
            'user_id' => $user->id,
            'gateway' => 'cmi',
            'token' => 'own-token',
            'is_active' => true,
            'is_default' => false,
        ]);
        $otherToken = PaymentToken::create([
            'user_id' => $otherUser->id,
            'gateway' => 'cmi',
            'token' => 'other-token',
            'is_active' => true,
            'is_default' => false,
        ]);

        $this->actingAs($user)
            ->post(route('payment_tokens.set_default', $ownToken))
            ->assertRedirect();

        $this->assertTrue($ownToken->fresh()->is_default);

        $this->actingAs($user)
            ->post(route('payment_tokens.set_default', $otherToken))
            ->assertForbidden();

        $this->actingAs($user)
            ->delete(route('payment_tokens.destroy', $ownToken))
            ->assertRedirect();

        $this->assertFalse($ownToken->fresh()->is_active);

        $this->actingAs($user)->get('/payment-methods/' . $otherToken->id . '/default')->assertNotFound();
        $this->actingAs($user)->get('/payment-methods/' . $otherToken->id . '/remove')->assertNotFound();
    }

    public function test_cmi_wallet_callback_credits_once_and_records_gateway_reference(): void
    {
        config(['cmi.secret_key' => 'test-cmi-secret']);
        $user = User::factory()->customer()->create(['balance' => 0]);
        $payload = $this->signedCmiPayload([
            'oid' => 'W-' . $user->id . '-1000',
            'amount' => '40.00',
            'ProcReturnCode' => '00',
            'TransId' => 'cmi-wallet-trans-1',
        ]);

        Cache::put('cmi_wallet_amount_' . $payload['oid'], 40.00, 3600);

        $this->post(route('cmi.callback'), $payload)->assertOk()->assertSee('ACTION=POSTAUTH');
        $this->post(route('cmi.callback'), $payload)->assertOk()->assertSee('ACTION=POSTAUTH');

        $this->assertSame(1, Wallet::where('user_id', $user->id)->count());
        $this->assertEquals(40, $user->fresh()->balance);
        $this->assertDatabaseHas('wallets', [
            'user_id' => $user->id,
            'amount' => 40,
            'payment_method' => 'cmi',
            'payment_reference' => 'cmi:cmi-wallet-trans-1',
        ]);
    }

    public function test_cmi_wallet_failed_callback_does_not_credit_wallet(): void
    {
        config(['cmi.secret_key' => 'test-cmi-secret']);
        $user = User::factory()->customer()->create(['balance' => 0]);
        $payload = $this->signedCmiPayload([
            'oid' => 'W-' . $user->id . '-1001',
            'amount' => '55.00',
            'ProcReturnCode' => '05',
            'TransId' => 'cmi-wallet-trans-failed',
        ]);

        Cache::put('cmi_wallet_amount_' . $payload['oid'], 55.00, 3600);

        $this->post(route('cmi.callback'), $payload)->assertOk()->assertSee('APPROVED');

        $this->assertSame(0, Wallet::where('user_id', $user->id)->count());
        $this->assertEquals(0, $user->fresh()->balance);
    }

    private function signedCmiPayload(array $payload): array
    {
        $payload += ['encoding' => 'UTF-8'];
        $controller = app(CmiController::class);
        $method = new \ReflectionMethod($controller, 'generateHash');
        $method->setAccessible(true);
        $payload['HASH'] = $method->invoke($controller, $payload, config('cmi.secret_key'));

        return $payload;
    }
}
