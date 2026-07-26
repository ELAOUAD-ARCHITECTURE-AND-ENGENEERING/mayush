<?php

namespace App\Services;

use App\Contracts\ProductTranslationService;
use Illuminate\Http\Client\ConnectionException;
use Illuminate\Support\Facades\Http;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Str;
use JsonException;
use Throwable;

class OpenRouterProductTranslationService implements ProductTranslationService
{
    private const RETRYABLE_STATUSES = [408, 429, 500, 502, 503, 504];

    public function __construct(private readonly OpenRouterProductTranslationPrompt $prompt)
    {
    }

    public function translateFields(array $fields, string $sourceLanguage = 'fr', string $targetLanguage = 'ar'): array
    {
        $result = [
            'success' => true,
            'fields' => $fields,
            'failed_fields' => [],
            'errors' => [],
            'translated_count' => 0,
        ];

        if ($sourceLanguage === $targetLanguage) {
            return $result;
        }

        $config = config('services.openrouter', []);
        if (blank($config['key'] ?? null) || blank($config['model'] ?? null) || blank($config['api_base'] ?? null)) {
            return $this->failAll($result, $fields, 'configuration');
        }

        $protected = [];
        $translatablePaths = [];
        $requestFields = $this->protectFields($fields, [], $protected, $translatablePaths);
        if ($translatablePaths === []) {
            return $result;
        }

        try {
            $structuredRequest = ['fields' => $requestFields];
            $requestJson = json_encode($structuredRequest, JSON_UNESCAPED_SLASHES | JSON_UNESCAPED_UNICODE | JSON_THROW_ON_ERROR);
            $schema = [
                'type' => 'object',
                'properties' => ['fields' => $this->schemaFor($requestFields)],
                'required' => ['fields'],
                'additionalProperties' => false,
            ];
        } catch (JsonException) {
            return $this->failAll($result, $fields, 'request_failed');
        }

        $maxPayload = (int) config('services.openrouter.translation_max_payload', 100000);
        if ($maxPayload > 0 && strlen($requestJson) > $maxPayload) {
            return $this->failAll($result, $fields, 'payload_too_large');
        }

        $operationId = (string) Str::uuid();
        $startedAt = microtime(true);
        $response = $this->request($requestFields, $schema, $sourceLanguage, $targetLanguage, $config, $operationId, strlen($requestJson));
        $durationMs = (int) round((microtime(true) - $startedAt) * 1000);
        $result['provider'] = 'openrouter';
        $result['requested_model'] = (string) $config['model'];
        $result['actual_model'] = $response['actual_model'] ?? null;
        $result['operation_id'] = $operationId;
        $result['attempt'] = (int) ($response['attempt'] ?? 1);
        $result['request_duration_ms'] = $durationMs;
        $result['input_characters'] = mb_strlen($requestJson);
        $result['retry_after'] = $response['retry_after'] ?? null;
        $result['usage'] = $response['usage'] ?? [];

        if (!$response['success']) {
            Log::warning('OpenRouter product translation failed', [
                'operation_id' => $operationId,
                'requested_model' => $config['model'],
                'actual_model' => $response['actual_model'] ?? null,
                'attempt' => $response['attempt'],
                'duration_ms' => $durationMs,
                'input_characters' => mb_strlen($requestJson),
                'error_code' => $response['error_code'],
            ]);

            return $this->failAll($result, $fields, $response['error_code'], $response['retry_after'] ?? null);
        }

        $decodedContainer = $this->decodeResponse($response['payload']);
        if (!is_array($decodedContainer) || !$this->sameStructure(['fields' => $requestFields], $decodedContainer)) {
            Log::warning('OpenRouter product translation returned an invalid structure', [
                'operation_id' => $operationId,
                'requested_model' => $config['model'],
                'actual_model' => $response['actual_model'] ?? null,
                'attempt' => $response['attempt'],
                'duration_ms' => $durationMs,
                'input_characters' => mb_strlen($requestJson),
                'error_code' => 'incomplete_response',
            ]);

            return $this->failAll($result, $fields, 'incomplete_response');
        }

        $decoded = $decodedContainer['fields'];

        [$decoded, $protectedOk] = $this->restoreProtectedFields($decoded, $protected);
        if (!$protectedOk || !$this->validateTranslatedHtml($fields, $decoded, $translatablePaths)) {
            Log::warning('OpenRouter product translation failed response validation', [
                'operation_id' => $operationId,
                'requested_model' => $config['model'],
                'actual_model' => $response['actual_model'] ?? null,
                'attempt' => $response['attempt'],
                'duration_ms' => $durationMs,
                'input_characters' => mb_strlen($requestJson),
                'error_code' => 'malformed_response',
            ]);

            return $this->failAll($result, $fields, 'malformed_response');
        }

        $result['fields'] = $decoded;
        $result['translated_count'] = count($translatablePaths);

        Log::info('OpenRouter product translation completed', [
            'operation_id' => $operationId,
            'requested_model' => $config['model'],
            'actual_model' => $response['actual_model'] ?? null,
            'attempt' => $response['attempt'],
            'duration_ms' => $durationMs,
            'input_characters' => mb_strlen($requestJson),
            'translated_fields' => count($translatablePaths),
        ]);

        return $result;
    }

    private function request(array $fields, array $schema, string $sourceLanguage, string $targetLanguage, array $config, string $operationId, int $inputCharacters): array
    {
        $endpoint = rtrim((string) $config['api_base'], '/').'/chat/completions';
        $body = [
            'model' => (string) $config['model'],
            'temperature' => (float) ($config['temperature'] ?? 0.1),
            'stream' => false,
            'messages' => [[
                'role' => 'system',
                'content' => $this->prompt->systemInstruction(),
            ], [
                'role' => 'user',
                'content' => $this->prompt->build(['fields' => $fields], $sourceLanguage, $targetLanguage),
            ]],
        ];
        $body['response_format'] = $this->strictResponseFormat($schema);
        $body['provider'] = ['require_parameters' => true];

        $maxRetries = max(0, (int) ($config['max_retries'] ?? 3));
        $timeout = max(1, (int) ($config['timeout'] ?? 90));
        $attempt = 0;
        $lastError = 'request_failed';
        $retryAfter = null;
        $structuredMode = 'json_schema';

        while ($attempt <= $maxRetries) {
            $attempt++;
            try {
                $headers = [
                    'Content-Type' => 'application/json',
                    'Authorization' => 'Bearer '.(string) $config['key'],
                ];
                if (filled($config['site_url'] ?? null)) {
                    $headers['HTTP-Referer'] = (string) $config['site_url'];
                }
                if (filled($config['app_name'] ?? null)) {
                    $headers['X-OpenRouter-Title'] = (string) $config['app_name'];
                }
                $response = Http::withHeaders($headers)
                    ->timeout($timeout)
                    ->post($endpoint, $body);
            } catch (ConnectionException) {
                $lastError = 'timeout';
                if ($attempt <= $maxRetries) {
                    $this->backoff($attempt, null);
                    continue;
                }

                return [
                    'success' => false,
                    'attempt' => $attempt,
                    'error_code' => $lastError,
                    'retry_after' => null,
                ];
            } catch (Throwable) {
                $lastError = 'request_failed';
                if ($attempt <= $maxRetries) {
                    $this->backoff($attempt, null);
                    continue;
                }

                return [
                    'success' => false,
                    'attempt' => $attempt,
                    'error_code' => $lastError,
                    'retry_after' => null,
                ];
            }

            $status = $response->status();
            if ($status === 400 && $structuredMode === 'json_schema' && $this->supportsJsonObjectFallback($response)) {
                $structuredMode = 'json_object';
                $body['response_format'] = ['type' => 'json_object'];
                unset($body['provider']);
                $attempt--;
                continue;
            }
            if ($status === 400 && $structuredMode === 'json_object' && $this->supportsJsonObjectFallback($response)) {
                return [
                    'success' => false,
                    'attempt' => $attempt,
                    'error_code' => 'structured_output_unsupported',
                    'retry_after' => null,
                ];
            }

            // OpenRouter may surface provider errors inside a 200 response
            // envelope, including choices[0].error for non-streaming chat
            // completions. Normalize them exactly like HTTP failures so a
            // rate-limit or temporary provider outage cannot look like an
            // empty translation response.
            if (is_array($response->json('error')) || is_array($response->json('choices.0.error'))) {
                $lastError = $this->errorCode($response);
                $retryAfter = $this->retryAfter($response);
                if (in_array($lastError, ['rate_limit', 'temporary_failure', 'timeout'], true) && $attempt <= $maxRetries) {
                    $this->backoff($attempt, $retryAfter);
                    continue;
                }

                return [
                    'success' => false,
                    'attempt' => $attempt,
                    'error_code' => $lastError,
                    'retry_after' => $retryAfter,
                ];
            }

            if (in_array($status, self::RETRYABLE_STATUSES, true)) {
                $lastError = $this->errorCode($response);
                $retryAfter = $this->retryAfter($response);
                if ($attempt <= $maxRetries) {
                    $this->backoff($attempt, $retryAfter);
                    continue;
                }

                return [
                    'success' => false,
                    'attempt' => $attempt,
                    'error_code' => $lastError,
                    'retry_after' => $retryAfter,
                ];
            }

            if ($status === 401 || $status === 403) {
                return [
                    'success' => false,
                    'attempt' => $attempt,
                    'error_code' => $this->errorCode($response),
                    'retry_after' => null,
                ];
            }

            if ($status === 404) {
                return [
                    'success' => false,
                    'attempt' => $attempt,
                    'error_code' => 'invalid_model',
                    'retry_after' => null,
                ];
            }

            if ($status === 413) {
                return [
                    'success' => false,
                    'attempt' => $attempt,
                    'error_code' => 'payload_too_large',
                    'retry_after' => null,
                ];
            }

            if ($response->failed()) {
                return [
                    'success' => false,
                    'attempt' => $attempt,
                    'error_code' => $this->errorCode($response),
                    'retry_after' => null,
                ];
            }

            $finishReason = strtoupper((string) $response->json('choices.0.finish_reason', ''));
            if (in_array($finishReason, ['SAFETY', 'BLOCKLIST', 'PROHIBITED_CONTENT', 'CONTENT_FILTER'], true)) {
                return [
                    'success' => false,
                    'attempt' => $attempt,
                    'error_code' => 'safety_blocked',
                    'retry_after' => null,
                ];
            }
            if (in_array($finishReason, ['LENGTH', 'ERROR'], true)) {
                return [
                    'success' => false,
                    'attempt' => $attempt,
                    'error_code' => $finishReason === 'LENGTH' ? 'incomplete_response' : 'temporary_failure',
                    'retry_after' => null,
                ];
            }

            if (!filled($response->json('choices.0.message.content'))) {
                return [
                    'success' => false,
                    'attempt' => $attempt,
                    'error_code' => 'empty_response',
                    'retry_after' => null,
                ];
            }

            return [
                'success' => true,
                'attempt' => $attempt,
                'payload' => $response->json(),
                'retry_after' => null,
                'actual_model' => $response->json('model'),
                'usage' => $response->json('usage', []),
            ];
        }

        return [
            'success' => false,
            'attempt' => $attempt,
            'error_code' => $lastError,
            'retry_after' => $retryAfter,
        ];
    }

    private function errorCode($response): string
    {
        $status = $response->status();
        $error = $response->json('error');
        if (!is_array($error)) {
            $error = $response->json('choices.0.error', []);
        }
        $message = strtolower((string) ($error['message'] ?? ''));
        $providerStatus = strtoupper((string) ($error['code'] ?? ''));
        $errorType = strtolower((string) data_get($error, 'metadata.error_type', ''));
        $normalizedStatus = is_numeric($providerStatus) ? (int) $providerStatus : $status;

        if ($normalizedStatus === 429 || in_array($errorType, ['rate_limit', 'rate_limit_exceeded', 'rate-limited', 'quota'], true) || str_contains($message, 'rate limit') || str_contains($message, 'quota')) {
            return 'rate_limit';
        }
        if ($normalizedStatus === 402 || in_array($errorType, ['insufficient_quota', 'insufficient_credits', 'payment_required'], true) || str_contains($message, 'credit') || str_contains($message, 'payment')) {
            return 'account_credit';
        }
        if ($normalizedStatus === 408 || $normalizedStatus === 504 || in_array($errorType, ['timeout', 'timed_out'], true) || str_contains($message, 'timeout') || str_contains($message, 'timed out')) {
            return 'timeout';
        }
        if ($normalizedStatus === 413 || in_array($errorType, ['context_length_exceeded', 'payload_too_large', 'max_tokens_exceeded', 'token_limit_exceeded', 'string_too_long'], true) || str_contains($message, 'too large') || str_contains($message, 'token limit') || str_contains($message, 'context length')) {
            return 'payload_too_large';
        }
        if ($normalizedStatus === 404 || in_array($errorType, ['model_not_found', 'not_found'], true) || str_contains($message, 'model')) {
            return 'invalid_model';
        }
        if (in_array($errorType, ['moderation', 'content_policy', 'content_policy_violation', 'guardrail', 'safety', 'refusal'], true) || str_contains($message, 'moderation') || str_contains($message, 'content policy') || str_contains($message, 'guardrail') || str_contains($message, 'safety')) {
            return 'safety_blocked';
        }
        if (in_array($normalizedStatus, [401, 403], true) || in_array($errorType, ['authentication', 'permission_denied'], true) || str_contains($message, 'api key') || str_contains($message, 'unauthorized') || str_contains($message, 'permission')) {
            return 'credentials';
        }
        if (in_array($normalizedStatus, [500, 502, 503], true) || in_array($errorType, ['provider_error', 'provider_overloaded', 'provider_unavailable', 'temporary_failure', 'upstream_error', 'server'], true) || str_contains($message, 'unavailable') || str_contains($message, 'overloaded')) {
            return 'temporary_failure';
        }
        if (in_array($errorType, ['invalid_request', 'invalid_prompt', 'unprocessable'], true)) {
            return 'request_failed';
        }

        return 'request_failed';
    }

    private function retryAfter($response): ?int
    {
        $header = $response->header('Retry-After');
        if (is_numeric($header)) {
            return max(1, min(3600, (int) $header));
        }

        foreach ((array) $response->json('error.details', []) as $detail) {
            if (!is_array($detail) || !isset($detail['retryDelay'])) {
                continue;
            }
            if (preg_match('/^(\d+(?:\.\d+)?)s$/', (string) $detail['retryDelay'], $matches)) {
                return max(1, min(3600, (int) ceil((float) $matches[1])));
            }
        }

        return (int) config('services.openrouter.retry_after', 60);
    }

    private function backoff(int $attempt, ?int $retryAfter): void
    {
        if (app()->environment('testing')) {
            return;
        }

        $seconds = $retryAfter ?: min(30, (2 ** max(0, $attempt - 1)) + (random_int(0, 1000) / 1000));
        usleep((int) round($seconds * 1000000));
    }

    private function decodeResponse(array $payload): ?array
    {
        $content = data_get($payload, 'choices.0.message.content');
        if (is_array($content)) {
            $text = collect($content)->map(fn ($part) => is_array($part) ? ($part['text'] ?? '') : (string) $part)->implode('');
        } else {
            $text = trim((string) $content);
        }
        $text = preg_replace('/^```(?:json)?\s*|\s*```$/i', '', $text) ?? $text;

        try {
            $decoded = json_decode(trim($text), true, 512, JSON_THROW_ON_ERROR);
        } catch (JsonException) {
            return null;
        }

        return is_array($decoded) ? $decoded : null;
    }

    private function protectFields(mixed $value, array $path, array &$protected, array &$translatablePaths): mixed
    {
        if (is_array($value)) {
            $result = [];
            foreach ($value as $key => $child) {
                $result[$key] = $this->protectFields($child, [...$path, $key], $protected, $translatablePaths);
            }
            return $result;
        }

        $pathKey = $this->pathKey($path);
        $stringValue = is_string($value) ? $value : null;
        $eligible = $stringValue !== null
            && trim($stringValue) !== ''
            && $this->isTranslatableValue((string) end($path), $stringValue)
            && !$this->isTechnicalTranslationPath($path);

        if (!$eligible) {
            $token = $this->token('VALUE', count($protected));
            $protected[$pathKey] = ['token' => $token, 'value' => $value, 'html' => false];
            return $token;
        }

        $translatablePaths[] = $pathKey;
        if ($this->containsHtml($stringValue)) {
            $translated = preg_replace_callback('/<!--.*?-->|<[^>]+>/s', function (array $match) use (&$protected, $pathKey) {
                $token = $this->token('HTML', count($protected));
                $protected[$pathKey.'#'.$token] = ['token' => $token, 'value' => $match[0], 'html' => true];
                return $token;
            }, $stringValue) ?? $stringValue;
            return $translated;
        }

        return $stringValue;
    }

    private function restoreProtectedFields(array $decoded, array $protected): array
    {
        foreach ($protected as $pathKey => $record) {
            $path = str_contains($pathKey, '#') ? explode('#', $pathKey, 2)[0] : $pathKey;
            $current = $this->getNestedValue($decoded, $this->parsePath($path));
            if (!is_string($current)) {
                return [$decoded, false];
            }

            if ($record['html']) {
                if (!str_contains($current, $record['token'])) {
                    return [$decoded, false];
                }
                $current = str_replace($record['token'], $record['value'], $current);
            } elseif ($current !== $record['token']) {
                return [$decoded, false];
            } else {
                $current = $record['value'];
            }
            $this->setNestedValue($decoded, $this->parsePath($path), $current);
        }

        return [$decoded, true];
    }

    private function validateTranslatedHtml(array $original, array $translated, array $paths): bool
    {
        foreach ($paths as $path) {
            $source = $this->getNestedValue($original, $this->parsePath($path));
            $target = $this->getNestedValue($translated, $this->parsePath($path));
            if (!is_string($source) || !is_string($target) || !$this->containsHtml($source)) {
                continue;
            }

            if ($this->htmlTags($source) !== $this->htmlTags($target)) {
                return false;
            }
            if (preg_match('/<\/?(?:script|style|iframe|object|embed|form)(?:\s|>)/i', $target)) {
                return false;
            }
        }

        return true;
    }

    private function sameStructure(mixed $expected, mixed $actual): bool
    {
        if (is_array($expected)) {
            if (!is_array($actual) || array_is_list($expected) !== array_is_list($actual) || count($expected) !== count($actual)) {
                return false;
            }

            if (array_is_list($expected)) {
                foreach ($expected as $index => $value) {
                    if (!$this->sameStructure($value, $actual[$index] ?? null)) {
                        return false;
                    }
                }
                return true;
            }

            if (array_diff_key($expected, $actual) !== [] || array_diff_key($actual, $expected) !== []) {
                return false;
            }
            foreach ($expected as $key => $value) {
                if (!$this->sameStructure($value, $actual[$key])) {
                    return false;
                }
            }
            return true;
        }

        return is_string($actual);
    }

    private function schemaFor(mixed $value): array
    {
        if (is_array($value)) {
            if (array_is_list($value)) {
                return [
                    'type' => 'array',
                    'items' => $this->schemaFor($value[0] ?? ''),
                ];
            }

            $properties = [];
            foreach ($value as $key => $child) {
                $properties[(string) $key] = $this->schemaFor($child);
            }
            return [
                'type' => 'object',
                'properties' => $properties,
                'required' => array_keys($properties),
                'additionalProperties' => false,
            ];
        }

        return ['type' => 'string'];
    }

    private function strictResponseFormat(array $schema): array
    {
        return [
            'type' => 'json_schema',
            'json_schema' => [
                'name' => 'product_translation',
                'strict' => true,
                'schema' => $schema,
            ],
        ];
    }

    private function supportsJsonObjectFallback($response): bool
    {
        $message = strtolower((string) $response->json('error.message', $response->body()));
        return str_contains($message, 'response_format')
            || str_contains($message, 'json_schema')
            || str_contains($message, 'structured')
            || str_contains($message, 'unsupported')
            || str_contains($message, 'require_parameters');
    }

    private function isTechnicalTranslationPath(array $path): bool
    {
        return collect($path)->contains(fn ($segment) => preg_match('/^choice_options_35$/i', (string) $segment) === 1);
    }

    private function isTranslatableValue(string $key, string $value): bool
    {
        $normalizedKey = strtolower(trim($key, "[] \t\n\r\0\x0B"));
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

    private function htmlTags(string $value): array
    {
        preg_match_all('/<!--.*?-->|<\/?[a-z][^>]*>/is', $value, $matches);
        return $matches[0] ?? [];
    }

    private function token(string $kind, int $index): string
    {
        return '[MAYUSH_'.$kind.'_'.$index.']';
    }

    private function pathKey(array $path): string
    {
        return implode('.', array_map(static fn ($part) => (string) $part, $path));
    }

    private function parsePath(string $path): array
    {
        return $path === '' ? [] : explode('.', $path);
    }

    private function getNestedValue(array $fields, array $path): mixed
    {
        $cursor = $fields;
        foreach ($path as $segment) {
            if (!is_array($cursor) || !array_key_exists($segment, $cursor)) {
                return null;
            }
            $cursor = $cursor[$segment];
        }
        return $cursor;
    }

    private function setNestedValue(array &$fields, array $path, mixed $value): void
    {
        $cursor =& $fields;
        $last = array_pop($path);
        foreach ($path as $segment) {
            $cursor =& $cursor[$segment];
        }
        $cursor[$last] = $value;
    }

    private function failAll(array $result, array $fields, string $errorCode, ?int $retryAfter = null): array
    {
        $paths = [];
        $this->flattenFieldPaths($fields, [], $paths);
        $result['success'] = false;
        $result['failed_fields'] = array_values(array_unique($paths));
        $result['errors'] = array_fill_keys($result['failed_fields'], $errorCode);
        $result['error_code'] = $errorCode;
        if ($retryAfter !== null) {
            $result['retry_after'] = $retryAfter;
        }
        return $result;
    }

    private function flattenFieldPaths(mixed $fields, array $prefix, array &$paths): void
    {
        if (is_array($fields)) {
            foreach ($fields as $key => $value) {
                $this->flattenFieldPaths($value, [...$prefix, $key], $paths);
            }
            return;
        }

        if (is_string($fields) && trim($fields) !== '') {
            $paths[] = $this->pathKey($prefix);
        }
    }
}
