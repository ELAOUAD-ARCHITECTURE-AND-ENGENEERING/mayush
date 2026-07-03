<?php

namespace App\Services;

use App\Models\ProductStock;

class ProductSkuService
{
    private const PREFIX = 'SKU-';

    public function next(int $offset = 0): string
    {
        return $this->format($this->nextNumber() + max($offset, 0));
    }

    public function candidates(int $count): array
    {
        if ($count <= 0) {
            return [];
        }

        $nextNumber = $this->nextNumber();

        return collect(range(0, max($count - 1, 0)))
            ->map(fn ($offset) => $this->format($nextNumber + $offset))
            ->all();
    }

    public function available($submittedSku, int $offset = 0): string
    {
        $submittedSku = trim((string) $submittedSku);
        if ($submittedSku !== '' && (!$this->isGenerated($submittedSku) || !$this->exists($submittedSku))) {
            return $submittedSku;
        }

        $number = $this->nextNumber() + max($offset, 0);
        do {
            $candidate = $this->format($number);
            $number++;
        } while ($this->exists($candidate));

        return $candidate;
    }

    private function nextNumber(): int
    {
        $highest = ProductStock::query()
            ->where('sku', 'like', self::PREFIX . '%')
            ->pluck('sku')
            ->map(fn ($sku) => $this->number($sku))
            ->filter()
            ->max();

        return ((int) $highest) + 1;
    }

    private function number($sku): ?int
    {
        if (!preg_match('/^' . preg_quote(self::PREFIX, '/') . '(\d+)$/', trim((string) $sku), $matches)) {
            return null;
        }

        return (int) $matches[1];
    }

    private function isGenerated(string $sku): bool
    {
        return $this->number($sku) !== null;
    }

    private function exists(string $sku): bool
    {
        return ProductStock::where('sku', $sku)->exists();
    }

    private function format(int $number): string
    {
        return self::PREFIX . str_pad((string) max($number, 1), 6, '0', STR_PAD_LEFT);
    }
}
