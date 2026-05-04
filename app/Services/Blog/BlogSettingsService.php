<?php

namespace App\Services\Blog;

class BlogSettingsService
{
    private const DEFAULTS = [
        'blog_enable_product_embeds' => true,
        'blog_products_per_embed' => 4,
        'blog_email_enable_listing_inline' => true,
        'blog_email_enable_sidebar' => true,
        'blog_email_enable_post_read' => true,
        'blog_email_provider' => 'local',
        'blog_email_success_message' => "You're in! Check your inbox.",
        'blog_enable_table_of_contents' => true,
        'blog_product_embed_cache_minutes' => 15,
    ];

    public function boolean(string $key): bool
    {
        $value = $this->get($key);

        if (is_bool($value)) {
            return $value;
        }

        return filter_var($value, FILTER_VALIDATE_BOOLEAN);
    }

    public function integer(string $key): int
    {
        return (int) $this->get($key);
    }

    public function string(string $key): string
    {
        return (string) $this->get($key);
    }

    public function array(string $key): array
    {
        $value = $this->get($key);

        if (is_array($value)) {
            return $value;
        }

        $decoded = json_decode((string) $value, true);

        return is_array($decoded) ? $decoded : [];
    }

    public function all(): array
    {
        return [
            'product_embeds_enabled' => $this->boolean('blog_enable_product_embeds'),
            'products_per_embed' => max(1, $this->integer('blog_products_per_embed')),
            'email_listing_inline_enabled' => $this->boolean('blog_email_enable_listing_inline'),
            'email_sidebar_enabled' => $this->boolean('blog_email_enable_sidebar'),
            'email_post_read_enabled' => $this->boolean('blog_email_enable_post_read'),
            'table_of_contents_enabled' => $this->boolean('blog_enable_table_of_contents'),
        ];
    }

    private function get(string $key)
    {
        return get_setting($key, self::DEFAULTS[$key] ?? null);
    }
}
