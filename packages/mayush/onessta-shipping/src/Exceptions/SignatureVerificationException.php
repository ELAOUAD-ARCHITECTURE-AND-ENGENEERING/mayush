<?php

namespace Mayush\Shipping\Onessta\Exceptions;

class SignatureVerificationException extends OnesstaException
{
    public function __construct(string $message = 'Webhook signature verification failed.', ?\Throwable $previous = null)
    {
        parent::__construct($message, 'SIGNATURE_ERROR', 403, $previous);
    }
}
