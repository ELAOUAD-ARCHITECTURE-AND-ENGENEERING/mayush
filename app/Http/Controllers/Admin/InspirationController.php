<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\Inspiration;
use App\Models\InspirationHotspot;
use App\Models\InspirationItem;
use App\Models\Product;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Storage;
use Illuminate\Support\Str;
use Illuminate\Validation\Rule;
use Illuminate\Validation\ValidationException;
use Throwable;

class InspirationController extends Controller
{
    public function __construct()
    {
        $this->middleware(['permission:view_inspiration'])->only('index');
        $this->middleware(['permission:add_inspiration'])->only('create', 'store');
        $this->middleware(['permission:edit_inspiration'])->only(
            'edit', 'update', 'updateFeatured', 'mapper',
            'storeHotspot', 'updateHotspot', 'destroyHotspot'
        );
        $this->middleware(['permission:delete_inspiration'])->only('destroy');
    }

    public function index(Request $request)
    {
        $query = Inspiration::withCount('items')->latest();

        if ($request->filled('status')) {
            $query->where('status', $request->string('status'));
        }

        return view('backend.inspirations.index', [
            'inspirations' => $query->paginate(20),
        ]);
    }

    public function create()
    {
        return view('backend.inspirations.form', [
            'inspiration' => new Inspiration(),
        ]);
    }

    public function store(Request $request)
    {
        $data = $this->validatedData($request);

        if ($data['status'] === 'published') {
            throw ValidationException::withMessages([
                'items' => [translate('Au moins un produit doit etre associe')],
            ]);
        }

        $data = $this->handleImageUpload($request, $data);
        $data['created_by'] = auth()->id();
        try {
            $inspiration = Inspiration::create($data);
        } catch (Throwable $exception) {
            Storage::disk('public')->delete($data['hero_image']);
            throw $exception;
        }

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

        if ($data['status'] === 'published') {
            $errors = $this->validateForPublication(
                $inspiration,
                $data,
                $request->hasFile('hero_image')
            );

            if ($errors) {
                throw ValidationException::withMessages($errors);
            }
        }

        $data = $this->handleImageUpload($request, $data);
        $previousHeroImage = $inspiration->hero_image;
        try {
            $inspiration->update($data);
        } catch (Throwable $exception) {
            if (isset($data['hero_image']) && $data['hero_image'] !== $previousHeroImage) {
                Storage::disk('public')->delete($data['hero_image']);
            }
            throw $exception;
        }

        flash(translate('Inspiration updated successfully.'))->success();

        return redirect()->route('inspirations.edit', $inspiration);
    }

    public function updateFeatured(Request $request, Inspiration $inspiration)
    {
        $validated = $request->validate(['is_featured' => ['required', 'boolean']]);
        $inspiration->update(['is_featured' => $validated['is_featured']]);

        return back()->with('success', translate('Inspiration updated successfully.'));
    }

    public function destroy(Inspiration $inspiration)
    {
        $inspiration->delete();
        flash(translate('Inspiration deleted successfully.'))->success();

        return redirect()->route('inspirations.index');
    }

    public function mapper(Inspiration $inspiration)
    {
        $inspiration->load(['items' => fn ($query) => $query
            ->orderBy('display_order')
            ->with([
                'hotspot',
                'product' => fn ($productQuery) => $productQuery->with(['user.shop', 'stocks', 'taxes']),
            ])]);

        $mapperItems = $inspiration->items->map(fn ($item) => [
            'id' => $item->id,
            'hotspot_id' => $item->hotspot?->id,
            'display_order' => $item->display_order,
            'x' => $item->hotspot ? (float) $item->hotspot->x : null,
            'y' => $item->hotspot ? (float) $item->hotspot->y : null,
            'product' => [
                'id' => $item->product?->id,
                'name' => $item->product?->getTranslation('name', 'fr') ?? translate('Unknown product'),
                'price' => $item->product ? home_discounted_base_price($item->product) : '-',
                'image' => $item->product ? uploaded_asset($item->product->thumbnail_img) : '',
                'available' => (bool) $item->product?->isAvailable(),
                'stock_status' => $item->product?->stockStatus() ?? 'out_of_stock',
            ],
        ])->values();

        return view('backend.inspirations.mapper', compact('inspiration', 'mapperItems'));
    }

    public function storeHotspot(Request $request, Inspiration $inspiration)
    {
        $validated = $request->validate([
            'product_id' => [
                'required',
                'exists:products,id',
                Rule::unique('inspiration_items', 'product_id')
                    ->where(fn ($query) => $query->where('inspiration_id', $inspiration->id)),
            ],
            'x' => ['required', 'numeric', 'between:0,1'],
            'y' => ['required', 'numeric', 'between:0,1'],
            'display_order' => ['sometimes', 'integer', 'min:0'],
        ]);

        [$item, $hotspot] = DB::transaction(function () use ($validated, $inspiration) {
            $displayOrder = $validated['display_order']
                ?? (((int) $inspiration->items()->max('display_order')) + 1);
            $item = $inspiration->items()->create([
                'product_id' => $validated['product_id'],
                'display_order' => $displayOrder,
            ]);
            $hotspot = $item->hotspot()->create([
                'inspiration_id' => $inspiration->id,
                'x' => $validated['x'],
                'y' => $validated['y'],
                'display_order' => $displayOrder,
            ]);

            return [$item, $hotspot];
        });

        $product = Product::with(['user.shop', 'stocks'])->findOrFail($validated['product_id']);

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
                    'price' => home_discounted_base_price($product),
                    'image' => uploaded_asset($product->thumbnail_img),
                    'available' => $product->isAvailable(),
                    'stock_status' => $product->stockStatus(),
                ],
            ],
        ], 201);
    }

    public function updateHotspot(
        Request $request,
        Inspiration $inspiration,
        InspirationHotspot $hotspot
    ) {
        $this->ensureHotspotBelongsToInspiration($hotspot, $inspiration);

        $validated = $request->validate([
            'x' => ['sometimes', 'required_with:y', 'numeric', 'between:0,1'],
            'y' => ['sometimes', 'required_with:x', 'numeric', 'between:0,1'],
            'product_id' => [
                'sometimes',
                'exists:products,id',
                Rule::unique('inspiration_items', 'product_id')
                    ->where(fn ($query) => $query->where('inspiration_id', $inspiration->id))
                    ->ignore($hotspot->inspiration_item_id),
            ],
        ]);

        if ($validated === []) {
            throw ValidationException::withMessages([
                'hotspot' => [translate('A position or product change is required')],
            ]);
        }

        DB::transaction(function () use ($validated, $hotspot) {
            if (array_key_exists('x', $validated)) {
                $hotspot->update(['x' => $validated['x'], 'y' => $validated['y']]);
            }

            if (array_key_exists('product_id', $validated)) {
                $hotspot->item()->update(['product_id' => $validated['product_id']]);
            }
        });

        return response()->json(['success' => true]);
    }

    public function destroyHotspot(
        Request $request,
        Inspiration $inspiration,
        InspirationHotspot $hotspot
    ) {
        $this->ensureHotspotBelongsToInspiration($hotspot, $inspiration);

        DB::transaction(function () use ($hotspot) {
            $item = $hotspot->item;
            $hotspot->delete();
            $item?->delete();
        });

        return response()->json(['success' => true]);
    }

    private function validatedData(Request $request, ?Inspiration $inspiration = null): array
    {
        $data = $request->validate([
            'title_fr' => ['required', 'string', 'max:255'],
            'title_ar' => ['nullable', 'string', 'max:255'],
            'subtitle_fr' => ['nullable', 'string', 'max:255'],
            'subtitle_ar' => ['nullable', 'string', 'max:255'],
            'description_fr' => ['nullable', 'string'],
            'description_ar' => ['nullable', 'string'],
            'slug' => [
                'nullable', 'string', 'max:255',
                Rule::unique('inspirations', 'slug')->ignore($inspiration?->id),
            ],
            'status' => ['required', Rule::in(['draft', 'published', 'archived'])],
            'sort_order' => ['nullable', 'integer', 'min:0'],
            'starts_at' => ['nullable', 'date'],
            'ends_at' => ['nullable', 'date', 'after_or_equal:starts_at'],
            'hero_image' => [
                $inspiration ? 'nullable' : 'required',
                'image',
                'mimes:jpeg,jpg,png,webp',
                'max:10240',
                'dimensions:min_width=800,min_height=400,max_width=6000,max_height=6000',
            ],
        ]);

        unset($data['hero_image']);
        $requestedSlug = $data['slug'] ?? null;
        $data['slug'] = Str::slug($requestedSlug ?: $data['title_fr']);

        if (!$requestedSlug) {
            $baseSlug = $data['slug'];
            $suffix = 2;
            while (Inspiration::where('slug', $data['slug'])
                ->when($inspiration, fn ($query) => $query->where('id', '!=', $inspiration->id))
                ->exists()) {
                $data['slug'] = $baseSlug.'-'.$suffix++;
            }
        }

        $data['is_featured'] = $request->boolean('is_featured');
        $data['show_on_home'] = $request->boolean('show_on_home');
        $data['sort_order'] = $data['sort_order'] ?? 0;

        if ($data['status'] === 'published' && !$inspiration?->published_at) {
            $data['published_at'] = now();
        }

        return $data;
    }

    private function handleImageUpload(Request $request, array $data): array
    {
        if (!$request->hasFile('hero_image')) {
            return $data;
        }

        $file = $request->file('hero_image');
        $data['hero_image'] = $file->store('inspirations', 'public');
        $imageSize = getimagesize($file->getRealPath());

        if ($imageSize) {
            $data['hero_image_width'] = $imageSize[0];
            $data['hero_image_height'] = $imageSize[1];
        }

        return $data;
    }

    private function validateForPublication(
        Inspiration $inspiration,
        array $data,
        bool $hasNewImage
    ): array {
        $errors = [];
        $currentImageExists = $inspiration->hero_image
            && Storage::disk('public')->exists($inspiration->hero_image);

        if (!$hasNewImage && !$currentImageExists) {
            $errors['hero_image'][] = translate("L'image de la scene est requise");
        }

        if (empty($data['title_fr'] ?? $inspiration->title_fr)) {
            $errors['title_fr'][] = translate('Le titre (francais) est requis');
        }

        $totalItems = $inspiration->items()->count();
        $validItems = $inspiration->items()->whereHas('product')->count();

        if ($validItems === 0) {
            $errors['items'][] = translate('Au moins un produit doit etre associe');
        }

        $unpositioned = $inspiration->items()->whereDoesntHave('hotspot')->count();
        if ($unpositioned > 0) {
            $errors['hotspots'][] = translate(
                "$unpositioned produits ne sont pas positionnes dans l'image"
            );
        }

        $missingProducts = $totalItems - $validItems;
        if ($missingProducts > 0) {
            $errors['products'][] = translate(
                "$missingProducts produits references n'existent plus dans le catalogue"
            );
        }

        return $errors;
    }

    private function ensureHotspotBelongsToInspiration(
        InspirationHotspot $hotspot,
        Inspiration $inspiration
    ): void {
        abort_unless(
            $hotspot->inspiration_id === $inspiration->id
            && $hotspot->item?->inspiration_id === $inspiration->id,
            404
        );
    }
}
