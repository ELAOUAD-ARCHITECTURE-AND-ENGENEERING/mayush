<?php

namespace App\Http\Middleware;

use Closure;
use Illuminate\Http\Request;

class AgentDiscoveryHeaders
{
    public function handle(Request $request, Closure $next)
    {
        $response = $next($request);

        if (!$this->shouldAdvertise($request, $response)) {
            return $response;
        }

        $links = [
            '</.well-known/api-catalog>; rel="api-catalog"; type="application/linkset+json"',
            '</openapi.json>; rel="service-desc"; type="application/openapi+json"',
            '</docs/api>; rel="service-doc"; type="text/html"',
            '</.well-known/agent-skills/index.json>; rel="https://agentskills.io/rels/skills-index"; type="application/json"',
        ];

        $response->headers->set('Link', implode(', ', $links));

        return $response;
    }

    private function shouldAdvertise(Request $request, $response): bool
    {
        if (!$request->isMethod('GET') || !$response->isSuccessful()) {
            return false;
        }

        if ($request->is('admin*') || $request->is('api*') || $request->is('seller*') || $request->is('customer*')) {
            return false;
        }

        if ($request->is('cart*') || $request->is('checkout*') || $request->is('wallet*')) {
            return false;
        }

        $contentType = strtolower((string) $response->headers->get('Content-Type'));

        return $contentType === '' || str_contains($contentType, 'text/html');
    }
}
