<?php

namespace App\Utility;

use App\Models\SemanticEmbedding;
use Illuminate\Support\Facades\Log;

class SemanticUtility
{
    /**
     * Generate an embedding vector for the given text.
     * Currently a shell/mock - should be replaced with OpenAI/Gemini API call.
     */
    public static function generateEmbedding($text)
    {
        // Mocking a vector (e.g., 512 dimensions of random floats for testing infrastructure)
        // In production, this would be: return AIZ::gemini()->embed($text);
        $vector = [];
        for ($i = 0; $i < 32; $i++) { // Reduced dimensions for mock simplicity
            $vector[] = (float)rand() / (float)getrandmax();
        }
        return $vector;
    }

    /**
     * Extracts relevant searchable text from a model (e.g. Product).
     */
    public static function extractText($model)
    {
        $text = "";
        if (method_exists($model, 'getTranslation')) {
            $text .= $model->getTranslation('name') . ". ";
            $text .= strip_tags($model->getTranslation('description')) . ". ";
        } else {
            $text .= ($model->name ?? '') . ". ";
            $text .= strip_tags($model->description ?? '') . ". ";
        }
        
        if (isset($model->tags)) {
            $text .= "Tags: " . $model->tags;
        }

        return trim($text);
    }

    /**
     * Stores or updates the embedding for a given model.
     */
    public static function syncEmbedding($model)
    {
        try {
            $content = self::extractText($model);
            $vector = self::generateEmbedding($content);
            
            SemanticEmbedding::updateOrCreate(
                [
                    'embeddable_type' => get_class($model),
                    'embeddable_id' => $model->id,
                ],
                [
                    'vector' => json_encode($vector),
                    'content' => $content,
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
            $embeddings = SemanticEmbedding::all();
            
            $results = [];
            foreach ($embeddings as $embedding) {
                $vector = json_decode($embedding->vector, true);
                if (!$vector) continue;
                
                $score = self::calculateSimilarity($queryVector, $vector);
                
                // Only include results with a decent similarity (e.g. > 0.4 for mock)
                if ($score > 0.4) {
                    $results[] = [
                        'score' => $score,
                        'model' => $embedding->embeddable,
                    ];
                }
            }
            
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
