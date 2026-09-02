<?php

namespace Tests\Feature;

use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class TwoFactorTest extends TestCase
{
    use RefreshDatabase;

    public function test_user_has_two_factor_disabled_by_default(): void
    {
        $user = User::factory()->create();
        $this->assertFalse($user->hasTwoFactorEnabled());
    }

    public function test_user_can_enable_two_factor(): void
    {
        $user = User::factory()->create();
        $user->two_factor_secret = encrypt('TESTSECRET123456');
        $user->two_factor_confirmed_at = now();
        $user->save();

        $this->assertTrue($user->fresh()->hasTwoFactorEnabled());
    }

    public function test_user_can_generate_recovery_codes(): void
    {
        $user = User::factory()->create();
        $codes = $user->generateRecoveryCodes();

        $this->assertCount(8, $codes);
        $this->assertNotNull($user->fresh()->two_factor_recovery_codes);
    }
}
