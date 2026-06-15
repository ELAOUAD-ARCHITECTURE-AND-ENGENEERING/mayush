<?php

namespace Mayush\Shipping\Onessta\Client;

use Illuminate\Http\Client\PendingRequest;
use Illuminate\Support\Facades\Http;
use Illuminate\Support\Facades\Log;
use Mayush\Shipping\Onessta\Exceptions\AuthenticationException;
use Mayush\Shipping\Onessta\Exceptions\RemoteApiException;
use Mayush\Shipping\Onessta\Exceptions\ValidationException;
use Illuminate\Http\Client\Response;

class OnesstaClient
{
    private string $baseUrl;
    private string $token;
    private string $apiKey;
    private string $clientId;
    private int $timeout;
    private int $connectTimeout;
    private int $retryTimes;
    private int $retrySleepMs;
    private array $retryCodes;

    public function __construct(
        string $baseUrl,
        string $token,
        string $apiKey,
        string $clientId,
        int $timeout = 30,
        int $connectTimeout = 10,
        int $retryTimes = 3,
        int $retrySleepMs = 500,
        array $retryCodes = [408, 502, 503, 504]
    ) {
        $this->baseUrl = rtrim($baseUrl, '/');
        $this->token = $token;
        $this->apiKey = $apiKey;
        $this->clientId = $clientId;
        $this->timeout = $timeout;
        $this->connectTimeout = $connectTimeout;
        $this->retryTimes = $retryTimes;
        $this->retrySleepMs = $retrySleepMs;
        $this->retryCodes = $retryCodes;
    }

    public function get(string $endpoint, array $query = []): Response
    {
        return $this->request('GET', $endpoint, [], $query);
    }

    public function post(string $endpoint, array $data = [], array $query = []): Response
    {
        return $this->request('POST', $endpoint, $data, $query);
    }

    private function request(string $method, string $endpoint, array $data = [], array $query = []): Response
    {
        $url = $this->baseUrl . '/' . ltrim($endpoint, '/');
        $attempt = 0;
        $lastException = null;

        while ($attempt <= $this->retryTimes) {
            try {
                $request = $this->buildRequest($method, $url, $data, $query);
                $response = ($method === 'POST') 
                    ? $request->post($url, $data)
                    : $request->get($url);

                if ($response->status() === 401) {
                    $body = $response->json();
                    $message = $body['message'] ?? 'Invalid ONESSTA credentials. Check your API token, key, and client ID.';
                    throw new AuthenticationException($message);
                }

                if ($response->status() === 422) {
                    $body = $response->json();
                    throw new ValidationException(
                        $body['error'] ?? 'Validation failed',
                        $body['errors'] ?? []
                    );
                }

                if (!$response->successful()) {
                    if (in_array($response->status(), $this->retryCodes)) {
                        throw new RemoteApiException(
                            "ONESSTA API returned error: {$response->status()}",
                            $response->status(),
                            $response->json()
                        );
                    }
                    if ($response->status() >= 500) {
                        throw new RemoteApiException(
                            "ONESSTA API returned error: {$response->status()}",
                            $response->status(),
                            $response->json()
                        );
                    }
                }

                return $response;

            } catch (RemoteApiException $e) {
                $lastException = $e;
                $attempt++;

                if ($attempt > $this->retryTimes) {
                    break;
                }

                $sleepMs = $this->retrySleepMs * $attempt;
                Log::warning("ONESSTA API call failed, retrying in {$sleepMs}ms", [
                    'attempt' => $attempt,
                    'max_attempts' => $this->retryTimes,
                    'status' => $e->getHttpStatus(),
                ]);
                usleep($sleepMs * 1000);
            }
        }

        throw $lastException ?? new RemoteApiException('ONESSTA API call failed after retries');
    }

    private function buildRequest(string $method, string $url, array $data, array $query): PendingRequest
    {
        $request = Http::timeout($this->timeout)
            ->connectTimeout($this->connectTimeout)
            ->withHeaders([
                'Authorization' => 'Bearer ' . $this->token,
                'API-Key' => $this->apiKey,
                'Client-ID' => $this->clientId,
                'Content-Type' => 'application/json',
                'Accept' => 'application/json',
            ]);



        return $request->withQueryParameters($query);
    }

    public function getBaseUrl(): string
    {
        return $this->baseUrl;
    }
}
