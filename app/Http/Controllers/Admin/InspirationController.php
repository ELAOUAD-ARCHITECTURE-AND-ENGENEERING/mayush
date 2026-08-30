<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\Inspiration;
use App\Models\InspirationItem;
use App\Models\InspirationHotspot;
use App\Models\Product;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Cache;
use Illuminate\Support\Str;
use Illuminate\Validation\Rule;

class InspirationController extends Controller
{
    public function __construct()
    {
        $this->middleware(['permission:view_inspirations'])->only('index');
        $this->middleware(['permission:add_inspiration'])->only('create', 'store');
        $this->middleware(['permission:edit_inspiration'])->only('edit', 'update', 'mapper', 'storeHotspot', 'updateHotspot', 'destroyHotspot');
        $this->middleware(['permission:delete_inspiration'])->only('destroy');
    }

    public function index(Request $request)
    {
        $query = Inspiration::withCount('items')->latest();

        if ($request->filled('status')) {
            $query->where('status', $request->status);
        }

        $inspirations = $query->paginate(20);
        return view('backend.inspirations.index', compact('inspirations'));
    }

    public function create()
    {
        $inspiration = new Inspiration();
        return view('backend.inspirations.form', compact('inspiration'));
    }

    public function store(Request $request)
    {
        $data = $this->validatedData($request);
        $data = $this->handleImageUpload($request, $data);
        $data['created_by'] = auth()->id();

        $inspiration = Inspiration::create($data);

        flash(translate('Inspiration created successfully.'))->success();
        return redirect()->route('inspirations.edit', $inspiration);
    }

    public function edit(Inspiration $inspiration)
    {
        return view('backend.inspirations.form', compact('inspiration'));
    }

    public function update(Request $request, Inspiration $inspiration)
    {
        $data = $this->validatedData($request, $inspiration);
        $data = $this->handleImageUpload($request, $data, $inspiration);

        // Publication validation
        if (($data['status'] ?? $inspiration->status) === 'published' && $inspiration->status !== 'published') {
            $errors = $this->validateForPublication($inspiration, $data);
            if (!empty($errors)) {
                flash(implode(' ', $errors))->error();
                return redirect()->back()->withInput();
            }
        }

        $inspiration->update($data);
        $this->clearInspirationCache($inspiration);

        flash(translate('Inspiration updated successfully.'))->success();
        return redirect()->route('inspirations.edit', $inspiration);
    }

    public function destroy(Inspiration $inspiration)
    {
        $this->clearInspirationCache($inspiration);
        $inspiration->delete();
        flash(translate('Inspiration deleted successfully.'))->success();
        return redirect()->route('inspirations.index');
    }

    // --- Hotspot Mapper ---

    public function mapper(Inspiration $inspiration)
    {
        $inspiration->load(['items' => function ($q) {
            $q->orderBy('display_order')->with(['product', 'hotspot']);
        }]);

        return view('backend.inspirations.mapper', compact('inspiration'));
    }

    public function storeHotspot(Request $request, Inspiration $inspiration)
    {
        $validated = $request->validate([
            'product_id' => 'required|exists:products,id',
            'x' => 'required|numeric|min:0|max:1',
            'y' => 'required|numeric|min:0|max:1',
        ]);

        $maxOrder = $inspiration->items()->max('display_order') ?? 0;

        $item = InspirationItem::create([
            'inspiration_id' => $inspiration->id,
            'product_id' => $validated['product_id'],
            'display_order' => $maxOrder + 1,
        ]);

        $hotspot = InspirationHotspot::create([
            'inspiration_id' => $inspiration->id,
            'inspiration_item_id' => $item->id,
            'x' => $validated['x'],
            'y' => $validated['y'],
            'display_order' => $maxOrder + 1,
        ]);

        $product = Product::find($validated['product_id']);
        $this->clearInspirationCache($inspiration);

        return response()->json([
            'success' => true,
            'item' => [
                'id' => $item->id,
                'hotspot_id' => $hotspot->id,
                'display_order' => $item->display_order,
                'x' => (float) $hotspot->x,
                'y' => (float) $hotspot->y,
                'product' => [
                    'id' => $product->id,
                    'name' => $product->getTranslation('name', 'fr'),
                    'price' => format_price(convert_price($product->unit_price)),
                    'image' => uploaded_asset($product->thumbnail_img),
                    'available' => (bool) ($product->published && $product->approved),
                ],
            ],
        ]);
    }

    public function updateHotspot(Request $request, Inspiration $inspiration, InspirationHotspot $hotspot)
    {
        $validated = $request->validate([
            'x' => 'sometimes|numeric|min:0|max:1',
            'y' => 'sometimes|numeric|min:0|max:1',
            'product_id' => 'sometimes|exists:products,id',
        ]);

        if (isset($validated['x']) && isset($validated['y'])) {
            $hotspot->update(['x' => $validated['x'], 'y' => $validated['y']]);
        }

        if (isset($validated['product_id'])) {
            $hotspot->item->update(['product_id' => $validated['product_id']]);
        }

        $this->clearInspirationCache($inspiration);

        return response()->json(['success' => true]);
    }

    public function destroyHotspot(Request $request, Inspiration $inspiration, InspirationHotspot $hotspot)
    {
        $item = $hotspot->item;
        $hotspot->delete();
        if ($item) {
            $item->delete();
        }

        $this->clearInspirationCache($inspiration);

        return response()->json(['success' => true]);
    }

    // --- Private helpers ---

    private function validatedData(Request $request, ?Inspiration $inspiration = null): array
    {
        $data = $request->validate([
            'title_fr' => ['required', 'string', 'max:255'],
            'title_ar' => ['nullable', 'string', 'max:255'],
            'subtitle_fr' => ['nullable', 'string', 'max:255'],
            'subtitle_ar' => ['nullable', 'string', 'max:255'],
            'description_fr' => ['nullable', 'string'],
            'description_ar' => ['nullable', 'string'],
            'slug' => ['nullable', 'string', 'max:255', Rule::unique('inspirations', 'slug')->ignore($inspiration?->id)],
            'status' => ['required', Rule::in(['draft', 'published', 'archived'])],
            'sort_order' => ['nullable', 'integer', 'min:0'],
            'starts_at' => ['nullable', 'date'],
            'ends_at' => ['nullable', 'date', 'after_or_equal:starts_at'],
        ]);

        $slug = $data['slug'] ?? null;
        $data['slug'] = Str::slug($slug ?: $data['title_fr']);
        if (!$slug) {
            $baseSlug = $data['slug'];
            $suffix = 2;
            while (Inspiration::where('slug', $data['slug'])
                ->when($inspiration, fn ($q) => $q->where('id', '!=', $inspiration->id))
                ->exists()) {
                $data['slug'] = $baseSlug . '-' . $suffix++;
            }
        }

        $data['is_featured'] = $request->boolean('is_featured');
        $data['show_on_home'] = $request->boolean('show_on_home');
        $data['sort_order'] = $data['sort_order'] ?? 0;

        if ($data['status'] === 'published' && !($inspiration?->published_at)) {
            $data['published_at'] = now();
        }

        return $data;
    }

    private function handleImageUpload(Request $request, array $data, ?Inspiration $inspiration = null): array
    {
        if ($request->hasFile('hero_image')) {
            $file = $request->file('hero_image');
            $path = $file->store('inspirations', 'public');
            $data['hero_image'] = $path;

            // Store image dimensions
            $imageSize = getimagesize($file->getRealPath());
            if ($imageSize) {
                $data['hero_image_width'] = $imageSize[0];
                $data['hero_image_height'] = $imageSize[1];
            }
        }
        return $data;
    }

    private function validateForPublication(Inspiration $inspiration, array $data): array
    {
        $errors = [];

        $heroImage = $data['hero_image'] ?? $inspiration->hero_image;
        if (empty($heroImage)) {
            $errors[] = translate("L'image de la scene est requise.");
        }

        $titleFr = $data['title_fr'] ?? $inspiration->title_fr;
        if (empty($titleFr)) {
            $errors[] = translate("Le titre (francais) est requis.");
        }

        $itemCount = $inspiration->items()->whereHas('product')->count();
        if ($itemCount === 0) {
            $errors[] = translate("Au moins un produit doit etre associe.");
        }

        $itemsWithoutHotspots = $inspiration->items()
            ->whereDoesntHave('hotspot')
            ->count();
        if ($itemsWithoutHotspots > 0) {
            $errors[] = translate("$itemsWithoutHotspots produit(s) ne sont pas positionnes dans l'image.");
        }

        return $errors;
    }

    private function clearInspirationCache(Inspiration $inspiration): void
    {
        Cache::forget("inspiration_detail_{$inspiration->slug}_fr");
        Cache::forget("inspiration_detail_{$inspiration->slug}_ar");
        Cache::forget('inspirations_featured_fr');
        Cache::forget('inspirations_featured_ar');
    }
}
