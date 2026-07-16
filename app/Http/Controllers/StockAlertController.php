<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use App\Models\StockSubscription;
use Auth;
use App\Models\Product;
use Illuminate\Validation\Rule;

class StockAlertController extends Controller
{
    /**
     * Subscribe to a product restock alert.
     */
    public function subscribe(Request $request)
    {
        $validated = $request->validate([
            'product_id' => ['required', 'integer', 'exists:products,id'],
            'variant' => ['nullable', 'string', 'max:255'],
            'email' => [Rule::requiredIf(!Auth::check()), 'nullable', 'email', 'max:255'],
        ]);

        $productId = $validated['product_id'];
        $variant = $validated['variant'] ?? null;
        $email = $validated['email'] ?? null;
        
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

        $product = Product::publiclyVisible()->findOrFail($productId);

        // Prevent duplicate active subscriptions
        $existing = StockSubscription::pending()
            ->where('product_id', $productId)
            ->where('email', $email)
            ->when($variant, function ($q) use ($variant) {
                return $q->where('variant', $variant);
            }, function ($q) {
                return $q->whereNull('variant');
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
