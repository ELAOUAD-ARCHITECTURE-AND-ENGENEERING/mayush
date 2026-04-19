<?php

namespace App\Http\Middleware;

use Closure;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Log;

class CmiIpWhitelist
{
    /**
     * Handle an incoming request.
     *
     * @param  \Illuminate\Http\Request  $request
     * @param  \Closure  $next
     * @return mixed
     */
    public function handle(Request $request, Closure $next)
    {
        $allowedIps = array_values(array_filter(array_map('trim', config('cmi.allowed_ips', []))));

        if (empty($allowedIps)) {
            return $next($request);
        }

        $clientIp = $request->ip();

        if (!in_array($clientIp, $allowedIps, true)) {
            Log::critical('CMI Webhook Blocked: Unauthorized IP Address', [
                'ip' => $clientIp,
                'url' => $request->fullUrl(),
            ]);
            return response('UNAUTHORIZED IP', 403)->header('Content-Type', 'text/plain');
        }

        return $next($request);
    }
}
