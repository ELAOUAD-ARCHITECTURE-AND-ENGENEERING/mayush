<?php

namespace Mayush\Shipping\Onessta\Exceptions;

class CityMappingException extends OnesstaException
{
    public function __construct(string $message = 'City mapping failed. Ensure cities are synced before creating shipments.', ?\Throwable $previous = null)
    {
        parent::__construct($message, 'CITY_MAPPING_ERROR', 422, $previous);
    }
}
