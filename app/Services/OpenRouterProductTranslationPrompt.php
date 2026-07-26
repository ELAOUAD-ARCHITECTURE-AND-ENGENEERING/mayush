<?php

namespace App\Services;

use JsonException;

class OpenRouterProductTranslationPrompt
{
    public const VERSION = 'product-translation-v1';

    public function systemInstruction(): string
    {
        return 'You are a professional French-to-Arabic e-commerce translator. Return valid JSON only, preserve the exact supplied structure and every field key, translate only human-readable text, preserve protected markers, HTML structure, technical identifiers, URLs, numbers, brands and model names, and never add explanations or Markdown.';
    }

    public function build(array $fields, string $sourceLanguage, string $targetLanguage): string
    {
        try {
            $json = json_encode($fields, JSON_UNESCAPED_SLASHES | JSON_UNESCAPED_UNICODE | JSON_THROW_ON_ERROR);
        } catch (JsonException) {
            $json = '{}';
        }

        return <<<PROMPT
You are a professional French-to-Arabic e-commerce translator specializing in furniture, interior design, decoration, lighting, and home products.

Translate every supplied French value into professional Modern Standard Arabic suitable for e-commerce. The source language is {$sourceLanguage}; the target language is {$targetLanguage} (Arabic).

Mandatory rules:
- Return valid JSON only, matching the supplied JSON structure exactly.
- Preserve every field key, nested object, array, and array order.
- Do not add, remove, rename, summarize, or invent any value.
- Do not use Markdown, code fences, explanations, or introductory text.
- Preserve brand names, model names, references, SKUs, numbers, prices, dimensions, measurements, URLs, email addresses, and technical identifiers exactly.
- Translate the human-readable `unit` field when it is a label such as `Pc`, `piece`, `set`, or `unité`; use natural Arabic such as `قطعة` or `طقم`. Keep only genuinely standardized measurement symbols unchanged.
- Preserve bracketed MAYUSH protection markers exactly; never translate or remove them.
- Translate only human-readable text.
- Preserve valid HTML structure. Do not translate HTML tags, attributes, CSS classes, element IDs, URLs, image paths, data attributes, or editor metadata.
- Keep terminology consistent across all fields of this product.

Input JSON:
{$json}
PROMPT;
    }
}
