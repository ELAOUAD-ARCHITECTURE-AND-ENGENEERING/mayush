<?php

namespace Mayush\Shipping\Onessta\DTOs;

class ShipmentResponseDto
{
    public function __construct(
        public readonly string $code,
        public readonly string $status,
        public readonly ?string $situation = null,
        public readonly ?string $receiver = null,
        public readonly ?string $phone = null,
        public readonly ?string $address = null,
        public readonly ?float $price = null,
        public readonly ?array $city = null,
        public readonly ?string $createdAt = null,
        public readonly ?string $updatedAt = null,
        public readonly ?array $raw = null
    ) {}

    public static function fromArray(array $data): self
    {
        return new self(
            code: $data['code'] ?? '',
            status: $data['status'] ?? '',
            situation: $data['situation'] ?? null,
            receiver: $data['receiver'] ?? null,
            phone: $data['phone'] ?? null,
            address: $data['address'] ?? null,
            price: isset($data['price']) ? (float) $data['price'] : null,
            city: $data['city'] ?? null,
            createdAt: $data['created_at'] ?? null,
            updatedAt: $data['updated_at'] ?? null,
            raw: $data
        );
    }

    public function isSuccess(): bool
    {
        if (empty($this->code) || empty($this->status)) {
            return false;
        }

        $failureStatuses = ['FAILED', 'CANCELLED', 'ERROR', 'REJECTED'];
        if (in_array(strtoupper($this->status), $failureStatuses)) {
            return false;
        }

        return true;
    }

    public function getCityId(): ?int
    {
        return isset($this->city['id']) ? (int) $this->city['id'] : null;
    }
}
