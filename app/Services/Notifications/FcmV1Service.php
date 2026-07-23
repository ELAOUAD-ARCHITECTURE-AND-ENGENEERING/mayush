<?php

namespace App\Services\Notifications;

use App\Models\NotificationDevice;
use Firebase\JWT\JWT;
use Illuminate\Support\Facades\Cache;
use Illuminate\Support\Facades\Http;
use RuntimeException;
use Laravel\Pulse\Facades\Pulse;

class FcmV1Service
{
    public function send(NotificationDevice $device, array $payload): string
    {
        $account = $this->serviceAccount();
        $projectId = config('notifications_v2.fcm.project_id') ?: ($account['project_id'] ?? null);
        if (!$projectId) {
            throw new RuntimeException('FCM project id is not configured.');
        }

        $response = Http::asJson()
            ->withToken($this->accessToken($account))
            ->timeout((int) config('notifications_v2.fcm.timeout', 10))
            ->post("https://fcm.googleapis.com/v1/projects/{$projectId}/messages:send", [
                'message' => [
                    'token' => $device->token,
                    'notification' => [
                        'title' => $payload['title'],
                        'body' => $payload['message'],
                    ],
                    'data' => [
                        'notification_id' => (string) $payload['id'],
                        'event_key' => (string) $payload['event_key'],
                        'action_url' => (string) ($payload['action_url'] ?? ''),
                    ],
                ],
            ]);

        if ($response->status() === 404
            || str_contains((string) $response->body(), 'UNREGISTERED')) {
            $device->forceFill(['revoked_at' => now()])->save();
            if (class_exists(Pulse::class)) {
                Pulse::record('notification_device', 'revoked:unregistered')
                    ->count()
                    ->onlyBuckets();
            }
            throw new PermanentNotificationFailure('FCM device token is no longer registered.');
        }

        if (!$response->successful()) {
            $message = 'FCM request failed with status '.$response->status().'.';
            if ($response->status() === 429 || $response->serverError()) {
                throw new RuntimeException($message);
            }

            throw new PermanentNotificationFailure($message);
        }

        return (string) $response->json('name', '');
    }

    private function serviceAccount(): array
    {
        $path = config('notifications_v2.fcm.service_account_path');
        if (!$path || !is_file($path)) {
            throw new RuntimeException('FCM service account is not configured.');
        }

        $account = json_decode((string) file_get_contents($path), true);
        if (!is_array($account) || empty($account['client_email']) || empty($account['private_key'])) {
            throw new RuntimeException('FCM service account is invalid.');
        }

        return $account;
    }

    private function accessToken(array $account): string
    {
        $cacheKey = 'fcm-v1-access-token:'.hash('sha256', $account['client_email']);

        return Cache::remember($cacheKey, now()->addMinutes(50), function () use ($account) {
            $now = time();
            $assertion = JWT::encode([
                'iss' => $account['client_email'],
                'scope' => 'https://www.googleapis.com/auth/firebase.messaging',
                'aud' => $account['token_uri'] ?? 'https://oauth2.googleapis.com/token',
                'iat' => $now,
                'exp' => $now + 3600,
            ], $account['private_key'], 'RS256');

            $response = Http::asForm()
                ->timeout((int) config('notifications_v2.fcm.timeout', 10))
                ->post($account['token_uri'] ?? 'https://oauth2.googleapis.com/token', [
                    'grant_type' => 'urn:ietf:params:oauth:grant-type:jwt-bearer',
                    'assertion' => $assertion,
                ]);

            if (!$response->successful() || !$response->json('access_token')) {
                throw new RuntimeException('Unable to obtain an FCM access token.');
            }

            return (string) $response->json('access_token');
        });
    }
}
