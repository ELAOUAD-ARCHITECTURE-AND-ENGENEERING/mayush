<?php

namespace Mayush\Shipping\Onessta\Tests\Unit\Client;

use Mayush\Shipping\Onessta\Client\WebhookSignatureVerifier;
use Mayush\Shipping\Onessta\Exceptions\SignatureVerificationException;
use Mayush\Shipping\Onessta\Tests\Unit\UnitTestCase;

class WebhookSignatureVerifierTest extends UnitTestCase
{
    private WebhookSignatureVerifier $verifier;

    protected function setUp(): void
    {
        parent::setUp();
        $this->verifier = new WebhookSignatureVerifier(
            new \Mayush\Shipping\Onessta\Client\RequestSigner()
        );
    }

    public function test_accepts_valid_signature(): void
    {
        $payload = '{"code":"ORD-12345","status":"DELIVERED"}';
        $secret = 'webhook-secret-key';
        $signature = hash_hmac('sha256', $payload, $secret);

        config(['onessta.webhook.secret' => $secret]);

        $result = $this->verifier->verify($payload, $signature);

        $this->assertTrue($result);
    }

    public function test_throws_on_invalid_signature(): void
    {
        $payload = '{"code":"ORD-12345","status":"DELIVERED"}';
        config(['onessta.webhook.secret' => 'real-secret']);
        config(['onessta.webhook.fail_on_signature_mismatch' => true]);

        $this->expectException(SignatureVerificationException::class);
        $this->verifier->verify($payload, 'invalid-signature');
    }

    public function test_throws_when_secret_is_null(): void
    {
        config(['onessta.webhook.secret' => null]);

        $this->expectException(SignatureVerificationException::class);
        $this->verifier->verify('{}', 'any-signature');
    }

    public function test_throws_when_signature_is_null(): void
    {
        config(['onessta.webhook.secret' => 'some-secret']);

        $this->expectException(SignatureVerificationException::class);
        $this->verifier->verify('{}', null);
    }

    public function test_throws_when_payload_is_empty(): void
    {
        config(['onessta.webhook.secret' => 'some-secret']);

        $this->expectException(SignatureVerificationException::class);
        $this->verifier->verify('', 'some-signature');
    }
}
