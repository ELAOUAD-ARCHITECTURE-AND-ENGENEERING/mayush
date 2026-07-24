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
                'message' => 'Le service Microsoft Azure Translator n’est pas configuré. Veuillez vérifier la clé, la région et le point de terminaison.',
                'fields' => $result['fields'],
                'failed_fields' => $result['failed_fields'],
            ], 503);
        }

        if (($result['error_code'] ?? null) === 'rate_limit') {
            return response()->json([
                'success' => false,
                'message' => 'La limite du service Microsoft Azure Translator a été atteinte. Réessayez plus tard ou vérifiez le quota Azure.',
                'fields' => $result['fields'],
                'failed_fields' => $result['failed_fields'],
            ], 429);
        }

        $result['message'] = $result['failed_fields'] === []
            ? 'Le contenu a été traduit en arabe avec Microsoft Azure Translator. Vérifiez la traduction avant d’enregistrer.'
            : 'Certains champs n’ont pas pu être traduits. Les traductions réussies ont été conservées et les valeurs d’origine des autres champs n’ont pas été modifiées.';

        return response()->json($result);
    }
}
