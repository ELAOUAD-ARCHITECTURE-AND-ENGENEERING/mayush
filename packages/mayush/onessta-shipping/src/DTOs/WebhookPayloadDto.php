<?php

namespace Mayush\Shipping\Onessta\DTOs;

class WebhookPayloadDto
{
    public function __construct(
        public readonly string $event,
        public readonly string $code,
        public readonly ?string $status = null,
        public readonly ?string $situation = null,
        public readonly ?string $timestamp = null,
        public readonly ?array $raw = null
    ) {}

    public static function fromArray(array $data): self
    {
        return new self(
            event: $data['event'] ?? '',
            code: $data['code'] ?? '',
            status: $data['status'] ?? null,
            situation: $data['situation'] ?? null,
            timestamp: $data['timestamp'] ?? null,
            raw: $data
        );
    }

    public static function fromRequest(string $payload): self
    {
        $data = json_decode($payload, true);
        if (json_last_error() !== JSON_ERROR_NONE) {
            throw new \InvalidArgumentException('Invalid JSON payload');
        }
        return self::fromArray($data);
    }

    public function isStatusUpdate(): bool
    {
        return $this->event === 'parcel.status_updated';
    }

    public function isSecondStatusUpdate(): bool
    {
        return $this->event === 'parcel.status_second_updated';
    }

    public function isInfoUpdate(): bool
    {
        return $this->event === 'parcel.info_updated';
    }
}
