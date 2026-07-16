<?php

namespace App\Http\Middleware;

use Closure;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;

class PortfolioView
{
    /**
     * Handle an incoming request.
     *
     * @param  \Illuminate\Http\Request  $request
     * @param  \Closure(\Illuminate\Http\Request): (\Illuminate\Http\Response|\Illuminate\Http\RedirectResponse)  $next
     * @return \Illuminate\Http\Response|\Illuminate\Http\RedirectResponse
     */
    public function handle(Request $request, Closure $next)
    {
    if (!get_setting('portfolio_landing')) {
        return $next($request);
    }

    $user = auth()->user();

    // Guest users
    if (!$user) {
        return redirect()->route('home');
    }

    // Seller onboarding must use the authoritative shop state.
    if ($user->user_type === 'seller' && (!$user->shop || !$user->shop->isFullyApproved())) {
        return redirect()->route('home');
    }

    return $next($request);
    }
}
