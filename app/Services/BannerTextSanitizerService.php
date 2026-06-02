<?php

namespace App\Services;

use DOMComment;
use DOMDocument;
use DOMElement;
use DOMNode;

class BannerTextSanitizerService
{
    private const TEXT_SETTING_KEYS = [
        'home_banner1_titles',
        'home_banner1_descriptions',
        'home_banner2_titles',
        'home_banner2_descriptions',
        'home_banner3_titles',
        'home_banner3_descriptions',
        'home_banner4_titles',
        'home_banner4_descriptions',
    ];

    private const ALLOWED_TAGS = [
        'b',
        'br',
        'div',
        'em',
        'i',
        'p',
        's',
        'span',
        'strike',
        'strong',
        'u',
    ];

    private const REMOVED_TAGS = [
        'iframe',
        'object',
        'script',
        'style',
        'svg',
        'template',
    ];

    private const FONT_FAMILIES = [
        'arial',
        'helvetica',
        'inherit',
        'inter',
        'outfit',
        'playfair display',
        'public sans',
        'sans-serif',
        'serif',
        'system-ui',
    ];

    public function isBannerTextSetting(string $settingKey): bool
    {
        return in_array($settingKey, self::TEXT_SETTING_KEYS, true);
    }

    public function sanitizeArray($items): array
    {
        if (!is_array($items)) {
            return [$this->sanitize(is_scalar($items) ? (string) $items : '')];
        }

        return array_map(function ($item): string {
            return $this->sanitize(is_scalar($item) ? (string) $item : '');
        }, $items);
    }

    public function sanitizeStoredValue(?string $value): string
    {
        $decoded = json_decode((string) $value, true);

        if (!is_array($decoded)) {
            $decoded = [];
        }

        return json_encode($this->sanitizeArray($decoded));
    }

    public function sanitize(?string $html): string
    {
        $html = trim((string) $html);

        if ($html === '') {
            return '';
        }

        $document = new DOMDocument('1.0', 'UTF-8');
        $previousErrors = libxml_use_internal_errors(true);
        $document->loadHTML(
            '<?xml encoding="utf-8" ?><div data-banner-root="1">' . $html . '</div>',
            LIBXML_HTML_NOIMPLIED | LIBXML_HTML_NODEFDTD
        );
        libxml_clear_errors();
        libxml_use_internal_errors($previousErrors);

        $root = $document->getElementsByTagName('div')->item(0);

        if (!$root instanceof DOMElement) {
            return '';
        }

        $this->sanitizeChildren($root);

        $safeHtml = '';
        foreach (iterator_to_array($root->childNodes) as $child) {
            $safeHtml .= $document->saveHTML($child);
        }

        return trim($safeHtml);
    }

    private function sanitizeChildren(DOMNode $parent): void
    {
        foreach (iterator_to_array($parent->childNodes) as $node) {
            $this->sanitizeNode($node);
        }
    }

    private function sanitizeNode(DOMNode $node): void
    {
        if ($node instanceof DOMComment) {
            $node->parentNode?->removeChild($node);
            return;
        }

        if (!$node instanceof DOMElement) {
            return;
        }

        $tag = strtolower($node->tagName);

        if (in_array($tag, self::REMOVED_TAGS, true)) {
            $node->parentNode?->removeChild($node);
            return;
        }

        $this->sanitizeChildren($node);

        if (!in_array($tag, self::ALLOWED_TAGS, true)) {
            $this->unwrap($node);
            return;
        }

        $safeStyle = $this->sanitizeStyle($node->getAttribute('style'));

        while ($node->attributes->length > 0) {
            $node->removeAttributeNode($node->attributes->item(0));
        }

        if ($safeStyle !== '') {
            $node->setAttribute('style', $safeStyle);
        }
    }

    private function unwrap(DOMElement $node): void
    {
        $parent = $node->parentNode;

        if (!$parent) {
            return;
        }

        while ($node->firstChild) {
            $parent->insertBefore($node->firstChild, $node);
        }

        $parent->removeChild($node);
    }

    private function sanitizeStyle(string $styleText): string
    {
        $styles = [];

        foreach (explode(';', $styleText) as $declaration) {
            if (!str_contains($declaration, ':')) {
                continue;
            }

            [$property, $value] = array_map('trim', explode(':', $declaration, 2));
            $property = strtolower($property);
            $value = trim($value);

            if ($value === '' || str_contains(strtolower($value), '!important')) {
                continue;
            }

            $safeValue = $this->sanitizeStyleValue($property, $value);

            if ($safeValue !== null) {
                $styles[$property] = $property . ': ' . $safeValue;
            }
        }

        return implode('; ', $styles);
    }

    private function sanitizeStyleValue(string $property, string $value): ?string
    {
        return match ($property) {
            'background-color', 'color' => $this->safeColor($value),
            'font-family' => $this->safeFontFamily($value),
            'font-size' => $this->safeLength($value, false),
            'font-style' => preg_match('/^(italic|normal)$/i', $value) === 1 ? strtolower($value) : null,
            'font-weight' => preg_match('/^(normal|bold|[1-9]00)$/i', $value) === 1 ? strtolower($value) : null,
            'letter-spacing' => strtolower($value) === 'normal' ? 'normal' : $this->safeLength($value, true),
            'line-height' => $this->safeLineHeight($value),
            'text-align' => preg_match('/^(left|center|right|justify|start|end)$/i', $value) === 1 ? strtolower($value) : null,
            'text-decoration', 'text-decoration-line' => $this->safeTextDecoration($value),
            default => null,
        };
    }

    private function safeColor(string $value): ?string
    {
        $value = trim($value);

        if (preg_match('/^#[0-9a-f]{3,8}$/i', $value) === 1) {
            return strtolower($value);
        }

        if (preg_match('/^rgba?\(\s*[0-9.%\s,]+\)$/i', $value) === 1) {
            return preg_replace('/\s+/', ' ', $value);
        }

        return preg_match('/^(black|currentcolor|gray|grey|transparent|white)$/i', $value) === 1
            ? strtolower($value)
            : null;
    }

    private function safeFontFamily(string $value): ?string
    {
        $families = array_filter(array_map(function (string $family): string {
            return trim($family, " \t\n\r\0\x0B'\"");
        }, explode(',', $value)));

        if ($families === []) {
            return null;
        }

        foreach ($families as $family) {
            if (!in_array(strtolower($family), self::FONT_FAMILIES, true)) {
                return null;
            }
        }

        return implode(', ', array_map(function (string $family): string {
            return str_contains($family, ' ') ? "'" . $family . "'" : $family;
        }, $families));
    }

    private function safeLength(string $value, bool $allowNegative): ?string
    {
        $pattern = $allowNegative
            ? '/^-?(?:\d+(?:\.\d+)?|\.\d+)(px|em|rem)$/i'
            : '/^(?:\d+(?:\.\d+)?|\.\d+)(px|em|rem)$/i';

        return preg_match($pattern, trim($value)) === 1 ? strtolower(trim($value)) : null;
    }

    private function safeLineHeight(string $value): ?string
    {
        $value = trim($value);

        if (preg_match('/^(normal|(?:\d+(?:\.\d+)?|\.\d+))$/i', $value) === 1) {
            return strtolower($value);
        }

        return $this->safeLength($value, false);
    }

    private function safeTextDecoration(string $value): ?string
    {
        $tokens = preg_split('/\s+/', strtolower(trim($value))) ?: [];
        $allowed = ['line-through', 'none', 'underline'];

        if ($tokens === [] || array_diff($tokens, $allowed) !== []) {
            return null;
        }

        return implode(' ', array_unique($tokens));
    }
}
