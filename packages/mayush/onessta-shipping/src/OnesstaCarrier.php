<?php

namespace Mayush\Shipping\Onessta;

use Mayush\Shipping\Onessta\Contracts\CarrierInterface;
use Mayush\Shipping\Onessta\DTOs\ShipmentRequestDto;
use Mayush\Shipping\Onessta\DTOs\ShipmentResponseDto;
use Mayush\Shipping\Onessta\DTOs\TrackingResponseDto;
use Mayush\Shipping\Onessta\Services\LabelService;
use Mayush\Shipping\Onessta\Services\QuoteService;
use Mayush\Shipping\Onessta\Services\ReferenceDataService;
use Mayush\Shipping\Onessta\Services\ShipmentService;
use Mayush\Shipping\Onessta\Services\TrackingService;

/**
 * OnesstaCarrier
 *
 * Root adapter that satisfies the CarrierInterface contract by delegating
 * to the individual focused services. This is the single binding point for
 * the IoC container's CarrierInterface alias.
 *
 * Services:
 *  - ShipmentService  → createShipment, fetchShipmentByCode
 *  - TrackingService  → track
 *  - ReferenceDataService → syncCities, syncPickupCities
 *  - QuoteService     → quote, supportsQuotes (unsupported by ONESSTA)
 *  - LabelService     → supportsLabels (unsupported by ONESSTA)
 */
class OnesstaCarrier implements CarrierInterface
{
    public function __construct(
        private readonly ShipmentService      $shipmentService,
        private readonly TrackingService      $trackingService,
        private readonly ReferenceDataService $referenceDataService,
        private readonly QuoteService         $quoteService,
        private readonly LabelService         $labelService,
    ) {}

    /**
     * {@inheritDoc}
     *
     * ONESSTA does not expose a public quoting endpoint.
     * Throws UnsupportedCapabilityException.
     */
    public function quote(array $payload): array
    {
        return $this->quoteService->getQuote($payload);
    }

    /**
     * {@inheritDoc}
     */
    public function createShipment(ShipmentRequestDto $dto): ShipmentResponseDto
    {
        $shipment = $this->shipmentService->createShipment($dto);

        return ShipmentResponseDto::fromArray(
            $shipment->raw_response ?? ['code' => $shipment->code, 'status' => $shipment->status]
        );
    }

    /**
     * {@inheritDoc}
     */
    public function fetchShipmentByCode(string $code): ShipmentResponseDto
    {
        $shipment = $this->shipmentService->fetchByCode($code);

        return ShipmentResponseDto::fromArray(
            $shipment->raw_response ?? ['code' => $shipment->code, 'status' => $shipment->status]
        );
    }

    /**
     * {@inheritDoc}
     */
    public function track(string $code): TrackingResponseDto
    {
        return $this->trackingService->track($code);
    }

    /**
     * {@inheritDoc}
     */
    public function syncCities(): array
    {
        return $this->referenceDataService->syncCities();
    }

    /**
     * {@inheritDoc}
     */
    public function syncPickupCities(): array
    {
        return $this->referenceDataService->syncPickupCities();
    }

    /**
     * {@inheritDoc}
     */
    public function supportsQuotes(): bool
    {
        return $this->quoteService->supportsQuotes();
    }

    /**
     * {@inheritDoc}
     */
    public function supportsLabels(): bool
    {
        return $this->labelService->supportsLabels();
    }
}
