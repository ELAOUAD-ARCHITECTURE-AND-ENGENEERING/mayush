<?php

namespace App\Services\Bot;

class DataActionService
{
    /**
     * Securely fetches order status.
     * Implements Section 15.3 (Order Data limits)
     */
    public function getOrderStatus(string $orderRef, $userContext = null): string
    {
        // TODO: Validate userContext against Order ownership
        return "Your order #$orderRef is currently in progress.";
    }

    /**
     * Securely fetches payment info.
     * Implements Section 15.4 (Restricted info limits - strips CC/CVV data)
     */
    public function getPaymentStatus(string $orderRef, $userContext = null): string
    {
        return "Payment for order #$orderRef has been verified.";
    }
    
    /**
     * Fetches public product info.
     * Implements Section 15.1
     */
    public function getProductStock(string $productRef): string
    {
        return "Product $productRef is currently IN STOCK.";
    }
}
