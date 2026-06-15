<?php

namespace Mayush\Shipping\Onessta\DTOs;

class ShipmentRequestDto
{
    public function __construct(
        public readonly string $code,
        public readonly string $receiver,
        public readonly string $phone,
        public readonly float $price,
        public readonly int $city,
        public readonly string $address,
        public readonly ?string $sku = null,
        public readonly ?int $pickup_city = null,
        public readonly ?string $note = null,
        public readonly ?string $product_nature = null,
        public readonly bool $can_open = false,
        public readonly bool $replace = false
    ) {
        $this->validate();
    }

    private function validate(): void
    {
        if (empty($this->code)) {
            throw new \InvalidArgumentException('Shipment code is required');
        }
        if (empty($this->receiver)) {
            throw new \InvalidArgumentException('Receiver name is required');
        }
        if (empty($this->phone)) {
            throw new \InvalidArgumentException('Phone number is required');
        }
        if ($this->price < 0) {
            throw new \InvalidArgumentException('Price cannot be negative');
        }
        if ($this->city <= 0) {
            throw new \InvalidArgumentException('Valid city ID is required');
        }
        if (empty($this->address)) {
            throw new \InvalidArgumentException('Address is required');
        }
    }

    public function toArray(): array
    {
        $data = [
            'code' => $this->code,
            'receiver' => $this->receiver,
            'phone' => $this->phone,
            'price' => (string) $this->price,
            'city' => ['id' => $this->city],
            'address' => $this->address,
        ];

        if ($this->sku !== null) {
            $data['sku'] = $this->sku;
        }
        if ($this->pickup_city !== null) {
            $data['pickup_city'] = ['id' => $this->pickup_city];
        }
        if ($this->note !== null) {
            $data['note'] = $this->note;
        }
        if ($this->product_nature !== null) {
            $data['product_nature'] = $this->product_nature;
        }
        if ($this->can_open) {
            $data['can_open'] = true;
        } else {
            $data['can_open'] = false;
        }
        $data['replace'] = $this->replace;

        return $data;
    }

    public function toJson(): string
    {
        return json_encode($this->toArray());
    }
}
