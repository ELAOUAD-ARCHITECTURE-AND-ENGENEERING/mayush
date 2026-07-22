<?php

namespace Tests\Feature;

use App\Models\BusinessSetting;
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
                $table->text('default_text')->nullable();
                $table->tinyInteger('status')->default(0);
                $table->timestamps();
            });
        } elseif (!Schema::hasColumn('email_templates', 'default_text')) {
            Schema::table('email_templates', function (Blueprint $table) {
                $table->text('default_text')->nullable();
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

    public function test_new_email_customer_can_create_account_without_registration_code(): void
    {
        $this->post('/register', [
            'name' => 'Buyer Example',
            'email' => 'buyer@example.test',
            'phone' => null,
            'country_code' => null,
            'verification_method' => 'email',
            'password' => 'Secret123',
            'password_confirmation' => 'Secret123',
            'checkbox_example_1' => 'on',
        ])->assertRedirect('/');

        $this->assertDatabaseHas('users', [
            'email' => 'buyer@example.test',
            'user_type' => 'customer',
        ]);

        $this->assertNotNull(User::where('email', 'buyer@example.test')->first()?->email_verified_at);
    }

    public function test_existing_email_customer_registration_is_rejected(): void
    {
        User::factory()->create(['email' => 'blocked@example.test']);

        $this->from('/register')
            ->post('/register', [
                'name' => 'Blocked Buyer',
                'email' => 'blocked@example.test',
                'verification_method' => 'email',
                'password' => 'Secret123',
                'password_confirmation' => 'Secret123',
                'checkbox_example_1' => 'on',
            ])
            ->assertRedirect('/register')
            ->assertSessionHasErrors('email');

        $this->assertSame(1, User::where('email', 'blocked@example.test')->count());
    }

    public function test_new_phone_customer_can_create_account_without_registration_code(): void
    {
        $this->post('/register', [
            'name' => 'Phone Buyer',
            'phone' => '5551234567',
            'country_code' => '1',
            'verification_method' => 'phone',
            'password' => 'Secret123',
            'password_confirmation' => 'Secret123',
            'checkbox_example_1' => 'on',
        ])->assertRedirect('/');

        $user = User::all()->first(fn (User $user) => $user->phone === '+15551234567');

        $this->assertNotNull($user);
        $this->assertSame('customer', $user->user_type);
        $this->assertSame('phone-' . sha1('+15551234567') . '@phone.local', $user->email);
        $this->assertNotNull($user->email_verified_at);
    }

    public function test_existing_phone_customer_registration_is_rejected(): void
    {
        User::factory()->create([
            'email' => 'existing-phone@example.test',
            'phone' => '+15557654321',
        ]);

        $this->from('/register')
            ->post('/register', [
                'name' => 'Duplicate Phone Buyer',
                'phone' => '5557654321',
                'country_code' => '1',
                'verification_method' => 'phone',
                'password' => 'Secret123',
                'password_confirmation' => 'Secret123',
                'checkbox_example_1' => 'on',
            ])
            ->assertRedirect('/register')
            ->assertSessionHasErrors('phone');

        $matches = User::all()->filter(fn (User $user) => $user->phone === '+15557654321');

        $this->assertCount(1, $matches);
    }

    public function test_checkout_account_creation_dispatches_registration_emails(): void
    {
        \Illuminate\Support\Facades\Mail::fake();

        if (!Schema::hasTable('countries')) {
            Schema::create('countries', function (Blueprint $table) {
                $table->id();
                $table->string('code')->nullable();
                $table->string('name')->nullable();
                $table->integer('status')->default(1);
                $table->timestamps();
            });
        }

        if (!Schema::hasTable('cities')) {
            Schema::create('cities', function (Blueprint $table) {
                $table->id();
                $table->string('name')->nullable();
                $table->unsignedBigInteger('country_id')->default(1);
                $table->integer('status')->default(1);
                $table->timestamps();
            });
        }

        $countryId = \DB::table('countries')->insertGetId(['code' => 'MA', 'name' => 'Morocco', 'status' => 1]);
        $cityId = \DB::table('cities')->insertGetId(['name' => 'Casablanca', 'country_id' => $countryId, 'status' => 1]);

        User::factory()->create(['user_type' => 'admin', 'email' => 'admin@example.test']);

        \App\Models\EmailTemplate::query()->where('identifier', 'registration_email_to_customer')->update([
            'status' => 1,
            'subject' => 'Welcome [[customer_name]]',
            'default_text' => 'Hello [[customer_name]]',
        ]);
        \App\Models\EmailTemplate::query()->where('identifier', 'customer_reg_email_to_admin')->update([
            'status' => 1,
            'subject' => 'New User [[customer_name]]',
            'default_text' => 'New user [[customer_name]] registered',
        ]);

        $response = $this->postJson('/checkout/account-address', [
            'action' => 'register',
            'name' => 'Checkout Buyer',
            'verification_method' => 'email',
            'email' => 'checkoutbuyer@example.test',
            'password' => 'Secret123!',
            'password_confirmation' => 'Secret123!',
            'delivery_address' => '123 Main St',
            'delivery_country_id' => $countryId,
            'delivery_city_id' => $cityId,
            'delivery_postal_code' => '20000',
            'delivery_phone' => '0612345678',
            'delivery_country_code' => '212',
        ]);

        $response->assertStatus(200);

        $this->assertDatabaseHas('users', [
            'email' => 'checkoutbuyer@example.test',
            'user_type' => 'customer',
        ]);

        \Illuminate\Support\Facades\Mail::assertQueued(\App\Mail\MailManager::class);
    }
}
