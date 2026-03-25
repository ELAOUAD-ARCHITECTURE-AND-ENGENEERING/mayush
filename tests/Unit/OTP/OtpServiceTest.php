<?php

namespace Tests\Unit\OTP;

use Tests\TestCase;
use App\Models\User;
use App\Models\Addon;
use App\Services\OtpService;
use App\Utility\SendSMSUtility;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Facades\Notification;
use App\Notifications\AppEmailVerificationNotification;
use Mockery;
use Illuminate\Support\Facades\Cache;

class OtpServiceTest extends TestCase
{
    use RefreshDatabase;

    protected function setUp(): void
    {
        parent::setUp();
        Cache::forget('addons');
    }

    /** @test */
    public function it_sends_otp_via_sms_when_phone_exists_and_addon_is_active()
    {
        // Arrange
        $user = User::factory()->create([
            'phone' => '+1234567890',
            'verification_code' => '123456'
        ]);

        Addon::insert([
            'name' => 'OTP System',
            'unique_identifier' => 'otp_system',
            'activated' => 1,
            'image' => 'otp.png',
            'purchase_code' => '123',
            'version' => '1.0'
        ]);

        // Mock SendSMSUtility
        $smsMock = Mockery::mock('alias:App\Utility\SendSMSUtility');
        $smsMock->shouldReceive('sendSMS')
            ->once()
            ->with($user->phone, Mockery::any(), Mockery::pattern('/123456/'), Mockery::any());

        // Act
        (new OtpService())->send_code($user);

        // Assert
        $this->assertTrue(true); // Verification handled by Mockery
    }

    /** @test */
    public function it_sends_otp_via_email_when_addon_is_not_active()
    {
        // Arrange
        $user = User::factory()->create([
            'email' => 'test@example.com',
            'phone' => '+1234567890',
            'verification_code' => '123456'
        ]);

        Notification::fake();

        // No addon seeded or addon not activated
        Addon::insert([
            'name' => 'OTP System',
            'unique_identifier' => 'otp_system',
            'activated' => 0,
            'image' => 'otp.png',
            'purchase_code' => '123',
            'version' => '1.0'
        ]);

        // Act
        (new OtpService())->send_code($user);

        // Assert
        Notification::assertSentTo($user, AppEmailVerificationNotification::class);
    }

    /** @test */
    public function it_sends_otp_via_email_when_phone_is_missing()
    {
        // Arrange
        $user = User::factory()->create([
            'email' => 'test@example.com',
            'phone' => null,
            'verification_code' => '123456'
        ]);

        Notification::fake();

        Addon::insert([
            'name' => 'OTP System',
            'unique_identifier' => 'otp_system',
            'activated' => 1,
            'image' => 'otp.png',
            'purchase_code' => '123',
            'version' => '1.0'
        ]);

        // Act
        (new OtpService())->send_code($user);

        // Assert
        Notification::assertSentTo($user, AppEmailVerificationNotification::class);
    }

    protected function tearDown(): void
    {
        Mockery::close();
        parent::tearDown();
    }
}
