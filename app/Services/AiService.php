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

        if (!filled(config('services.openrouter.key'))) {
            return response()->json([
                'success' => false,
                'message' => translate('OpenRouter API key is not configured.'),
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

        $sectionConfig = $fieldMap[$section];
        $prompt = str_replace(
            ['{product_name}', '{language}', '{prompt_fields}'],
            [$productName, $sectionConfig['language_target'], $sectionConfig['prompt_fields']],
            $promptTemplate
        );

        if (!empty($existingData)) {
            $prompt .= "\nImprove this content:\n" . json_encode($existingData);
        }

        $openRouterConfig = config('services.openrouter', []);
        $model = get_setting('openrouter_model') ?: ($openRouterConfig['model'] ?? 'openrouter/free');

        $headers = [
            'Content-Type' => 'application/json',
            'Authorization' => 'Bearer '.(string) ($openRouterConfig['key'] ?? ''),
        ];
        if (filled($openRouterConfig['site_url'] ?? null)) {
            $headers['HTTP-Referer'] = (string) $openRouterConfig['site_url'];
        }
        if (filled($openRouterConfig['app_name'] ?? null)) {
            $headers['X-OpenRouter-Title'] = (string) $openRouterConfig['app_name'];
        }

        $response = Http::withHeaders($headers)
            ->timeout((int) ($openRouterConfig['timeout'] ?? 90))
            ->post(rtrim((string) ($openRouterConfig['api_base'] ?? 'https://openrouter.ai/api/v1'), '/').'/chat/completions', [
                'model' => $model,
                'temperature' => 0.7,
                'stream' => false,
                'messages' => [['role' => 'user', 'content' => $prompt]],
            ]);

        if (!$response->successful()) {
            Log::warning('OpenRouter product generation failed', [
                'status' => $response->status(),
                'model' => $model,
            ]);

            return response()->json([
                'success' => false,
                'message' => translate('AI request failed.'),
            ], 502);
        }

        $result = $response->json();
        $text = $result['choices'][0]['message']['content'] ?? '';
        $text = trim(preg_replace('/^```(?:json)?\s*|\s*```$/m', '', $text));
        $decoded = json_decode($text, true);

        if (!is_array($decoded)) {
            Log::warning('OpenRouter product generation returned invalid JSON', [
                'model' => $model,
                'json_error' => json_last_error_msg(),
            ]);

            return response()->json([
                'success' => false,
                'message' => translate('AI returned invalid JSON.'),
            ], 502);
        }

        $clean = [];
        foreach ($sectionConfig['fields'] as $field) {
            $clean[$field] = $decoded[$field] ?? null;
        }

        $this->logUsage($result['usage'] ?? [], $model);

        return response()->json([
            'success' => true,
            'data' => $clean,
            'section' => $section,
            'language' => $language,
            'is_regenerated' => !empty($existingData),
            'tokens' => $result['usage'] ?? [],
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
            'prompt_tokens' => $tokenUsage['prompt_tokens'] ?? 0,
            'completion_tokens' => $tokenUsage['completion_tokens'] ?? 0,
            'total_tokens' => $tokenUsage['total_tokens'] ?? 0,
            'model' => $model,
        ]);
    }
}
