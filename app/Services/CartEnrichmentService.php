<?php

namespace App\Services;

use App\Models\Product;
use App\Models\Cart;
use Auth;

class CartEnrichmentService
{
    /**
     * Get accent items that match the style of products in the cart.
     * Accent categories: Lighting, Decor, Textiles, etc.
     */
    public static function getSuggestions($limit = 2)
    {
        $user_id = Auth::check() ? Auth::user()->id : null;
        $temp_user_id = session()->get('temp_user_id');

        $query = Cart::query();
        if ($user_id) {
            $query->where('user_id', $user_id);
        } else {
            $query->where('temp_user_id', $temp_user_id);
        }

        $cartItems = $query->with('product')->get();
        if ($cartItems->isEmpty()) {
            return collect();
        }

        $cartProductIds = $cartItems->pluck('product_id')->toArray();
        $tags = [];
        
        foreach ($cartItems as $item) {
            if ($item->product && $item->product->tags) {
                $productTags = explode(',', $item->product->tags);
                $tags = array_merge($tags, array_map('trim', $productTags));
            }
        }

        $tags = array_unique(array_filter($tags));

        if (empty($tags)) {
            // Fallback: suggest from accent categories without tag matching if no tags found
            return filter_products(Product::query())
                ->whereIn('category_id', [1, 33]) // Lighting, Textiles
                ->whereNotIn('id', $cartProductIds)
                ->inRandomOrder()
                ->take($limit)
                ->get();
        }

        // Matching logic: Find products in accent categories
        // We broadly search for accessories, lighting, decor based on name keywords if IDs are inconsistent
        $suggestionQuery = filter_products(Product::query())
            ->whereNotIn('id', $cartProductIds);

        if (!empty($tags)) {
            $suggestionQuery->where(function($q) use ($tags) {
                foreach ($tags as $tag) {
                    $q->orWhere('tags', 'like', '%' . $tag . '%');
                    $q->orWhere('meta_keywords', 'like', '%' . $tag . '%');
                }
            });
        }

        $results = $suggestionQuery->inRandomOrder()->take($limit)->get();

        if ($results->isEmpty()) {
            // Ultimate fallback: Suggest best sellers or random approved products
            return filter_products(Product::query())
                ->whereNotIn('id', $cartProductIds)
                ->orderBy('num_of_sale', 'desc')
                ->take($limit)
                ->get();
        }

        return $results;
    }
}
