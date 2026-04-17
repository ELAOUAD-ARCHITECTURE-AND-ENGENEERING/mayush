<?php

namespace Mayush\Shipping\Onessta\Tests\Unit\Client;

use Mayush\Shipping\Onessta\Client\RequestSigner;
use Mayush\Shipping\Onessta\Tests\Unit\UnitTestCase;

class RequestSignerTest extends UnitTestCase
{
    private RequestSigner $signer;

    protected function setUp(): void
    {
        parent::setUp();
        $this->signer = new RequestSigner();
    }

    public function test_sign_produces_hmac_sha256(): void
    {
        $payload = '{"code":"ORD-123"}';
        $secret = 'my-secret-key';

        $signature = $this->signer->sign($payload, $secret);

        $expected = hash_hmac('sha256', $payload, $secret);
        $this->assertEquals($expected, $signature);
    }

    public function test_verify_returns_true_for_valid_signature(): void
    {
        $payload = '{"code":"ORD-123"}';
        $secret = 'my-secret-key';
        $signature = hash_hmac('sha256', $payload, $secret);

        $result = $this->signer->verify($payload, $signature, $secret);

        $this->assertTrue($result);
    }

    public function test_verify_returns_false_for_invalid_signature(): void
    {
        $payload = '{"code":"ORD-123"}';
        $secret = 'my-secret-key';

        $result = $this->signer->verify($payload, 'wrong-signature', $secret);

        $this->assertFalse($result);
    }

    public function test_different_payloads_produce_different_signatures(): void
    {
        $secret = 'my-secret-key';

        $sig1 = $this->signer->sign('payload-1', $secret);
        $sig2 = $this->signer->sign('payload-2', $secret);

        $this->assertNotEquals($sig1, $sig2);
    }

    public function test_same_payload_same_secret_produces_same_signature(): void
    {
        $payload = '{"code":"ORD-123"}';
        $secret = 'my-secret-key';

        $sig1 = $this->signer->sign($payload, $secret);
        $sig2 = $this->signer->sign($payload, $secret);

        $this->assertEquals($sig1, $sig2);
    }
}
