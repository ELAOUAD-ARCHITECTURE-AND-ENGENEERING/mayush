<?php

namespace App\Services;

use Illuminate\Support\Facades\Log;

class SearchTelemetry
{
    public function __construct(private readonly SearchQueryNormalizer $normalizer)
    {
    }

    /**
     * Record privacy-safe search diagnostics. Telemetry must never make search
     * fail, so logging errors are intentionally swallowed.
     */
    public function record(string $event, array $context = []): void
    {
        if (!config('search.telemetry.enabled', false)) {
            return;
        }

        $sampleRate = min(1.0, max(0.0, (float) config('search.telemetry.sample_rate', 1.0)));
        if ($sampleRate < 1.0 && (mt_rand() / mt_getrandmax()) > $sampleRate) {
            return;
        }

        try {
            $query = array_key_exists('query', $context) ? (string) $context['query'] : null;
            $normalized = $query !== null ? $this->normalizer->normalize($query, $context['locale'] ?? null) : null;

            $payload = [
                'event' => $event,
                'query_hash' => $normalized['hash'] ?? null,
                'query_length' => $normalized ? mb_strlen($normalized['normalized'], 'UTF-8') : null,
                'query_token_count' => $normalized ? count($normalized['tokens']) : null,
                'locale' => $normalized['locale'] ?? ($context['locale'] ?? null),
                'mode' => $context['mode'] ?? 'standard',
                'backend' => $context['backend'] ?? config('search.backend', 'mysql'),
                'result_count' => isset($context['result_count']) ? (int) $context['result_count'] : null,
                'duration_ms' => isset($context['duration_ms']) ? (float) $context['duration_ms'] : null,
                'dataset_version' => config('search.telemetry.dataset_version', 'v1'),
            ];

            if (config('search.telemetry.store_raw_query', false)) {
                $payload['query'] = $normalized['original'] ?? $query;
            }

            if (isset($context['request_id'])) {
                $payload['request_id'] = (string) $context['request_id'];
            }

            Log::info('search.telemetry', $payload);
        } catch (\Throwable $e) {
            Log::warning('Search telemetry skipped', [
                'event' => $event,
                'message' => $e->getMessage(),
            ]);
        }
    }
}
