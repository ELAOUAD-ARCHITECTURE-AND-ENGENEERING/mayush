<?php

namespace App\Services;

use App\Models\User;
use App\Models\AffiliateConfig;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Facades\Cookie;
use Illuminate\Http\Request;
use App\Http\Controllers\AffiliateController;

class AuthService
{
    /**
     * Handle the registration logic including referral cookies.
     */
    public function processRegistrationReferral(Request $request)
    {
        if ($request->has('referral_code') && addon_is_activated('affiliate_system')) {
            try {
                $affiliate_validation_time = AffiliateConfig::where('type', 'validation_time')->first();
                $cookie_minute = 30 * 24 * 60; // 30 days default in minutes
                
                if ($affiliate_validation_time) {
                    $cookie_minute = $affiliate_validation_time->value * 60;
                }

                Cookie::queue('referral_code', $request->referral_code, $cookie_minute);
                $referred_by_user = User::where('referral_code', $request->referral_code)->first();

                if ($referred_by_user) {
                    $affiliateController = new AffiliateController;
                    $affiliateController->processAffiliateStats($referred_by_user->id, 1, 0, 0, 0);
                }
            } catch (\Exception $e) {
                // Silently fail if affiliate processing errors
            }
        }
    }

    /**
     * Handle authentication logic from cart or other specific logins.
     */
    public function authenticateUser(array $credentials, $remember = false)
    {
        $user = null;
        
        if (!empty($credentials['phone'])) {
            $user = User::whereIn('user_type', ['customer', 'seller'])
                ->where('phone', "+" . $credentials['country_code'] . $credentials['phone'])
                ->first();
        } elseif (!empty($credentials['email'])) {
            $user = User::whereIn('user_type', ['customer', 'seller'])
                ->where('email', $credentials['email'])
                ->first();
        }

        if ($user && Hash::check($credentials['password'], $user->password)) {
            Auth::login($user, $remember);
            return true;
        }

        return false;
    }

    /**
     * Get the appropriate login view based on route/context.
     */
    public function getLoginView($routeName)
    {
        $layout = get_setting('authentication_layout_select');
        
        if ($routeName == 'seller.login' && get_setting('vendor_system_activation') == 1) {
            return 'auth.' . $layout . '.seller_login';
        } 
        
        if ($routeName == 'deliveryboy.login' && addon_is_activated('delivery_boy')) {
            return 'auth.' . $layout . '.deliveryboy_login';
        }
        
        return 'auth.' . $layout . '.user_login';
    }

    /**
     * Determine the redirect route after login based on user type.
     */
    public function getDashboardRedirect(User $user)
    {
        if ($user->user_type == 'seller') {
            return 'seller.dashboard';
        } 
        
        if ($user->user_type == 'delivery_boy') {
            return 'deliveryboy.dashboard'; // Custom logic might be needed for view vs route
        }
        
        return 'dashboard';
    }
}
