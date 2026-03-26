<?php

namespace App\Http\Controllers\Backend;

use App\Http\Controllers\Controller;
use App\Models\EliteSubscription;
use App\Models\Shop;
use Illuminate\Http\Request;
use Carbon\Carbon;

class EliteSubscriptionController extends Controller
{
    /**
     * Show all Elite subscriptions with settings panel.
     */
    public function index(Request $request)
    {
        $subscriptions = EliteSubscription::with('shop.user')
            ->when($request->status, fn($q) => $q->where('status', $request->status))
            ->latest()
            ->paginate(15);

        $pending_count = EliteSubscription::pending()->count();

        return view('backend.elite.index', compact('subscriptions', 'pending_count'));
    }

    /**
     * Approve a pending subscription.
     */
    public function approve(Request $request, $id)
    {
        $subscription = EliteSubscription::findOrFail($id);

        $expiresAt = $subscription->billing_cycle === 'yearly'
            ? Carbon::now()->addYear()
            : Carbon::now()->addMonth();

        $subscription->update([
            'status'     => 'active',
            'expires_at' => $expiresAt,
        ]);

        flash(translate('Subscription approved successfully.'))->success();
        return back();
    }

    /**
     * Reject a pending subscription with optional notes.
     */
    public function reject(Request $request, $id)
    {
        $request->validate(['admin_notes' => 'nullable|string|max:500']);

        EliteSubscription::findOrFail($id)->update([
            'status'      => 'rejected',
            'admin_notes' => $request->admin_notes,
        ]);

        flash(translate('Subscription rejected.'))->warning();
        return back();
    }

    /**
     * Revoke an active subscription.
     */
    public function revoke(Request $request, $id)
    {
        EliteSubscription::findOrFail($id)->update([
            'status'      => 'expired',
            'admin_notes' => $request->admin_notes,
        ]);

        flash(translate('Subscription revoked.'))->warning();
        return back();
    }

    /**
     * Save Elite system-level settings.
     */
    public function updateSettings(Request $request)
    {
        $request->validate([
            'elite_system_active'  => 'required|in:0,1',
            'elite_monthly_price'  => 'required|numeric|min:0',
            'elite_yearly_price'   => 'required|numeric|min:0',
        ]);

        \App\Models\BusinessSetting::updateOrCreate(['type' => 'elite_system_active'],  ['value' => $request->elite_system_active]);
        \App\Models\BusinessSetting::updateOrCreate(['type' => 'elite_monthly_price'],  ['value' => $request->elite_monthly_price]);
        \App\Models\BusinessSetting::updateOrCreate(['type' => 'elite_yearly_price'],   ['value' => $request->elite_yearly_price]);

        flash(translate('Elite settings updated successfully.'))->success();
        return back();
    }
}
