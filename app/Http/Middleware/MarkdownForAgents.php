<?php

namespace App\Http\Middleware;

use Closure;
use Illuminate\Http\Request;

class MarkdownForAgents
{
    public function handle(Request $request, Closure $next)
    {
        $response = $next($request);

        if (!$this->shouldConvert($request, $response)) {
            return $response;
        }

        $markdown = $this->htmlToMarkdown((string) $response->getContent(), $request->fullUrl());
        $response->setContent($markdown);
        $response->headers->set('Content-Type', 'text/markdown; charset=UTF-8');
        $response->headers->set('X-Markdown-Tokens', (string) max(1, str_word_count($markdown)));

        return $response;
    }

    private function shouldConvert(Request $request, $response): bool
    {
        if (!$request->isMethod('GET') || !$response->isSuccessful()) {
            return false;
        }

        if (!str_contains(strtolower((string) $request->headers->get('Accept')), 'text/markdown')) {
            return false;
        }

        if ($request->is('admin*') || $request->is('api*') || $request->is('seller*') || $request->is('customer*')) {
            return false;
        }

        return str_contains(strtolower((string) $response->headers->get('Content-Type')), 'text/html');
    }

    private function htmlToMarkdown(string $html, string $url): string
    {
        $title = $this->firstMatch('/<title[^>]*>(.*?)<\/title>/is', $html);
        $description = $this->firstMatch('/<meta[^>]+name=["\']description["\'][^>]+content=["\']([^"\']*)["\']/is', $html)
            ?: $this->firstMatch('/<meta[^>]+content=["\']([^"\']*)["\'][^>]+name=["\']description["\']/is', $html);
        $canonical = $this->firstMatch('/<link[^>]+rel=["\']canonical["\'][^>]+href=["\']([^"\']*)["\']/is', $html)
            ?: $url;

        preg_match_all('/<h([1-3])[^>]*>(.*?)<\/h\1>/is', $html, $headingMatches, PREG_SET_ORDER);
        preg_match_all('/<a[^>]+href=["\']([^"\']+)["\'][^>]*>(.*?)<\/a>/is', $html, $linkMatches, PREG_SET_ORDER);

        $lines = [];
        $lines[] = '# ' . ($this->clean($title) ?: 'Mayush');
        $lines[] = '';
        $lines[] = 'Canonical: ' . $this->clean($canonical);

        if ($description) {
            $lines[] = '';
            $lines[] = $this->clean($description);
        }

        if ($headingMatches) {
            $lines[] = '';
            $lines[] = '## Page Headings';
            foreach (array_slice($headingMatches, 0, 20) as $match) {
                $heading = $this->clean($match[2]);
                if ($heading !== '') {
                    $lines[] = str_repeat('#', min(3, ((int) $match[1]) + 1)) . ' ' . $heading;
                }
            }
        }

        $links = [];
        foreach ($linkMatches as $match) {
            $text = $this->clean($match[2]);
            $href = $this->clean($match[1]);
            if ($text !== '' && $href !== '' && !str_starts_with($href, 'javascript:')) {
                $links[$text . $href] = '- [' . $text . '](' . $href . ')';
            }
            if (count($links) >= 40) {
                break;
            }
        }

        if ($links) {
            $lines[] = '';
            $lines[] = '## Important Links';
            $lines = array_merge($lines, array_values($links));
        }

        return implode("\n", $lines) . "\n";
    }

    private function firstMatch(string $pattern, string $html): string
    {
        if (!preg_match($pattern, $html, $matches)) {
            return '';
        }

        return $this->clean($matches[1]);
    }

    private function clean(string $value): string
    {
        $value = html_entity_decode(strip_tags($value), ENT_QUOTES | ENT_HTML5, 'UTF-8');
        return trim(preg_replace('/\s+/', ' ', $value));
    }
}
