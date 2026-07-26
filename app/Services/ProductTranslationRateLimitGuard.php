<?php

namespace App\Services;

use Illuminate\Contracts\Cache\Lock;
use Illuminate\Support\Facades\Cache;

class ProductTranslationRateLimitGuard
{
    private const COOLDOWN_KEY = 'product-translation:openrouter-rate-limit-until';
    private const REQUEST_LOCK_KEY = 'product-translation:openrouter-request';

    public function retryAfter(): int
    {
        $until = (int) Cache::get(self::COOLDOWN_KEY, 0);
        $remaining = $until - now()->timestamp;

        if ($remaining <= 0) {
            if ($until > 0) {
                Cache::forget(self::COOLDOWN_KEY);
            }

            return 0;
        }

        return $remaining;
    }

    public function block(?int $seconds = null): int
    {
        $requested = (int) ($seconds ?? config('product_translation.quota_retry_delay', 60));
        $requested = max(60, min(3600, $requested));
        $remaining = max($requested, $this->retryAfter());
        $until = now()->addSeconds($remaining)->timestamp;

        Cache::put(self::COOLDOWN_KEY, $until, $remaining + 60);

        return $remaining;
    }

    public function requestLock(): Lock
    {
        $ttl = max(60, min(900, (int) config('product_translation.worker_timeout', 480) + 30));

        return Cache::lock(self::REQUEST_LOCK_KEY, $ttl);
    }
}
