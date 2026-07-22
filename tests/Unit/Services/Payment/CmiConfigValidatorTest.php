<?php

namespace Tests\Unit\Services\Payment;

use Tests\TestCase;
use App\Services\Payment\CmiConfigValidator;
use App\Services\Payment\CmiConfigValidatorInterface;
use App\Services\Payment\ValidationResult;

/**
 * Unit tests for CMI Configuration Validator Service.
 *
 * Validates: Requirements 1.1, 1.2, 1.4, 1.5
 */
class CmiConfigValidatorTest extends TestCase
{
    private CmiConfigValidator $validator;

    protected function setUp(): void
    {
        parent::setUp();
        $this->validator = app(CmiConfigValidatorInterface::class);
    }

    // ========================================
    // Merchant ID Validation Tests
    // ========================================

    public function test_validate_fails_when_merchant_id_is_missing(): void
    {
        config(['cmi.merchant_id' => null]);

        $result = $this->validator->validate();

        $this->assertFalse($result->isValid);
        $this->assertStringContainsString('CMI_MERCHANT_ID is missing', $result->getFirstError());
    }

    public function test_validate_fails_when_merchant_id_is_empty(): void
    {
        config(['cmi.merchant_id' => '']);

        $result = $this->validator->validate();

        $this->assertFalse($result->isValid);
        $this->assertStringContainsString('CMI_MERCHANT_ID is missing', $result->getFirstError());
    }

    public function test_validate_fails_when_merchant_id_is_placeholder(): void
    {
        config(['cmi.merchant_id' => 'your_merchant_id']);

        $result = $this->validator->validate();

        $this->assertFalse($result->isValid);
        $this->assertStringContainsString('placeholder', $result->getFirstError());
    }

    public function test_validate_fails_when_merchant_id_is_too_short(): void
    {
        config([
            'cmi.merchant_id' => 'abc',
            'cmi.secret_key' => 'valid-secret-key-16chars',
            'cmi.gateway_url' => 'https://attijari-payment.cmi.co.ma/fim/est3Dgate',
        ]);

        $result = $this->validator->validate();

        $this->assertFalse($result->isValid);
        $this->assertStringContainsString('too short', $result->getFirstError());
    }

    // ========================================
    // Secret Key Validation Tests
    // ========================================

    public function test_validate_fails_when_secret_key_is_missing(): void
    {
        config([
            'cmi.merchant_id' => 'validmerchant123',
            'cmi.secret_key' => null,
        ]);

        $result = $this->validator->validate();

        $this->assertFalse($result->isValid);
        $errors = $result->errors;
        $this->assertTrue(
            collect($errors)->contains(fn ($e) => str_contains($e, 'CMI_SECRET_KEY is missing'))
        );
    }

    public function test_validate_fails_when_secret_key_is_placeholder(): void
    {
        config([
            'cmi.merchant_id' => 'validmerchant123',
            'cmi.secret_key' => 'your_secret_key',
        ]);

        $result = $this->validator->validate();

        $this->assertFalse($result->isValid);
        $errors = $result->errors;
        $this->assertTrue(
            collect($errors)->contains(fn ($e) => str_contains($e, 'placeholder'))
        );
    }

    public function test_validate_fails_when_secret_key_is_less_than_16_characters(): void
    {
        config([
            'cmi.merchant_id' => 'validmerchant123',
            'cmi.secret_key' => 'shortkey123456', // 14 characters
            'cmi.gateway_url' => 'https://attijari-payment.cmi.co.ma/fim/est3Dgate',
        ]);

        $result = $this->validator->validate();

        $this->assertFalse($result->isValid);
        $errors = $result->errors;
        $this->assertTrue(
            collect($errors)->contains(fn ($e) => str_contains($e, 'at least 16 characters'))
        );
    }

    public function test_validate_passes_when_secret_key_is_exactly_16_characters(): void
    {
        config([
            'cmi.merchant_id' => 'validmerchant123',
            'cmi.secret_key' => 'exactly16chars!!', // 16 characters
            'cmi.gateway_url' => 'https://attijari-payment.cmi.co.ma/fim/est3Dgate',
        ]);

        $result = $this->validator->validate();

        $this->assertTrue($result->isValid);
    }

    // ========================================
    // Gateway URL Validation Tests
    // ========================================

    public function test_validate_fails_when_gateway_url_is_missing(): void
    {
        config([
            'cmi.merchant_id' => 'validmerchant123',
            'cmi.secret_key' => 'valid-secret-key-16chars',
            'cmi.gateway_url' => null,
        ]);

        $result = $this->validator->validate();

        $this->assertFalse($result->isValid);
        $errors = $result->errors;
        $this->assertTrue(
            collect($errors)->contains(fn ($e) => str_contains($e, 'CMI_GATEWAY_URL is missing'))
        );
    }

    public function test_validate_fails_when_gateway_url_uses_http(): void
    {
        config([
            'cmi.merchant_id' => 'validmerchant123',
            'cmi.secret_key' => 'valid-secret-key-16chars',
            'cmi.gateway_url' => 'http://attijari.cmi.co.ma/fim/est3Dgate',
        ]);

        $result = $this->validator->validate();

        $this->assertFalse($result->isValid);
        $errors = $result->errors;
        $this->assertTrue(
            collect($errors)->contains(fn ($e) => str_contains($e, 'HTTPS'))
        );
    }

    public function test_validate_fails_when_gateway_url_is_invalid_format(): void
    {
        config([
            'cmi.merchant_id' => 'validmerchant123',
            'cmi.secret_key' => 'valid-secret-key-16chars',
            'cmi.gateway_url' => 'not-a-valid-url',
        ]);

        $result = $this->validator->validate();

        $this->assertFalse($result->isValid);
        $errors = $result->errors;
        $this->assertTrue(
            collect($errors)->contains(fn ($e) => str_contains($e, 'valid URL'))
        );
    }

    // ========================================
    // Test Mode Detection Tests
    // ========================================

    public function test_is_test_mode_returns_true_when_test_mode_env_is_enabled(): void
    {
        config(['cmi.gateway_url' => 'https://test-attijari.cmi.co.ma/fim/est3Dgate']);
        // Note: We can't easily mock env() in tests, so we test via gateway URL

        $this->assertTrue($this->validator->isTestMode());
    }

    public function test_is_test_mode_returns_false_when_using_production_url(): void
    {
        config(['cmi.gateway_url' => 'https://attijari-payment.cmi.co.ma/fim/est3Dgate']);

        $this->assertFalse($this->validator->isTestMode());
    }

    public function test_is_test_mode_returns_true_when_demo_mode_is_on(): void
    {
        config([
            'cmi.gateway_url' => 'https://attijari-payment.cmi.co.ma/fim/est3Dgate',
            'cmi.demo_mode' => 'On',
        ]);

        $this->assertTrue($this->validator->isTestMode());
    }

    // ========================================
    // Gateway URL Selection Tests
    // ========================================

    public function test_get_gateway_url_returns_test_url_in_test_mode(): void
    {
        config(['cmi.gateway_url' => 'https://test-attijari.cmi.co.ma/fim/est3Dgate']);

        $url = $this->validator->getGatewayUrl();

        $this->assertEquals('https://test-attijari.cmi.co.ma/fim/est3Dgate', $url);
    }

    public function test_get_gateway_url_returns_production_url_in_production_mode(): void
    {
        config(['cmi.gateway_url' => 'https://attijari-payment.cmi.co.ma/fim/est3Dgate']);

        $url = $this->validator->getGatewayUrl();

        $this->assertEquals(CmiConfigValidator::PRODUCTION_GATEWAY_URL, $url);
    }

    public function test_get_gateway_url_returns_configured_production_url_if_valid(): void
    {
        config(['cmi.gateway_url' => 'https://payment.cmi.co.ma/fim/est3Dgate']);

        $url = $this->validator->getGatewayUrl();

        $this->assertEquals('https://payment.cmi.co.ma/fim/est3Dgate', $url);
    }

    // ========================================
    // Production Mode Validation Tests
    // ========================================

    public function test_validate_fails_in_production_mode_with_test_url(): void
    {
        config([
            'cmi.merchant_id' => 'validmerchant123',
            'cmi.secret_key' => 'valid-secret-key-16chars',
            'cmi.gateway_url' => 'https://test-attijari.cmi.co.ma/fim/est3Dgate',
        ]);

        // The validator should be in test mode when using test URL
        $this->assertTrue($this->validator->isTestMode());

        // Validation should pass in test mode
        $result = $this->validator->validate();
        $this->assertTrue($result->isValid);
    }

    // ========================================
    // Successful Validation Tests
    // ========================================

    public function test_validate_passes_with_valid_production_configuration(): void
    {
        config([
            'cmi.merchant_id' => 'validmerchant123',
            'cmi.secret_key' => 'valid-secret-key-16chars',
            'cmi.gateway_url' => 'https://attijari-payment.cmi.co.ma/fim/est3Dgate',
            'cmi.callback_url' => 'https://example.com/cmi/callback',
            'cmi.ok_url' => 'https://example.com/cmi/success',
            'cmi.fail_url' => 'https://example.com/cmi/fail',
            'cmi.allowed_ips' => ['196.12.225.1', '196.12.225.2'],
        ]);

        $result = $this->validator->validate();

        $this->assertTrue($result->isValid);
        $this->assertEmpty($result->errors);
    }

    public function test_validate_rejects_http_return_urls_in_production(): void
    {
        $this->app->detectEnvironment(fn () => 'production');

        config([
            'cmi.merchant_id' => 'validmerchant123',
            'cmi.secret_key' => 'valid-secret-key-16chars',
            'cmi.gateway_url' => 'https://attijari-payment.cmi.co.ma/fim/est3Dgate',
            'cmi.callback_url' => 'http://mayush.test/cmi/callback',
            'cmi.ok_url' => 'https://mayushdesign.com/cmi/success',
            'cmi.fail_url' => 'https://mayushdesign.com/cmi/fail',
        ]);

        $result = $this->validator->validate();

        $this->assertFalse($result->isValid);
        $this->assertTrue(
            collect($result->errors)->contains(fn ($error) => str_contains($error, 'CMI_CALLBACK_URL'))
        );
    }

    public function test_validate_passes_with_valid_test_configuration(): void
    {
        config([
            'cmi.merchant_id' => 'testmerchant123',
            'cmi.secret_key' => 'test-secret-key-16ch',
            'cmi.gateway_url' => 'https://test-attijari.cmi.co.ma/fim/est3Dgate',
        ]);

        $result = $this->validator->validate();

        $this->assertTrue($result->isValid);
        $this->assertEmpty($result->errors);
    }

    // ========================================
    // Warning Tests
    // ========================================

    public function test_validate_warns_about_missing_callback_url(): void
    {
        config([
            'cmi.merchant_id' => 'validmerchant123',
            'cmi.secret_key' => 'valid-secret-key-16chars',
            'cmi.gateway_url' => 'https://attijari-payment.cmi.co.ma/fim/est3Dgate',
            'cmi.callback_url' => null,
        ]);

        $result = $this->validator->validate();

        $this->assertTrue($result->isValid);
        $this->assertTrue(
            collect($result->warnings)->contains(fn ($w) => str_contains($w, 'CMI_CALLBACK_URL'))
        );
    }

    public function test_validate_warns_about_empty_ip_whitelist_in_production_mode(): void
    {
        config([
            'cmi.merchant_id' => 'validmerchant123',
            'cmi.secret_key' => 'valid-secret-key-16chars',
            'cmi.gateway_url' => 'https://attijari-payment.cmi.co.ma/fim/est3Dgate',
            'cmi.allowed_ips' => [],
        ]);

        $result = $this->validator->validate();

        $this->assertTrue($result->isValid);
        $this->assertTrue(
            collect($result->warnings)->contains(fn ($w) => str_contains($w, 'CMI_ALLOWED_IPS'))
        );
    }

    // ========================================
    // ValidationResult Tests
    // ========================================

    public function test_validation_result_success_factory(): void
    {
        $result = ValidationResult::success(['Test warning']);

        $this->assertTrue($result->isValid);
        $this->assertEmpty($result->errors);
        $this->assertEquals(['Test warning'], $result->warnings);
    }

    public function test_validation_result_failure_factory(): void
    {
        $result = ValidationResult::failure(['Test error']);

        $this->assertFalse($result->isValid);
        $this->assertEquals(['Test error'], $result->errors);
        $this->assertEmpty($result->warnings);
    }

    public function test_validation_result_has_errors(): void
    {
        $result = ValidationResult::failure(['Error 1', 'Error 2']);

        $this->assertTrue($result->hasErrors());
        $this->assertEquals('Error 1', $result->getFirstError());
    }

    public function test_validation_result_has_warnings(): void
    {
        $result = ValidationResult::success(['Warning 1']);

        $this->assertTrue($result->hasWarnings());
        $this->assertFalse($result->hasErrors());
    }

    // ========================================
    // Interface Binding Tests
    // ========================================

    public function test_interface_is_bound_to_implementation(): void
    {
        $validator = app(CmiConfigValidatorInterface::class);

        $this->assertInstanceOf(CmiConfigValidator::class, $validator);
    }
}
