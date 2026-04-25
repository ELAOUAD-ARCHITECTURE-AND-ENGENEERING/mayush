<?php

namespace App\Services;

use App\Models\Order;
use App\Models\Product;
use App\Models\Shop;
use Carbon\Carbon;

class SeoStatsService
{
    public function homepageStats(): array
    {
        return [
            'verified_sellers' => $this->verifiedSellerCount(),
            'published_products' => $this->publishedProductCount(),
            'delivery_success_rate' => $this->deliverySuccessRate(),
        ];
    }

    public function verifiedSellerCount(): int
    {
        return (int) Shop::where('verification_status', 1)->count();
    }

    public function publishedProductCount(): int
    {
        return (int) Product::isApprovedPublished()->count();
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
            ? 'Mayush connects shoppers with ' . number_format($stats['verified_sellers']) . ' verified sellers for furniture, decor, lighting, and interior design products in Morocco.'
            : 'Mayush connects shoppers with verified sellers for furniture, decor, lighting, and interior design products in Morocco.';

        $productText = $stats['published_products'] > 0
            ? 'The marketplace currently lists ' . number_format($stats['published_products']) . ' approved and published products across furniture, decor, lighting, materials, and home accessories.'
            : 'The marketplace organizes approved products across furniture, decor, lighting, materials, and home accessories.';

        $deliveryText = $stats['delivery_success_rate'] !== null
            ? 'Based on eligible orders from the last 180 days, Mayush has a ' . $stats['delivery_success_rate'] . '% delivery success rate for completed delivery outcomes.'
            : 'Mayush supports coordinated delivery workflows for Moroccan furniture and home decor orders, with order tracking available for customers.';

        return [
            '@context' => 'https://schema.org',
            '@type' => 'FAQPage',
            'mainEntity' => [
                [
                    '@type' => 'Question',
                    'name' => 'What can I buy on Mayush?',
                    'acceptedAnswer' => [
                        '@type' => 'Answer',
                        'text' => $productText,
                    ],
                ],
                [
                    '@type' => 'Question',
                    'name' => 'Who sells on Mayush?',
                    'acceptedAnswer' => [
                        '@type' => 'Answer',
                        'text' => $sellerText,
                    ],
                ],
                [
                    '@type' => 'Question',
                    'name' => 'Does Mayush support delivery in Morocco?',
                    'acceptedAnswer' => [
                        '@type' => 'Answer',
                        'text' => $deliveryText,
                    ],
                ],
            ],
        ];
    }
}
