<?php

namespace Mayush\Shipping\Onessta\Exceptions;

class ValidationException extends OnesstaException
{
    protected array $errors;

    public function __construct(string $message = 'Validation failed', array $errors = [], ?\Throwable $previous = null)
    {
        parent::__construct($message, 'VALIDATION_ERROR', 422, $previous);
        $this->errors = $errors;
    }

    public function getErrors(): array
    {
        return $this->errors;
    }
}
