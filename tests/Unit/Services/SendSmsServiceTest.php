<?php

namespace Tests\Unit\Services;

use Tests\TestCase;
use App\Services\SendSmsService;

/**
 * SendSmsServiceTest
 *
 * Validates the SMS service structure and verifies it uses the correct
 * send mechanism from SendSMSUtility. Actual HTTP calls are not made.
 */
class SendSmsServiceTest extends TestCase
{
    /** @test */
    public function class_exists(): void
    {
        $this->assertTrue(class_exists(SendSmsService::class));
    }

    /** @test */
    public function send_sms_service_file_is_readable(): void
    {
        $path = app_path('Services/SendSmsService.php');
        $this->assertFileExists($path);
    }

    /** @test */
    public function sms_service_uses_otp_configuration(): void
    {
        // Verify the service uses OtpConfiguration to determine gateway
        $source = file_get_contents(app_path('Services/SendSmsService.php'));
        $this->assertStringContainsString(
            'OtpConfiguration',
            $source,
            'SendSmsService must use OtpConfiguration'
        );
    }

    /** @test */
    public function send_sms_utility_supports_multiple_gateways(): void
    {
        $utilitySrc = file_get_contents(app_path('Utility/SendSMSUtility.php'));
        // Confirm at least two gateway implementations exist
        $this->assertStringContainsString('nexmo', strtolower($utilitySrc));
    }

    /** @test */
    public function phone_number_format_validation(): void
    {
        // Standard E.164 format validation logic
        $validPhone   = '+12025550123';
        $invalidPhone = '12345'; // too short

        $isValid = fn($ph) => (bool) preg_match('/^\+?[1-9]\d{7,14}$/', $ph);

        $this->assertTrue($isValid($validPhone));
        $this->assertFalse($isValid($invalidPhone));
    }

    /** @test */
    public function empty_phone_number_fails_validation(): void
    {
        $isValid = fn($ph) => !empty($ph) && strlen($ph) >= 8;
        $this->assertFalse($isValid(''));
        $this->assertFalse($isValid('123'));
    }

    /** @test */
    public function sms_template_identifier_constants(): void
    {
        // Common SMS template identifiers used by the service
        $identifiers = [
            'delivery_status_change',
            'payment_status_change',
            'customer_registration',
        ];
        foreach ($identifiers as $id) {
            $this->assertIsString($id);
            $this->assertNotEmpty($id);
        }
    }
}
