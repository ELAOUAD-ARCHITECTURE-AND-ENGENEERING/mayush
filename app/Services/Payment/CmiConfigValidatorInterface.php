<?php

namespace App\Services\Payment;

/**
 * Interface for CMI configuration validation.
 */
interface CmiConfigValidatorInterface
{
    /**
     * Validate all required CMI configuration.
     *
     * @return ValidationResult
     */
    public function validate(): ValidationResult;

    /**
     * Check if test mode is enabled.
     *
     * @return bool
     */
    public function isTestMode(): bool;

    /**
     * Get the appropriate gateway URL based on mode.
     *
     * @return string
     */
    public function getGatewayUrl(): string;
}
