<?php

namespace Mayush\Shipping\Onessta\Tests\Feature;

use Illuminate\Support\Facades\Event;
use Illuminate\Support\Facades\Http;
use Mayush\Shipping\Onessta\DTOs\ShipmentRequestDto;
use Mayush\Shipping\Onessta\Events\ShipmentCreated;
use Mayush\Shipping\Onessta\Jobs\CreateShipmentJob;
use Mayush\Shipping\Onessta\Models\OnesstaShipment;
use Mayush\Shipping\Onessta\Services\ReferenceDataService;
use Mayush\Shipping\Onessta\Services\ShipmentService;

class ShipmentCreationTest extends FeatureTestCase
{
    protected function setUp(): void
    {
        if (!env('TESTING_WITH_DB', false)) {
            $this->markTestSkipped('Database tests require TESTING_WITH_DB=true and MySQL connection');
        }
        parent::setUp();
    }

    public function test_create_shipment_job_persists_shipment(): void
    {
        Http::fake([
            '*' => Http::response([
                'code' => 'ORD-12345',
                'status' => 'WAITING_PICKUP',
                'situation' => 'none',
                'receiver' => 'John Doe',
                'phone' => '+1234567890',
                'address' => '123 Main St',
                'price' => 99.99,
                'city' => ['id' => 1],
            ], 200),
        ]);

        Event::fake([ShipmentCreated::class]);

        $dto = new ShipmentRequestDto(
            code: 'ORD-12345',
            receiver: 'John Doe',
            phone: '+1234567890',
            price: 99.99,
            city: 1,
            address: '123 Main St'
        );

        $referenceDataService = \Mockery::mock(ReferenceDataService::class);
        $referenceDataService->shouldReceive('resolveCity')->andReturn(1);

        $client = new \Mayush\Shipping\Onessta\Client\OnesstaClient(
            'https://api.onessta.com/api/v1',
            'test-token',
            'test-api-key',
            'test-client-id'
        );

        $service = new ShipmentService($client, $referenceDataService);
        $shipment = $service->createShipment($dto, 1);

        $this->assertInstanceOf(OnesstaShipment::class, $shipment);
        $this->assertEquals('ORD-12345', $shipment->code);
        $this->assertEquals('WAITING_PICKUP', $shipment->status);
        $this->assertEquals(1, $shipment->order_id);

        Event::assertDispatched(ShipmentCreated::class);
    }

    public function test_create_shipment_job_dispatches_event_on_failure(): void
    {
        Http::fake([
            '*' => Http::response(['error' => 'Server Error'], 500),
        ]);

        Event::fake([ShipmentCreated::class]);

        $dto = new ShipmentRequestDto(
            code: 'ORD-FAIL-123',
            receiver: 'John Doe',
            phone: '+1234567890',
            price: 99.99,
            city: 1,
            address: '123 Main St'
        );

        $referenceDataService = \Mockery::mock(ReferenceDataService::class);
        $referenceDataService->shouldReceive('resolveCity')->andReturn(1);

        $client = new \Mayush\Shipping\Onessta\Client\OnesstaClient(
            'https://api.onessta.com/api/v1',
            'test-token',
            'test-api-key',
            'test-client-id'
        );

        $service = new ShipmentService($client, $referenceDataService);

        $this->expectException(\Mayush\Shipping\Onessta\Exceptions\ShipmentCreationException::class);
        $service->createShipment($dto, 1);
    }
}
