<?php

namespace Mayush\Shipping\Onessta\Contracts;

interface LabelInterface
{
    public function getLabel(string $shipmentCode): string;
}
