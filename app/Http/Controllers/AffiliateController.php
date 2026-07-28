<?php

namespace App\Http\Controllers;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use App\Models\AffiliateConfig;
use App\Models\AffiliateLog;
use App\Models\AffiliateStats;
use App\Models\AffiliateUser;
use App\Models\AffiliateWithdrawRequest;
use App\Models\Order;
use App\Models\User;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\View;
use Illuminate\Support\Str;
use Auth;

class AffiliateController extends Controller
{
    /**
     * Display affiliate dashboard for user
     */
    public function index()
    {
        if (Auth::user()->user_type == 'admin' || Auth::user()->user_type == 'staff') {
            return $this->configuration();
        }

        return $this->userDashboard();
    }

    public function userDashboard()
    {
        $user = Auth::user();
        $this->ensureReferralCode($user);

        if (!addon_is_activated('affiliate_system') || get_setting('affiliate_system_activation') == 0) {
            return view('frontend.user.affiliate.coming_soon', compact('user'));
        }
        
        $affiliate_user = AffiliateUser::where('user_id', $user->id)->first();
        $affiliate_logs = AffiliateLog::where('user_id', $user->id)->latest()->paginate(10);
        return View::make('frontend.user.affiliate.index', compact('affiliate_user', 'affiliate_logs', 'user'));
    }

    /**
     * Process affiliate commission for an order
     * Called by Helpers.php@calculateCommissionAffilationClubPoint
     */
    public function processAffiliatePoints(Order $order)
    {
        $user = $order->user;
        if (!$user || !$user->referred_by) {
            return;
        }

        $referrer = User::find($user->referred_by);
        if (!$referrer) {
            return;
        }

        $affiliateUser = AffiliateUser::where('user_id', $referrer->id)->where('status', 1)->first();
        if (!$affiliateUser) {
            return;
        }

        if (AffiliateLog::where('user_id', $referrer->id)->where('order_id', $order->id)->exists()) {
            return;
        }

        // Get commission percentage from config or default to 5%
        $commission_percentage = get_setting('affiliate_commission_percentage', 5);
        $amount = ($order->grand_total * $commission_percentage) / 100;

        if ($amount > 0) {
            DB::transaction(function () use ($affiliateUser, $referrer, $order, $amount) {
                $affiliateLog = new AffiliateLog();
                $affiliateLog->user_id = $referrer->id;
                $affiliateLog->order_id = $order->id;
                $affiliateLog->amount = $amount;
                $affiliateLog->status = 1; // Mark as awarded
                $affiliateLog->save();

                $affiliateUser->increment('balance', $amount);
            });
        }
    }

    /**
     * Display affiliate configuration for admin
     */
    public function configuration()
    {
        return View::make('backend.marketing.affiliate.index');
    }

    /**
     * Update affiliate settings
     */
    public function updateSettings(Request $request)
    {
        foreach ($request->types as $key => $type) {
            set_setting($type, $request[$type]);
        }

        flash(translate('Affiliate settings updated successfully'))->success();
        return back();
    }

    /**
     * Store affiliate configuration options (alias for updateSettings)
     */
    public function affiliate_option_store(Request $request)
    {
        return $this->updateSettings($request);
    }

    /**
     * User applies to become an affiliate
     */
    public function apply(Request $request)
    {
        $user = Auth::user();
        $this->ensureReferralCode($user);

        $affiliate_user = AffiliateUser::where('user_id', $user->id)->first();
        
        if (!$affiliate_user) {
            $affiliate_user = new AffiliateUser();
            $affiliate_user->user_id = $user->id;
            $affiliate_user->status = 0; // Pending admin approval
            $affiliate_user->balance = 0;
            $affiliate_user->save();
            
            flash(translate('Your application has been submitted successfully.'))->success();
        } else {
            flash(translate('You have already applied.'))->info();
        }
        
        return back();
    }

    /**
     * Display affiliate users list for admin
     */
    public function users(Request $request)
    {
        $users = AffiliateUser::latest()->paginate(15);
        return View::make('backend.marketing.affiliate.users', compact('users'));
    }

    /**
     * Approve affiliate user
     */
    public function approve($id)
    {
        $affiliate_user = AffiliateUser::findOrFail($id);
        $affiliate_user->status = 1;
        $affiliate_user->save();
        
        flash(translate('Affiliate user has been approved successfully'))->success();
        return back();
    }

    public function approve_user($id)
    {
        return $this->approve($id);
    }

    public function reject_user($id)
    {
        $affiliate_user = AffiliateUser::findOrFail($id);
        $affiliate_user->status = 2;
        $affiliate_user->save();

        flash(translate('Affiliate user has been rejected successfully'))->success();

        return back();
    }

    public function updateApproved(Request $request)
    {
        $request->validate([
            'id' => ['required', 'exists:affiliate_users,id'],
            'status' => ['required', 'integer', 'in:0,1,2'],
        ]);

        $affiliate_user = AffiliateUser::findOrFail($request->id);
        $affiliate_user->status = (int) $request->status;
        $affiliate_user->save();

        return response()->json(['success' => true]);
    }

    public function user_payment_history()
    {
        $affiliate_logs = AffiliateLog::where('user_id', Auth::user()->id)->latest()->paginate(10);
        return View::make('frontend.user.affiliate.payment_history', compact('affiliate_logs'));
    }

    public function user_withdraw_request_history()
    {
        $withdraw_requests = \App\Models\AffiliateWithdrawRequest::where('user_id', Auth::user()->id)->latest()->paginate(10);
        return View::make('frontend.user.affiliate.withdraw_request_history', compact('withdraw_requests'));
    }

    public function withdraw_request_store(Request $request)
    {
        $request->validate([
            'amount' => ['required', 'numeric', 'min:1'],
        ]);

        $affiliate_user = AffiliateUser::where('user_id', Auth::id())->where('status', 1)->first();

        if (!$affiliate_user) {
            flash(translate('Your affiliate account is not approved yet.'))->error();

            return back()->withInput();
        }

        if ((float) $request->amount > (float) $affiliate_user->balance) {
            return back()
                ->withErrors(['amount' => translate('Withdrawal amount cannot exceed your affiliate balance.')])
                ->withInput();
        }

        AffiliateWithdrawRequest::create([
            'user_id' => Auth::id(),
            'amount' => $request->amount,
            'status' => 0,
        ]);

        flash(translate('Withdraw request has been submitted successfully.'))->success();

        return back();
    }

    public function payment_settings()
    {
        flash(translate('Affiliate payment settings are managed by the site administrator.'))->info();

        return redirect()->route('affiliate.user.index');
    }

    public function payment_settings_store(Request $request)
    {
        flash(translate('Affiliate payment settings are managed by the site administrator.'))->info();

        return redirect()->route('affiliate.user.index');
    }

    public function processAffiliateStats($user_id, $no_of_click = 0, $no_of_order_item = 0, $no_of_delivered = 0, $no_of_canceled = 0)
    {
        $stats = AffiliateStats::firstOrCreate(['user_id' => $user_id]);
        $stats->no_of_click += (int) $no_of_click;
        $stats->no_of_order_item += (int) $no_of_order_item;
        $stats->no_of_delivered += (int) $no_of_delivered;
        $stats->no_of_canceled += (int) $no_of_canceled;
        $stats->save();

        return $stats;
    }

    public function configs()
    {
        return $this->configuration();
    }

    public function config_store(Request $request)
    {
        return $this->updateSettings($request);
    }

    public function payment_history($id)
    {
        $affiliate_user = AffiliateUser::findOrFail($id);
        $affiliate_logs = AffiliateLog::where('user_id', $affiliate_user->user_id)->latest()->paginate(15);

        return View::make('frontend.user.affiliate.payment_history', compact('affiliate_logs'));
    }

    public function affiliate_withdraw_requests()
    {
        $withdraw_requests = AffiliateWithdrawRequest::with('user')->latest()->paginate(15);

        return View::make('frontend.user.affiliate.withdraw_request_history', compact('withdraw_requests'));
    }

    public function affiliate_withdraw_modal(Request $request)
    {
        $withdraw_request = AffiliateWithdrawRequest::findOrFail($request->id);

        return response()->json([
            'id' => $withdraw_request->id,
            'amount' => $withdraw_request->amount,
            'status' => $withdraw_request->status,
        ]);
    }

    public function withdraw_request_payment_store(Request $request)
    {
        $request->validate([
            'id' => ['required', 'exists:affiliate_withdraw_requests,id'],
        ]);

        $withdraw_request = AffiliateWithdrawRequest::findOrFail($request->id);
        $withdraw_request->status = 1;
        $withdraw_request->save();

        flash(translate('Withdraw request has been approved.'))->success();

        return back();
    }

    public function reject_withdraw_request($id)
    {
        $withdraw_request = AffiliateWithdrawRequest::findOrFail($id);
        $withdraw_request->status = 2;
        $withdraw_request->save();

        flash(translate('Withdraw request has been rejected.'))->success();

        return back();
    }

    public function affiliate_logs_admin()
    {
        $affiliate_logs = AffiliateLog::with('user')->latest()->paginate(15);

        return View::make('frontend.user.affiliate.payment_history', compact('affiliate_logs'));
    }

    private function ensureReferralCode(User $user): void
    {
        if ($user->referral_code) {
            return;
        }

        do {
            $code = Str::lower(Str::random(10));
        } while (User::where('referral_code', $code)->exists());

        $user->referral_code = $code;
        $user->save();
    }
}
