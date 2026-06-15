<?php

namespace Mayush\Shipping\Onessta\Services;

use Mayush\Shipping\Onessta\Exceptions\UnsupportedCapabilityException;

class LabelService
{
    public function getLabel(string $shipmentCode): string
    {
        throw new UnsupportedCapabilityException('labels');
    }

    public function supportsLabels(): bool
    {
        return config('onessta.capabilities.labels', false);
    }
}
