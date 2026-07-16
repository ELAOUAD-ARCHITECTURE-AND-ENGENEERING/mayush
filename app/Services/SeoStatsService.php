<?php

namespace App\Services;

use App\Models\Order;
use App\Models\Product;
use App\Models\Shop;
use Carbon\Carbon;
use Illuminate\Support\Facades\Cache;

class SeoStatsService
{
    public function homepageStats(): array
    {
        return Cache::remember('homepage_seo_stats', 900, fn () => [
            'verified_sellers' => $this->verifiedSellerCount(),
            'published_products' => $this->publishedProductCount(),
            'delivery_success_rate' => $this->deliverySuccessRate(),
        ]);
    }

    public function verifiedSellerCount(): int
    {
        return (int) Shop::publiclyVisible()->count();
    }

    public function publishedProductCount(): int
    {
        return (int) Product::publiclyVisible()->count();
    }

    public function deliverySuccessRate(): ?int
    {
        $eligibleStatuses = ['delivered', 'cancelled', 'canceled'];
        $query = Order::query()
            ->where('created_at', '>=', Carbon::now()->subDays(180))
            ->whereIn('delivery_status', $eligibleStatuses);

        $eligible = (clone $query)->count();
        if ($eligible < 30) {
            return null;
        }

        $delivered = (clone $query)->where('delivery_status', 'delivered')->count();

        return (int) round(($delivered / $eligible) * 100);
    }

    public function homepageFaqSchema(): array
    {
        $stats = $this->homepageStats();
        $sellerText = $stats['verified_sellers'] > 0
            ? 'Mayush relie les acheteurs a ' . number_format($stats['verified_sellers']) . ' vendeurs verifies pour le mobilier, la decoration, les luminaires et l amenagement interieur au Maroc.'
            : 'Mayush relie les acheteurs a des vendeurs verifies pour le mobilier, la decoration, les luminaires et l amenagement interieur au Maroc.';

        $productText = $stats['published_products'] > 0
            ? 'Le catalogue Mayush presente actuellement ' . number_format($stats['published_products']) . ' produits approuves et publies dans le mobilier, la decoration, les luminaires, les materiaux et les accessoires maison.'
            : 'Le catalogue Mayush organise des produits approuves dans le mobilier, la decoration, les luminaires, les materiaux et les accessoires maison.';

        $deliveryText = $stats['delivery_success_rate'] !== null
            ? 'Sur les commandes eligibles des 180 derniers jours, Mayush affiche un taux de livraison reussie de ' . $stats['delivery_success_rate'] . '% pour les statuts de livraison finalises.'
            : 'Mayush prend en charge des parcours de livraison coordonnes pour les commandes de mobilier et decoration au Maroc, avec suivi de commande disponible pour les clients.';

        return [
            '@context' => 'https://schema.org',
            '@type' => 'FAQPage',
            'mainEntity' => [
                [
                    '@type' => 'Question',
                    'name' => 'Que peut-on acheter sur Mayush au Maroc ?',
                    'acceptedAnswer' => [
                        '@type' => 'Answer',
                        'text' => $productText,
                    ],
                ],
                [
                    '@type' => 'Question',
                    'name' => 'Qui vend sur Mayush Marketplace ?',
                    'acceptedAnswer' => [
                        '@type' => 'Answer',
                        'text' => $sellerText,
                    ],
                ],
                [
                    '@type' => 'Question',
                    'name' => 'Mayush propose-t-il la livraison au Maroc ?',
                    'acceptedAnswer' => [
                        '@type' => 'Answer',
                        'text' => $deliveryText,
                    ],
                ],
            ],
        ];
    }

    public function homepageItemListSchema(int $limit = 8): array
    {
        $locale = app()->getLocale();

        return Cache::remember("homepage_seo_item_list:{$locale}:{$limit}", 900, function () use ($limit) {
            $products = Product::publiclyVisible()
                ->with('thumbnail')
                ->latest('updated_at')
                ->take($limit)
                ->get();

            return [
            '@context' => 'https://schema.org',
            '@type' => 'ItemList',
            '@id' => route('home') . '#featured-products',
            'name' => 'Produits Mayush selectionnes',
            'itemListElement' => $products->values()->map(function (Product $product, int $index) {
                return [
                    '@type' => 'ListItem',
                    'position' => $index + 1,
                    'url' => route('product', $product->slug),
                    'item' => [
                        '@type' => 'Product',
                        'name' => SeoService::cleanText($product->getTranslation('name'), $product->name, 120),
                        'url' => route('product', $product->slug),
                        'image' => SeoService::absoluteUrl(uploaded_asset($product->thumbnail ?: $product->thumbnail_img)),
                    ],
                ];
            })->all(),
            ];
        });
    }
}
