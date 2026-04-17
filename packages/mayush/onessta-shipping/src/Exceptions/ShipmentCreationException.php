<?php

namespace Mayush\Shipping\Onessta\Exceptions;

class ShipmentCreationException extends OnesstaException
{
    protected ?int $orderId;

    public function __construct(
        string $message = 'Failed to create shipment with ONESSTA.',
        ?int $orderId = null,
        ?\Throwable $previous = null
    ) {
        parent::__construct($message, 'SHIPMENT_CREATION_ERROR', 500, $previous);
        $this->orderId = $orderId;
    }

    public function getOrderId(): ?int
    {
        return $this->orderId;
    }
}
