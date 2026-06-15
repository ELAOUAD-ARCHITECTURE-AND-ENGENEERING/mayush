<?php

namespace App\Http\Middleware;

use Closure;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Symfony\Component\HttpFoundation\Response;

class StorefrontServerTiming
{
    public function handle(Request $request, Closure $next): Response
    {
        if (! config('storefront-performance.server_timing')) {
            return $next($request);
        }

        $startedAt = hrtime(true);
        DB::enableQueryLog();

        try {
            $response = $next($request);
            $queries = DB::getQueryLog();
        } finally {
            DB::disableQueryLog();
        }

        if (str_contains((string) $response->headers->get('Content-Type'), 'text/html')) {
            $appDuration = (hrtime(true) - $startedAt) / 1_000_000;
            $queryDuration = collect($queries)->sum('time');
            $response->headers->set(
                'Server-Timing',
                sprintf('app;dur=%.1f, db;dur=%.1f;desc="%d queries"', $appDuration, $queryDuration, count($queries))
            );
        }

        return $response;
    }
}
