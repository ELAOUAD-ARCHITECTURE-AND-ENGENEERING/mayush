<?php

namespace App\Services\Blog;

use App\Models\Blog;
use App\Models\BlogSubscriberLog;
use Illuminate\Http\Request;
use Illuminate\Support\Arr;
use Illuminate\Support\Str;

class BlogEmailService
{
    public function subscribe(array $data, Request $request): array
    {
        $provider = $this->provider();
        $blog = $this->findBlog(Arr::get($data, 'blog_id'));

        BlogSubscriberLog::create([
            'email' => Str::lower(Arr::get($data, 'email')),
            'placement' => Arr::get($data, 'placement'),
            'blog_id' => optional($blog)->id,
            'blog_title' => optional($blog)->title,
            'provider' => $provider,
            'provider_status' => $provider === 'local' ? 'logged' : 'logged_locally',
            'provider_response' => $provider === 'local'
                ? null
                : 'External provider delivery is not enabled in this safe implementation phase.',
            'ip_address' => $request->ip(),
            'user_agent' => Str::limit((string) $request->userAgent(), 1000, ''),
            'subscribed_at' => now(),
        ]);

        return [
            'success' => true,
            'message' => get_setting('blog_email_success_message')
                ?: translate("You're in! Check your inbox."),
        ];
    }

    private function provider(): string
    {
        $provider = get_setting('blog_email_provider') ?: 'local';

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
}
