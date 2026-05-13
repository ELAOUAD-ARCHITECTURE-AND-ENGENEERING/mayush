<?php

namespace App\Services;

use App\Models\AiPrompt;
use App\Models\AiUsageLog;
use App\Models\Language;
use Illuminate\Http\JsonResponse;
use Illuminate\Support\Facades\Http;
use Illuminate\Support\Facades\Log;

class AiService
{
    public function productGenerateWithAI(array $data): JsonResponse
    {
        if ((int) get_setting('ai_activation') !== 1) {
            return response()->json([
                'success' => false,
                'message' => translate('AI generation is disabled.'),
            ], 403);
        }

        if (!filled(config('services.gemini.key') ?: env('GEMINI_API_KEY'))) {
            return response()->json([
                'success' => false,
                'message' => translate('Gemini API key is not configured.'),
            ], 422);
        }

        $productName = trim($data['product_name'] ?? '');
        $section = $data['section'] ?? null;
        $language = $data['lang'] ?? default_language();
        $existingData = $data['existing_data'] ?? [];

        if ($productName === '' || $section === null) {
            return response()->json([
                'success' => false,
                'message' => translate('Product name and section are required.'),
            ], 422);
        }

        $fieldMap = $this->fieldMap($language);
        if (!isset($fieldMap[$section])) {
            return response()->json([
                'success' => false,
                'message' => translate('Invalid AI section.'),
            ], 422);
        }

        $promptTemplate = AiPrompt::where('identifier', 'product_add_edit_prompt')->value('prompt');
        if (!filled($promptTemplate)) {
            return response()->json([
                'success' => false,
                'message' => translate('AI prompt is not configured.'),
            ], 422);
        }

        $config = $fieldMap[$section];
        $prompt = str_replace(
            ['{product_name}', '{language}', '{prompt_fields}'],
            [$productName, $config['language_target'], $config['prompt_fields']],
            $promptTemplate
        );

        if (!empty($existingData)) {
            $prompt .= "\nImprove this content:\n" . json_encode($existingData);
        }

        $model = get_setting('gemini_model') ?: 'gemini-2.0-flash-lite';
        $apiKey = config('services.gemini.key') ?: env('GEMINI_API_KEY');

        $response = Http::timeout(15)
            ->acceptJson()
            ->post("https://generativelanguage.googleapis.com/v1/models/{$model}:generateContent?key={$apiKey}", [
                'contents' => [[
                    'parts' => [['text' => $prompt]],
                ]],
                'generationConfig' => [
                    'temperature' => 0.7,
                    'topP' => 0.95,
                    'maxOutputTokens' => 1024,
                ],
            ]);

        if (!$response->successful()) {
            Log::warning('Gemini product generation failed', [
                'status' => $response->status(),
                'model' => $model,
            ]);

            return response()->json([
                'success' => false,
                'message' => translate('AI request failed.'),
            ], 502);
        }

        $result = $response->json();
        $text = $result['candidates'][0]['content']['parts'][0]['text'] ?? '';
        $text = trim(preg_replace('/^```(?:json)?\s*|\s*```$/m', '', $text));
        $decoded = json_decode($text, true);

        if (!is_array($decoded)) {
            Log::warning('Gemini product generation returned invalid JSON', [
                'model' => $model,
                'json_error' => json_last_error_msg(),
            ]);

            return response()->json([
                'success' => false,
                'message' => translate('AI returned invalid JSON.'),
            ], 502);
        }

        $clean = [];
        foreach ($config['fields'] as $field) {
            $clean[$field] = $decoded[$field] ?? null;
        }

        $this->logUsage($result['usageMetadata'] ?? [], $model);

        return response()->json([
            'success' => true,
            'data' => $clean,
            'section' => $section,
            'language' => $language,
            'is_regenerated' => !empty($existingData),
            'tokens' => $result['usageMetadata'] ?? [],
        ]);
    }

    private function fieldMap(string $language): array
    {
        $languageName = Language::where('code', $language)->value('name') ?? 'English';

        return [
            'basic-information' => [
                'fields' => ['name'],
                'prompt_fields' => 'name: a clean, attractive, SEO-friendly product title, max 100 characters',
                'language_target' => $languageName,
            ],
            'product-description' => [
                'fields' => ['description'],
                'prompt_fields' => 'description: 2 to 4 benefit-focused HTML paragraphs',
                'language_target' => $languageName,
            ],
            'product-seo-meta-tag' => [
                'fields' => ['meta_title', 'meta_description', 'meta_keywords'],
                'prompt_fields' => 'meta_title, meta_description, meta_keywords',
                'language_target' => 'English',
            ],
            'product-configuration' => [
                'fields' => ['unit', 'weight', 'min_qty', 'tags'],
                'prompt_fields' => 'unit, weight in kg, min_qty, tags',
                'language_target' => $languageName,
            ],
        ];
    }

    private function logUsage(array $tokenUsage, string $model): void
    {
        if (!auth()->check()) {
            return;
        }

        AiUsageLog::create([
            'user_id' => auth()->id(),
            'prompt_tokens' => $tokenUsage['promptTokenCount'] ?? 0,
            'completion_tokens' => $tokenUsage['candidatesTokenCount'] ?? 0,
            'total_tokens' => $tokenUsage['totalTokenCount'] ?? 0,
            'model' => $model,
        ]);
    }
}
