<?php

namespace App\Services;

use App\Models\Category;
use App\Models\Product;
use App\Models\Shop;
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
        $defaultTitle = self::cleanText(get_setting('meta_title'), self::homepageTitle(), 70);
        $defaultDescription = self::homepageDescription();
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
            'robots' => ($overrides['robots'] ?? '') ?: 'index, follow, max-image-preview:large, max-snippet:-1, max-video-preview:-1',
            'site_name' => $siteName,
        ];
    }

    public static function homepageTitle(): string
    {
        return 'Mayush Marketplace : Mobilier & Decoration au Maroc';
    }

    public static function homepageDescription(): string
    {
        return 'Decouvrez Mayush, marketplace marocaine de mobilier, decoration, luminaires et amenagement interieur avec vendeurs verifies.';
    }

    public static function categoryMetaTitle(Category $category): string
    {
        $name = self::cleanText($category->getTranslation('name'), $category->name, 46);

        return self::cleanText($name . ' au Maroc | Mayush Marketplace', $name, 70);
    }

    public static function categoryMetaDescription(Category $category, ?int $productCount = null): string
    {
        $name = self::cleanText($category->getTranslation('name'), $category->name, 60);
        $count = $productCount !== null && $productCount > 0
            ? number_format($productCount) . ' references'
            : 'une selection de references';

        return self::cleanText("Comparez {$count} {$name} sur Mayush : mobilier, decoration et amenagement interieur livres au Maroc.", '', 160);
    }

    public static function productMetaTitle(Product $product): string
    {
        $name = self::cleanText($product->getTranslation('name'), $product->name, 48);

        return self::cleanText($name . ' | Mayush Maroc', $name, 70);
    }

    public static function productMetaDescription(Product $product): string
    {
        $name = self::cleanText($product->getTranslation('name'), $product->name, 70);
        $category = optional($product->main_category)->getTranslation('name') ?: 'mobilier et decoration';

        return self::cleanText("Achetez {$name} sur Mayush Maroc. Comparez les details, dimensions, prix et options de livraison pour {$category}.", '', 160);
    }

    public static function shopMetaTitle(Shop $shop): string
    {
        $name = self::cleanText($shop->name, 'Vendeur Mayush', 42);

        return self::cleanText($name . ' - Vendeur mobilier au Maroc | Mayush', $name, 70);
    }

    public static function shopMetaDescription(Shop $shop): string
    {
        $name = self::cleanText($shop->name, 'Ce vendeur Mayush', 60);
        $location = self::cleanText($shop->address, 'Maroc', 70);
        $verified = $shop->isFullyApproved() ? 'verifie' : 'reference';

        return self::cleanText("Decouvrez {$name}, vendeur {$verified} sur Mayush Marketplace au Maroc. Consultez ses produits, avis, profil, localisation ({$location}) et options de contact.", '', 170);
    }

    public static function jsonLd(array $data): string
    {
        return json_encode($data, JSON_UNESCAPED_SLASHES | JSON_UNESCAPED_UNICODE | JSON_PRETTY_PRINT);
    }

    public static function organizationSchema(?array $seo = null): array
    {
        $seo = $seo ?: self::meta();
        $logo = self::absoluteUrl(uploaded_asset(get_setting('header_logo')));
        $stats = app(SeoStatsService::class)->homepageStats();

        $schema = [
            '@context' => 'https://schema.org',
            '@type' => 'Organization',
            '@id' => route('home') . '#organization',
            'name' => $seo['site_name'],
            'alternateName' => 'Mayush Marketplace',
            'url' => route('home'),
            'description' => self::marketplaceDescription($stats),
            'foundingLocation' => [
                '@type' => 'Country',
                'name' => 'Maroc',
            ],
            'areaServed' => [
                '@type' => 'Country',
                'name' => 'Maroc',
            ],
            'knowsAbout' => [
                'mobilier Maroc',
                'decoration maison Maroc',
                'amenagement interieur',
                'luminaires',
                'marketplace mobilier',
            ],
            'additionalProperty' => self::marketplaceProperties($stats),
            'sameAs' => self::socialProfileUrls(),
        ];

        if ($logo) {
            $schema['logo'] = [
                '@type' => 'ImageObject',
                'url' => $logo,
                'width' => 600,
                'height' => 60,
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

    public static function categoryFaqSchema(Category $category, ?int $productCount = null, ?int $verifiedSellers = null): array
    {
        return [
            '@context' => 'https://schema.org',
            '@type' => 'FAQPage',
            'mainEntity' => array_map(function (array $item) {
                return [
                    '@type' => 'Question',
                    'name' => $item['question'],
                    'acceptedAnswer' => [
                        '@type' => 'Answer',
                        'text' => $item['answer'],
                    ],
                ];
            }, self::categoryFaqItems($category, $productCount, $verifiedSellers)),
        ];
    }

    public static function categoryFaqItems(Category $category, ?int $productCount = null, ?int $verifiedSellers = null): array
    {
        $categoryName = self::cleanText($category->getTranslation('name'), $category->name, 80);
        $countText = $productCount !== null && $productCount > 0
            ? 'Cette categorie regroupe ' . number_format($productCount) . ' produits publies sur Mayush.'
            : 'Cette categorie regroupe une selection de produits publies sur Mayush.';
        $sellerText = $verifiedSellers !== null && $verifiedSellers > 0
            ? 'Le catalogue Mayush s appuie sur ' . number_format($verifiedSellers) . ' vendeurs verifies au Maroc.'
            : 'Le catalogue Mayush s appuie sur des vendeurs verifies au Maroc.';
        $intent = self::categoryIntentProfile($category);

        return [
            [
                'question' => $intent['primary_question'] ?: "Comment choisir {$categoryName} au Maroc sur Mayush ?",
                'answer' => $countText . ' ' . $intent['primary_answer'],
            ],
            [
                'question' => $intent['secondary_question'] ?: "Mayush propose-t-il des vendeurs verifies pour {$categoryName} ?",
                'answer' => $sellerText . ' ' . $intent['secondary_answer'],
            ],
        ];
    }

    public static function categoryExpertNote(Category $category): string
    {
        $categoryName = self::cleanText($category->getTranslation('name'), $category->name, 80);
        $intent = self::categoryIntentProfile($category);

        return self::cleanText(
            "Note de l'équipe conseil Mayush : pour la catégorie {$categoryName}, privilégiez {$intent['expert_focus']} et vérifiez toujours les dimensions, les finitions et les options de livraison avant de commander.",
            '',
            260
        );
    }

    public static function productExpertNote(Product $product): string
    {
        $category = optional($product->main_category)->getTranslation('name') ?: 'mobilier et decoration';

        return self::cleanText(
            "Note de l'équipe conseil Mayush : pour vos achats dans la catégorie {$category} au Maroc, comparez les dimensions, la disponibilité, les délais de livraison et les informations du vendeur afin de choisir une pièce adaptée à votre espace.",
            '',
            260
        );
    }

    public static function categoryFreshnessLabel(): string
    {
        return 'Derniere mise a jour : ' . now()->translatedFormat('F Y');
    }

    public static function productDirectAnswer(Product $product, string $availabilityLabel): string
    {
        $name = self::cleanText($product->getTranslation('name'), $product->name, 90);
        $category = optional($product->main_category)->getTranslation('name') ?: 'mobilier et decoration';
        $brand = optional($product->brand)->name ? ' de la marque ' . self::cleanText($product->brand->name, '', 60) : '';

        return self::cleanText("{$name} est un produit {$category}{$brand} disponible sur Mayush Maroc. Consultez les dimensions, le prix, la disponibilite ({$availabilityLabel}) et les options de livraison partout au Maroc, notamment Casablanca, Rabat, Tanger et Marrakech.", '', 300);
    }

    public static function productSpecRows(Product $product, string $availabilityLabel): array
    {
        $rows = [
            'Categorie' => optional($product->main_category)->getTranslation('name'),
            'Marque' => optional($product->brand)->name,
            'Disponibilite' => $availabilityLabel,
            'Prix' => single_price(self::productSchemaPrice($product)),
            'Livraison' => $product->est_shipping_days
                ? 'Estimation ' . (int) $product->est_shipping_days . ' jours au Maroc'
                : 'Options de livraison selon le vendeur au Maroc',
            'Zones de livraison' => 'Partout au Maroc (Casablanca, Rabat, Tanger, Marrakech et autres villes selon vendeur)',
            'SKU' => $product->slug,
        ];

        $dimensions = self::productDimensions($product);
        if ($dimensions !== '') {
            $rows['Dimensions'] = $dimensions;
        }

        return array_filter($rows, fn ($value) => filled($value));
    }

    public static function productDimensions(Product $product): string
    {
        $parts = array_filter([
            $product->length ? (float) $product->length : null,
            $product->width ? (float) $product->width : null,
            $product->height ? (float) $product->height : null,
        ], fn ($value) => $value !== null);

        return $parts === [] ? '' : implode(' x ', $parts) . ' cm';
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

    public static function shopSchema(Shop $shop): array
    {
        $schema = [
            '@context' => 'https://schema.org',
            '@type' => 'Store',
            '@id' => route('shop.visit', $shop->slug) . '#seller',
            'name' => self::cleanText($shop->name, 'Vendeur Mayush', 90),
            'url' => route('shop.visit', $shop->slug),
            'image' => self::absoluteUrl(uploaded_asset($shop->logo)),
            'description' => self::shopMetaDescription($shop),
            'address' => self::cleanText($shop->address, 'Maroc', 140),
            'telephone' => self::cleanText($shop->phone, '', 40),
            'memberOf' => [
                '@id' => route('home') . '#organization',
            ],
            'areaServed' => [
                '@type' => 'Country',
                'name' => 'Maroc',
            ],
            'additionalProperty' => [
                [
                    '@type' => 'PropertyValue',
                    'name' => 'Statut Mayush',
                    'value' => $shop->isFullyApproved() ? 'Vendeur verifie' : 'Vendeur reference',
                ],
            ],
        ];

        if ((int) $shop->num_of_reviews > 0) {
            $schema['aggregateRating'] = [
                '@type' => 'AggregateRating',
                'ratingValue' => (float) $shop->rating,
                'reviewCount' => (int) $shop->num_of_reviews,
            ];
        }

        return self::filterSchema($schema);
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

    public static function productAltText(Product $product, string $suffix = 'Livraison Maroc'): string
    {
        $name = self::cleanText($product->getTranslation('name'), $product->name, 70);
        $category = optional($product->main_category)->getTranslation('name');
        $parts = array_filter([$name, $category, $suffix]);

        return self::cleanText(implode(' - ', $parts), $name, 140);
    }

    public static function marketplaceDescription(?array $stats = null): string
    {
        $stats = $stats ?: app(SeoStatsService::class)->homepageStats();
        $sellerText = ($stats['verified_sellers'] ?? 0) > 0
            ? number_format($stats['verified_sellers']) . ' vendeurs verifies'
            : 'vendeurs verifies';
        $productText = ($stats['published_products'] ?? 0) > 0
            ? number_format($stats['published_products']) . ' produits publies'
            : 'produits publies';

        return "Marketplace marocaine reliant {$sellerText} a des acheteurs de mobilier, decoration, luminaires et amenagement interieur, avec {$productText}.";
    }

    private static function marketplaceProperties(array $stats): array
    {
        $properties = [];
        foreach ([
            'verified_sellers' => 'Vendeurs verifies',
            'published_products' => 'Produits publies',
        ] as $key => $name) {
            if (($stats[$key] ?? 0) > 0) {
                $properties[] = [
                    '@type' => 'PropertyValue',
                    'name' => $name,
                    'value' => (string) $stats[$key],
                ];
            }
        }

        return $properties;
    }

    private static function categoryIntentProfile(Category $category): array
    {
        $source = Str::lower($category->slug . ' ' . $category->name . ' ' . $category->getTranslation('name'));
        $default = [
            'primary_question' => null,
            'primary_answer' => 'Comparez les dimensions, matieres, prix, vendeurs et options de livraison avant de choisir le produit adapte a votre espace.',
            'secondary_question' => null,
            'secondary_answer' => 'Chaque page produit presente les informations disponibles sur le prix, la disponibilite, la livraison et les caracteristiques.',
            'expert_focus' => 'des dimensions coherentes avec votre piece, des finitions durables',
        ];

        if (Str::contains($source, ['office', 'bureau', 'bureaux', 'workspace'])) {
            return [
                'primary_question' => 'Comment optimiser un espace de bureau ou coworking au Maroc ?',
                'primary_answer' => 'Pour un bureau efficace, recherchez des postes de travail modulaires, des fauteuils ergonomiques, des rangements accessibles et, si possible, des cloisons acoustiques pour reduire le bruit.',
                'secondary_question' => 'Quels criteres verifier pour le mobilier de bureau sur Mayush ?',
                'secondary_answer' => 'Verifiez la hauteur, la profondeur, le soutien ergonomique, la disponibilite, le prix et les conditions de livraison au Maroc avant de commander.',
                'expert_focus' => 'l ergonomie, les postes de travail modulaires, la gestion acoustique',
            ];
        }

        if (Str::contains($source, ['salon', 'canape', 'canapes', 'fauteuil'])) {
            return [
                'primary_question' => 'Comment choisir un canape pour un petit salon au Maroc ?',
                'primary_answer' => 'Pour un petit salon marocain, privilegiez un canape compact ou d angle, des rangements integres et des dimensions qui laissent circuler facilement autour de la table basse.',
                'secondary_question' => 'Quels details comparer pour les canapes et fauteuils sur Mayush ?',
                'secondary_answer' => 'Comparez le revetement, la profondeur d assise, la modularite, les couleurs disponibles, la disponibilite et les delais de livraison.',
                'expert_focus' => 'la profondeur d assise, la modularite, les revetements faciles a entretenir',
            ];
        }

        if (Str::contains($source, ['eclairage', 'luminaire', 'lighting', 'lampe'])) {
            return [
                'primary_question' => 'Quel eclairage choisir pour un interieur marocain moderne ?',
                'primary_answer' => 'Associez un eclairage general, des lampes d ambiance et des points lumineux fonctionnels. Les temperatures chaleureuses conviennent souvent aux salons et chambres.',
                'secondary_question' => 'Quels criteres verifier avant d acheter un luminaire sur Mayush ?',
                'secondary_answer' => 'Controlez les dimensions, la puissance, le type d ampoule, la couleur de lumiere, le style et les informations de livraison au Maroc.',
                'expert_focus' => 'la temperature de lumiere, la hauteur de pose, la compatibilite ampoule',
            ];
        }

        if (Str::contains($source, ['ameublement', 'meuble', 'furniture'])) {
            return [
                'primary_question' => 'Quel bois ou materiau choisir pour des meubles au Maroc ?',
                'primary_answer' => 'Les meubles destines a un usage quotidien doivent combiner structure stable, finitions faciles a entretenir et dimensions adaptees au climat et aux espaces marocains.',
                'secondary_question' => 'Comment comparer les meubles sur Mayush Marketplace ?',
                'secondary_answer' => 'Comparez les materiaux, dimensions, prix, disponibilite, vendeur, delais de livraison et photos produit avant de prendre une decision.',
                'expert_focus' => 'la stabilite de la structure, les materiaux, les finitions durables',
            ];
        }

        return $default;
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
