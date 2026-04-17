<?php

namespace Mayush\Shipping\Onessta\Services;

use Illuminate\Support\Facades\Http;
use Illuminate\Support\Facades\Log;
use Mayush\Shipping\Onessta\Client\OnesstaClient;
use Mayush\Shipping\Onessta\Exceptions\AuthenticationException;

class AuthService
{
    private OnesstaClient $client;

    public function __construct(OnesstaClient $client)
    {
        $this->client = $client;
    }

    public function validateCredentials(): bool
    {
        try {
            $response = $this->client->get('/p/cities', ['page' => 1, 'limit' => 1]);
            return $response->successful();
        } catch (AuthenticationException $e) {
            Log::error('ONESSTA credentials validation failed', [
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
