<?php

namespace App\Services;

class HeroTitleSanitizerService
{
    public function sanitizeArray($titles): array
    {
        if (!is_array($titles)) {
            return [$this->sanitize($titles)];
        }

        return array_map(function ($title) {
            return $this->sanitize(is_scalar($title) ? (string) $title : '');
        }, $titles);
    }

    public function sanitize(?string $html): string
    {
        $html = (string) $html;

        if ($html === '') {
            return '';
        }

        $html = preg_replace('#<(script|style)\b[^>]*>.*?</\1>#is', '', $html) ?? '';
        $html = preg_replace('#</p>\s*<p[^>]*>#i', '<br>', $html) ?? '';
        $html = preg_replace('#</?(p|div|h[1-6])[^>]*>#i', '', $html) ?? '';
        $html = preg_replace('/\s+on[a-z]+\s*=\s*("|\').*?\1/is', '', $html) ?? '';
        $html = preg_replace('/\s+(href|src)\s*=\s*("|\')\s*javascript:.*?\2/is', '', $html) ?? '';
        $html = preg_replace_callback('/<font\b([^>]*)>/i', function (array $matches) {
            if (preg_match('/\scolor\s*=\s*("|\')(.*?)\1/is', $matches[1], $colorMatch)
                && $this->isSafeCssColor($colorMatch[2])
            ) {
                return '<span style="color: ' . htmlspecialchars(trim($colorMatch[2]), ENT_QUOTES, 'UTF-8') . ';">';
            }

            return '<span>';
        }, $html) ?? '';
        $html = preg_replace('/<\/font>/i', '</span>', $html) ?? '';
        $html = strip_tags($html, '<span><strong><b><em><i><u><br>');

        $html = preg_replace_callback('/<span\b([^>]*)>/i', function (array $matches) {
            $styles = [];
            $styleText = '';

            if (preg_match('/\sstyle\s*=\s*("|\')(.*?)\1/is', $matches[1], $styleMatch)) {
                $styleText = $styleMatch[2];
            }

            if (preg_match('/(?:^|;)\s*color\s*:\s*([^;]+)/i', $styleText, $colorMatch)) {
                $color = trim($colorMatch[1]);
                if ($this->isSafeCssColor($color)) {
                    $styles[] = 'color: ' . htmlspecialchars($color, ENT_QUOTES, 'UTF-8');
                }
            }

            if (preg_match('/(?:^|;)\s*font-weight\s*:\s*([^;]+)/i', $styleText, $weightMatch)
                && preg_match('/^(bold|[6-9]00)$/i', trim($weightMatch[1]))
            ) {
                $styles[] = 'font-weight: 700';
            }

            if (preg_match('/(?:^|;)\s*font-style\s*:\s*([^;]+)/i', $styleText, $styleMatchValue)
                && strtolower(trim($styleMatchValue[1])) === 'italic'
            ) {
                $styles[] = 'font-style: italic';
            }

            if (preg_match('/(?:^|;)\s*text-decoration(?:-line)?\s*:\s*([^;]+)/i', $styleText, $decorationMatch)
                && str_contains(strtolower($decorationMatch[1]), 'underline')
            ) {
                $styles[] = 'text-decoration: underline';
            }

            return $styles === [] ? '<span>' : '<span style="' . implode('; ', $styles) . ';">';
        }, $html) ?? '';

        $html = preg_replace('/<(strong|b|em|i|u)\b[^>]*>/i', '<$1>', $html) ?? '';
        $html = preg_replace('/<br\b[^>]*>/i', '<br>', $html) ?? '';

        return $html;
    }

    private function isSafeCssColor(string $color): bool
    {
        $color = trim($color);

        return preg_match('/^(#[0-9a-f]{3,8}|rgba?\([0-9,\s.]+\)|[a-z]+)$/i', $color) === 1;
    }
}
