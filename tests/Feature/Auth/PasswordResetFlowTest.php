<?php

namespace Tests\Feature\Auth;

use App\Mail\MailManager;
use App\Models\BusinessSetting;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Facades\Cache;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Facades\Mail;
use Tests\TestCase;

class PasswordResetFlowTest extends TestCase
{
    use RefreshDatabase;

    protected function setUp(): void
    {
        parent::setUp();

        foreach ([
            'google_recaptcha' => '0',
            'recaptcha_forgot_password' => '0',
            'cloudflare_turnstile' => '0',
            'turnstile_forgot_password' => '0',
            'site_name' => 'Mayush Test',
        ] as $type => $value) {
            BusinessSetting::query()->updateOrCreate(['type' => $type], ['value' => $value]);
        }

        DB::table('email_templates')->insertOrIgnore([
            [
                'identifier' => 'password_reset_email_to_all',
                'subject' => 'Reset [[store_name]] password',
                'content' => 'Code [[code]] for [[user_email]] at [[store_name]].',
                'status' => 1,
            ],
        ]);

        Cache::forget('business_settings');
    }

    public function test_email_reset_request_generates_code_and_queues_mail_without_sending_live_mail(): void
    {
        Mail::fake();
        $user = User::factory()->create([
            'email' => 'reset@example.test',
            'password' => Hash::make('OldSecret123'),
            'verification_code' => null,
        ]);

        $this->post(route('password.email'), [
            'email' => $user->email,
        ])->assertOk()
            ->assertSee($user->email, false);

        $this->assertNotNull($user->fresh()->verification_code);
        Mail::assertQueued(MailManager::class);
    }

    public function test_valid_reset_code_updates_password_clears_code_and_old_password_no_longer_works(): void
    {
        $user = User::factory()->create([
            'email' => 'valid-reset@example.test',
            'password' => Hash::make('OldSecret123'),
            'verification_code' => '112233',
            'updated_at' => now(),
        ]);

        $this->post(route('password.update.email'), [
            'email' => $user->email,
            'code' => '112233',
            'password' => 'NewSecret123',
            'password_confirmation' => 'NewSecret123',
        ])->assertRedirect(route('home'));

        $user->refresh();

        $this->assertFalse(Hash::check('OldSecret123', $user->password));
        $this->assertTrue(Hash::check('NewSecret123', $user->password));
        $this->assertNull($user->verification_code);
    }

    public function test_invalid_reset_code_does_not_change_password(): void
    {
        $user = User::factory()->create([
            'email' => 'invalid-reset@example.test',
            'password' => Hash::make('OldSecret123'),
            'verification_code' => '445566',
        ]);

        $this->post(route('password.update.email'), [
            'email' => $user->email,
            'code' => '000000',
            'password' => 'NewSecret123',
            'password_confirmation' => 'NewSecret123',
        ])->assertOk()
            ->assertSee($user->email, false);

        $this->assertTrue(Hash::check('OldSecret123', $user->fresh()->password));
    }

    public function test_expired_reset_code_does_not_change_password(): void
    {
        $user = User::factory()->create([
            'email' => 'expired-reset@example.test',
            'password' => Hash::make('OldSecret123'),
            'verification_code' => '778899',
            'updated_at' => now()->subMinutes(config('auth.passwords.users.expire') + 1),
        ]);

        $this->post(route('password.update.email'), [
            'email' => $user->email,
            'code' => '778899',
            'password' => 'NewSecret123',
            'password_confirmation' => 'NewSecret123',
        ])->assertOk()
            ->assertSee($user->email, false);

        $this->assertTrue(Hash::check('OldSecret123', $user->fresh()->password));
    }
}
