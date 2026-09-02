<?php

namespace App\Http\Middleware;

use Closure;
use Illuminate\Http\Request;

class Cors
{
    /**
     * Allowed origins for CORS requests.
     */
    private function allowedOrigins(): array
    {
        return array_filter([
            env('APP_URL'),
            env('VITE_ASSET_URL'),
            'https://mayushdesign.com',
            'https://www.mayushdesign.com',
        ]);
    }

    /**
     * Handle an incoming request and append CORS headers.
     */
    public function handle(Request $request, Closure $next)
    {
        $origin = $request->header('Origin');
        $allowedOrigin = $this->resolveOrigin($origin);

        if ($request->isMethod('OPTIONS')) {
            return response('', 200)
                ->header('Access-Control-Allow-Origin', $allowedOrigin)
                ->header('Access-Control-Allow-Methods', 'GET, POST, PUT, PATCH, DELETE, OPTIONS')
                ->header('Access-Control-Allow-Headers', 'Content-Type, Authorization, X-Requested-With, System-Key, App-Language, Accept')
                ->header('Access-Control-Max-Age', '86400');
        }

        $response = $next($request);

        if (method_exists($response, 'header') && $allowedOrigin) {
            $response->header('Access-Control-Allow-Origin', $allowedOrigin)
                ->header('Access-Control-Allow-Methods', 'GET, POST, PUT, PATCH, DELETE, OPTIONS')
                ->header('Access-Control-Allow-Headers', 'Content-Type, Authorization, X-Requested-With, System-Key, App-Language, Accept');
        }

        return $response;
    }

    private function resolveOrigin(?string $origin): ?string
    {
        if (!$origin) {
            return null;
        }

        // Allow exact match against allowlist
        if (in_array($origin, $this->allowedOrigins(), true)) {
            return $origin;
        }

        // Allow mobile app requests (no origin or capacitor/expo schemes)
        if (str_starts_with($origin, 'capacitor://') || str_starts_with($origin, 'exp://')) {
            return $origin;
        }

        return null;
    }
}
