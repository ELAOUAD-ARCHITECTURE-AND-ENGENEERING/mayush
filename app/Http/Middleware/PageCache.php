<?php

namespace App\Http\Middleware;

use Closure;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Cache;
use App\Services\StorefrontCacheService;

/**
 * Full-page cache for guest visitors on cacheable routes.
 *
 * Stores the rendered HTML response in Redis for a configurable TTL.
 * Only caches GET requests from unauthenticated visitors with 200 responses.
 * Cache is automatically invalidated when storefront revision bumps.
 */
class PageCache
{
    public function handle(Request $request, Closure $next, int $ttl = 300)
    {
        // Only cache GET requests from guests.
        if (! $request->isMethod('GET') || Auth::check()) {
            return $next($request);
        }

        // Skip if there's a session flash (e.g. after a redirect with message).
        if ($request->session()->has('_flash')) {
            $flash = $request->session()->get('_flash');
            if (! empty($flash['old']) || ! empty($flash['new'])) {
                return $next($request);
            }
        }

        $cacheService = app(StorefrontCacheService::class);
        $revision = $cacheService->revision();
        $locale = app()->getLocale();
        $key = "page-cache:v{$revision}:{$locale}:" . md5($request->fullUrl());

        $cached = Cache::get($key);
        if ($cached !== null) {
            return response($cached['body'], 200, [
                'Content-Type' => $cached['content_type'] ?? 'text/html; charset=UTF-8',
                'X-Page-Cache' => 'HIT',
            ]);
        }

        $response = $next($request);

        // Only cache successful HTML responses.
        if ($response->getStatusCode() === 200 && ! $response->isRedirection()) {
            $contentType = $response->headers->get('Content-Type', 'text/html; charset=UTF-8');

            if (str_contains($contentType, 'text/html')) {
                Cache::put($key, [
                    'body' => $response->getContent(),
                    'content_type' => $contentType,
                ], $ttl);

                $response->headers->set('X-Page-Cache', 'MISS');
            }
        }

        return $response;
    }
}
