<?php

namespace App\Services\Blog;

use App\Models\Blog;
use App\Models\BlogSubscriberLog;
use Illuminate\Http\Request;
use Illuminate\Support\Arr;
use Illuminate\Support\Facades\Http;
use Illuminate\Support\Str;

class BlogEmailService
{
    public function __construct(private BlogSettingsService $settings)
    {
    }

    public function subscribe(array $data, Request $request): array
    {
        $provider = $this->provider();
        $blog = $this->findBlog(Arr::get($data, 'blog_id'));
        $delivery = $this->deliver($provider, $data, $blog);

        BlogSubscriberLog::create([
            'email' => Str::lower(Arr::get($data, 'email')),
            'placement' => Arr::get($data, 'placement'),
            'blog_id' => optional($blog)->id,
            'blog_title' => optional($blog)->title,
            'provider' => $provider,
            'provider_status' => $delivery['status'],
            'provider_response' => $delivery['response'],
            'ip_address' => $request->ip(),
            'user_agent' => Str::limit((string) $request->userAgent(), 1000, ''),
            'subscribed_at' => now(),
        ]);

        return [
            'success' => true,
            'message' => $this->settings->string('blog_email_success_message')
                ?: translate("You're in! Check your inbox."),
        ];
    }

    private function provider(): string
    {
        $provider = $this->settings->string('blog_email_provider') ?: 'local';

        return in_array($provider, ['local', 'mailchimp', 'klaviyo', 'webhook'], true)
            ? $provider
            : 'local';
    }

    private function findBlog($blogId): ?Blog
    {
        if (!$blogId) {
            return null;
        }

        return Blog::query()->whereKey($blogId)->first();
    }

    private function deliver(string $provider, array $data, ?Blog $blog): array
    {
        if ($provider === 'local') {
            return ['status' => 'logged', 'response' => null];
        }

        if ($provider === 'mailchimp') {
            return $this->deliverToMailchimp($data, $blog);
        }

        if ($provider === 'klaviyo') {
            return $this->deliverToKlaviyo($data, $blog);
        }

        if ($provider === 'webhook') {
            return $this->deliverToWebhook($data, $blog);
        }

        return ['status' => 'logged', 'response' => null];
    }

    private function deliverToWebhook(array $data, ?Blog $blog): array
    {
        $url = $this->settings->string('blog_webhook_url');
        if ($url === '') {
            return ['status' => 'config_missing', 'response' => 'Webhook URL is not configured.'];
        }

        try {
            $response = Http::timeout(5)->acceptJson()->post($url, [
                'email' => Arr::get($data, 'email'),
                'placement' => Arr::get($data, 'placement'),
                'blog_id' => optional($blog)->id,
                'blog_title' => optional($blog)->title,
                'subscribed_at' => now()->toIso8601String(),
            ]);

            return [
                'status' => $response->successful() ? 'delivered' : 'failed',
                'response' => Str::limit($response->body(), 1000, ''),
            ];
        } catch (\Throwable $exception) {
            return [
                'status' => 'failed',
                'response' => Str::limit($exception->getMessage(), 1000, ''),
            ];
        }
    }

    private function deliverToMailchimp(array $data, ?Blog $blog): array
    {
        $apiKey = $this->settings->secret('blog_mailchimp_api_key');
        $listId = $this->settings->string('blog_mailchimp_list_id');
        $dataCenter = Str::afterLast($apiKey, '-');

        if ($apiKey === '' || $listId === '' || $dataCenter === $apiKey) {
            return ['status' => 'config_missing', 'response' => 'Mailchimp API key or list ID is not configured.'];
        }

        $email = Str::lower(Arr::get($data, 'email'));
        $subscriberHash = md5($email);

        try {
            $response = Http::timeout(8)
                ->acceptJson()
                ->withBasicAuth('mayush', $apiKey)
                ->put("https://{$dataCenter}.api.mailchimp.com/3.0/lists/{$listId}/members/{$subscriberHash}", [
                    'email_address' => $email,
                    'status_if_new' => 'subscribed',
                    'status' => 'subscribed',
                    'merge_fields' => [
                        'SOURCE' => Arr::get($data, 'placement'),
                    ],
                    'tags' => array_values(array_filter([
                        'Mayush Blog',
                        optional($blog)->slug,
                    ])),
                ]);

            return [
                'status' => $response->successful() ? 'delivered' : 'failed',
                'response' => Str::limit($response->body(), 1000, ''),
            ];
        } catch (\Throwable $exception) {
            return [
                'status' => 'failed',
                'response' => Str::limit($exception->getMessage(), 1000, ''),
            ];
        }
    }

    private function deliverToKlaviyo(array $data, ?Blog $blog): array
    {
        $apiKey = $this->settings->secret('blog_klaviyo_api_key');
        $listId = $this->settings->string('blog_klaviyo_list_id');
        $revision = $this->settings->string('blog_klaviyo_revision') ?: '2026-04-15';

        if ($apiKey === '' || $listId === '') {
            return ['status' => 'config_missing', 'response' => 'Klaviyo API key or list ID is not configured.'];
        }

        try {
            $response = Http::timeout(8)
                ->accept('application/vnd.api+json')
                ->contentType('application/vnd.api+json')
                ->withHeaders([
                    'Authorization' => 'Klaviyo-API-Key ' . $apiKey,
                    'revision' => $revision,
                ])
                ->post('https://a.klaviyo.com/api/profile-subscription-bulk-create-jobs', [
                    'data' => [
                        'type' => 'profile-subscription-bulk-create-job',
                        'attributes' => [
                            'profiles' => [
                                'data' => [[
                                    'type' => 'profile',
                                    'attributes' => [
                                        'email' => Str::lower(Arr::get($data, 'email')),
                                        'properties' => array_filter([
                                            'source' => 'Mayush Blog',
                                            'placement' => Arr::get($data, 'placement'),
                                            'blog_title' => optional($blog)->title,
                                            'blog_slug' => optional($blog)->slug,
                                        ]),
                                        'subscriptions' => [
                                            'email' => [
                                                'marketing' => [
                                                    'consent' => 'SUBSCRIBED',
                                                ],
                                            ],
                                        ],
                                    ],
                                ]],
                            ],
                        ],
                        'relationships' => [
                            'list' => [
                                'data' => [
                                    'type' => 'list',
                                    'id' => $listId,
                                ],
                            ],
                        ],
                    ],
                ]);

            return [
                'status' => $response->successful() ? 'delivered' : 'failed',
                'response' => Str::limit($response->body(), 1000, ''),
            ];
        } catch (\Throwable $exception) {
            return [
                'status' => 'failed',
                'response' => Str::limit($exception->getMessage(), 1000, ''),
            ];
        }
    }
}
