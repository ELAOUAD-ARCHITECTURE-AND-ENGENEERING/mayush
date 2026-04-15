<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use App\Models\StockAlertSubscription;
use Auth;
use App\Models\Product;

class StockAlertController extends Controller
{
    /**
     * Subscribe to a product restock alert.
     */
    public function subscribe(Request $request)
    {
        if (!Auth::check()) {
            return response()->json([
                'status' => 'error',
                'message' => translate('Please login to subscribe to stock alerts.')
            ], 401);
        }

        $productId = $request->input('product_id');
        $product = Product::find($productId);

        if (!$product) {
            return response()->json([
                'status' => 'error',
                'message' => translate('Product not found.')
            ], 404);
        }

        // Prevent duplicate subscriptions
        $existing = StockAlertSubscription::where('user_id', Auth::id())
            ->where('product_id', $productId)
            ->where('notified', 0)
            ->first();

        if ($existing) {
            return response()->json([
                'status' => 'info',
                'message' => translate('You are already subscribed to alerts for this product.')
            ]);
        }

        StockAlertSubscription::create([
            'user_id' => Auth::id(),
            'product_id' => $productId,
            'notified' => 0
        ]);

        return response()->json([
            'status' => 'success',
            'message' => translate('Success! We will notify you when this item is back in stock.')
        ]);
    }
}
