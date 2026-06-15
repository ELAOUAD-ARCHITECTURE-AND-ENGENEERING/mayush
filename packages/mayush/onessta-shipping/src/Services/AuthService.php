<?php

namespace Mayush\Shipping\Onessta\Services;

use Illuminate\Support\Facades\Http;
use Illuminate\Support\Facades\Log;
use Mayush\Shipping\Onessta\Client\OnesstaClient;
use Mayush\Shipping\Onessta\Exceptions\AuthenticationException;
use Throwable;

class AuthService
{
    private OnesstaClient $client;

    public function __construct(OnesstaClient $client)
    {
        $this->client = $client;
    }

    public function validateCredentials(): bool
    {
        if (!$this->isConfigured()) {
            Log::warning('ONESSTA credentials validation skipped because credentials are incomplete.');
            return false;
        }

        try {
            $response = $this->client->get('/p/cities', ['page' => 1, 'limit' => 1]);
            return $response->successful();
        } catch (AuthenticationException $e) {
            Log::error('ONESSTA credentials validation failed', [
                'message' => $e->getMessage(),
            ]);
            return false;
        } catch (Throwable $e) {
            Log::warning('ONESSTA credentials validation could not reach the API.', [
                'message' => $e->getMessage(),
            ]);
            return false;
        }
    }

    public function isConfigured(): bool
    {
        $token = config('onessta.auth.token');
        $apiKey = config('onessta.auth.api_key');
        $clientId = config('onessta.auth.client_id');

        return !empty($token) && !empty($apiKey) && !empty($clientId);
    }
}
