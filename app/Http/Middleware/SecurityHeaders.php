<?php

namespace App\Http\Middleware;

use Closure;
use Illuminate\Http\Request;
use Symfony\Component\HttpFoundation\Response;

class SecurityHeaders
{
    public function handle(Request $request, Closure $next): Response
    {
        $response = $next($request);

        $headers = config('security.headers');

        if (empty($headers)) {
            return $response;
        }

        // HSTS
        if (!empty($headers['hsts']['enabled'])) {
            $hsts = $headers['hsts'];
            $value = 'max-age=' . ($hsts['max_age'] ?? 31536000);
            if (!empty($hsts['include_subdomains'])) {
                $value .= '; includeSubDomains';
            }
            if (!empty($hsts['preload'])) {
                $value .= '; preload';
            }
            $response->headers->set('Strict-Transport-Security', $value);
        }

        // X-Frame-Options
        if (!empty($headers['x_frame_options'])) {
            $response->headers->set('X-Frame-Options', $headers['x_frame_options']);
        }

        // X-Content-Type-Options
        if (!empty($headers['x_content_type_options'])) {
            $response->headers->set('X-Content-Type-Options', $headers['x_content_type_options']);
        }

        // X-XSS-Protection
        if (!empty($headers['x_xss_protection'])) {
            $response->headers->set('X-XSS-Protection', $headers['x_xss_protection']);
        }

        // Referrer-Policy
        if (!empty($headers['referrer_policy'])) {
            $response->headers->set('Referrer-Policy', $headers['referrer_policy']);
        }

        // Permissions-Policy
        if (!empty($headers['permissions_policy'])) {
            $response->headers->set('Permissions-Policy', $headers['permissions_policy']);
        }

        // Content-Security-Policy
        if (!empty($headers['csp']['enabled'])) {
            $csp = $this->buildCsp($headers['csp']);
            if ($csp) {
                $response->headers->set('Content-Security-Policy', $csp);
            }
        }

        return $response;
    }

    private function buildCsp(array $csp): string
    {
        $directives = [];

        $map = [
            'default_src' => 'default-src',
            'script_src' => 'script-src',
            'style_src' => 'style-src',
            'img_src' => 'img-src',
            'font_src' => 'font-src',
            'connect_src' => 'connect-src',
            'media_src' => 'media-src',
            'object_src' => 'object-src',
            'frame_src' => 'frame-src',
            'frame_ancestors' => 'frame-ancestors',
            'base_uri' => 'base-uri',
            'form_action' => 'form-action',
        ];

        foreach ($map as $key => $directive) {
            if (!empty($csp[$key])) {
                $directives[] = $directive . ' ' . implode(' ', $csp[$key]);
            }
        }

        return implode('; ', $directives);
    }
}
