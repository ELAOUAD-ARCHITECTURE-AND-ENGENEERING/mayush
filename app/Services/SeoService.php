<?php

namespace App\Services;

use App\Models\Product;
use Illuminate\Support\Str;

class SeoService
{
    public static function cleanText($value, string $fallback = '', int $limit = 170): string
    {
        $text = html_entity_decode(strip_tags((string) $value), ENT_QUOTES | ENT_HTML5, 'UTF-8');
        $text = preg_replace('/[\x00-\x1F\x7F]+/u', ' ', $text);
        $text = preg_replace('/\s+/u', ' ', $text);
        $text = trim($text);

        if ($text === '') {
            $text = $fallback;
        }

        if ($limit > 0 && Str::length($text) > $limit) {
            $text = rtrim(Str::limit($text, max(1, $limit - 1), ''), " \t\n\r\0\x0B.,;:-") . '.';
        }

        return $text;
    }

    public static function meaningfulText($value, string $fallback, int $limit = 170, int $minimumLength = 1): string
    {
        $text = self::cleanText($value, '', $limit);

        if (Str::length($text) < $minimumLength) {
            return self::cleanText($fallback, $fallback, $limit);
        }

        return $text;
    }

    public static function demoteH1ToH2($html): string
    {
        return preg_replace_callback('/<\\/?h1(\\s[^>]*)?>/i', function ($matches) {
            if (Str::startsWith(Str::lower($matches[0]), '</')) {
                return '</h2>';
            }

            return '<h2' . ($matches[1] ?? '') . '>';
        }, (string) $html);
    }

    public static function absoluteUrl(?string $url): ?string
    {
        if (!$url) {
            return null;
        }

        if (Str::startsWith($url, '//')) {
            return 'https:' . $url;
        }

        if (Str::startsWith($url, ['http://', 'https://'])) {
            return $url;
        }

        return url($url);
    }

    public static function meta(array $overrides = []): array
    {
        $siteName = self::cleanText(get_setting('website_name'), config('app.name'), 80);
        $defaultTitle = self::cleanText(get_setting('meta_title'), $siteName . ' | ' . self::cleanText(get_setting('site_motto'), 'Luxury interior design marketplace', 80), 70);
        $defaultDescription = 'Discover Mayush, a Moroccan marketplace for furniture, interior design products, decor, lighting, and premium home materials.';
        $description = self::cleanText($overrides['description'] ?? get_setting('meta_description'), $defaultDescription, 170);
        $title = self::cleanText($overrides['title'] ?? $defaultTitle, $defaultTitle, 70);
        $imageInput = $overrides['image'] ?? '';
        $canonicalInput = $overrides['canonical'] ?? '';
        $image = self::absoluteUrl($imageInput ?: (uploaded_asset(get_setting('meta_image')) ?: uploaded_asset(get_setting('header_logo')) ?: static_asset('assets/img/logo.png')));
        $canonical = self::absoluteUrl($canonicalInput ?: url()->current());

        return [
            'title' => $title,
            'description' => $description,
            'keywords' => self::cleanText($overrides['keywords'] ?? get_setting('meta_keywords'), '', 220),
            'image' => $image,
            'canonical' => $canonical,
            'type' => ($overrides['type'] ?? '') ?: 'website',
            'robots' => ($overrides['robots'] ?? '') ?: 'index, follow',
            'site_name' => $siteName,
        ];
    }

    public static function jsonLd(array $data): string
    {
        return json_encode($data, JSON_UNESCAPED_SLASHES | JSON_UNESCAPED_UNICODE | JSON_PRETTY_PRINT);
    }

    public static function organizationSchema(?array $seo = null): array
    {
        $seo = $seo ?: self::meta();

        return array_filter([
            '@context' => 'https://schema.org',
            '@type' => 'Organization',
            'name' => $seo['site_name'],
            'url' => route('home'),
            'logo' => self::absoluteUrl(uploaded_asset(get_setting('header_logo'))),
            'description' => $seo['description'],
        ]);
    }

    public static function websiteSchema(?array $seo = null): array
    {
        $seo = $seo ?: self::meta();

        return [
            '@context' => 'https://schema.org',
            '@type' => 'WebSite',
            'name' => $seo['site_name'],
            'url' => route('home'),
            'potentialAction' => [
                '@type' => 'SearchAction',
                'target' => route('home') . '/search?keyword={search_term_string}',
                'query-input' => 'required name=search_term_string',
            ],
        ];
    }

    public static function webPageSchema(array $seo, ?string $name = null): array
    {
        return [
            '@context' => 'https://schema.org',
            '@type' => 'WebPage',
            'name' => self::cleanText($name ?: $seo['title'], $seo['title'], 90),
            'url' => $seo['canonical'],
            'description' => $seo['description'],
        ];
    }

    public static function breadcrumbSchema(array $items): array
    {
        $elements = [];
        foreach (array_values($items) as $index => $item) {
            $elements[] = [
                '@type' => 'ListItem',
                'position' => $index + 1,
                'name' => self::cleanText($item['name'] ?? '', 'Page', 90),
                'item' => self::absoluteUrl($item['url'] ?? url()->current()),
            ];
        }

        return [
            '@context' => 'https://schema.org',
            '@type' => 'BreadcrumbList',
            'itemListElement' => $elements,
        ];
    }

    public static function articleSchema($blog): array
    {
        $title = self::cleanText($blog->meta_title ?: $blog->title, $blog->title, 90);
        $description = self::cleanText($blog->meta_description ?: $blog->short_description, $title, 170);

        return array_filter([
            '@context' => 'https://schema.org',
            '@type' => 'Article',
            'headline' => $title,
            'image' => self::absoluteUrl(uploaded_asset($blog->banner)),
            'datePublished' => optional($blog->created_at)->toIso8601String(),
            'dateModified' => optional($blog->updated_at)->toIso8601String(),
            'author' => [
                '@type' => 'Organization',
                'name' => self::cleanText(get_setting('website_name'), config('app.name'), 80),
            ],
            'publisher' => [
                '@type' => 'Organization',
                'name' => self::cleanText(get_setting('website_name'), config('app.name'), 80),
                'logo' => [
                    '@type' => 'ImageObject',
                    'url' => self::absoluteUrl(uploaded_asset(get_setting('header_logo'))),
                ],
            ],
            'description' => $description,
            'mainEntityOfPage' => [
                '@type' => 'WebPage',
                '@id' => route('blog.details', $blog->slug),
            ],
        ]);
    }

    public static function productSchema(Product $product, string $availability = 'OutOfStock'): array
    {
        $name = self::cleanText($product->getTranslation('name'), $product->name, 120);
        $description = self::cleanText($product->meta_description ?: $product->description, $name, 170);
        $images = array_filter(array_unique(array_map([self::class, 'absoluteUrl'], array_merge(
            [uploaded_asset($product->thumbnail_img)],
            array_map('uploaded_asset', array_filter(explode(',', (string) $product->photos)))
        ))));

        $schema = [
            '@context' => 'https://schema.org/',
            '@type' => 'Product',
            'name' => $name,
            'image' => array_values($images),
            'description' => $description,
            'sku' => (string) $product->slug,
            'mpn' => (string) $product->id,
            'brand' => [
                '@type' => 'Brand',
                'name' => self::cleanText(optional($product->brand)->name, get_setting('website_name') ?: config('app.name'), 80),
            ],
            'offers' => [
                '@type' => 'Offer',
                'url' => route('product', $product->slug),
                'priceCurrency' => optional(get_system_default_currency())->code ?: 'MAD',
                'price' => number_format(self::productSchemaPrice($product), 2, '.', ''),
                'priceValidUntil' => now()->endOfYear()->toDateString(),
                'itemCondition' => 'https://schema.org/NewCondition',
                'availability' => 'https://schema.org/' . $availability,
                'seller' => [
                    '@type' => 'Organization',
                    'name' => self::cleanText($product->added_by === 'seller' ? optional(optional($product->user)->shop)->name : get_setting('site_name'), get_setting('website_name') ?: config('app.name'), 80),
                ],
            ],
        ];

        $approvedReviews = $product->reviews->where('status', 1);
        if ($approvedReviews->count() > 0) {
            $schema['aggregateRating'] = [
                '@type' => 'AggregateRating',
                'ratingValue' => (float) $product->rating,
                'reviewCount' => $approvedReviews->count(),
            ];
        }

        return $schema;
    }

    public static function productSchemaPrice(Product $product): float
    {
        $raw = (string) home_discounted_price($product, false);
        $first = trim(explode('-', $raw)[0]);

        return max(0, (float) preg_replace('/[^0-9.]/', '', $first));
    }

    public static function altText(?string $name, string $fallback = 'Mayush'): string
    {
        return self::cleanText($name, $fallback, 120);
    }
}
