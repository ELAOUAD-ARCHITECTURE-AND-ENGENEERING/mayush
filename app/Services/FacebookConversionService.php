<?php

namespace App\Services;

use App\Models\Currency;
use Illuminate\Support\Facades\Http;
use Illuminate\Support\Facades\Log;

class FacebookConversionService
{
    public function sendPurchase($combinedOrder): bool
    {
        $contentIds = [];

        foreach ($combinedOrder->orders ?? [] as $order) {
            foreach ($order->orderDetails ?? [] as $detail) {
                $contentIds[] = (string) $detail->product_id;
            }
        }

        return $this->sendToFacebook('Purchase', [
            'currency' => $this->currencyCode(),
            'value' => $combinedOrder->grand_total,
            'content_ids' => $contentIds,
            'content_type' => 'product',
        ], 'purchase_' . $combinedOrder->id);
    }

    public function sendAddToCart($product, $price, $eventId = null): bool
    {
        return $this->sendToFacebook('AddToCart', [
            'currency' => $this->currencyCode(),
            'value' => $price,
            'content_ids' => [(string) $product->id],
            'content_type' => 'product',
        ], $eventId);
    }

    public function sendAddToWishlist($productId, $eventId = null): bool
    {
        return $this->sendToFacebook('AddToWishlist', [
            'content_ids' => [(string) $productId],
            'content_type' => 'product',
        ], $eventId);
    }

    public function sendViewContent($product, $eventId = null): bool
    {
        return $this->sendToFacebook('ViewContent', [
            'content_ids' => [(string) $product->id],
            'content_name' => $product->getTranslation('name'),
            'content_type' => 'product',
            'value' => home_discounted_price($product),
            'currency' => $this->currencyCode(),
        ], $eventId ?: 'view_' . $product->id . '_' . time());
    }

    private function sendToFacebook(string $eventName, array $customData = [], ?string $eventId = null): bool
    {
        if (!$this->enabled()) {
            return false;
        }

        $pixelId = env('FACEBOOK_PIXEL_ID');
        $accessToken = env('FACEBOOK_PIXEL_API');

        try {
            Http::timeout(10)->post("https://graph.facebook.com/v18.0/{$pixelId}/events", [
                'access_token' => $accessToken,
                'data' => [[
                    'event_name' => $eventName,
                    'event_time' => time(),
                    'event_id' => $eventId,
                    'action_source' => 'website',
                    'user_data' => $this->buildUserData(),
                    'custom_data' => $customData,
                ]],
            ]);
        } catch (\Throwable $e) {
            Log::warning('Facebook Conversion API request failed', [
                'event' => $eventName,
                'message' => $e->getMessage(),
            ]);

            return false;
        }

        return true;
    }

    private function enabled(): bool
    {
        return (int) get_setting('facebook_pixel_capi') === 1
            && filled(env('FACEBOOK_PIXEL_ID'))
            && filled(env('FACEBOOK_PIXEL_API'));
    }

    private function buildUserData(): array
    {
        $user = auth()->user();

        return array_filter([
            'em' => $user && $user->email ? hash('sha256', strtolower(trim($user->email))) : null,
            'ph' => $user && $user->phone ? hash('sha256', preg_replace('/\D+/', '', $user->phone)) : null,
            'client_ip_address' => request()->ip(),
            'client_user_agent' => request()->userAgent(),
            'fbp' => request()->cookie('_fbp'),
            'fbc' => request()->cookie('_fbc'),
        ]);
    }

    private function currencyCode(): string
    {
        return optional(Currency::find(get_setting('system_default_currency')))->code ?: 'MAD';
    }
}
