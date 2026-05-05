<?php

namespace App\Services\Blog;

use Illuminate\Support\Facades\Crypt;

class BlogSettingsService
{
    private const DEFAULTS = [
        'blog_enable_product_embeds' => true,
        'blog_products_per_embed' => 4,
        'blog_enable_hero' => true,
        'blog_featured_article_id' => '',
        'blog_hero_cta_text' => 'Read guide',
        'blog_articles_per_page' => 12,
        'blog_enable_category_tabs' => true,
        'blog_enable_read_time' => true,
        'blog_enable_product_count_badge' => true,
        'blog_enable_scroll_progress' => true,
        'blog_email_enable_listing_inline' => true,
        'blog_email_listing_interval' => 3,
        'blog_email_enable_sidebar' => true,
        'blog_email_enable_mid_article' => true,
        'blog_email_enable_post_read' => true,
        'blog_email_provider' => 'local',
        'blog_webhook_url' => '',
        'blog_mailchimp_api_key' => '',
        'blog_mailchimp_list_id' => '',
        'blog_klaviyo_api_key' => '',
        'blog_klaviyo_list_id' => '',
        'blog_klaviyo_revision' => '2026-04-15',
        'blog_email_success_message' => "You're in! Check your inbox.",
        'blog_enable_table_of_contents' => true,
        'blog_product_embed_cache_minutes' => 15,
        'blog_enable_article_schema' => true,
        'blog_enable_product_schema' => true,
        'blog_enable_sidebar_products' => true,
        'blog_sidebar_products_count' => 3,
        'blog_enable_post_read_products' => true,
        'blog_post_read_products_count' => 4,
        'blog_enable_lazy_product_loading' => false,
        'blog_enable_share_bar' => true,
        'blog_enable_vendor_cta' => true,
        'blog_enable_related_articles' => true,
        'blog_related_articles_count' => 3,
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

    public function secret(string $key): string
    {
        $value = $this->string($key);

        if (!str_starts_with($value, 'encrypted:')) {
            return $value;
        }

        try {
            return Crypt::decryptString(substr($value, strlen('encrypted:')));
        } catch (\Throwable) {
            return '';
        }
    }

    public function secretIsConfigured(string $key): bool
    {
        return $this->secret($key) !== '';
    }

    public static function encryptSecret(string $value): string
    {
        return 'encrypted:' . Crypt::encryptString($value);
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
            'hero_enabled' => $this->boolean('blog_enable_hero'),
            'featured_article_id' => $this->integer('blog_featured_article_id'),
            'hero_cta_text' => $this->string('blog_hero_cta_text') ?: 'Read guide',
            'articles_per_page' => max(1, $this->integer('blog_articles_per_page')),
            'category_tabs_enabled' => $this->boolean('blog_enable_category_tabs'),
            'read_time_enabled' => $this->boolean('blog_enable_read_time'),
            'product_count_badge_enabled' => $this->boolean('blog_enable_product_count_badge'),
            'scroll_progress_enabled' => $this->boolean('blog_enable_scroll_progress'),
            'email_listing_inline_enabled' => $this->boolean('blog_email_enable_listing_inline'),
            'email_listing_interval' => max(1, $this->integer('blog_email_listing_interval')),
            'email_mid_article_enabled' => $this->boolean('blog_email_enable_mid_article'),
            'email_sidebar_enabled' => $this->boolean('blog_email_enable_sidebar'),
            'email_post_read_enabled' => $this->boolean('blog_email_enable_post_read'),
            'table_of_contents_enabled' => $this->boolean('blog_enable_table_of_contents'),
            'article_schema_enabled' => $this->boolean('blog_enable_article_schema'),
            'product_schema_enabled' => $this->boolean('blog_enable_product_schema'),
            'sidebar_products_enabled' => $this->boolean('blog_enable_sidebar_products'),
            'sidebar_products_count' => max(1, $this->integer('blog_sidebar_products_count')),
            'post_read_products_enabled' => $this->boolean('blog_enable_post_read_products'),
            'post_read_products_count' => max(1, $this->integer('blog_post_read_products_count')),
            'lazy_product_loading_enabled' => $this->boolean('blog_enable_lazy_product_loading'),
            'share_bar_enabled' => $this->boolean('blog_enable_share_bar'),
            'vendor_cta_enabled' => $this->boolean('blog_enable_vendor_cta'),
            'related_articles_enabled' => $this->boolean('blog_enable_related_articles'),
            'related_articles_count' => max(1, $this->integer('blog_related_articles_count')),
        ];
    }

    private function get(string $key)
    {
        return get_setting($key, self::DEFAULTS[$key] ?? null);
    }
}
