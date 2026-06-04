<?php

namespace Tests\Feature\Auth;

use App\Models\BusinessSetting;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Facades\Cache;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Facades\Schema;
use Illuminate\Database\Schema\Blueprint;
use Tests\TestCase;

class PasswordRegistrationValidationTest extends TestCase
{
    use RefreshDatabase;

    protected function setUp(): void
    {
        parent::setUp();

        if (! Schema::hasTable('coupons')) {
            Schema::create('coupons', function (Blueprint $table) {
                $table->id();
                $table->string('type')->nullable();
                $table->tinyInteger('status')->default(0);
                $table->string('code')->nullable();
                $table->text('details')->nullable();
                $table->double('discount')->default(0);
                $table->string('discount_type')->nullable();
                $table->timestamps();
            });
        }

        foreach ([
            'customer_registration_verify' => '1',
            'google_recaptcha' => '0',
            'recaptcha_customer_register' => '0',
            'cloudflare_turnstile' => '0',
            'turnstile_customer_register' => '0',
            'email_verification' => '0',
            'portfolio_landing' => '0',
            'customer_verification' => '0',
        ] as $type => $value) {
            BusinessSetting::query()->updateOrCreate(['type' => $type], ['value' => $value]);
        }

        DB::table('email_templates')->insertOrIgnore([
            ['identifier' => 'registration_email_to_customer', 'status' => 0],
            ['identifier' => 'customer_reg_email_to_admin', 'status' => 0],
        ]);

        Cache::forget('business_settings');
    }

    public function test_weak_registration_password_is_rejected(): void
    {
        $this->from(route('register'))
            ->post(route('register'), [
                'name' => 'Weak Password',
                'email' => 'weak-password@example.test',
                'verification_method' => 'email',
                'password' => 'password',
                'password_confirmation' => 'password',
            ])
            ->assertRedirect(route('register'))
            ->assertSessionHasErrors('password');

        $this->assertDatabaseMissing('users', [
            'email' => 'weak-password@example.test',
        ]);
    }

    public function test_valid_registration_password_creates_customer_account(): void
    {
        $this->post(route('register'), [
            'name' => 'Strong Password',
            'email' => 'strong-password@example.test',
            'verification_method' => 'email',
            'password' => 'Secret123',
            'password_confirmation' => 'Secret123',
        ])->assertRedirect(route('home'));

        $user = \App\Models\User::where('email', 'strong-password@example.test')->first();

        $this->assertNotNull($user);
        $this->assertTrue(Hash::check('Secret123', $user->password));
        $this->assertSame('customer', $user->user_type);
    }
}
