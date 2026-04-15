<?php

namespace App\Http\Controllers;

use App\Services\LoyaltyService;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;

/**
 * LoyaltyController — Phase 4: Customer Allegiance & Loyalty
 *
 * Serves the customer-facing Loyalty Hub dashboard.
 */
class LoyaltyController extends Controller
{
    protected LoyaltyService $loyaltyService;

    public function __construct(LoyaltyService $loyaltyService)
    {
        $this->loyaltyService = $loyaltyService;
    }

    /**
     * Display the Loyalty Hub dashboard for the authenticated user.
     */
    public function hub()
    {
        $user = Auth::user();

        $tierProgress = $this->loyaltyService->getTierProgress($user);
        $pointBalance = $this->loyaltyService->getPointBalance($user);
        $pointHistory = $this->loyaltyService->getPointHistory($user, 10);
        $annualSpend  = $this->loyaltyService->getAnnualSpend($user);

        // Determine current tier meta
        $currentTier = $tierProgress['current_tier'];
        $tierLevel   = $currentTier ? ($currentTier->tier_level ?? 0) : 0;
        $tierMeta    = LoyaltyService::getTierMeta($tierLevel);

        return view('frontend.user.loyalty.hub', compact(
            'tierProgress',
            'pointBalance',
            'pointHistory',
            'annualSpend',
            'tierMeta',
            'tierLevel',
        ));
    }

    /**
     * Admin: Show loyalty configuration page.
     */
    public function adminConfig()
    {
        $tiers = \App\Models\CustomerPackage::where('is_loyalty_tier', true)
            ->orderBy('tier_level')
            ->get();

        return view('backend.setup_configurations.loyalty_config', compact('tiers'));
    }

    /**
     * Admin: Update loyalty tier configuration.
     */
    public function adminConfigUpdate(Request $request)
    {
        $tierData = $request->input('tiers', []);

        foreach ($tierData as $id => $data) {
            $pkg = \App\Models\CustomerPackage::find($id);
            if ($pkg) {
                $pkg->min_spend = $data['min_spend'] ?? 0;
                $pkg->loyalty_multiplier = $data['loyalty_multiplier'] ?? 1.0;
                $pkg->tier_level = $data['tier_level'] ?? 0;
                $pkg->is_loyalty_tier = true;
                $pkg->save();
            }
        }

        // Handle new tier creation
        if ($request->filled('new_tier_name')) {
            $newPkg = new \App\Models\CustomerPackage();
            $newPkg->name = $request->input('new_tier_name');
            $newPkg->amount = 0;
            $newPkg->product_upload = 0;
            $newPkg->min_spend = $request->input('new_tier_min_spend', 0);
            $newPkg->loyalty_multiplier = $request->input('new_tier_multiplier', 1.0);
            $newPkg->tier_level = $request->input('new_tier_level', 0);
            $newPkg->is_loyalty_tier = true;
            $newPkg->save();
        }

        flash(translate('Loyalty tiers updated successfully'))->success();
        return redirect()->route('admin.loyalty.config');
    }
}
