<?php

namespace Mayush\Shipping\Onessta\Exceptions;

class AuthenticationException extends OnesstaException
{
    public function __construct(string $message = 'ONESSTA authentication failed. Check your credentials.', ?\Throwable $previous = null)
    {
        parent::__construct($message, 'AUTH_ERROR', 401, $previous);
    }
}
