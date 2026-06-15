<?php

namespace Tests\Unit\Services;

use Tests\TestCase;
use App\Services\PaymentVaultService;
use App\Models\User;
use App\Models\Address;
use App\Models\PaymentToken;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Facades\Auth;

class PaymentVaultServiceTest extends TestCase
{
    use RefreshDatabase;

    /** @test */
    public function it_returns_false_eligibility_for_guests()
    {
        Auth::logout();
        $this->assertFalse(PaymentVaultService::isEligible());
    }

    /** @test */
    public function it_returns_false_if_user_has_no_default_address()
    {
        $user = User::factory()->create();
        Auth::login($user);

        // User exists but has no address
        $this->assertFalse(PaymentVaultService::isEligible());
    }

    /** @test */
    public function it_returns_false_if_user_has_address_but_no_vaulted_token()
    {
        $user = User::factory()->create();
        Address::create([
            'user_id' => $user->id,
            'address' => '123 Test St',
            'country_id' => 1,
            'city_id' => 1,
            'set_default' => 1
        ]);
        Auth::login($user);

        $this->assertFalse(PaymentVaultService::isEligible());
    }

    /** @test */
    public function it_returns_true_if_user_has_address_and_active_token()
    {
        $user = User::factory()->create();
        Address::create([
            'user_id' => $user->id,
            'address' => '123 Test St',
            'country_id' => 1,
            'city_id' => 1,
            'set_default' => 1
        ]);
        PaymentToken::create([
            'user_id' => $user->id,
            'gateway' => 'cmi',
            'token' => 'vault-token-123',
            'is_active' => true,
            'is_default' => true
        ]);
        Auth::login($user);

        $this->assertTrue(PaymentVaultService::isEligible());
    }

    /** @test */
    public function it_correctly_stores_tokens_and_detects_card_brands()
    {
        $user = User::factory()->create();
        
        // Scenario 1: Visa
        $callbackDataVisa = [
            'TransId' => 'VISA_TEST_TOKEN',
            'MaskedPan' => '411111XXXXXX1122'
        ];
        
        $tokenVisa = PaymentVaultService::storeToken($user->id, $callbackDataVisa);
        
        $this->assertEquals('Visa', $tokenVisa->card_brand);
        $this->assertEquals('1122', $tokenVisa->card_last_four);
        $this->assertEquals('VISA_TEST_TOKEN', $tokenVisa->token);

        // Scenario 2: Mastercard
        $callbackDataMc = [
            'TransId' => 'MC_TEST_TOKEN',
            'MaskedPan' => '512345XXXXXX4455'
        ];
        
        $tokenMc = PaymentVaultService::storeToken($user->id, $callbackDataMc);
        
        $this->assertEquals('Mastercard', $tokenMc->card_brand);
        $this->assertEquals('4455', $tokenMc->card_last_four);
        
        // Verify MC became the new default
        $this->assertTrue($tokenMc->is_default);
        $this->assertFalse($tokenVisa->fresh()->is_default);
    }
}
