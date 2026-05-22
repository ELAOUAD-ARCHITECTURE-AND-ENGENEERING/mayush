<?php

namespace App\Utility;

use App\Models\Cart;
use Cookie;

class CartUtility
{
    public static function product_stock($product, $variation)
    {
        if (!$product) {
            return null;
        }

        return self::find_product_stock($product, $variation)
            ?: $product->stocks->first();
    }

    public static function find_product_stock($product, $variation)
    {
        if (!$product) {
            return null;
        }

        $stock = $product->stocks->where('variant', $variation)->first();
        if ($stock || !is_string($variation)) {
            return $stock;
        }

        $dimension = self::parse_dimension_variation($variation);
        if (!$dimension) {
            return null;
        }

        return $product->stocks->first(function ($candidate) use ($dimension) {
            return self::same_dimension($candidate->length, $dimension['length'])
                && self::same_dimension($candidate->width, $dimension['width'])
                && self::same_dimension($candidate->height, $dimension['height'])
                && strtolower((string) ($candidate->dimension_unit ?: 'cm')) === $dimension['unit'];
        });
    }

    public static function cart_item_availability($cartItem, $product = null): array
    {
        $product = $product ?: $cartItem->product;

        if (!$product) {
            return [
                'available' => false,
                'stock' => null,
                'stock_qty' => 0,
                'out_of_stock' => true,
                'insufficient_quantity' => false,
            ];
        }

        if ($product->digital == 1 || $product->auction_product == 1) {
            return [
                'available' => true,
                'stock' => null,
                'stock_qty' => null,
                'out_of_stock' => false,
                'insufficient_quantity' => false,
            ];
        }

        $stock = self::product_stock($product, $cartItem->variation);
        $stockQty = $stock ? (int) $stock->qty : 0;

        return [
            'available' => $stockQty >= (int) $cartItem->quantity && $stockQty >= (int) $product->min_qty,
            'stock' => $stock,
            'stock_qty' => $stockQty,
            'out_of_stock' => $stockQty <= 0,
            'insufficient_quantity' => $stockQty > 0 && $stockQty < (int) $cartItem->quantity,
        ];
    }

    public static function is_cart_item_available($cartItem, $product = null): bool
    {
        return self::cart_item_availability($cartItem, $product)['available'];
    }

    public static function sync_cart_item_stock_status($cartItem): void
    {
        $product = $cartItem->product;
        if (!$product || $product->digital == 1 || $product->auction_product == 1) {
            return;
        }

        $availability = self::cart_item_availability($cartItem, $product);

        if ($availability['out_of_stock']) {
            $cartItem->status = 0;
        } elseif ($availability['insufficient_quantity']) {
            $cartItem->quantity = $availability['stock_qty'];
            if ($availability['stock_qty'] < (int) $product->min_qty) {
                $cartItem->status = 0;
            }
        } elseif (!$availability['available']) {
            $cartItem->status = 0;
        }

        $cartItem->save();
    }

    public static function sync_cart_stock_statuses($carts): void
    {
        foreach ($carts as $cartItem) {
            self::sync_cart_item_stock_status($cartItem);
        }
    }

    public static function create_cart_variant($product, $request)
    {
        $str = null;
        if (isset($request['color'])) {
            $str = $request['color'];
        }

        if (isset($product->choice_options) && count(json_decode($product->choice_options)) > 0) {
            //Gets all the choice values of customer choice option and generate a string like Black-S-Cotton
            foreach (json_decode($product->choice_options) as $key => $choice) {
                if ($str != null) {
                    $str .= '-' . str_replace(' ', '', $request['attribute_id_' . $choice->attribute_id]);
                } else {
                    $str .= str_replace(' ', '', $request['attribute_id_' . $choice->attribute_id]);
                }
            }
        }
        return $str;
    }

    private static function parse_dimension_variation(string $variation): ?array
    {
        $number = '(\d+(?:\.\d+)?)';
        if (!preg_match('/^' . $number . '\s*x\s*' . $number . '\s*x\s*' . $number . '\s*(cm|mm|m|in|inch|inches)$/i', trim($variation), $matches)) {
            return null;
        }

        return [
            'length' => (float) $matches[1],
            'width' => (float) $matches[2],
            'height' => (float) $matches[3],
            'unit' => strtolower($matches[4]),
        ];
    }

    private static function same_dimension($left, float $right): bool
    {
        return abs((float) $left - $right) < 0.0001;
    }

    public static function get_price($product, $product_stock, $quantity)
    {
        $price = $product_stock->price;
        if ($product->auction_product == 1) {
            $price = $product->bids->max('amount');
        }

        if ($product->wholesale_product) {
            $wholesalePrice = $product_stock->wholesalePrices->where('min_qty', '<=', $quantity)
                ->where('max_qty', '>=', $quantity)
                ->first();
            if ($wholesalePrice) {
                $price = $wholesalePrice->price;
            }
        }

        $price = self::discount_calculation($product, $price);
        return $price;
    }

    public static function discount_calculation($product, $price)
    {
        $discount_applicable = false;

        if (
            $product->discount_start_date == null ||
            (strtotime(date('d-m-Y H:i:s')) >= $product->discount_start_date &&
                strtotime(date('d-m-Y H:i:s')) <= $product->discount_end_date)
        ) {
            $discount_applicable = true;
        }

        if ($discount_applicable) {
            if ($product->discount_type == 'percent') {
                $price -= ($price * $product->discount) / 100;
            } elseif ($product->discount_type == 'amount') {
                $price -= $product->discount;
            }
        }
        return $price;
    }

    public static function tax_calculation($product, $price)
    {
        $tax = 0;
        foreach ($product->taxes as $product_tax) {
            if ($product_tax->tax_type == 'percent') {
                $tax += ($price * $product_tax->tax) / 100;
            } elseif ($product_tax->tax_type == 'amount') {
                $tax += $product_tax->tax;
            }
        }

        return $tax;
    }

    public static function save_cart_data($cart, $product, $price, $tax, $quantity)
    {
        $cart->quantity = $quantity;
        $cart->product_id = $product->id;
        $cart->owner_id = $product->user_id;
        $cart->price = $price;
        $cart->tax = $tax;
        $cart->product_referral_code = null;
        $cart->status = 1;

        if (Cookie::has('referred_product_id') && Cookie::get('referred_product_id') == $product->id) {
            $cart->product_referral_code = Cookie::get('product_referral_code');
        }

        // Cart::create($data);
        $cart->save();
    }

    public static function check_auction_in_cart($carts)
    {
        foreach ($carts as $cart) {
            if ($cart->product->auction_product == 1) {
                return true;
            }
        }

        return false;
    }
}
