<?php

namespace App\Http\Controllers;

use App\Contracts\ProductTranslationService;
use App\Http\Requests\ProductTranslationRequest;
use App\Models\Product;
use Illuminate\Http\JsonResponse;

class ProductTranslationController extends Controller
{
    public function __construct(private readonly ProductTranslationService $translationService)
    {
    }

    public function translate(ProductTranslationRequest $request): JsonResponse
    {
        $user = $request->user();
        $product = $request->filled('product_id') ? Product::findOrFail($request->integer('product_id')) : null;

        if ($product && !$user->can('update', $product)) {
            abort(403);
        }

        if (!$product && $user->user_type !== 'seller' && !$user->can('add_new_product') && !$user->can('product_edit')) {
            abort(403);
        }

        $result = $this->translationService->translateFields(
            $request->validated('fields'),
            $request->validated('source_language', 'fr'),
            $request->validated('target_language', 'ar')
        );

        if (($result['error_code'] ?? null) === 'configuration') {
            return response()->json([
                'success' => false,
                'message' => 'Le service de traduction automatique n’est pas correctement configuré.',
                'fields' => $result['fields'],
                'failed_fields' => $result['failed_fields'],
            ], 503);
        }

        if (($result['error_code'] ?? null) === 'rate_limit') {
            return response()->json([
                'success' => false,
                'message' => 'La limite temporaire du service de traduction a été atteinte. Réessayez manuellement lorsque le quota sera disponible.',
                'fields' => $result['fields'],
                'failed_fields' => $result['failed_fields'],
            ], 429);
        }

        if (in_array($result['error_code'] ?? null, ['credentials', 'invalid_model', 'account_credit', 'structured_output_unsupported'], true)) {
            return response()->json([
                'success' => false,
                'message' => match ($result['error_code'] ?? null) {
                    'account_credit' => 'Le compte du service de traduction ne dispose pas de crédits suffisants.',
                    'structured_output_unsupported' => 'Le modèle de traduction configuré ne prend pas en charge les réponses structurées.',
                    'invalid_model' => 'Le modèle de traduction configuré n’est pas disponible.',
                    default => 'Le service de traduction automatique n’est pas correctement configuré.',
                },
                'fields' => $result['fields'],
                'failed_fields' => $result['failed_fields'],
            ], 503);
        }

        if (in_array($result['error_code'] ?? null, ['temporary_failure', 'timeout'], true)) {
            return response()->json([
                'success' => false,
                'message' => 'Le service de traduction est temporairement indisponible. Réessayez dans quelques instants.',
                'fields' => $result['fields'],
                'failed_fields' => $result['failed_fields'],
            ], 503);
        }

        if (($result['error_code'] ?? null) === 'payload_too_large') {
            return response()->json([
                'success' => false,
                'message' => 'Le contenu à traduire dépasse la taille maximale autorisée.',
                'fields' => $result['fields'],
                'failed_fields' => $result['failed_fields'],
            ], 413);
        }

        $result['message'] = $result['failed_fields'] === []
            ? 'Le contenu a été traduit en arabe avec succès. Vérifiez la traduction avant d’enregistrer.'
            : 'Certains champs n’ont pas pu être traduits. Les traductions réussies ont été conservées et les valeurs d’origine des autres champs n’ont pas été modifiées.';

        return response()->json($result);
    }
}
