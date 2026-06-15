<?php

namespace Mayush\Shipping\Onessta\Exceptions;

class UnsupportedCapabilityException extends OnesstaException
{
    protected string $capability;

    public function __construct(string $capability, ?\Throwable $previous = null)
    {
        $this->capability = $capability;
        parent::__construct(
            "ONESSTA does not support this capability: {$capability}. Check documentation.",
            'UNSUPPORTED_CAPABILITY',
            501,
            $previous
        );
    }

    public function getCapability(): string
    {
        return $this->capability;
    }
}
