<?php

namespace Tests\Feature;

use App\Models\Order;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Facades\Bus;
use Illuminate\Support\Facades\Event;
use Illuminate\Support\Facades\Http;
use Illuminate\Support\Facades\Log;
use Mayush\Shipping\Onessta\Client\OnesstaClient;
use Mayush\Shipping\Onessta\Client\RequestSigner;
use Mayush\Shipping\Onessta\DTOs\ShipmentRequestDto;
use Mayush\Shipping\Onessta\Events\ShipmentCreated;
use Mayush\Shipping\Onessta\Events\ShipmentStatusUpdated;
use Mayush\Shipping\Onessta\Exceptions\ShipmentCreationException;
use Mayush\Shipping\Onessta\Jobs\PollTrackingJob;
use Mayush\Shipping\Onessta\Jobs\SyncCitiesJob;
use Mayush\Shipping\Onessta\Jobs\SyncPickupCitiesJob;
use Mayush\Shipping\Onessta\Models\OnesstaCityMap;
use Mayush\Shipping\Onessta\Models\OnesstaShipment;
use Mayush\Shipping\Onessta\Models\OnesstaTrackingEvent;
use Mayush\Shipping\Onessta\Models\OnesstaWebhookLog;
use Mayush\Shipping\Onessta\Services\AuthService;
use Mayush\Shipping\Onessta\Services\ShipmentService;
use Mayush\Shipping\Onessta\Services\TrackingService;
use Tests\TestCase;

class OnesstaIntegrationTest extends TestCase
{
    use RefreshDatabase;

    protected function setUp(): void
    {
        parent::setUp();

        config([
            'onessta.enabled' => true,
            'onessta.base_url' => 'https://onessta.test/api/v1',
            'onessta.auth.token' => 'test-token',
            'onessta.auth.api_key' => 'test-api-key',
            'onessta.auth.client_id' => 'test-client-id',
            'onessta.http.retry_times' => 0,
            'onessta.http.retry_sleep_ms' => 0,
            'onessta.webhook.secret' => 'webhook-secret',
            'onessta.webhook.queue' => false,
            'onessta.webhook.fail_on_signature_mismatch' => true,
        ]);

        $this->app->forgetInstance(OnesstaClient::class);
        $this->app->forgetInstance(AuthService::class);
        $this->app->forgetInstance(ShipmentService::class);
        $this->app->forgetInstance(TrackingService::class);

        Http::preventStrayRequests();
    }

    public function test_missing_credentials_fail_safely_without_remote_call(): void
    {
        config([
            'onessta.auth.token' => null,
            'onessta.auth.api_key' => null,
            'onessta.auth.client_id' => null,
        ]);

        Log::shouldReceive('warning')
            ->once()
            ->with('ONESSTA credentials validation skipped because credentials are incomplete.');

        $this->assertFalse(app(AuthService::class)->isConfigured());
        $this->assertFalse(app(AuthService::class)->validateCredentials());

        Http::assertNothingSent();
    }

    public function test_admin_credential_validation_uses_fake_http_and_reports_success(): void
    {
        Http::fake([
            'https://onessta.test/api/v1/p/cities*' => Http::response(['data' => []], 200),
        ]);

        $admin = User::factory()->admin()->create();

        $this->actingAs($admin)
            ->getJson(route('onessta.validate-credentials'))
            ->assertOk()
            ->assertJson([
                'valid' => true,
                'configured' => true,
            ]);

        Http::assertSentCount(1);
    }

    public function test_onessta_admin_routes_require_admin_access(): void
    {
        $this->get(route('onessta.index'))
            ->assertRedirect(route('login'));

        $customer = User::factory()->customer()->create();

        $this->actingAs($customer)
            ->get(route('onessta.index'))
            ->assertNotFound();
    }

    public function test_admin_actions_dispatch_onessta_jobs(): void
    {
        Bus::fake();

        $admin = User::factory()->admin()->create();

        $this->actingAs($admin)
            ->postJson(route('onessta.sync-cities'), ['force' => true])
            ->assertOk()
            ->assertJson(['status' => 'queued', 'force' => true]);

        $this->actingAs($admin)
            ->postJson(route('onessta.sync-pickup-cities'))
            ->assertOk()
            ->assertJson(['status' => 'queued']);

        $this->actingAs($admin)
            ->postJson(route('onessta.poll-tracking'))
            ->assertOk()
            ->assertJson(['status' => 'queued']);

        Bus::assertDispatched(SyncCitiesJob::class);
        Bus::assertDispatched(SyncPickupCitiesJob::class);
        Bus::assertDispatched(PollTrackingJob::class);
    }

    public function test_shipment_creation_persists_fake_http_response(): void
    {
        Event::fake([ShipmentCreated::class]);
        $this->mappedCity(10);

        Http::fake([
            'https://onessta.test/api/v1/p/parcels/add*' => Http::response([
                'data' => [
                    'parcel' => [
                        'code' => 'ORD-1001',
                        'status' => 'WAITING_PICKUP',
                        'situation' => 'NOT_PAID',
                        'receiver' => 'Buyer Name',
                        'phone' => '0600000000',
                        'address' => '123 Main Street',
                        'price' => 199.50,
                        'city' => ['id' => 10, 'name' => 'Casablanca'],
                    ],
                ],
            ], 200),
        ]);

        $shipment = app(ShipmentService::class)->createShipment(new ShipmentRequestDto(
            code: 'ORD-1001',
            receiver: 'Buyer Name',
            phone: '0600000000',
            price: 199.50,
            city: 10,
            address: '123 Main Street',
        ), orderId: 42, meta: ['is_cod' => true]);

        $this->assertSame('ORD-1001', $shipment->code);
        $this->assertSame('WAITING_PICKUP', $shipment->status);
        $this->assertTrue($shipment->is_cod);
        $this->assertSame('awaiting_payment', $shipment->payment_situation);
        $this->assertDatabaseHas('onessta_shipments', [
            'order_id' => 42,
            'code' => 'ORD-1001',
            'remote_city_id' => 10,
        ]);

        Event::assertDispatched(ShipmentCreated::class);
    }

    public function test_shipment_creation_failure_is_controlled(): void
    {
        $this->mappedCity(10);

        Http::fake([
            'https://onessta.test/api/v1/p/parcels/add*' => Http::response(['error' => 'Carrier unavailable'], 500),
        ]);

        $this->expectException(ShipmentCreationException::class);

        app(ShipmentService::class)->createShipment(new ShipmentRequestDto(
            code: 'ORD-FAIL',
            receiver: 'Buyer Name',
            phone: '0600000000',
            price: 199.50,
            city: 10,
            address: '123 Main Street',
        ), orderId: 42);
    }

    public function test_tracking_poll_updates_shipment_and_persists_events(): void
    {
        Event::fake([ShipmentStatusUpdated::class]);

        $shipment = OnesstaShipment::create([
            'code' => 'ORD-TRACK',
            'receiver' => 'Buyer Name',
            'phone' => '0600000000',
            'address' => '123 Main Street',
            'price' => 100,
            'status' => 'WAITING_PICKUP',
        ]);

        Http::fake([
            'https://onessta.test/api/v1/p/parcels/tracking*' => Http::response([
                'data' => [
                    'parcel' => [
                        'code' => 'ORD-TRACK',
                        'status' => 'SENT',
                        'situation' => 'NOT_PAID',
                        'history' => [
                            [
                                'status' => 'WAITING_PICKUP',
                                'name' => 'Waiting pickup',
                                'created_at' => '2026-05-06 09:00:00',
                            ],
                            [
                                'status' => 'SENT',
                                'name' => 'Sent to hub',
                                'created_at' => '2026-05-06 10:00:00',
                            ],
                        ],
                    ],
                ],
            ], 200),
        ]);

        app(TrackingService::class)->pollAndUpdate('ORD-TRACK');

        $this->assertSame('SENT', $shipment->fresh()->status);
        $this->assertSame(2, OnesstaTrackingEvent::where('onessta_shipment_id', $shipment->id)->count());

        Event::assertDispatched(ShipmentStatusUpdated::class);
    }

    public function test_webhook_rejects_invalid_signature_with_403(): void
    {
        $payload = json_encode([
            'event' => 'parcel.status_updated',
            'code' => 'ORD-WEBHOOK',
            'status' => 'DELIVERED',
        ]);

        $this->withHeaders([
            'X-Signature' => 'invalid-signature',
            'X-Webhook-Event' => 'parcel.status_updated',
        ])->postJson(route('onessta.webhook'), json_decode($payload, true))
            ->assertForbidden()
            ->assertJson([
                'status' => 'error',
            ]);

        $this->assertDatabaseHas('onessta_webhook_logs', [
            'event_type' => 'parcel.status_updated',
            'signature_valid' => false,
        ]);
    }

    public function test_webhook_updates_shipment_status_with_valid_signature(): void
    {
        Event::fake([ShipmentStatusUpdated::class]);

        $order = Order::factory()->create(['payment_status' => 'unpaid']);
        $shipment = OnesstaShipment::create([
            'order_id' => $order->id,
            'code' => 'ORD-WEBHOOK',
            'receiver' => 'Buyer Name',
            'phone' => '0600000000',
            'address' => '123 Main Street',
            'price' => 100,
            'status' => 'SENT',
            'is_cod' => true,
            'payment_situation' => 'cod_awaiting',
        ]);

        $payload = json_encode([
            'event' => 'parcel.status_updated',
            'code' => 'ORD-WEBHOOK',
            'status' => 'DELIVERED',
            'situation' => 'PAID',
        ]);

        $signature = (new RequestSigner())->sign($payload, 'webhook-secret');

        $this->call('POST', route('onessta.webhook'), [], [], [], [
            'CONTENT_TYPE' => 'application/json',
            'HTTP_ACCEPT' => 'application/json',
            'HTTP_X_SIGNATURE' => $signature,
            'HTTP_X_WEBHOOK_EVENT' => 'parcel.status_updated',
        ], $payload)
            ->assertOk()
            ->assertJson(['status' => 'ok']);

        $this->assertSame('DELIVERED', $shipment->fresh()->status);
        $this->assertSame('paid', $order->fresh()->payment_status);
        $this->assertDatabaseHas('onessta_webhook_logs', [
            'event_type' => 'parcel.status_updated',
            'signature_valid' => true,
            'processed' => true,
        ]);

        Event::assertDispatched(ShipmentStatusUpdated::class);
    }

    private function mappedCity(int $remoteCityId): OnesstaCityMap
    {
        return OnesstaCityMap::create([
            'remote_city_id' => $remoteCityId,
            'remote_city_name' => 'Casablanca',
            'local_city_id' => $remoteCityId,
            'local_city_name' => 'Casablanca',
            'active' => true,
        ]);
    }
}
