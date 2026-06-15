<?php

namespace Mayush\Shipping\Onessta\Contracts;

use Mayush\Shipping\Onessta\DTOs\ShipmentRequestDto;
use Mayush\Shipping\Onessta\DTOs\ShipmentResponseDto;
use Mayush\Shipping\Onessta\DTOs\TrackingResponseDto;

interface CarrierInterface
{
    public function quote(array $payload): array;

    public function createShipment(ShipmentRequestDto $dto): ShipmentResponseDto;

    public function fetchShipmentByCode(string $code): ShipmentResponseDto;

    public function track(string $code): TrackingResponseDto;

    public function syncCities(): array;

    public function syncPickupCities(): array;

    public function supportsQuotes(): bool;

    public function supportsLabels(): bool;
}
