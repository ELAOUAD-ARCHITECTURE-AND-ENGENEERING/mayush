<?php

namespace Mayush\Shipping\Onessta\DTOs;

class TrackingEventDto
{
    public function __construct(
        public readonly string $status,
        public readonly ?string $name = null,
        public readonly ?string $createdAt = null,
        public readonly ?string $newDate = null,
        public readonly ?array $raw = null
    ) {}

    public static function fromArray(array $data): self
    {
        return new self(
            status: $data['status'] ?? '',
            name: $data['name'] ?? null,
            createdAt: $data['created_at'] ?? null,
            newDate: $data['new_date'] ?? null,
            raw: $data
        );
    }
}
