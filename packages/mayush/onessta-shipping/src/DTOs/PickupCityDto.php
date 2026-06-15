<?php

namespace Mayush\Shipping\Onessta\DTOs;

class PickupCityDto
{
    public function __construct(
        public readonly int $id,
        public readonly string $name,
        public readonly ?array $raw = null
    ) {}

    public static function fromArray(array $data): self
    {
        return new self(
            id: (int) ($data['id'] ?? 0),
            name: $data['name'] ?? '',
            raw: $data
        );
    }

    public function toArray(): array
    {
        return [
            'id' => $this->id,
            'name' => $this->name,
            'raw' => $this->raw,
        ];
    }
}
