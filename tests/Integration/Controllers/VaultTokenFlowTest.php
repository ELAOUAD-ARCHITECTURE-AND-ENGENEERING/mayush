<?php

namespace Tests\Integration\Controllers;

use Tests\TestCase;
use App\Models\User;
use App\Models\Product;
use App\Models\Address;
use App\Models\PaymentToken;
use App\Services\PaymentVaultService;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Facades\Session;

class VaultTokenFlowTest extends TestCase
{
    use RefreshDatabase;

    protected $user;

    protected function setUp(): void
    {
        parent::setUp();
        $this->user = User::factory()->create(['balance' => 5000]);
    }

    // ─── Token Storage ──────────────────────────────────────────

    /** @test */
    public function it_stores_a_token_from_cmi_callback_data()
    {
        $callbackData = [
            'TransId' => 'TXN-LIVE-001',
            'MaskedPan' => '411111****1234',
            'ExpDate' => '2812', // YYMM → Dec 2028
        ];

        $token = PaymentVaultService::storeToken($this->user->id, $callbackData);

        $this->assertNotNull($token);
        $this->assertEquals('cmi', $token->gateway);
        $this->assertEquals('Visa', $token->card_brand);
        $this->assertEquals(2028, $token->card_expiry_year);
        $this->assertEquals(12, $token->card_expiry_month);
        $this->assertTrue($token->is_default);
        $this->assertTrue($token->is_active);
        $this->assertNotNull($token->last_used_at);
    }

    /** @test */
    public function it_rejects_callback_without_trans_id()
    {
        $callbackData = [
            'MaskedPan' => '411111****1234',
        ];

        $result = PaymentVaultService::storeToken($this->user->id, $callbackData);

        $this->assertNull($result);
        $this->assertEquals(0, PaymentToken::count());
    }

    /** @test */
    public function it_parses_mastercard_brand_correctly()
    {
        $callbackData = [
            'TransId' => 'TXN-LIVE-MC-001',
            'MaskedPan' => '522222****5678',
            'ExpDate' => '2706',
        ];

        $token = PaymentVaultService::storeToken($this->user->id, $callbackData);

        $this->assertEquals('Mastercard', $token->card_brand);
        $this->assertEquals(2027, $token->card_expiry_year);
        $this->assertEquals(6, $token->card_expiry_month);
    }

    // ─── Token Expiry ───────────────────────────────────────────

    /** @test */
    public function it_detects_expired_tokens()
    {
        $expired = PaymentToken::create([
            'user_id' => $this->user->id,
            'gateway' => 'cmi',
            'token' => 'old-token',
            'card_expiry_month' => 1,
            'card_expiry_year' => 2020,
            'is_active' => true,
            'is_default' => true
        ]);

        $this->assertTrue($expired->isExpired());
    }

    /** @test */
    public function it_marks_future_tokens_as_valid()
    {
        $valid = PaymentToken::create([
            'user_id' => $this->user->id,
            'gateway' => 'cmi',
            'token' => 'future-token',
            'card_expiry_month' => 12,
            'card_expiry_year' => 2030,
            'is_active' => true,
            'is_default' => true
        ]);

        $this->assertFalse($valid->isExpired());
    }

    /** @test */
    public function it_treats_tokens_without_expiry_as_valid()
    {
        $noExpiry = PaymentToken::create([
            'user_id' => $this->user->id,
            'gateway' => 'cmi',
            'token' => 'no-exp-token',
            'is_active' => true,
            'is_default' => true
        ]);

        $this->assertFalse($noExpiry->isExpired());
    }

    // ─── Token Pruning ──────────────────────────────────────────

    /** @test */
    public function prune_deactivates_expired_tokens_only()
    {
        // 1. Expired token
        $expired = PaymentToken::create([
            'user_id' => $this->user->id,
            'gateway' => 'cmi',
            'token' => 'expired-one',
            'card_expiry_month' => 3,
            'card_expiry_year' => 2020,
            'is_active' => true,
            'is_default' => false
        ]);

        // 2. Valid token
        $valid = PaymentToken::create([
            'user_id' => $this->user->id,
            'gateway' => 'cmi',
            'token' => 'valid-one',
            'card_expiry_month' => 12,
            'card_expiry_year' => 2030,
            'is_active' => true,
            'is_default' => true
        ]);

        $pruned = PaymentToken::pruneExpired();

        $this->assertEquals(1, $pruned);
        $this->assertFalse(PaymentToken::find($expired->id)->is_active);
        $this->assertTrue(PaymentToken::find($valid->id)->is_active);
    }

    // ─── Rate Limiting ──────────────────────────────────────────

    /** @test */
    public function it_enforces_max_tokens_per_user_limit()
    {
        // Create MAX tokens
        $firstTokenId = null;
        for ($i = 1; $i <= PaymentToken::MAX_TOKENS_PER_USER; $i++) {
            $t = PaymentToken::create([
                'user_id' => $this->user->id,
                'gateway' => 'cmi',
                'token' => "token-{$i}",
                'is_active' => true,
                'is_default' => ($i === PaymentToken::MAX_TOKENS_PER_USER),
                'created_at' => now()->subMinutes(PaymentToken::MAX_TOKENS_PER_USER - $i)
            ]);
            if ($i === 1) $firstTokenId = $t->id;
        }

        $this->assertEquals(PaymentToken::MAX_TOKENS_PER_USER, PaymentToken::where('is_active', true)->count());

        // Store one more — should deactivate the oldest
        $newToken = PaymentVaultService::storeToken($this->user->id, [
            'TransId' => 'TXN-NEW-OVERFLOW',
            'MaskedPan' => '411111****9999',
        ]);

        $this->assertNotNull($newToken);
        
        // Total active should still be MAX
        $activeCount = PaymentToken::where('user_id', $this->user->id)
            ->where('is_active', true)
            ->count();
        $this->assertLessThanOrEqual(PaymentToken::MAX_TOKENS_PER_USER, $activeCount);

        // Oldest token should be deactivated
        $this->assertFalse(PaymentToken::find($firstTokenId)->is_active);
    }

    // ─── Eligibility Filtering ──────────────────────────────────

    /** @test */
    public function it_excludes_expired_tokens_from_eligibility()
    {
        // Only expired token
        PaymentToken::create([
            'user_id' => $this->user->id,
            'gateway' => 'cmi',
            'token' => 'expired-only',
            'card_expiry_month' => 1,
            'card_expiry_year' => 2020,
            'is_active' => true,
            'is_default' => true
        ]);

        $this->assertFalse(PaymentVaultService::hasVaultedToken($this->user->id));
        $this->assertNull(PaymentVaultService::getActiveToken($this->user->id));
    }

    /** @test */
    public function it_returns_valid_token_when_mixed_with_expired()
    {
        // Expired
        PaymentToken::create([
            'user_id' => $this->user->id,
            'gateway' => 'cmi',
            'token' => 'old-dead-token',
            'card_expiry_month' => 6,
            'card_expiry_year' => 2022,
            'is_active' => true,
            'is_default' => false
        ]);

        // Valid
        $validToken = PaymentToken::create([
            'user_id' => $this->user->id,
            'gateway' => 'cmi',
            'token' => 'fresh-valid-token',
            'card_expiry_month' => 12,
            'card_expiry_year' => 2030,
            'is_active' => true,
            'is_default' => true
        ]);

        $this->assertTrue(PaymentVaultService::hasVaultedToken($this->user->id));
        
        $active = PaymentVaultService::getActiveToken($this->user->id);
        $this->assertNotNull($active);
        $this->assertEquals($validToken->id, $active->id);
    }
}
