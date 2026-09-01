<?php

namespace Tests\Feature;

use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Facades\Hash;
use Tests\TestCase;

class MobilePasswordSessionRevocationTest extends TestCase
{
    use RefreshDatabase;

    public function test_password_change_revokes_every_sanctum_token(): void
    {
        $user = User::factory()->create(['password' => Hash::make('old-password')]);
        $firstToken = $user->createToken('phone-one')->plainTextToken;
        $user->createToken('phone-two');

        $this->withToken($firstToken)->postJson('/api/v2/auth/password/change', [
            'current_password' => 'old-password',
            'password' => 'new-password-123',
            'password_confirmation' => 'new-password-123',
        ])->assertOk()
            ->assertJsonPath('result', true)
            ->assertJsonPath('sessions_revoked', true);

        $this->assertSame(0, $user->tokens()->count());
        $this->assertTrue(Hash::check('new-password-123', $user->fresh()->password));
    }

    public function test_password_change_rejects_wrong_current_password_without_revoking_tokens(): void
    {
        $user = User::factory()->create(['password' => Hash::make('old-password')]);
        $token = $user->createToken('phone-one')->plainTextToken;

        $this->withToken($token)->postJson('/api/v2/auth/password/change', [
            'current_password' => 'wrong-password',
            'password' => 'new-password-123',
            'password_confirmation' => 'new-password-123',
        ])->assertStatus(422)
            ->assertJsonPath('code', 'CURRENT_PASSWORD_INVALID');

        $this->assertSame(1, $user->tokens()->count());
    }

    public function test_password_reset_revokes_every_sanctum_token(): void
    {
        $user = User::factory()->create([
            'password' => Hash::make('old-password'),
            'verification_code' => '654321',
        ]);
        $user->createToken('phone-one');
        $user->createToken('phone-two');

        $this->postJson('/api/v2/auth/password/confirm_reset', [
            'verification_code' => '654321',
            'password' => 'reset-password-123',
            'password_confirmation' => 'reset-password-123',
        ])->assertOk()
            ->assertJsonPath('result', true)
            ->assertJsonPath('sessions_revoked', true);

        $user->refresh();
        $this->assertSame(0, $user->tokens()->count());
        $this->assertNull($user->verification_code);
        $this->assertTrue(Hash::check('reset-password-123', $user->password));
    }
}
