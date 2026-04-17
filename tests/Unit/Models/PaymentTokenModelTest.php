<?php

namespace Tests\Unit\Models;

use Tests\TestCase;
use App\Models\PaymentToken;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Crypt;

class PaymentTokenModelTest extends TestCase
{
    use RefreshDatabase;

    /** @test */
    public function it_encrypts_sensitive_fields_in_the_database()
    {
        $user = User::factory()->create();
        
        $tokenValue = 'C1234567890';
        $lastFour = '1111';

        $paymentToken = PaymentToken::create([
            'user_id' => $user->id,
            'gateway' => 'cmi',
            'token' => $tokenValue,
            'card_last_four' => $lastFour,
            'is_active' => true,
        ]);

        // 1. Verify model access returns decrypted value (handled by Laravel casts)
        $this->assertEquals($tokenValue, $paymentToken->token);
        $this->assertEquals($lastFour, $paymentToken->card_last_four);

        // 2. Verify database directly contains encrypted data
        $rawRecord = DB::table('payment_tokens')->where('id', $paymentToken->id)->first();
        
        // Assert that the raw database value is NOT the plaintext value
        $this->assertNotEquals($tokenValue, $rawRecord->token);
        $this->assertNotEquals($lastFour, $rawRecord->card_last_four);

        // 3. Verify that we can manually decrypt the raw DB value using Laravel's Crypt
        $this->assertEquals($tokenValue, Crypt::decryptString($rawRecord->token));
        $this->assertEquals($lastFour, Crypt::decryptString($rawRecord->card_last_four));
    }

    /** @test */
    public function it_belongs_to_a_user()
    {
        $user = User::factory()->create();
        $token = PaymentToken::create([
            'user_id' => $user->id,
            'gateway' => 'cmi',
            'token' => 'test-token',
            'is_active' => true,
        ]);

        $this->assertInstanceOf(User::class, $token->user);
        $this->assertEquals($user->id, $token->user->id);
    }
}
