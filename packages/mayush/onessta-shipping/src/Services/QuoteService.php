<?php

namespace Mayush\Shipping\Onessta\Services;

use Mayush\Shipping\Onessta\Exceptions\UnsupportedCapabilityException;

class QuoteService
{
    public function getQuote(array $payload): array
    {
        throw new UnsupportedCapabilityException('quotes');
    }

    public function supportsQuotes(): bool
    {
        return config('onessta.capabilities.quotes', false);
    }
}
