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
