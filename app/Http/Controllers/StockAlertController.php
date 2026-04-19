<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use App\Models\StockSubscription;
use Auth;
use App\Models\Product;

class StockAlertController extends Controller
{
    /**
     * Subscribe to a product restock alert.
     */
    public function subscribe(Request $request)
    {
        $productId = $request->input('product_id');
        $variant = $request->input('variant'); // Assuming 'variant' is passed from the form
        $email = $request->input('email');
        
        if (Auth::check()) {
            $user_id = Auth::id();
            $email = $email ?? Auth::user()->email;
        } else {
            $user_id = null;
            if (!$email) {
                return response()->json([
                    'status' => 'error',
                    'message' => translate('Please provide your email address.')
                ], 422);
            }
        }

        $product = Product::find($productId);
        if (!$product) {
            return response()->json([
                'status' => 'error',
                'message' => translate('Product not found.')
            ], 404);
        }

        // Prevent duplicate active subscriptions
        $existing = StockSubscription::pending()
            ->where('product_id', $productId)
            ->where('email', $email)
            ->when($variant, function($q) use ($variant) {
                return $q->where('variant', $variant);
            })
            ->first();

        if ($existing) {
            return response()->json([
                'status' => 'info',
                'message' => translate('You are already subscribed to alerts for this product.')
            ]);
        }

        StockSubscription::create([
            'user_id' => $user_id,
            'product_id' => $productId,
            'variant' => $variant,
            'email' => $email,
            'notified_at' => null
        ]);

        return response()->json([
            'status' => 'success',
            'message' => translate('Success! We will notify you when this item is back in stock.')
        ]);
    }
}
