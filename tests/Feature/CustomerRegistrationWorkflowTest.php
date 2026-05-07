<?php

namespace Tests\Feature;

use App\Models\BusinessSetting;
use App\Models\RegistrationVerificationCode;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Facades\Cache;
use Illuminate\Support\Facades\Schema;
use Illuminate\Database\Schema\Blueprint;
use Tests\TestCase;

class CustomerRegistrationWorkflowTest extends TestCase
{
    use RefreshDatabase;

    protected function setUp(): void
    {
        parent::setUp();

        if (!Schema::hasTable('registration_verification_codes')) {
            Schema::create('registration_verification_codes', function (Blueprint $table) {
                $table->id();
                $table->string('email')->nullable();
                $table->string('phone')->nullable();
                $table->string('code');
                $table->boolean('is_verified')->default(false);
                $table->timestamps();
            });
        }

        if (!Schema::hasTable('coupons')) {
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

        if (!Schema::hasTable('email_templates')) {
            Schema::create('email_templates', function (Blueprint $table) {
                $table->id();
                $table->string('identifier')->nullable();
                $table->string('subject')->nullable();
                $table->text('content')->nullable();
                $table->tinyInteger('status')->default(0);
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

        \DB::table('email_templates')->insert([
            ['identifier' => 'registration_email_to_customer', 'status' => 0],
            ['identifier' => 'customer_reg_email_to_admin', 'status' => 0],
        ]);

        Cache::forget('business_settings');
    }

    public function test_verified_email_customer_can_create_account(): void
    {
        RegistrationVerificationCode::query()->create([
            'email' => 'buyer@example.test',
            'phone' => null,
            'code' => '123456',
            'is_verified' => 1,
        ]);

        $this->post('/register', [
            'name' => 'Buyer Example',
            'email' => 'buyer@example.test',
            'phone' => null,
            'country_code' => null,
            'verification_method' => 'email',
            'code' => '123456',
            'password' => 'Secret123',
            'password_confirmation' => 'Secret123',
            'checkbox_example_1' => 'on',
        ])->assertRedirect('/');

        $this->assertDatabaseHas('users', [
            'email' => 'buyer@example.test',
            'user_type' => 'customer',
        ]);
    }

    public function test_unverified_registration_code_is_rejected(): void
    {
        RegistrationVerificationCode::query()->create([
            'email' => 'blocked@example.test',
            'phone' => null,
            'code' => '654321',
            'is_verified' => 0,
        ]);

        $this->from('/register')
            ->post('/register', [
                'name' => 'Blocked Buyer',
                'email' => 'blocked@example.test',
                'verification_method' => 'email',
                'code' => '654321',
                'password' => 'Secret123',
                'password_confirmation' => 'Secret123',
                'checkbox_example_1' => 'on',
            ])
            ->assertRedirect('/register')
            ->assertSessionHasErrors('code');

        $this->assertDatabaseMissing('users', [
            'email' => 'blocked@example.test',
        ]);
    }
}
