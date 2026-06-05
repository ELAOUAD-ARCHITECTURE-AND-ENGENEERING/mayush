<?php

namespace App\Services;

use App\Models\Category;
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
        $logo = self::absoluteUrl(uploaded_asset(get_setting('header_logo')));

        $schema = [
            '@context' => 'https://schema.org',
            '@type' => 'Organization',
            '@id' => route('home') . '#organization',
            'name' => $seo['site_name'],
            'url' => route('home'),
            'description' => $seo['description'],
            'sameAs' => self::socialProfileUrls(),
        ];

        if ($logo) {
            $schema['logo'] = [
                '@type' => 'ImageObject',
                'url' => $logo,
            ];
        }

        return self::filterSchema($schema);
    }

    public static function websiteSchema(?array $seo = null): array
    {
        $seo = $seo ?: self::meta();

        return [
            '@context' => 'https://schema.org',
            '@type' => 'WebSite',
            '@id' => route('home') . '#website',
            'name' => $seo['site_name'],
            'url' => route('home'),
            'publisher' => [
                '@id' => route('home') . '#organization',
            ],
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
            '@id' => $seo['canonical'] . '#webpage',
            'name' => self::cleanText($name ?: $seo['title'], $seo['title'], 90),
            'url' => $seo['canonical'],
            'description' => $seo['description'],
            'isPartOf' => [
                '@id' => route('home') . '#website',
            ],
        ];
    }

    public static function collectionPageSchema(Category $category, array $seo, ?int $productCount = null): array
    {
        $name = self::cleanText($category->getTranslation('name'), $category->name, 90);

        return self::filterSchema([
            '@context' => 'https://schema.org',
            '@type' => 'CollectionPage',
            '@id' => $seo['canonical'] . '#webpage',
            'url' => $seo['canonical'],
            'name' => self::cleanText($seo['title'], $name, 90),
            'description' => $seo['description'],
            'isPartOf' => [
                '@id' => route('home') . '#website',
            ],
            'about' => [
                '@type' => 'Thing',
                'name' => $name,
            ],
            'numberOfItems' => $productCount,
        ]);
    }

    public static function categoryFaqSchema(Category $category, ?int $productCount = null): array
    {
        $categoryName = self::cleanText($category->getTranslation('name'), $category->name, 80);
        $countText = $productCount !== null && $productCount > 0
            ? 'Cette categorie regroupe ' . number_format($productCount) . ' produits publies sur Mayush.'
            : 'Cette categorie regroupe une selection de produits publies sur Mayush.';

        return [
            '@context' => 'https://schema.org',
            '@type' => 'FAQPage',
            'mainEntity' => [
                [
                    '@type' => 'Question',
                    'name' => "Quels produits trouver dans la categorie {$categoryName} sur Mayush ?",
                    'acceptedAnswer' => [
                        '@type' => 'Answer',
                        'text' => $countText . ' Les acheteurs peuvent comparer les vendeurs, les styles, les prix et les options de livraison au Maroc.',
                    ],
                ],
                [
                    '@type' => 'Question',
                    'name' => "Mayush livre-t-il les produits {$categoryName} au Maroc ?",
                    'acceptedAnswer' => [
                        '@type' => 'Answer',
                        'text' => 'Mayush presente les produits de vendeurs marocains et indique les informations de disponibilite, de prix et de livraison sur les pages produit lorsque ces donnees sont disponibles.',
                    ],
                ],
            ],
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

        if ((int) $product->digital !== 1) {
            $schema['offers']['hasMerchantReturnPolicy'] = self::merchantReturnPolicySchema($product);
            $shippingDetails = self::shippingDetailsSchema($product);
            if ($shippingDetails !== []) {
                $schema['offers']['shippingDetails'] = $shippingDetails;
            }
        }

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

    public static function merchantReturnPolicySchema(Product $product): array
    {
        $days = (int) (optional($product->main_category)->refund_request_time ?: get_setting('refund_request_time') ?: config('seo.return_policy_days', 15));
        $days = max(1, $days);

        return [
            '@type' => 'MerchantReturnPolicy',
            'applicableCountry' => 'MA',
            'returnPolicyCategory' => 'https://schema.org/MerchantReturnFiniteReturnWindow',
            'merchantReturnDays' => $days,
            'returnMethod' => 'https://schema.org/ReturnByMail',
            'returnFees' => 'https://schema.org/ReturnShippingFees',
        ];
    }

    public static function shippingDetailsSchema(Product $product): array
    {
        $currency = optional(get_system_default_currency())->code ?: 'MAD';
        $shippingCost = (float) ($product->flat_shipping_cost ?? $product->shipping_cost ?? 0);
        $estimatedDays = (int) ($product->est_shipping_days ?: config('seo.shipping_default_days', 7));
        $estimatedDays = max(1, $estimatedDays);

        return [
            '@type' => 'OfferShippingDetails',
            'shippingDestination' => [
                '@type' => 'DefinedRegion',
                'addressCountry' => 'MA',
            ],
            'shippingRate' => [
                '@type' => 'MonetaryAmount',
                'value' => number_format(max(0, $shippingCost), 2, '.', ''),
                'currency' => $currency,
            ],
            'deliveryTime' => [
                '@type' => 'ShippingDeliveryTime',
                'handlingTime' => [
                    '@type' => 'QuantitativeValue',
                    'minValue' => 0,
                    'maxValue' => 2,
                    'unitCode' => 'DAY',
                ],
                'transitTime' => [
                    '@type' => 'QuantitativeValue',
                    'minValue' => 1,
                    'maxValue' => $estimatedDays,
                    'unitCode' => 'DAY',
                ],
            ],
        ];
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

    private static function socialProfileUrls(): array
    {
        return array_values(array_filter(array_map([self::class, 'absoluteUrl'], [
            get_setting('facebook_link'),
            get_setting('instagram_link'),
            get_setting('twitter_link'),
            get_setting('youtube_link'),
            get_setting('linkedin_link'),
        ])));
    }

    private static function filterSchema(array $schema): array
    {
        return array_filter($schema, function ($value) {
            return $value !== null && $value !== '' && $value !== [];
        });
    }
}
