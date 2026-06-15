<?php

namespace Mayush\Shipping\Onessta\Tests\Unit\DTOs;

use Mayush\Shipping\Onessta\DTOs\ShipmentRequestDto;
use Mayush\Shipping\Onessta\Tests\Unit\UnitTestCase;

class ShipmentRequestDtoTest extends UnitTestCase
{
    public function test_builds_correct_json_structure(): void
    {
        $dto = new ShipmentRequestDto(
            code: 'ORD-12345',
            receiver: 'John Doe',
            phone: '+1234567890',
            price: 99.99,
            city: 1,
            address: '123 Main St',
            sku: 'SKU001;SKU002',
            pickup_city: 5,
            note: 'Order #12345',
            product_nature: 'general',
            can_open: true,
            replace: false
        );

        $array = $dto->toArray();

        $this->assertEquals('ORD-12345', $array['code']);
        $this->assertEquals('John Doe', $array['receiver']);
        $this->assertEquals('+1234567890', $array['phone']);
        $this->assertEquals(99.99, $array['price']);
        $this->assertEquals(['id' => 1], $array['city']);
        $this->assertEquals('123 Main St', $array['address']);
        $this->assertEquals('SKU001;SKU002', $array['sku']);
        $this->assertEquals(['id' => 5], $array['pickup_city']);
        $this->assertEquals('Order #12345', $array['note']);
        $this->assertEquals('general', $array['product_nature']);
        $this->assertTrue($array['can_open']);
        $this->assertFalse($array['replace']);
    }

    public function test_validates_required_code(): void
    {
        $this->expectException(\InvalidArgumentException::class);
        new ShipmentRequestDto(
            code: '',
            receiver: 'John',
            phone: '123',
            price: 10.0,
            city: 1,
            address: 'Street'
        );
    }

    public function test_validates_required_receiver(): void
    {
        $this->expectException(\InvalidArgumentException::class);
        new ShipmentRequestDto(
            code: 'ORD-1',
            receiver: '',
            phone: '123',
            price: 10.0,
            city: 1,
            address: 'Street'
        );
    }

    public function test_validates_required_phone(): void
    {
        $this->expectException(\InvalidArgumentException::class);
        new ShipmentRequestDto(
            code: 'ORD-1',
            receiver: 'John',
            phone: '',
            price: 10.0,
            city: 1,
            address: 'Street'
        );
    }

    public function test_validates_price_not_negative(): void
    {
        $this->expectException(\InvalidArgumentException::class);
        new ShipmentRequestDto(
            code: 'ORD-1',
            receiver: 'John',
            phone: '123',
            price: -5.0,
            city: 1,
            address: 'Street'
        );
    }

    public function test_validates_city_id_positive(): void
    {
        $this->expectException(\InvalidArgumentException::class);
        new ShipmentRequestDto(
            code: 'ORD-1',
            receiver: 'John',
            phone: '123',
            price: 10.0,
            city: 0,
            address: 'Street'
        );
    }

    public function test_validates_required_address(): void
    {
        $this->expectException(\InvalidArgumentException::class);
        new ShipmentRequestDto(
            code: 'ORD-1',
            receiver: 'John',
            phone: '123',
            price: 10.0,
            city: 1,
            address: ''
        );
    }

    public function test_optional_fields_omitted_when_null(): void
    {
        $dto = new ShipmentRequestDto(
            code: 'ORD-1',
            receiver: 'John',
            phone: '123',
            price: 10.0,
            city: 1,
            address: 'Street'
        );

        $array = $dto->toArray();

        $this->assertArrayNotHasKey('sku', $array);
        $this->assertArrayNotHasKey('pickup_city', $array);
        $this->assertArrayNotHasKey('note', $array);
        $this->assertArrayHasKey('can_open', $array);
        $this->assertArrayHasKey('replace', $array);
    }

    public function test_to_json_produces_valid_json(): void
    {
        $dto = new ShipmentRequestDto(
            code: 'ORD-1',
            receiver: 'John',
            phone: '123',
            price: 10.0,
            city: 1,
            address: 'Street'
        );

        $json = $dto->toJson();
        $decoded = json_decode($json, true);

        $this->assertEquals('ORD-1', $decoded['code']);
        $this->assertEquals('John', $decoded['receiver']);
    }
}
