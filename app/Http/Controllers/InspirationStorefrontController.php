<?php

namespace App\Http\Controllers;

use App\Models\Inspiration;
use App\Services\InspirationCacheService;
use Illuminate\Http\Request;

class InspirationStorefrontController extends Controller
{
    public function index(Request $request)
    {
        $inspirations = Inspiration::query()
            ->published()
            ->orderBy('sort_order')
            ->orderBy('id')
            ->with(['items' => function ($query) {
                $query->where('is_visible', true)
                    ->orderBy('display_order')
                    ->with('product');
            }, 'hotspots'])
            ->get();

        return view('frontend.metro.inspirations.index', compact('inspirations'));
    }

    public function show(string $slug, Request $request, InspirationCacheService $cache)
    {
        $inspiration = Inspiration::query()
            ->published()
            ->where('slug', $slug)
            ->with([
                'items' => function ($query) {
                    $query->where('is_visible', true)
                        ->orderBy('display_order')
                        ->with(['product.reviews', 'product.taxes', 'product.stocks', 'product.user.shop', 'hotspot']);
                },
                'hotspots' => function ($query) {
                    $query->orderBy('display_order');
                },
            ])
            ->firstOrFail();

        return view('frontend.metro.inspirations.show', compact('inspiration'));
    }
}
