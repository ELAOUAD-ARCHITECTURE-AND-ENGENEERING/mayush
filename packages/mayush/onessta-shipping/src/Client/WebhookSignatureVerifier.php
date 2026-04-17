<?php

namespace Mayush\Shipping\Onessta\Client;

use Illuminate\Http\Request;
use Illuminate\Support\Facades\Log;
use Mayush\Shipping\Onessta\Exceptions\SignatureVerificationException;

class WebhookSignatureVerifier
{
    private RequestSigner $signer;

    public function __construct(RequestSigner $signer)
    {
        $this->signer = $signer;
    }

    public function verify(string $payload, ?string $signature): bool
    {
        $secret = config('onessta.webhook.secret');

        if (empty($secret)) {
            Log::error('ONESSTA webhook secret is not configured');
            throw new SignatureVerificationException('Webhook secret is not configured.');
        }

        if (empty($payload)) {
            Log::error('ONESSTA webhook payload is empty');
            throw new SignatureVerificationException('Webhook payload is empty.');
        }

        if (empty($signature)) {
            Log::warning('ONESSTA webhook signature is missing');
            throw new SignatureVerificationException('Webhook signature is missing.');
        }

        if (!$this->signer->verify($payload, $signature, $secret)) {
            Log::warning('ONESSTA webhook signature mismatch', [
                'expected' => hash_hmac('sha256', $payload, $secret),
                'received' => $signature,
            ]);
            throw new SignatureVerificationException('Webhook signature verification failed.');
        }

        return true;
    }

    public function verifyFromRequest(Request $request): bool
    {
        $payload = $request->getContent();
        $signature = $request->header('X-Signature');

        return $this->verify($payload, $signature);
    }
}
