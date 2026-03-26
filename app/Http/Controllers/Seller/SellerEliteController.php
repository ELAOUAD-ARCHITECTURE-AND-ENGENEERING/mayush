<?php

namespace App\Http\Controllers\Seller;

use App\Http\Controllers\Controller;
use App\Models\EliteSubscription;
use Illuminate\Http\Request;
use Auth;

class SellerEliteController extends Controller
{
    /**
     * Show the Artisan Profile / Elite subscription page.
     */
    public function index()
    {
        $shop         = Auth::user()->shop;
        $subscription = EliteSubscription::where('shop_id', $shop->id)->latest()->first();
        $monthly_price = get_setting('elite_monthly_price', '19.99');
        $yearly_price  = get_setting('elite_yearly_price', '179.99');

        return view('seller.elite_profile', compact('shop', 'subscription', 'monthly_price', 'yearly_price'));
    }

    /**
     * Seller submits an Elite application.
     */
    public function apply(Request $request)
    {
        $request->validate([
            'billing_cycle' => 'required|in:monthly,yearly',
        ]);

        $shop = Auth::user()->shop;

        // Prevent duplicate active/pending subscriptions
        $existing = EliteSubscription::where('shop_id', $shop->id)
            ->whereIn('status', ['active', 'pending'])
            ->first();

        if ($existing) {
            flash(translate('You already have an active or pending Elite subscription.'))->warning();
            return back();
        }

        $price = $request->billing_cycle === 'yearly'
            ? get_setting('elite_yearly_price', 179.99)
            : get_setting('elite_monthly_price', 19.99);

        EliteSubscription::create([
            'shop_id'       => $shop->id,
            'billing_cycle' => $request->billing_cycle,
            'amount_paid'   => $price,
            'status'        => 'pending',
        ]);

        flash(translate('Your Elite application has been submitted. Please wait for admin approval.'))->success();
        return back();
    }

    /**
     * Seller cancels their pending subscription.
     */
    public function cancel()
    {
        $shop = Auth::user()->shop;

        EliteSubscription::where('shop_id', $shop->id)
            ->where('status', 'pending')
            ->latest()
            ->first()
            ?->update(['status' => 'expired']);

        flash(translate('Your Elite application has been cancelled.'))->info();
        return back();
    }

    /**
     * Seller updates their Artisan Profile (story, hero media, social links).
     */
    public function updateProfile(Request $request)
    {
        $request->validate([
            'story_title'   => 'nullable|string|max:255',
            'story_content' => 'nullable|string',
            'hero_media_id' => 'nullable|integer|exists:uploads,id',
            'social_links'  => 'nullable|array',
        ]);

        $shop = Auth::user()->shop;

        // Only Elite sellers can save profiles
        if (!$shop->isElite()) {
            flash(translate('You need an active Elite subscription to update your Artisan Profile.'))->error();
            return back();
        }

        $shop->update([
            'story_title'   => $request->story_title,
            'story_content' => $request->story_content,
            'hero_media_id' => $request->hero_media_id,
            'social_links'  => $request->social_links,
        ]);

        flash(translate('Artisan Profile updated successfully.'))->success();
        return back();
    }
}
