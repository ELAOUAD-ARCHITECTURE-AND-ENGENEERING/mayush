<?php

namespace Mayush\Shipping\Onessta\DTOs;

use Illuminate\Support\Collection;

class TrackingResponseDto
{
    /**
     * @param Collection<int, TrackingEventDto>|array<int, TrackingEventDto>|null $history
     */
    public function __construct(
        public readonly string $code,
        public readonly string $status,
        public readonly ?string $situation = null,
        public readonly ?Collection $history = null,
        public readonly ?array $raw = null
    ) {}

    public static function fromArray(array $data): self
    {
        $history = null;
        if (isset($data['history']) && is_array($data['history'])) {
            $history = collect($data['history'])->map(
                fn(array $event) => TrackingEventDto::fromArray($event)
            );
        }

        return new self(
            code: $data['code'] ?? '',
            status: $data['status'] ?? '',
            situation: $data['situation'] ?? null,
            history: $history,
            raw: $data
        );
    }

    public function isSuccess(): bool
    {
        return !empty($this->code);
    }
}
