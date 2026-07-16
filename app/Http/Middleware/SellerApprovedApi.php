<?php

namespace App\Http\Middleware;

use Closure;

class SellerApprovedApi
{
    public function handle($request, Closure $next)
    {
        // These endpoints are retained only as non-mutating compatibility
        // shims and must remain able to return their stable 410 response to
        // restricted sellers.
        if (in_array($request->path(), [
            'api/v2/seller/shop-verify-form',
            'api/v2/seller/shop-verify-info-store',
        ], true)) {
            return $next($request);
        }

        $shop = auth()->user()?->shop;

        if ($shop?->canManageProducts()) {
            return $next($request);
        }

        return response()->json([
            'message' => translate('Seller onboarding is not complete.'),
            'error' => 'seller_onboarding_incomplete',
        ], 403);
    }
}
