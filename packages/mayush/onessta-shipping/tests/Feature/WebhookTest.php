<?php

namespace Mayush\Shipping\Onessta\Tests\Feature;

use Illuminate\Http\Request;
use Illuminate\Support\Facades\Cache;
use Illuminate\Support\Facades\Http;
use Mayush\Shipping\Onessta\Client\RequestSigner;
use Mayush\Shipping\Onessta\Client\WebhookSignatureVerifier;
use Mayush\Shipping\Onessta\Models\OnesstaShipment;
use Mayush\Shipping\Onessta\Models\OnesstaWebhookLog;
use Mayush\Shipping\Onessta\Services\WebhookService;

class WebhookTest extends FeatureTestCase
{
    protected function setUp(): void
    {
        if (!env('TESTING_WITH_DB', false)) {
            $this->markTestSkipped('Database tests require TESTING_WITH_DB=true and MySQL connection');
        }

        parent::setUp();

        config(['onessta.webhook.secret' => 'webhook-secret-key']);
        config(['onessta.webhook.fail_on_signature_mismatch' => true]);
        config(['onessta.webhook.queue' => false]);
    }

    public function test_webhook_signature_verification_passes_with_valid_signature(): void
    {
        $payload = json_encode([
            'event' => 'parcel.status_updated',
            'code' => 'ORD-12345',
            'status' => 'DELIVERED',
            'situation' => 'none',
        ]);

        $signer = new RequestSigner();
        $signature = $signer->sign($payload, 'webhook-secret-key');

        $verifier = new WebhookSignatureVerifier(new RequestSigner());

        $this->assertTrue($verifier->verify($payload, $signature));
    }

    public function test_webhook_signature_verification_fails_with_invalid_signature(): void
    {
        $payload = json_encode([
            'event' => 'parcel.status_updated',
            'code' => 'ORD-12345',
            'status' => 'DELIVERED',
        ]);

        $verifier = new WebhookSignatureVerifier(new RequestSigner());

        $this->expectException(\Mayush\Shipping\Onessta\Exceptions\SignatureVerificationException::class);
        $verifier->verify($payload, 'invalid-signature');
    }

    public function test_webhook_service_returns_log_with_valid_signature(): void
    {
        $payload = json_encode([
            'event' => 'parcel.status_updated',
            'code' => 'ORD-12345',
            'status' => 'SENT',
        ]);

        $signer = new RequestSigner();
        $signature = $signer->sign($payload, 'webhook-secret-key');

        $request = Request::create('/webhooks/onessta', 'POST', [], [], [], [
            'HTTP_X_SIGNATURE' => $signature,
            'HTTP_X_WEBHOOK_EVENT' => 'parcel.status_updated',
        ], $payload);

        $webhookService = $this->app->make(WebhookService::class);
        $log = $webhookService->handle($request);

        $this->assertInstanceOf(OnesstaWebhookLog::class, $log);
        $this->assertEquals('parcel.status_updated', $log->event_type);
    }

    public function test_webhook_logs_raw_payload(): void
    {
        $payload = json_encode([
            'event' => 'parcel.status_updated',
            'code' => 'ORD-12345',
            'status' => 'SENT',
        ]);

        $signer = new RequestSigner();
        $signature = $signer->sign($payload, 'webhook-secret-key');

        $request = Request::create('/webhooks/onessta', 'POST', [], [], [], [
            'HTTP_X_SIGNATURE' => $signature,
            'HTTP_X_WEBHOOK_EVENT' => 'parcel.status_updated',
        ], $payload);

        $webhookService = $this->app->make(WebhookService::class);
        $webhookService->handle($request);

        $log = OnesstaWebhookLog::latest('id')->first();
        $this->assertNotNull($log);
        $this->assertEquals('parcel.status_updated', $log->event_type);
    }
}
