<?php

namespace App\Services;

use Illuminate\Http\Client\RequestException;
use Illuminate\Support\Facades\Http;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Str;

class IndexNowService
{
    public function submitUrls(array $urls): array
    {
        $urls = $this->normalizeUrls($urls);

        if ($urls === []) {
            return [
                'submitted' => false,
                'reason' => 'No valid URLs were provided.',
                'url_count' => 0,
            ];
        }

        if (!$this->isConfigured()) {
            return [
                'submitted' => false,
                'reason' => 'IndexNow is disabled or INDEXNOW_KEY is missing.',
                'url_count' => count($urls),
            ];
        }

        $payload = [
            'host' => parse_url(config('app.url'), PHP_URL_HOST) ?: parse_url($urls[0], PHP_URL_HOST),
            'key' => config('seo.indexnow.key'),
            'urlList' => $urls,
        ];

        if ($keyLocation = config('seo.indexnow.key_location')) {
            $payload['keyLocation'] = $keyLocation;
        }

        try {
            $response = Http::timeout(10)
                ->acceptJson()
                ->post(config('seo.indexnow.endpoint'), $payload);

            return [
                'submitted' => $response->successful(),
                'status' => $response->status(),
                'reason' => $response->successful() ? null : Str::limit($response->body(), 300),
                'url_count' => count($urls),
            ];
        } catch (RequestException $exception) {
            Log::warning('IndexNow submission failed.', [
                'message' => $exception->getMessage(),
                'url_count' => count($urls),
            ]);

            return [
                'submitted' => false,
                'reason' => $exception->getMessage(),
                'url_count' => count($urls),
            ];
        } catch (\Throwable $exception) {
            Log::warning('IndexNow submission could not be completed.', [
                'message' => $exception->getMessage(),
                'url_count' => count($urls),
            ]);

            return [
                'submitted' => false,
                'reason' => $exception->getMessage(),
                'url_count' => count($urls),
            ];
        }
    }

    public function submitUrl(string $url): array
    {
        return $this->submitUrls([$url]);
    }

    public function isConfigured(): bool
    {
        return (bool) config('seo.indexnow.enabled')
            && filled(config('seo.indexnow.key'))
            && filled(config('seo.indexnow.endpoint'));
    }

    private function normalizeUrls(array $urls): array
    {
        return collect($urls)
            ->map(fn ($url) => $this->absoluteSubmissionUrl((string) $url))
            ->filter(fn ($url) => is_string($url) && Str::startsWith($url, ['http://', 'https://']))
            ->unique()
            ->values()
            ->all();
    }

    private function absoluteSubmissionUrl(string $url): ?string
    {
        $url = trim($url);

        if ($url === '') {
            return null;
        }

        if (Str::startsWith($url, ['http://', 'https://'])) {
            return $url;
        }

        if (Str::startsWith($url, '/')) {
            return rtrim(config('app.url'), '/') . $url;
        }

        return SeoService::absoluteUrl($url);
    }
}
