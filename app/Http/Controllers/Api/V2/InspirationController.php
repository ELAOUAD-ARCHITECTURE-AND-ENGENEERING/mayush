<?php

namespace App\Http\Controllers\Api\V2;

use App\Http\Resources\V2\InspirationDetailResource;
use App\Http\Resources\V2\InspirationResource;
use App\Models\Inspiration;
use App\Services\InspirationCacheService;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Cache;

class InspirationController extends Controller
{
    public function index(Request $request)
    {
        $inspirations = $this->query()
            ->orderBy('sort_order')
            ->orderBy('id')
            ->get();

        return response()->json([
            'data' => $inspirations
                ->map(fn ($inspiration) => (new InspirationResource($inspiration))->resolve($request))
                ->values(),
        ]);
    }

    public function featured(Request $request, InspirationCacheService $cache)
    {
        $language = $cache->language($this->requestLanguage($request));

        $data = Cache::remember($cache->featuredKey($language), now()->addMinutes(15), function () use ($request) {
            return $this->query()
                ->featured()
                ->orderBy('sort_order')
                ->orderBy('id')
                ->limit(3)
                ->get()
                ->map(fn ($inspiration) => (new InspirationResource($inspiration))->resolve($request))
                ->values()
                ->all();
        });

        return response()->json(['data' => $data]);
    }

    public function show(Request $request, string $slug, InspirationCacheService $cache)
    {
        $language = $cache->language($this->requestLanguage($request));

        $data = Cache::remember(
            $cache->detailKey($slug, $language),
            now()->addMinutes(5),
            function () use ($request, $slug) {
                $inspiration = $this->query()
                    ->where('slug', $slug)
                    ->firstOrFail();

                return (new InspirationDetailResource($inspiration))->resolve($request);
            }
        );

        return response()->json(['data' => $data]);
    }

    private function query()
    {
        return Inspiration::published()->with([
            'items' => fn ($query) => $query
                ->where('is_visible', true)
                ->orderBy('display_order')
                ->with([
                    'hotspot',
                    'product' => fn ($productQuery) => $productQuery
                        ->withCount('reviews')
                        ->with(['user.shop', 'taxes', 'stocks']),
                ]),
        ]);
    }

    private function requestLanguage(Request $request): string
    {
        return (string) $request->header(
            'App-Language',
            $request->header('Accept-Language', 'fr')
        );
    }
}
