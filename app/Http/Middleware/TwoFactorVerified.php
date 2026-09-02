<?php

namespace App\Http\Middleware;

use Closure;
use Illuminate\Http\Request;

class TwoFactorVerified
{
    public function handle(Request $request, Closure $next)
    {
        $user = $request->user();

        if ($user
            && $user->hasTwoFactorEnabled()
            && !session('two_factor_verified')
            && !$request->routeIs('two-factor.*')
            && !$request->routeIs('logout')
        ) {
            return redirect()->route('two-factor.challenge');
        }

        return $next($request);
    }
}
