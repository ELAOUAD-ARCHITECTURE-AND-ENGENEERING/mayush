<?php

namespace App\Services\Payment;

use Illuminate\Support\Facades\Log;
use Illuminate\Support\Str;

/**
 * Validates CMI payment gateway configuration.
 *
 * This service validates CMI credentials and configuration to ensure
 * the payment gateway is properly configured for the current environment.
 */
class CmiConfigValidator implements CmiConfigValidatorInterface
{
    /**
     * Test gateway URL for CMI.
     */
    public const TEST_GATEWAY_URL = 'https://testpayment.cmi.co.ma/fim/est3Dgate';

    /**
     * Production gateway URL for CMI.
     */
    public const PRODUCTION_GATEWAY_URL = 'https://attijari-payment.cmi.co.ma/fim/est3Dgate';

    /**
     * Known CMI/Attijari hosted payment gateways.
     */
    private const TEST_GATEWAY_URLS = [
        self::TEST_GATEWAY_URL,
        'https://test-attijari.cmi.co.ma/fim/est3Dgate',
    ];

    private const PRODUCTION_GATEWAY_HOSTS = [
        'attijari-payment.cmi.co.ma',
        'payment.cmi.co.ma',
    ];

    /**
     * Minimum required length for the secret key.
     */
    private const MIN_SECRET_KEY_LENGTH = 16;

    /**
     * Placeholder values that indicate unconfigured credentials.
     */
    private const PLACEHOLDER_MERCHANT_IDS = [
        'your_merchant_id',
        'merchant_id',
        'test_merchant',
        'xxx',
        'demo',
        'placeholder',
        'changeme',
    ];

    private const PLACEHOLDER_SECRET_KEYS = [
        'your_secret_key',
        'secret_key',
        'test_secret',
        'xxx',
        'demo',
        'placeholder',
        'changeme',
    ];

    /**
     * Validate all required CMI configuration.
     *
     * @return ValidationResult
     */
    public function validate(): ValidationResult
    {
        $errors = [];
        $warnings = [];

        // Validate Merchant ID
        $merchantIdError = $this->validateMerchantId();
        if ($merchantIdError !== null) {
            $errors[] = $merchantIdError;
        }

        // Validate Secret Key
        $secretKeyError = $this->validateSecretKey();
        if ($secretKeyError !== null) {
            $errors[] = $secretKeyError;
        }

        // Validate Gateway URL
        $gatewayErrors = $this->validateGatewayUrl();
        $errors = array_merge($errors, $gatewayErrors);

        // The hosted 3-D Secure flow must be able to return to the merchant.
        // Fail early in production instead of allowing a card challenge to
        // complete against an unreachable HTTP/.test callback URL.
        $errors = array_merge($errors, $this->validateReturnUrls());

        // Check for warnings
        $warnings = $this->gatherWarnings();

        if (!empty($errors)) {
            Log::critical('CMI Configuration Validation Failed', [
                'errors' => $errors,
                'warnings' => $warnings,
                'is_test_mode' => $this->isTestMode(),
            ]);

            return ValidationResult::failure($errors, $warnings);
        }

        if (!empty($warnings)) {
            Log::warning('CMI Configuration Validation passed with warnings', [
                'warnings' => $warnings,
            ]);
        }

        return ValidationResult::success($warnings);
    }

    /**
     * Check if test mode is enabled.
     *
     * Test mode is determined by checking if the configured gateway URL
     * matches the test gateway URL, or if explicitly set via environment.
     *
     * @return bool
     */
    public function isTestMode(): bool
    {
        // Check explicit test mode flag
        if (env('CMI_TEST_MODE', false) === true || env('CMI_TEST_MODE') === 'true') {
            return true;
        }

        // Check if using a known test gateway URL
        $gatewayUrl = config('cmi.gateway_url');
        if (is_string($gatewayUrl) && in_array(rtrim($gatewayUrl, '/'), self::TEST_GATEWAY_URLS, true)) {
            return true;
        }

        // Check if demo mode is enabled (backward compatibility)
        $demoMode = config('cmi.demo_mode', 'Off');
        if (strtolower($demoMode) === 'on') {
            return true;
        }

        return false;
    }

    /**
     * Get the appropriate gateway URL based on mode.
     *
     * If test mode is enabled, returns the test gateway URL.
     * Otherwise, returns the production gateway URL.
     *
     * @return string
     */
    public function getGatewayUrl(): string
    {
        $configuredUrl = config('cmi.gateway_url');

        if ($this->isTestMode()) {
            return is_string($configuredUrl) && $this->isKnownTestUrl($configuredUrl)
                ? $configuredUrl
                : self::TEST_GATEWAY_URL;
        }

        // Return configured URL if it's a valid production URL
        if ($this->isValidProductionUrl($configuredUrl)) {
            return $configuredUrl;
        }

        // Fall back to default production URL
        return self::PRODUCTION_GATEWAY_URL;
    }

    /**
     * Validate the merchant ID configuration.
     *
     * @return string|null Error message or null if valid
     */
    private function validateMerchantId(): ?string
    {
        $merchantId = config('cmi.merchant_id');

        if (empty($merchantId)) {
            return 'CMI_MERCHANT_ID is missing or empty. Please configure a valid merchant ID.';
        }

        if (!is_string($merchantId)) {
            return 'CMI_MERCHANT_ID must be a string value.';
        }

        // Check for placeholder values
        $lowerMerchantId = strtolower($merchantId);
        foreach (self::PLACEHOLDER_MERCHANT_IDS as $placeholder) {
            if (Str::contains($lowerMerchantId, $placeholder)) {
                return 'CMI_MERCHANT_ID contains a placeholder value. Please configure a valid production merchant ID.';
            }
        }

        // Check for minimum length
        if (strlen($merchantId) < 4) {
            return 'CMI_MERCHANT_ID appears to be too short. Please verify your merchant ID.';
        }

        return null;
    }

    /**
     * Validate the secret key configuration.
     *
     * @return string|null Error message or null if valid
     */
    private function validateSecretKey(): ?string
    {
        $secretKey = config('cmi.secret_key');

        if (empty($secretKey)) {
            return 'CMI_SECRET_KEY is missing or empty. Please configure a valid secret key.';
        }

        if (!is_string($secretKey)) {
            return 'CMI_SECRET_KEY must be a string value.';
        }

        // Check for placeholder values
        $lowerSecretKey = strtolower($secretKey);
        foreach (self::PLACEHOLDER_SECRET_KEYS as $placeholder) {
            if (Str::contains($lowerSecretKey, $placeholder)) {
                return 'CMI_SECRET_KEY contains a placeholder value. Please configure a valid production secret key.';
            }
        }

        // Check minimum length for security
        if (strlen($secretKey) < self::MIN_SECRET_KEY_LENGTH) {
            return sprintf(
                'CMI_SECRET_KEY must be at least %d characters long for security.',
                self::MIN_SECRET_KEY_LENGTH
            );
        }

        return null;
    }

    /**
     * Validate the gateway URL configuration.
     *
     * @return array<string> List of error messages
     */
    private function validateGatewayUrl(): array
    {
        $errors = [];
        $gatewayUrl = config('cmi.gateway_url');

        if (empty($gatewayUrl)) {
            $errors[] = 'CMI_GATEWAY_URL is missing. Please configure the gateway URL.';
            return $errors;
        }

        if (!is_string($gatewayUrl)) {
            $errors[] = 'CMI_GATEWAY_URL must be a string value.';
            return $errors;
        }

        // Validate HTTPS
        if (!str_starts_with($gatewayUrl, 'https://')) {
            $errors[] = 'CMI_GATEWAY_URL must use HTTPS protocol for security.';
        }

        // Validate URL format
        if (!filter_var($gatewayUrl, FILTER_VALIDATE_URL)) {
            $errors[] = 'CMI_GATEWAY_URL is not a valid URL format.';
        }

        // In production mode, validate that production URL is used
        if (!$this->isTestMode() && !$this->isValidProductionUrl($gatewayUrl)) {
            $errors[] = 'Production mode requires a valid CMI production gateway URL (https://attijari-payment.cmi.co.ma/fim/est3Dgate or https://payment.cmi.co.ma/fim/est3Dgate).';
        }

        return $errors;
    }

    private function validateReturnUrls(): array
    {
        if (!$this->isProductionEnvironment() || $this->isTestMode()) {
            return [];
        }

        $errors = [];
        $urls = [
            'CMI_OK_URL' => config('cmi.ok_url') ?: route('cmi.success'),
            'CMI_FAIL_URL' => config('cmi.fail_url') ?: route('cmi.fail'),
            'CMI_CALLBACK_URL' => config('cmi.callback_url') ?: route('cmi.callback'),
        ];

        foreach ($urls as $label => $url) {
            $parts = is_string($url) ? parse_url($url) : false;
            if (!is_array($parts) || ($parts['scheme'] ?? null) !== 'https' || empty($parts['host'])) {
                $errors[] = "{$label} must be an absolute HTTPS URL in production.";
            }
        }

        return $errors;
    }

    /**
     * Check if the given URL is a valid CMI production URL.
     *
     * @param string|null $url
     * @return bool
     */
    private function isValidProductionUrl(?string $url): bool
    {
        if (empty($url)) {
            return false;
        }

        $parts = parse_url($url);

        if (($parts['scheme'] ?? null) !== 'https') {
            return false;
        }

        if (!in_array(strtolower($parts['host'] ?? ''), self::PRODUCTION_GATEWAY_HOSTS, true)) {
            return false;
        }

        return strcasecmp(rtrim($parts['path'] ?? '', '/'), '/fim/est3Dgate') === 0;
    }

    private function isKnownTestUrl(string $url): bool
    {
        return in_array(rtrim($url, '/'), self::TEST_GATEWAY_URLS, true);
    }

    /**
     * Gather configuration warnings.
     *
     * @return array<string>
     */
    private function gatherWarnings(): array
    {
        $warnings = [];

        // Warn about test mode in production environment
        if ($this->isTestMode() && $this->isProductionEnvironment()) {
            $warnings[] = 'CMI is configured in test mode while running in production environment.';
        }

        // Warn about empty IP whitelist in production
        $allowedIps = config('cmi.allowed_ips', []);
        $allowedIps = is_array($allowedIps) ? array_filter($allowedIps) : [];
        if (empty($allowedIps) && !$this->isTestMode()) {
            $warnings[] = 'CMI_ALLOWED_IPS is empty. Consider configuring IP whitelist for enhanced security.';
        }

        // Warn about missing callback URL
        if (empty(config('cmi.callback_url'))) {
            $warnings[] = 'CMI_CALLBACK_URL is not configured. Default route will be used.';
        }

        // Warn about missing OK/FAIL URLs
        if (empty(config('cmi.ok_url'))) {
            $warnings[] = 'CMI_OK_URL is not configured. Default route will be used.';
        }
        if (empty(config('cmi.fail_url'))) {
            $warnings[] = 'CMI_FAIL_URL is not configured. Default route will be used.';
        }

        return $warnings;
    }

    /**
     * Check if the application is running in production environment.
     *
     * @return bool
     */
    private function isProductionEnvironment(): bool
    {
        return app()->environment('production');
    }
}
