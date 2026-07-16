<?php

namespace App\Http\Middleware;

use Closure;
use Auth;

class IsSeller
{
    /**
     * Handle an incoming request.
     *
     * @param  \Illuminate\Http\Request  $request
     * @param  \Closure  $next
     * @return mixed
     */
    public function handle($request, Closure $next)
    {
        if (Auth::check() && isSeller() && !Auth::user()->banned) {
            $routeName = $request->route()?->getName() ?? '';
            $allowed = $routeName === 'seller.dashboard'
                || str_starts_with($routeName, 'seller.onboarding.')
                || str_starts_with($routeName, 'seller.profile.')
                || str_starts_with($routeName, 'seller.notification.');

            if (!$allowed && !Auth::user()->shop) {
                abort(403);
            }

            if (!$allowed && !Auth::user()->shop?->canManageProducts()) {
                flash(translate('Please complete seller onboarding before using this feature.'))->warning();
                return redirect()->route('seller.onboarding.index');
            }

            return $next($request);
        }
        if (Auth::check() && can_switch_account_mode() && active_account_mode() === 'buyer') {
            return redirect()->route('dashboard');
        }
        else{
            abort(404);
        }
    }
}
