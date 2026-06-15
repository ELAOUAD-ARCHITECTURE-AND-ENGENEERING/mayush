<?php

namespace Mayush\Shipping\Onessta\Tests\Unit\Services;

use Mayush\Shipping\Onessta\Client\OnesstaClient;
use Mayush\Shipping\Onessta\DTOs\ShipmentRequestDto;
use Mayush\Shipping\Onessta\DTOs\ShipmentResponseDto;
use Mayush\Shipping\Onessta\Events\ShipmentCreated;
use Mayush\Shipping\Onessta\Models\OnesstaShipment;
use Mayush\Shipping\Onessta\Services\ShipmentService;
use Mayush\Shipping\Onessta\Tests\Unit\UnitTestCase;
use Illuminate\Support\Facades\Event;
use Illuminate\Support\Facades\Http;

class ShipmentServiceTest extends UnitTestCase
{
    public function test_shipment_request_dto_produces_correct_api_payload(): void
    {
        $dto = new ShipmentRequestDto(
            code: 'ORD-12345',
            receiver: 'John Doe',
            phone: '+1234567890',
            price: 99.99,
            city: 1,
            address: '123 Main St',
            replace: true
        );

        $array = $dto->toArray();

        $this->assertEquals('ORD-12345', $array['code']);
        $this->assertEquals('John Doe', $array['receiver']);
        $this->assertEquals('+1234567890', $array['phone']);
        $this->assertEquals('99.99', $array['price']);
        $this->assertEquals(['id' => 1], $array['city']);
        $this->assertEquals('123 Main St', $array['address']);
        $this->assertTrue($array['replace']);
        $this->assertFalse($array['can_open']);
    }

    public function test_shipment_request_dto_cod_has_correct_flags(): void
    {
        $dto = new ShipmentRequestDto(
            code: 'ORD-COD-123',
            receiver: 'Jane Doe',
            phone: '+212666666666',
            price: 500,
            city: 1,
            address: 'Casablanca',
            replace: true,
            can_open: true
        );

        $array = $dto->toArray();

        $this->assertEquals('ORD-COD-123', $array['code']);
        $this->assertEquals('500', $array['price']);
        $this->assertTrue($array['replace']);
        $this->assertTrue($array['can_open']);
        $this->assertEquals(['id' => 1], $array['city']);
    }

    public function test_shipment_response_dto_parses_success_response(): void
    {
        $data = [
            'code' => 'ON123456789',
            'status' => 'NEW_PARCEL',
            'situation' => 'NOT_PAID',
            'receiver' => 'Ahmed Test',
            'phone' => '+212600000000',
            'address' => 'Casablanca',
            'price' => '500',
            'city' => ['id' => 1, 'name' => 'Casablanca'],
        ];

        $dto = ShipmentResponseDto::fromArray($data);

        $this->assertEquals('ON123456789', $dto->code);
        $this->assertEquals('NEW_PARCEL', $dto->status);
        $this->assertEquals('NOT_PAID', $dto->situation);
        $this->assertEquals('Ahmed Test', $dto->receiver);
        $this->assertEquals('+212600000000', $dto->phone);
        $this->assertTrue($dto->isSuccess());
    }

    public function test_shipment_response_dto_detects_failure(): void
    {
        $data = [
            'code' => 'FAILED',
            'status' => 'FAILED',
            'situation' => 'ERROR',
        ];

        $dto = ShipmentResponseDto::fromArray($data);

        $this->assertFalse($dto->isSuccess());
        $this->assertEquals('FAILED', $dto->status);
    }

    public function test_create_shipment_job_dispatches_with_cod_flag(): void
    {
        Event::fake([ShipmentCreated::class]);

        $job = new \Mayush\Shipping\Onessta\Jobs\CreateShipmentJob(
            orderId: 1,
            shipmentData: [
                'code' => 'ORD-TEST-001',
                'receiver' => 'Test Customer',
                'phone' => '+212600000000',
                'price' => 500.00,
                'city' => 1,
                'address' => 'Casablanca Morocco',
                'is_cod' => true,
                'product_nature' => 'general',
            ]
        );

        $this->assertEquals(1, $job->orderId);
        $this->assertTrue($job->shipmentData['is_cod']);
        $this->assertEquals('ORD-TEST-001', $job->shipmentData['code']);
    }

    public function test_create_shipment_job_dispatches_with_non_cod_flag(): void
    {
        $job = new \Mayush\Shipping\Onessta\Jobs\CreateShipmentJob(
            orderId: 2,
            shipmentData: [
                'code' => 'ORD-TEST-002',
                'receiver' => 'Prepaid Customer',
                'phone' => '+212600000001',
                'price' => 1000.00,
                'city' => 1,
                'address' => 'Rabat Morocco',
                'is_cod' => false,
                'product_nature' => 'general',
            ]
        );

        $this->assertEquals(2, $job->orderId);
        $this->assertFalse($job->shipmentData['is_cod']);
        $this->assertEquals('ORD-TEST-002', $job->shipmentData['code']);
    }
}
