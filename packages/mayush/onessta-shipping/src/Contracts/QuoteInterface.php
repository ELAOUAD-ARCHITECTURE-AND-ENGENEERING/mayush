<?php

namespace Mayush\Shipping\Onessta\Contracts;

interface QuoteInterface
{
    public function getQuote(array $payload): array;
}
