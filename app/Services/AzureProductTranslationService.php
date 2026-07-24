<?php

namespace App\Services;

use App\Contracts\ProductTranslationService;
use Illuminate\Http\Client\ConnectionException;
use Illuminate\Http\Client\RequestException;
use Illuminate\Support\Facades\Http;
use Illuminate\Support\Facades\Log;
use Throwable;

class AzureProductTranslationService implements ProductTranslationService
{
    private const MAX_BATCH_SIZE = 50;

    public function translateFields(array $fields, string $sourceLanguage = 'fr', string $targetLanguage = 'ar'): array
    {
        $result = [
            'success' => true,
            'fields' => $fields,
            'failed_fields' => [],
            'errors' => [],
        ];

        if ($sourceLanguage === $targetLanguage) {
            return $result;
        }

        $config = config('services.azure_translator', []);
        if (blank($config['key'] ?? null) || blank($config['endpoint'] ?? null) || blank($config['api_version'] ?? null)) {
            return $this->failAll($result, $fields, 'configuration');
        }

        $items = [];
        $this->flattenTranslatableFields($fields, [], $items);

        if ($items === []) {
            return $result;
        }

        $translatedCount = 0;
        foreach (array_chunk($items, self::MAX_BATCH_SIZE) as $batch) {
            $batchResult = $this->translateBatch($batch, $config, $sourceLanguage, $targetLanguage);

            foreach ($batchResult['translations'] as $index => $translation) {
                if (!array_key_exists($index, $batch) || $translation === null) {
                    continue;
                }

                $item = $batch[$index];
                $this->setNestedValue($result['fields'], $item['path'], $item['html'] ? $this->sanitizeTranslatedHtml($translation) : $translation);
                $translatedCount++;
            }

            foreach ($batchResult['failed'] as $index => $errorCode) {
                if (!array_key_exists($index, $batch)) {
                    continue;
                }

                $fieldKey = implode('.', $batch[$index]['path']);
                $result['failed_fields'][] = $fieldKey;
                $result['errors'][$fieldKey] = $errorCode;
            }

            if (isset($batchResult['error_code'])) {
                $result['error_code'] = $batchResult['error_code'];
            }
        }

        $result['failed_fields'] = array_values(array_unique($result['failed_fields']));
        $result['translated_count'] = $translatedCount;
        // A batch with some successful items is a usable partial success; failed
        // fields remain untouched and are reported separately to the caller.
        $result['success'] = $result['failed_fields'] === [] || $translatedCount > 0;

        $errorCodes = array_values($result['errors']);
        if (in_array('rate_limit', $errorCodes, true)) {
            $result['error_code'] = 'rate_limit';
        } elseif (in_array('credentials', $errorCodes, true)) {
            $result['error_code'] = 'credentials';
        } elseif (in_array('timeout', $errorCodes, true)) {
            $result['error_code'] = 'timeout';
        }

        return $result;
    }

    private function translateBatch(array $batch, array $config, string $sourceLanguage, string $targetLanguage): array
    {
        $translations = [];
        $failed = [];
        $html = collect($batch)->contains(fn (array $item) => $item['html'] === true);
        $query = http_build_query([
            'api-version' => $config['api_version'],
            'from' => $sourceLanguage,
            'to' => $targetLanguage,
        ]);

        if ($html) {
            $query .= '&textType=html';
        }

        $endpoint = rtrim($config['endpoint'], '/') . '/translate?' . $query;
        $headers = [
            'Ocp-Apim-Subscription-Key' => $config['key'],
            'Content-Type' => 'application/json',
        ];
        if (filled($config['region'] ?? null)) {
            $headers['Ocp-Apim-Subscription-Region'] = $config['region'];
        }

        try {
            $client = Http::withHeaders($headers)
                ->connectTimeout((int) ($config['connect_timeout'] ?? 5))
                ->timeout((int) ($config['timeout'] ?? 15))
                ->retry(2, 250, function (Throwable $exception) {
                    return $exception instanceof ConnectionException
                        || ($exception instanceof RequestException && in_array($exception->response?->status(), [408, 429, 500, 502, 503, 504], true));
                });
            $body = array_map(fn (array $item) => ['Text' => $item['value']], $batch);
            $response = null;
            for ($attempt = 0; $attempt < 3; $attempt++) {
                $response = $client->post($endpoint, $body);
                if (!in_array($response->status(), [408, 429, 500, 502, 503, 504], true) || $attempt === 2) {
                    break;
                }
                usleep(250000);
            }
        } catch (ConnectionException) {
            foreach ($batch as $index => $_item) {
                $failed[$index] = 'timeout';
            }
            Log::warning('Azure product translation timed out', ['batch_size' => count($batch)]);
            return ['translations' => $translations, 'failed' => $failed, 'error_code' => 'timeout'];
        } catch (RequestException $exception) {
            $status = $exception->response?->status();
            $errorCode = $status === 429 ? 'rate_limit' : (($status === 401 || $status === 403) ? 'credentials' : 'request_failed');
            foreach ($batch as $index => $_item) {
                $failed[$index] = $errorCode;
            }
            Log::warning('Azure product translation request was rejected', ['status' => $status]);
            return ['translations' => $translations, 'failed' => $failed, 'error_code' => $errorCode];
        } catch (Throwable $exception) {
            foreach ($batch as $index => $_item) {
                $failed[$index] = 'request_failed';
            }
            Log::warning('Azure product translation request failed', [
                'exception' => get_class($exception),
            ]);
            return ['translations' => $translations, 'failed' => $failed, 'error_code' => 'request_failed'];
        }

        if ($response->status() === 401 || $response->status() === 403) {
            foreach ($batch as $index => $_item) {
                $failed[$index] = 'credentials';
            }
            Log::warning('Azure product translation credentials were rejected', ['status' => $response->status()]);
            return ['translations' => $translations, 'failed' => $failed, 'error_code' => 'credentials'];
        }

        if ($response->status() === 429) {
            foreach ($batch as $index => $_item) {
                $failed[$index] = 'rate_limit';
            }
            Log::warning('Azure product translation was rate limited');
            return ['translations' => $translations, 'failed' => $failed, 'error_code' => 'rate_limit'];
        }

        if ($response->failed()) {
            foreach ($batch as $index => $_item) {
                $failed[$index] = 'request_failed';
            }
            Log::warning('Azure product translation returned an error', ['status' => $response->status()]);
            return ['translations' => $translations, 'failed' => $failed, 'error_code' => 'request_failed'];
        }

        $payload = $response->json();
        if (!is_array($payload) || count($payload) !== count($batch)) {
            foreach ($batch as $index => $_item) {
                $failed[$index] = 'incomplete_response';
            }
            Log::warning('Azure product translation returned an incomplete response', [
                'expected' => count($batch),
                'received' => is_array($payload) ? count($payload) : 0,
            ]);
            return ['translations' => $translations, 'failed' => $failed, 'error_code' => 'incomplete_response'];
        }

        foreach ($batch as $index => $_item) {
            $text = data_get($payload, $index . '.translations.0.text');
            if (!is_string($text)) {
                $failed[$index] = 'incomplete_response';
                continue;
            }

            $translations[$index] = $text;
        }

        return compact('translations', 'failed');
    }

    private function flattenTranslatableFields(array $fields, array $prefix, array &$items): void
    {
        foreach ($fields as $key => $value) {
            $path = [...$prefix, $key];

            if (is_array($value)) {
                $this->flattenTranslatableFields($value, $path, $items);
                continue;
            }

            if (!is_string($value) || trim($value) === '' || $this->isTechnicalTranslationPath($path) || !$this->isTranslatableValue((string) $key, $value)) {
                continue;
            }

            $items[] = [
                'path' => $path,
                'value' => $value,
                'html' => $this->containsHtml($value),
            ];
        }
    }

    private function isTechnicalTranslationPath(array $path): bool
    {
        return collect($path)->contains(function ($segment) {
            return preg_match('/^choice_options_35$/i', (string) $segment) === 1;
        });
    }

    private function isTranslatableValue(string $key, string $value): bool
    {
        $normalizedKey = strtolower(trim($key, "[] \t\n\r\0\x0B"));

        // Product identifiers, numbers, URLs, files, and technical values must never reach Azure.
        if (preg_match('/(^|_)(id|price|discount|stock|qty|quantity|sku|barcode|url|uri|slug|img|image|photo|thumbnail|pdf|color|width|height|length|weight|tax|token|method|provider|date|published|featured|deal|cash|refundable)(_|$)/', $normalizedKey)) {
            return false;
        }
        if (in_array($normalizedKey, ['category', 'brand', 'shipping', 'type'], true)) {
            return false;
        }
        if (filter_var(trim($value), FILTER_VALIDATE_URL) || preg_match('/^#[0-9a-f]{3,8}$/i', trim($value))) {
            return false;
        }
        if (preg_match('~^[\s\d.,+%x×:/()\-]+$~u', trim($value))) {
            return false;
        }

        return preg_match('/[\p{L}]/u', $value) === 1;
    }

    private function containsHtml(string $value): bool
    {
        return preg_match('/<\/?[a-z][^>]*>/i', $value) === 1;
    }

    private function sanitizeTranslatedHtml(string $value): string
    {
        $value = preg_replace('/<\/?(?:script|style|iframe|object|embed|form)(?:\s[^>]*)?>.*?<\/?(?:script|style|iframe|object|embed|form)\s*>/is', '', $value) ?? '';
        $value = preg_replace('/\s+on[a-z]+\s*=\s*(?:"[^"]*"|\'[^\']*\'|[^\s>]+)/i', '', $value) ?? $value;
        $value = preg_replace_callback('/\s+(href|src)\s*=\s*("[^"]*"|\'[^\']*\'|[^\s>]+)/i', function (array $match) {
            $attribute = trim($match[2], "\"'");
            return preg_match('/^(?:javascript|data):/i', $attribute) ? '' : $match[0];
        }, $value) ?? $value;

        return $value;
    }

    private function setNestedValue(array &$fields, array $path, string $value): void
    {
        $cursor =& $fields;
        $last = array_pop($path);
        foreach ($path as $segment) {
            if (!isset($cursor[$segment]) || !is_array($cursor[$segment])) {
                $cursor[$segment] = [];
            }
            $cursor =& $cursor[$segment];
        }
        $cursor[$last] = $value;
    }

    private function failAll(array $result, array $fields, string $errorCode): array
    {
        $items = [];
        $this->flattenFieldPaths($fields, [], $items);
        $result['success'] = false;
        $result['failed_fields'] = array_values(array_unique($items));
        $result['errors'] = array_fill_keys($result['failed_fields'], $errorCode);
        $result['error_code'] = $errorCode;
        return $result;
    }

    private function flattenFieldPaths(array $fields, array $prefix, array &$paths): void
    {
        foreach ($fields as $key => $value) {
            $path = [...$prefix, $key];
            if (is_array($value)) {
                $this->flattenFieldPaths($value, $path, $paths);
            } elseif (is_string($value) && trim($value) !== '') {
                $paths[] = implode('.', $path);
            }
        }
    }
}
