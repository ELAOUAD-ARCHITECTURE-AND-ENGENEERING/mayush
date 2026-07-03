<?php

namespace App\Utility;

use App\Models\SemanticEmbedding;
use Illuminate\Support\Facades\Log;

class SemanticUtility
{
    /**
     * Generate an embedding vector for the given text.
     */
    public static function generateEmbedding($text)
    {
        $apiKey = config('services.gemini.key');
        if (empty($apiKey)) {
            if (app()->environment('testing')) {
                return self::testingFallbackEmbedding($text);
            }

            Log::error("Gemini API Key missing in configuration.");
            return [];
        }

        try {
            $response = \Illuminate\Support\Facades\Http::withHeaders([
                'Content-Type' => 'application/json',
            ])->post("https://generativelanguage.googleapis.com/v1beta/models/gemini-embedding-001:embedContent?key={$apiKey}", [
                'model' => 'models/gemini-embedding-001',
                'content' => [
                    'parts' => [
                        ['text' => $text]
                    ]
                ],
                'outputDimensionality' => 768
            ]);

            if ($response->successful()) {
                $values = $response->json('embedding.values');
                if (is_array($values) && count($values) === 768) {
                    return $values;
                }
            }
            
            Log::error("Gemini API Error: " . ($response->json('error.message') ?? $response->body()));
        } catch (\Exception $e) {
            Log::error("Gemini Request Failed: " . $e->getMessage());
        }

        return [];
    }

    private static function testingFallbackEmbedding($text): array
    {
        $seed = crc32((string) $text);
        $values = [];

        for ($i = 0; $i < 32; $i++) {
            $hash = crc32($seed . ':' . $i);
            $values[] = round(($hash / 0xffffffff) * 2 - 1, 6);
        }

        return $values;
    }

    /**
     * Extracts relevant searchable text from a model (e.g. Product).
     */
    public static function extractText($model)
    {
        $text = "";
        if (method_exists($model, 'getTranslation')) {
            $text .= (string) $model->getTranslation('name') . ". ";
            $text .= strip_tags((string) $model->getTranslation('description')) . ". ";
        } else {
            $text .= ($model->name ?? '') . ". ";
            $text .= strip_tags($model->description ?? '') . ". ";
        }
        
        if (!empty($model->tags)) {
            $text .= "Tags: " . $model->tags;
        }

        // Limit to 2000 characters to prevent API token limit crashes
        return substr(trim($text), 0, 2000);
    }

    /**
     * Stores or updates the embedding for a given model.
     */
    public static function syncEmbedding($model, $force = false)
    {
        try {
            $content = self::extractText($model);
            $hash = hash('sha256', $content);

            // Check if existing embedding is still valid (same content)
            $existing = SemanticEmbedding::where('embeddable_type', get_class($model))
                ->where('embeddable_id', $model->id)
                ->first();

            if (!$force && $existing && $existing->content_hash === $hash) {
                return true; // No changes needed
            }

            $vector = self::generateEmbedding($content);
            if (empty($vector)) return false;
            
            SemanticEmbedding::updateOrCreate(
                [
                    'embeddable_type' => get_class($model),
                    'embeddable_id' => $model->id,
                ],
                [
                    'vector' => json_encode($vector),
                    'content' => $content,
                    'content_hash' => $hash,
                    'metadata' => json_encode(['last_updated' => now()->toDateTimeString()])
                ]
            );
            
            return true;
        } catch (\Exception $e) {
            Log::error("Semantic Embedding Sync Failed: " . $e->getMessage());
            return false;
        }
    }

    /**
     * Performs a semantic search for a given query string.
     */
    public static function search($query, $limit = 10)
    {
        try {
            $queryVector = self::generateEmbedding($query);
            if (empty($queryVector)) return [];

            $results = [];
            
            // Process in chunks of 500 to maintain low memory profile
            SemanticEmbedding::chunk(500, function($embeddings) use ($queryVector, &$results) {
                foreach ($embeddings as $embedding) {
                    $vector = json_decode($embedding->vector, true);
                    if (!$vector) continue;
                    
                    $score = self::calculateSimilarity($queryVector, $vector);
                    
                    // Similarity threshold for Gemini 768-dim embeddings
                    if ($score > 0.68) {
                        $results[] = [
                            'score' => $score,
                            'model' => $embedding->embeddable,
                        ];
                    }
                }
            });
            
            // Sort by score descending
            usort($results, fn($a, $b) => $b['score'] <=> $a['score']);
            
            return array_slice($results, 0, $limit);
        } catch (\Exception $e) {
            Log::error("Semantic Search Failed: " . $e->getMessage());
            return [];
        }
    }

    /**
     * Placeholder for cosine similarity calculation.
     */
    public static function calculateSimilarity($vecA, $vecB)
    {
        $dotProduct = 0;
        $normA = 0;
        $normB = 0;
        
        $count = count($vecA);
        for ($i = 0; $i < $count; $i++) {
            $valA = $vecA[$i] ?? 0;
            $valB = $vecB[$i] ?? 0;
            
            $dotProduct += ($valA * $valB);
            $normA += ($valA * $valA);
            $normB += ($valB * $valB);
        }

        if ($normA == 0 || $normB == 0) return 0;
        return $dotProduct / (sqrt($normA) * sqrt($normB));
    }
}
