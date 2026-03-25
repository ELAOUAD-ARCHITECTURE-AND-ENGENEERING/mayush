<?php

namespace App\Services;

use App\Models\Product;

class FlashDealStockService
{
    /**
     * Get stock percentage, UI color, and text for a flash deal product.
     *
     * @param Product $product
     * @return array
     */
    public static function getStockData(Product $product)
    {
        $current_stock = $product->stocks->sum('qty');
        $total_qty     = $current_stock + $product->num_of_sale;
        $remaining_pct = ($total_qty > 0) ? round(($current_stock / $total_qty) * 100) : 100;

        // Color Logic
        if ($remaining_pct >= 80) {
            $color = '#28a745'; // Green
        } elseif ($remaining_pct >= 50) {
            $color = '#fd7e14'; // Orange
        } else {
            $color = '#dc3545'; // Red
        }

        // Text Logic
        $text = $current_stock > 0 
            ? $current_stock . ' ' . translate('left in stock') 
            : translate('Out of stock');

        return [
            'percentage' => $remaining_pct,
            'color'      => $color,
            'text'       => $text,
            'stock'      => $current_stock
        ];
    }
}
