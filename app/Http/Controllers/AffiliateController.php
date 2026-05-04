<?php

namespace App\Http\Controllers;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use App\Models\AffiliateConfig;
use App\Models\AffiliateLog;
use App\Models\AffiliateUser;
use App\Models\Order;
use App\Models\User;
use Illuminate\Support\Facades\View;
use Illuminate\Support\Facades\Response;
use Auth;

class AffiliateController extends Controller
{
    /**
     * Display affiliate dashboard for user
     */
    public function index()
    {
        $user = Auth::user();
        if (!$user->referral_code) {
            $user->referral_code = substr(md5($user->id . time()), 0, 10);
            $user->save();
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

        // Get commission percentage from config or default to 5%
        $commission_percentage = get_setting('affiliate_commission_percentage', 5);
        $amount = ($order->grand_total * $commission_percentage) / 100;

        if ($amount > 0) {
            $affiliateLog = new AffiliateLog();
            $affiliateLog->user_id = $referrer->id;
            $affiliateLog->order_id = $order->id;
            $affiliateLog->amount = $amount;
            $affiliateLog->status = 1; // Mark as awarded
            $affiliateLog->save();

            $affiliateUser->balance += $amount;
            $affiliateUser->save();
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
     * User applies to become an affiliate
     */
    public function apply(Request $request)
    {
        $user = Auth::user();
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
}