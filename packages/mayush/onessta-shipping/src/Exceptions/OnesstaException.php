<?php

namespace Mayush\Shipping\Onessta\Exceptions;

use Exception;

class OnesstaException extends Exception
{
    protected string $errorCode;

    public function __construct(string $message, string $errorCode = 'ONESSTA_ERROR', int $code = 0, ?\Throwable $previous = null)
    {
        parent::__construct($message, $code, $previous);
        $this->errorCode = $errorCode;
    }

    public function getErrorCode(): string
    {
        return $this->errorCode;
    }
}
