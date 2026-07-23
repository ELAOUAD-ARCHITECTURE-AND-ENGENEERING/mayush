<?php

namespace Tests\Feature\Notifications;

use App\Models\NotificationDevice;
use App\Models\User;
use App\Services\Notifications\FcmV1Service;
use App\Services\Notifications\PermanentNotificationFailure;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Facades\Cache;
use Illuminate\Support\Facades\Http;
use Illuminate\Support\Str;
use RuntimeException;
use Tests\TestCase;

class FcmV1ServiceTest extends TestCase
{
    use RefreshDatabase;

    private const TEST_PRIVATE_KEY = <<<'PEM'
-----BEGIN PRIVATE KEY-----
MIIEvQIBADANBgkqhkiG9w0BAQEFAASCBKcwggSjAgEAAoIBAQDM3mO1dVWHjEk0
eirLzkiAM6LHHeahLW7rrXfUwp9cchT9DQrun2isjIM2V+lMwiTLLr/8Q5tbd2Fu
iwDwHLBLFDcAZ5AfCOC2pyUM1UOQ56UzGD6Po5pitwCndYAVaCSEgquhfv3AmCOj
H95+mags4qSL+e7axkeKg5ISi3WFgb2D9trazd/i1QAebhuRirdCwyQXRBLYEck1
IcyUYtVYxk3ksJnAuTqcGObxzTnDp+Qt6bg9lVO4F5yfHj1zxyTW0RAn9ja3z3yz
9kZ0XXquwbedtYME2OVtN7E0bUbqZAvyb+V7CWiGDylQeRzLJyjUfq+Xm7w5EbU0
AUccZXeDAgMBAAECggEAC22oFavL3FaUxAyNzZyEykE9jKqcC0eCPzuhvY8ong4V
7wxAUCs5U7YE7c4KpPsH9WoQUKciCACvwTkxqxm8/3sxtEaSsU1PLjFCk/KW2DY4
rquxqO6hKQeSmqtBCWPe9NUBp23y0HPl026hyUf5RZ1UPSPxawF4ZTKaV+/kQh27
x9xzF4CwUkdblmn3XfviCNxGutuZjtL650Vb4xx2dKnhAT4RFDZl0488tkZp+lyr
ZI6BK/QodVrLLq2jsB6zgBODHbumo294gZZe3NNYjMKOPTQpuOtWriqly/Cb7El9
JGT3Fe5GM5zqgUpylp/Nf5hYYvuyu4jzI4uFr3HTwQKBgQD0mIdtkzhhYuVgCpEK
Nue+8mdJD5uuCgUNm6rVHuhlCAQzrRLY/hixH+/Esze8fbMqJsAcssrTZOojqhax
lDzpuke8cFImJZ9OrKHAz+kVET2LuCqfafXGOFbX0a3Eu/sitI7pSM5ULXiyiRDQ
WJeAKxZy8RYEpEsBlEgUqMgJQQKBgQDWa66FiD7LxfYXQpRkM+OQzTtCm1JnSDQe
iFNVnzrfmjmJhoyNunbFqpwp8jw0jiPiRNGUJMARHoXo8rsuSEEah+5Z08db1WV6
n6P7RpDwuy2Q7D5hFsMUO8RGaBrAi3Tj3z+IXPZMwhPOKUFGnAemaDnY8Qy6mMv5
7Z0WX9irwwKBgQCHOmD9crxkEzIvdAgWNp7PkeMKoKd4hC+hA2RiabEgUltIWrKl
522tV7PO8yqoenJ4pm3mL+81CoDscNgVG5jFu+h3RT4Vuw0liQnwuMHrlNmLCqn9
8q4gBfv1XAaPU0KYQG0WitsP7Qfi5Sa3mZNBGBFUQPfkXxTc7acgTOQzQQKBgGxR
W4mDaU2hhI79iF8SQ2x78OrV8gvQ0tt1vLttQ91/WH+b49cZEjUK2fbqsvVNtNzy
LPlsk0ZSqA0kyeA4igLwFj10mapPpAMNMFaYsFGcDQm//kcnq4bGJoH2x0TrbjJL
Lhoh9B6UkFK+ToZv/gFXd5mbM/XziF+KZKfP5XHnAoGAeAM2znWjnN6M5Fh/J7P6
hWzkhEksE5cQL2JMzpUDLD5/iz2NC6AQ5H6p2ZlX7+3M/Xzk5DQbVfjrGuVWUZ3H
NK/umjESxzTMFWRMJx1ALGgX6ixhl+HQPhVcaFZ9DHYU7IAxyQ9dV0F7t/6md8Ew
T58pAuyUDl1s/yPXrU6hV74=
-----END PRIVATE KEY-----
PEM;

    private string $serviceAccountPath;

    protected function setUp(): void
    {
        parent::setUp();
        Cache::flush();

        $this->serviceAccountPath = tempnam(sys_get_temp_dir(), 'mayush-fcm-');
        file_put_contents($this->serviceAccountPath, json_encode([
            'project_id' => 'mayush-test',
            'client_email' => Str::uuid().'@mayush-test.iam.gserviceaccount.com',
            'private_key' => self::TEST_PRIVATE_KEY,
            'token_uri' => 'https://oauth2.googleapis.com/token',
        ]));

        config()->set('notifications_v2.fcm.service_account_path', $this->serviceAccountPath);
        config()->set('notifications_v2.fcm.project_id', 'mayush-test');
        config()->set('notifications_v2.fcm.timeout', 7);
    }

    protected function tearDown(): void
    {
        if (isset($this->serviceAccountPath) && is_file($this->serviceAccountPath)) {
            unlink($this->serviceAccountPath);
        }

        parent::tearDown();
    }

    public function test_provider_acceptance_returns_reference_without_claiming_delivery(): void
    {
        Http::fake([
            'https://oauth2.googleapis.com/token' => Http::response(['access_token' => 'oauth-token']),
            'https://fcm.googleapis.com/*' => Http::response(['name' => 'projects/mayush-test/messages/123']),
        ]);

        $reference = app(FcmV1Service::class)->send($this->device(), $this->payload());

        $this->assertSame('projects/mayush-test/messages/123', $reference);
        Http::assertSentCount(2);
        Http::assertSent(fn ($request) => $request->url() === 'https://oauth2.googleapis.com/token'
            && $request['grant_type'] === 'urn:ietf:params:oauth:grant-type:jwt-bearer');
        Http::assertSent(fn ($request) => str_contains($request->url(), '/messages:send')
            && $request->hasHeader('Authorization', 'Bearer oauth-token'));
    }

    public function test_rate_limit_is_retryable_and_does_not_revoke_device(): void
    {
        Http::fake([
            'https://oauth2.googleapis.com/token' => Http::response(['access_token' => 'oauth-token']),
            'https://fcm.googleapis.com/*' => Http::response([], 429),
        ]);
        $device = $this->device();

        try {
            app(FcmV1Service::class)->send($device, $this->payload());
            $this->fail('A rate-limited FCM request must be retried by the queue.');
        } catch (RuntimeException $exception) {
            $this->assertNotInstanceOf(PermanentNotificationFailure::class, $exception);
        }

        $this->assertNull($device->refresh()->revoked_at);
    }

    public function test_unregistered_token_is_revoked_and_not_retried(): void
    {
        Http::fake([
            'https://oauth2.googleapis.com/token' => Http::response(['access_token' => 'oauth-token']),
            'https://fcm.googleapis.com/*' => Http::response([
                'error' => ['details' => [['errorCode' => 'UNREGISTERED']]],
            ], 404),
        ]);
        $device = $this->device();

        $this->expectException(PermanentNotificationFailure::class);

        try {
            app(FcmV1Service::class)->send($device, $this->payload());
        } finally {
            $this->assertNotNull($device->refresh()->revoked_at);
        }
    }

    private function device(): NotificationDevice
    {
        $user = User::factory()->customer()->create();
        $token = 'device-token-'.Str::uuid();

        return NotificationDevice::create([
            'id' => (string) Str::uuid(),
            'user_id' => $user->id,
            'token' => $token,
            'token_hash' => hash('sha256', $token),
            'platform' => 'android',
            'last_seen_at' => now(),
        ]);
    }

    private function payload(): array
    {
        return [
            'id' => (string) Str::uuid(),
            'event_key' => 'order.shipped',
            'title' => 'Order shipped',
            'message' => 'Your order is on its way.',
            'action_url' => '/purchase-history',
        ];
    }
}
