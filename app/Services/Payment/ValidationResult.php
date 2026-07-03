<?php

namespace App\Services\Payment;

/**
 * Result object for CMI configuration validation.
 */
class ValidationResult
{
    /**
     * @var bool Whether the validation passed
     */
    public readonly bool $isValid;

    /**
     * @var array<string> List of error messages
     */
    public readonly array $errors;

    /**
     * @var array<string> List of warning messages
     */
    public readonly array $warnings;

    /**
     * Create a new ValidationResult instance.
     *
     * @param bool $isValid
     * @param array<string> $errors
     * @param array<string> $warnings
     */
    public function __construct(bool $isValid, array $errors = [], array $warnings = [])
    {
        $this->isValid = $isValid;
        $this->errors = $errors;
        $this->warnings = $warnings;
    }

    /**
     * Create a successful validation result.
     *
     * @param array<string> $warnings
     * @return self
     */
    public static function success(array $warnings = []): self
    {
        return new self(true, [], $warnings);
    }

    /**
     * Create a failed validation result.
     *
     * @param array<string> $errors
     * @param array<string> $warnings
     * @return self
     */
    public static function failure(array $errors, array $warnings = []): self
    {
        return new self(false, $errors, $warnings);
    }

    /**
     * Check if there are any errors.
     *
     * @return bool
     */
    public function hasErrors(): bool
    {
        return !empty($this->errors);
    }

    /**
     * Check if there are any warnings.
     *
     * @return bool
     */
    public function hasWarnings(): bool
    {
        return !empty($this->warnings);
    }

    /**
     * Get the first error message.
     *
     * @return string|null
     */
    public function getFirstError(): ?string
    {
        return $this->errors[0] ?? null;
    }
}
