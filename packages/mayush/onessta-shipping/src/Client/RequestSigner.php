<?php

namespace Mayush\Shipping\Onessta\Client;

class RequestSigner
{
    public function sign(string $payload, string $secret): string
    {
        return hash_hmac('sha256', $payload, $secret);
    }

    public function verify(string $payload, string $signature, string $secret): bool
    {
        $expected = $this->sign($payload, $secret);
        return hash_equals($expected, $signature);
    }
}
