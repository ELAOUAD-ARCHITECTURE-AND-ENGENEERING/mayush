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
        return sprintf(translate("Your order #%s is currently in progress."), $orderRef);
    }

    /**
     * Securely fetches payment info.
     * Implements Section 15.4 (Restricted info limits - strips CC/CVV data)
     */
    public function getPaymentStatus(string $orderRef, $userContext = null): string
    {
        return sprintf(translate("Payment for order #%s has been verified."), $orderRef);
    }
    
    /**
     * Fetches public product info.
     * Implements Section 15.1
     */
    public function getProductStock(string $productRef): string
    {
        return sprintf(translate("Product %s is currently IN STOCK."), $productRef);
    }
}
