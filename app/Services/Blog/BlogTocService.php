<?php

namespace App\Services\Blog;

use DOMDocument;
use DOMElement;
use DOMXPath;
use Illuminate\Support\Str;
use Throwable;

class BlogTocService
{
    public function parse(?string $html): array
    {
        $html = (string) $html;

        if ($html === '' || !class_exists(DOMDocument::class)) {
            return $this->emptyResult($html);
        }

        $previousErrors = libxml_use_internal_errors(true);

        try {
            $document = new DOMDocument('1.0', 'UTF-8');
            $document->loadHTML(
                '<?xml encoding="UTF-8"><div id="blog-toc-root">' . $html . '</div>',
                LIBXML_HTML_NOIMPLIED | LIBXML_HTML_NODEFDTD
            );

            $root = $document->getElementById('blog-toc-root');
            if (!$root) {
                return $this->emptyResult($html);
            }

            $toc = $this->injectHeadingIds($document);

            return [
                'content' => $this->innerHtml($document, $root),
                'toc' => $toc,
            ];
        } catch (Throwable) {
            return $this->emptyResult($html);
        } finally {
            libxml_clear_errors();
            libxml_use_internal_errors($previousErrors);
        }
    }

    private function injectHeadingIds(DOMDocument $document): array
    {
        $xpath = new DOMXPath($document);
        $headings = $xpath->query('//*[@id="blog-toc-root"]//*[self::h2 or self::h3]');
        $used = [];
        $toc = [];

        if (!$headings) {
            return [];
        }

        foreach ($headings as $heading) {
            if (!$heading instanceof DOMElement) {
                continue;
            }

            $text = trim(preg_replace('/\s+/', ' ', $heading->textContent) ?? '');
            if ($text === '') {
                continue;
            }

            $id = $this->uniqueId($heading->getAttribute('id') ?: $text, $used);
            $heading->setAttribute('id', $id);

            $toc[] = [
                'id' => $id,
                'text' => $text,
                'level' => (int) substr($heading->tagName, 1),
            ];
        }

        return $toc;
    }

    private function uniqueId(string $value, array &$used): string
    {
        $base = Str::slug($value) ?: 'section';
        $id = $base;
        $index = 2;

        while (isset($used[$id])) {
            $id = $base . '-' . $index;
            $index++;
        }

        $used[$id] = true;

        return $id;
    }

    private function innerHtml(DOMDocument $document, DOMElement $root): string
    {
        $html = '';

        foreach ($root->childNodes as $child) {
            $html .= $document->saveHTML($child);
        }

        return $html;
    }

    private function emptyResult(string $html): array
    {
        return [
            'content' => $html,
            'toc' => [],
        ];
    }
}
