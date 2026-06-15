<?php

namespace Mayush\Shipping\Onessta\Tests\Unit\DTOs;

use Mayush\Shipping\Onessta\DTOs\ShipmentResponseDto;
use Mayush\Shipping\Onessta\Tests\Unit\UnitTestCase;

class ShipmentResponseDtoTest extends UnitTestCase
{
    public function test_from_array_extracts_all_fields(): void
    {
        $data = [
            'code' => 'ORD-12345',
            'status' => 'WAITING_PICKUP',
            'situation' => 'none',
            'receiver' => 'John Doe',
            'phone' => '+1234567890',
            'address' => '123 Main St',
            'price' => 99.99,
            'city' => ['id' => 1, 'name' => 'Test City'],
            'created_at' => '2026-06-20T10:00:00Z',
            'updated_at' => '2026-06-20T12:00:00Z',
        ];

        $dto = ShipmentResponseDto::fromArray($data);

        $this->assertEquals('ORD-12345', $dto->code);
        $this->assertEquals('WAITING_PICKUP', $dto->status);
        $this->assertEquals('none', $dto->situation);
        $this->assertEquals('John Doe', $dto->receiver);
        $this->assertEquals('+1234567890', $dto->phone);
        $this->assertEquals(99.99, $dto->price);
        $this->assertEquals(1, $dto->getCityId());
        $this->assertNotNull($dto->raw);
    }

    public function test_is_success_returns_true_when_code_and_status_present(): void
    {
        $dto = ShipmentResponseDto::fromArray([
            'code' => 'ORD-123',
            'status' => 'WAITING_PICKUP',
        ]);

        $this->assertTrue($dto->isSuccess());
    }

    public function test_is_success_returns_false_when_code_missing(): void
    {
        $dto = ShipmentResponseDto::fromArray([
            'status' => 'WAITING_PICKUP',
        ]);

        $this->assertFalse($dto->isSuccess());
    }

    public function test_get_city_id_returns_null_when_city_missing(): void
    {
        $dto = ShipmentResponseDto::fromArray([
            'code' => 'ORD-123',
            'status' => 'OK',
        ]);

        $this->assertNull($dto->getCityId());
    }

    public function test_handles_missing_optional_fields(): void
    {
        $dto = ShipmentResponseDto::fromArray([
            'code' => 'ORD-123',
            'status' => 'OK',
        ]);

        $this->assertNull($dto->situation);
        $this->assertNull($dto->receiver);
        $this->assertNull($dto->price);
        $this->assertNull($dto->getCityId());
    }
}
