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
            'rich_text' => [
                'type' => 'rich_text',
                'data' => [
                    'text' => $this->sanitizer->sanitize(trim((string) Arr::get($data, 'text'))), // Sanitized HTML
                ],
            ],
            'html' => \Illuminate\Support\Facades\Auth::check() && \Illuminate\Support\Facades\Auth::user()->can('manage_blog_html') ? [
                'type' => 'html',
                'data' => [
                    'code' => trim((string) Arr::get($data, 'code')), // Raw HTML allowed for trusted admins
                ],
            ] : null,
            'image' => [
                'type' => 'image',
                'data' => [
                    'upload_id' => trim((string) Arr::get($data, 'upload_id')),
                    'alt' => Str::limit(trim((string) Arr::get($data, 'alt')), 180, ''),
                    'caption' => Str::limit(trim((string) Arr::get($data, 'caption')), 220, ''),
                ],
            ],
            'gallery' => [
                'type' => 'gallery',
                'data' => [
                    'upload_ids' => collect(explode(',', (string) Arr::get($data, 'upload_ids', '')))
                        ->map(fn($id) => trim((string)$id))
                        ->filter()
                        ->take(12)
                        ->implode(','),
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
            'faq' => [
                'type' => 'faq',
                'data' => [
                    'items' => collect(Arr::get($data, 'items', []))
                        ->map(function ($item) {
                            return [
                                'question' => Str::limit(trim((string) Arr::get($item, 'question')), 200, ''),
                                'answer' => trim((string) Arr::get($item, 'answer')),
                            ];
                        })
                        ->filter(fn($item) => !empty($item['question']) && !empty($item['answer']))
                        ->take(10)
                        ->values()
                        ->all(),
                ],
            ],
            'product_recommendation' => [
                'type' => 'product_recommendation',
                'data' => [
                    'product_ids' => collect(explode(',', (string) Arr::get($data, 'product_ids', '')))
                        ->map(fn($id) => trim((string)$id))
                        ->filter()
                        ->take(4)
                        ->implode(','),
                    'title' => Str::limit(trim((string) Arr::get($data, 'title')), 100, ''),
                ],
            ],
            'shop_highlight' => [
                'type' => 'shop_highlight',
                'data' => [
                    'shop_id' => trim((string) Arr::get($data, 'shop_id')),
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
            'rich_text' => $this->renderRichText($data),
            'html' => $this->renderHtml($data),
            'image' => $this->renderImage($data),
            'gallery' => $this->renderGallery($data),
            'quote' => $this->renderQuote($data),
            'list' => $this->renderList($data),
            'faq' => $this->renderFaq($data),
            // Complex blocks won't be fully rendered into the DB description field anymore, 
            // but we leave placeholders or basic markup for fallback.
            'product_recommendation' => '<div class="block-placeholder product-recommendation-placeholder"></div>',
            'shop_highlight' => '<div class="block-placeholder shop-highlight-placeholder"></div>',
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

    private function renderRichText(array $data): string
    {
        // Already sanitized during normalizeBlock
        $text = (string) ($data['text'] ?? '');
        return $text === '' ? '' : '<div class="rich-text">' . $text . '</div>';
    }

    private function renderHtml(array $data): string
    {
        return (string) ($data['code'] ?? '');
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

    private function renderGallery(array $data): string
    {
        $uploadIds = array_filter(explode(',', (string) ($data['upload_ids'] ?? '')));
        if (empty($uploadIds)) {
            return '';
        }

        $imagesHtml = '';
        foreach ($uploadIds as $id) {
            $src = uploaded_asset($id);
            if ($src) {
                $imagesHtml .= '<img src="' . e($src) . '" loading="lazy">';
            }
        }

        return $imagesHtml !== '' ? '<div class="gallery-block">' . $imagesHtml . '</div>' : '';
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

    private function renderFaq(array $data): string
    {
        $itemsHtml = collect($data['items'] ?? [])
            ->map(function ($item) {
                $q = e((string) $item['question']);
                $a = nl2br(e((string) $item['answer']));
                return "<details><summary>{$q}</summary><p>{$a}</p></details>";
            })
            ->filter()
            ->implode('');

        return $itemsHtml !== '' ? '<div class="faq-block">' . $itemsHtml . '</div>' : '';
    }

    private function supportedTypes(): array
    {
        return ['heading', 'paragraph', 'rich_text', 'html', 'image', 'gallery', 'quote', 'list', 'faq', 'product_recommendation', 'shop_highlight', 'divider'];
    }
}
