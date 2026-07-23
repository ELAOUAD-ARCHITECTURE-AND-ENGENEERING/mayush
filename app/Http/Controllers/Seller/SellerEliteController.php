<?php

namespace App\Http\Controllers\Seller;

use App\Http\Controllers\Controller;
use App\Models\EliteSubscription;
use App\Models\BusinessSetting;
use App\Models\User;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Session;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Facades\Notification;
use App\Services\Notifications\NotificationDispatcher;
use App\Notifications\EliteApplicationNotification;
use Carbon\Carbon;

class SellerEliteController extends Controller
{
    /**
     * Show the Elite Artisan landing page.
     * - New applicants see the benefits page
     * - Pending applicants see waiting state
     * - Active subscribers see profile editor
     */
    public function index()
    {
        $shop         = Auth::user()->shop;
        $subscription = EliteSubscription::where('shop_id', $shop->id)->latest()->first();

        // Active Elite subscription → show profile editor
        if ($shop->isElite()) {
            return view('seller.elite.profile', compact('shop', 'subscription'));
        }

        // Pending subscription → show pending state
        if ($subscription && $subscription->status == 'pending') {
            return view('seller.elite.pending', compact('shop', 'subscription'));
        }

        // No subscription or expired/rejected → show benefits landing
        return view('seller.elite.benefits', compact('shop'));
    }

    /**
     * Show the pricing comparison page.
     */
    public function pricing()
    {
        $shop = Auth::user()->shop;

        // Prevent access if already elite or pending
        $existing = EliteSubscription::where('shop_id', $shop->id)
            ->whereIn('status', ['active', 'pending'])
            ->first();

        if ($existing) {
            flash(translate('You already have an active or pending Elite subscription.'))->warning();
            return redirect()->route('seller.elite.index');
        }

        $monthly_price = (float) get_setting('elite_monthly_price', 19.99);
        $yearly_price  = (float) get_setting('elite_yearly_price', 179.99);
        $yearly_savings = round(($monthly_price * 12) - $yearly_price, 2);
        $yearly_discount_pct = $monthly_price > 0 ? round(($yearly_savings / ($monthly_price * 12)) * 100) : 0;

        return view('seller.elite.pricing', compact(
            'shop', 'monthly_price', 'yearly_price', 'yearly_savings', 'yearly_discount_pct'
        ));
    }

    /**
     * Show the order recap / summary page before payment.
     */
    public function recap(Request $request)
    {
        $request->validate([
            'billing_cycle' => 'required|in:monthly,yearly',
        ]);

        $shop = Auth::user()->shop;
        $billing_cycle = $request->billing_cycle;

        $monthly_price = (float) get_setting('elite_monthly_price', 19.99);
        $yearly_price  = (float) get_setting('elite_yearly_price', 179.99);

        $subtotal = $billing_cycle === 'yearly' ? $yearly_price : $monthly_price;
        $tax_rate = (float) get_setting('elite_tax_rate', 20);
        $tax_amount = round($subtotal * ($tax_rate / 100), 2);
        $total = round($subtotal + $tax_amount, 2);

        $next_billing_date = $billing_cycle === 'yearly'
            ? Carbon::now()->addYear()->format('d M Y')
            : Carbon::now()->addMonth()->format('d M Y');

        $plan_name = $billing_cycle === 'yearly'
            ? translate('Elite Artisan — Yearly Plan')
            : translate('Elite Artisan — Monthly Plan');

        return view('seller.elite.recap', compact(
            'shop', 'billing_cycle', 'subtotal', 'tax_rate', 'tax_amount', 'total',
            'next_billing_date', 'plan_name'
        ));
    }

    /**
     * Create the pending subscription and redirect to CMI payment gateway.
     */
    public function processPayment(Request $request)
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
            return redirect()->route('seller.elite.index');
        }

        $monthly_price = (float) get_setting('elite_monthly_price', 19.99);
        $yearly_price  = (float) get_setting('elite_yearly_price', 179.99);
        $subtotal = $request->billing_cycle === 'yearly' ? $yearly_price : $monthly_price;
        $tax_rate = (float) get_setting('elite_tax_rate', 20);
        $tax_amount = round($subtotal * ($tax_rate / 100), 2);
        $total = round($subtotal + $tax_amount, 2);

        // Create pending subscription
        $subscription = EliteSubscription::create([
            'shop_id'        => $shop->id,
            'billing_cycle'  => $request->billing_cycle,
            'amount_paid'    => $total,
            'status'         => 'pending',
            'payment_method' => 'cmi',
        ]);

        Log::info('Elite Payment: Subscription created', [
            'subscription_id' => $subscription->id,
            'shop_id'         => $shop->id,
            'amount'          => $total,
            'billing_cycle'   => $request->billing_cycle,
        ]);

        // Set session for CMI payment flow (same pattern as cart/wallet/seller_package)
        Session::put('payment_type', 'elite_payment');
        Session::put('payment_data', [
            'subscription_id' => $subscription->id,
            'amount'          => $total,
        ]);
        Session::put('elite_subscription_id', $subscription->id);

        return redirect()->route('cmi.pay');
    }

    /**
     * Handle successful payment return — show confirmation receipt.
     */
    public function paymentSuccess()
    {
        $subscription_id = Session::get('elite_subscription_id');

        if (!$subscription_id) {
            flash(translate('Invalid session. Please contact support.'))->error();
            return redirect()->route('seller.elite.index');
        }

        $subscription = EliteSubscription::with('shop.user')->find($subscription_id);

        if (!$subscription) {
            flash(translate('Subscription not found.'))->error();
            return redirect()->route('seller.elite.index');
        }

        $shop = $subscription->shop;

        // Clean up session
        Session::forget(['payment_type', 'payment_data', 'elite_subscription_id']);

        return view('seller.elite.payment_success', compact('subscription', 'shop'));
    }

    /**
     * Handle failed payment return — show failure receipt with retry.
     */
    public function paymentFail()
    {
        $subscription_id = Session::get('elite_subscription_id');
        $payment_error = Session::get('payment_error', translate('Payment was cancelled or failed.'));

        $subscription = null;
        if ($subscription_id) {
            $subscription = EliteSubscription::find($subscription_id);
        }

        return view('seller.elite.payment_failed', compact('subscription', 'payment_error'));
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
        return redirect()->route('seller.elite.index');
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

    /**
     * Activate a subscription after successful payment (called from CmiController callback).
     */
    public static function activateSubscription($subscriptionId, $paymentDetails, $transactionId = null)
    {
        $subscription = EliteSubscription::findOrFail($subscriptionId);

        $expiresAt = $subscription->billing_cycle === 'yearly'
            ? Carbon::now()->addYear()
            : Carbon::now()->addMonth();

        $subscription->update([
            'status'          => 'active',
            'expires_at'      => $expiresAt,
            'transaction_id'  => $transactionId,
            'payment_details' => $paymentDetails,
        ]);

        Log::info('Elite Payment: Subscription activated', [
            'subscription_id' => $subscription->id,
            'transaction_id'  => $transactionId,
            'expires_at'      => $expiresAt,
        ]);

        // Notify all admin users
        try {
            $admins = User::where('user_type', 'admin')->get();
            if ($admins->isNotEmpty()) {
                if (config('notifications_v2.enabled')) {
                    app(NotificationDispatcher::class)->dispatch(
                        'seller.status',
                        'elite_subscription',
                        $subscription->id,
                        'status:active:'.$subscription->updated_at?->format('U.u'),
                        $admins->pluck('id'),
                        [
                            'seller_id' => $subscription->user_id,
                            'status' => 'elite_subscription_active',
                            'title' => 'Elite subscription activated',
                            'message' => 'An Elite subscription has been activated.',
                        ]
                    );
                } else {
                    Notification::send($admins, new EliteApplicationNotification($subscription));
                }
            }
        } catch (\Exception $e) {
            Log::warning('Elite Payment: Admin notification failed', ['error' => $e->getMessage()]);
        }

        return $subscription;
    }
}
