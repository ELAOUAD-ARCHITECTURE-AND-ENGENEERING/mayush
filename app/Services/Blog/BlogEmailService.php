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

        if ($provider !== 'webhook') {
            return [
                'status' => 'logged_locally',
                'response' => 'Provider delivery is not configured yet; submission was logged locally.',
            ];
        }

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
}
