<?php

namespace App\Services\Blog;

use Illuminate\Support\Arr;
use Illuminate\Support\Str;

class BlogContentBlockService
{
    public function __construct(private BlogContentSanitizerService $sanitizer)
    {
    }

    public function normalize($rawBlocks): array
    {
        if (is_string($rawBlocks)) {
            $rawBlocks = json_decode($rawBlocks, true);
        }

        if (!is_array($rawBlocks)) {
            return [];
        }

        return collect($rawBlocks)
            ->filter(fn ($block) => is_array($block) && in_array(Arr::get($block, 'type'), $this->supportedTypes(), true))
            ->map(fn ($block) => $this->normalizeBlock($block))
            ->filter()
            ->values()
            ->all();
    }

    public function compileHtml(array $blocks, ?string $fallbackHtml = null): string
    {
        if (empty($blocks)) {
            return $this->sanitizer->sanitize($fallbackHtml);
        }

        $html = collect($blocks)
            ->map(fn ($block) => $this->renderBlock($block))
            ->filter()
            ->implode("\n");

        return $this->sanitizer->sanitize($html);
    }

    private function normalizeBlock(array $block): ?array
    {
        $type = Arr::get($block, 'type');
        $data = is_array(Arr::get($block, 'data')) ? Arr::get($block, 'data') : [];

        return match ($type) {
            'heading' => [
                'type' => 'heading',
                'data' => [
                    'level' => in_array((int) Arr::get($data, 'level', 2), [2, 3, 4], true) ? (int) Arr::get($data, 'level', 2) : 2,
                    'text' => Str::limit(trim((string) Arr::get($data, 'text')), 180, ''),
                ],
            ],
            'paragraph' => [
                'type' => 'paragraph',
                'data' => [
                    'text' => trim((string) Arr::get($data, 'text')),
                ],
            ],
            'image' => [
                'type' => 'image',
                'data' => [
                    'upload_id' => trim((string) Arr::get($data, 'upload_id')),
                    'alt' => Str::limit(trim((string) Arr::get($data, 'alt')), 180, ''),
                    'caption' => Str::limit(trim((string) Arr::get($data, 'caption')), 220, ''),
                ],
            ],
            'quote' => [
                'type' => 'quote',
                'data' => [
                    'text' => trim((string) Arr::get($data, 'text')),
                    'cite' => Str::limit(trim((string) Arr::get($data, 'cite')), 120, ''),
                ],
            ],
            'list' => [
                'type' => 'list',
                'data' => [
                    'style' => Arr::get($data, 'style') === 'ordered' ? 'ordered' : 'unordered',
                    'items' => collect(Arr::get($data, 'items', []))
                        ->map(fn ($item) => trim((string) $item))
                        ->filter()
                        ->take(20)
                        ->values()
                        ->all(),
                ],
            ],
            'divider' => [
                'type' => 'divider',
                'data' => [],
            ],
            default => null,
        };
    }

    private function renderBlock(array $block): string
    {
        $data = $block['data'] ?? [];

        return match ($block['type']) {
            'heading' => $this->renderHeading($data),
            'paragraph' => $this->renderParagraph($data),
            'image' => $this->renderImage($data),
            'quote' => $this->renderQuote($data),
            'list' => $this->renderList($data),
            'divider' => '<hr>',
            default => '',
        };
    }

    private function renderHeading(array $data): string
    {
        $text = e((string) ($data['text'] ?? ''));

        if ($text === '') {
            return '';
        }

        $level = in_array((int) ($data['level'] ?? 2), [2, 3, 4], true) ? (int) $data['level'] : 2;

        return "<h{$level}>{$text}</h{$level}>";
    }

    private function renderParagraph(array $data): string
    {
        $text = e((string) ($data['text'] ?? ''));

        return $text === '' ? '' : '<p>' . nl2br($text) . '</p>';
    }

    private function renderImage(array $data): string
    {
        $uploadId = $data['upload_id'] ?? null;
        $src = $uploadId ? uploaded_asset($uploadId) : null;

        if (!$src) {
            return '';
        }

        $alt = e((string) ($data['alt'] ?? ''));
        $caption = e((string) ($data['caption'] ?? ''));
        $captionHtml = $caption !== '' ? "<figcaption>{$caption}</figcaption>" : '';

        return '<figure><img src="' . e($src) . '" alt="' . $alt . '" loading="lazy">' . $captionHtml . '</figure>';
    }

    private function renderQuote(array $data): string
    {
        $text = e((string) ($data['text'] ?? ''));
        $cite = e((string) ($data['cite'] ?? ''));

        if ($text === '') {
            return '';
        }

        $citeHtml = $cite !== '' ? "<figcaption>{$cite}</figcaption>" : '';

        return "<blockquote><p>{$text}</p>{$citeHtml}</blockquote>";
    }

    private function renderList(array $data): string
    {
        $items = collect($data['items'] ?? [])
            ->map(fn ($item) => '<li>' . e((string) $item) . '</li>')
            ->filter()
            ->implode('');

        if ($items === '') {
            return '';
        }

        $tag = ($data['style'] ?? 'unordered') === 'ordered' ? 'ol' : 'ul';

        return "<{$tag}>{$items}</{$tag}>";
    }

    private function supportedTypes(): array
    {
        return ['heading', 'paragraph', 'image', 'quote', 'list', 'divider'];
    }
}
