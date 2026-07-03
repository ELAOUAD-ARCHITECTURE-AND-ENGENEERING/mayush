<?php

namespace App\Http\Middleware;

use Closure;
use Illuminate\Support\Facades\Auth;

class SellerApproved
{
    /**
     * Block access to product management routes for sellers whose
     * application has not yet been approved by an admin.
     */
    public function handle($request, Closure $next)
    {
        $user = Auth::user();

        if (!$user) {
            abort(404);
        }

        $shop = optional($user->shop);

        // Allow access if the shop is fully approved
        if ($shop && $shop->approval_status === 'approved') {
            return $next($request);
        }

        // Redirect pending / under_review / rejected sellers to the dashboard
        // with a descriptive message based on their current status.
        $status  = $shop ? $shop->approval_status : 'pending';
        $message = match ($status) {
            'under_review' => translate('Your documents are currently under review. Product management will be enabled after approval.'),
            'rejected'     => translate('Your application was rejected. Please resubmit your corrected documents to gain access.'),
            default        => translate('Your account is pending approval. Please complete document submission to unlock product management.'),
        };

        flash($message)->warning();
        return redirect()->route('seller.onboarding.index');
    }
}
