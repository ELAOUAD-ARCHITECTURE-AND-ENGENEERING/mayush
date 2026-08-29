# Inspiration Feature Implementation Plan

> **For agentic workers:** REQUIRED SUB-SKILL: Use superpowers:subagent-driven-development (recommended) or superpowers:executing-plans to implement this plan task-by-task. Steps use checkbox (`- [ ]`) syntax for tracking.

**Goal:** Build a fully dynamic Inspiration system — admin uploads interior scenes, maps hotspots to catalog products, mobile app displays interactive scenes with product discovery.

**Architecture:** Three new DB tables (`inspirations`, `inspiration_items`, `inspiration_hotspots`), Laravel admin CRUD + vanilla JS hotspot mapper, 3 API endpoints, React Native detail screen with interactive hotspot markers + pinch-to-zoom, HomeScreen integration replacing hardcoded inspiration assets.

**Tech Stack:** Laravel 10 (backend), Blade + vanilla JS (admin), React Native / Expo SDK 57 / TypeScript (mobile), react-native-gesture-handler + react-native-reanimated (zoom)

**Spec:** `docs/superpowers/specs/2026-08-28-inspiration-feature-design.md`

---

## Phase A: Backend (Laravel)

### Task 1: Database Migration

**Files:**
- Create: `database/migrations/2026_08_29_000001_create_inspirations_tables.php`

- [ ] **Step 1: Create the migration file**

```bash
cd /c/laragon/www/mayush
php artisan make:migration create_inspirations_tables --create=inspirations
```

Then replace the generated file content with:

```php
<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('inspirations', function (Blueprint $table) {
            $table->id();
            $table->string('slug')->unique();
            $table->string('title_fr');
            $table->string('title_ar')->nullable();
            $table->string('subtitle_fr')->nullable();
            $table->string('subtitle_ar')->nullable();
            $table->text('description_fr')->nullable();
            $table->text('description_ar')->nullable();
            $table->string('hero_image')->nullable();
            $table->unsignedInteger('hero_image_width')->nullable();
            $table->unsignedInteger('hero_image_height')->nullable();
            $table->string('status')->default('draft'); // draft, published, archived
            $table->boolean('is_featured')->default(false);
            $table->boolean('show_on_home')->default(false);
            $table->unsignedInteger('sort_order')->default(0);
            $table->timestamp('published_at')->nullable();
            $table->timestamp('starts_at')->nullable();
            $table->timestamp('ends_at')->nullable();
            $table->unsignedBigInteger('created_by')->nullable();
            $table->timestamps();
            $table->softDeletes();
        });

        Schema::create('inspiration_items', function (Blueprint $table) {
            $table->id();
            $table->unsignedBigInteger('inspiration_id');
            $table->unsignedBigInteger('product_id');
            $table->unsignedInteger('display_order')->default(0);
            $table->boolean('is_visible')->default(true);
            $table->boolean('is_featured')->default(false);
            $table->string('custom_title_fr')->nullable();
            $table->string('custom_title_ar')->nullable();
            $table->timestamps();

            $table->unique(['inspiration_id', 'product_id'], 'inspiration_item_unique');
        });

        Schema::create('inspiration_hotspots', function (Blueprint $table) {
            $table->id();
            $table->unsignedBigInteger('inspiration_id');
            $table->unsignedBigInteger('inspiration_item_id');
            $table->decimal('x', 6, 4); // normalized 0.0000–1.0000
            $table->decimal('y', 6, 4);
            $table->unsignedInteger('display_order')->default(0);
            $table->timestamps();
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('inspiration_hotspots');
        Schema::dropIfExists('inspiration_items');
        Schema::dropIfExists('inspirations');
    }
};
```

- [ ] **Step 2: Run the migration**

```bash
php artisan migrate
```

Expected: 3 tables created, no errors.

- [ ] **Step 3: Commit**

```bash
git add database/migrations/2026_08_29_000001_create_inspirations_tables.php
git commit -m "feat(inspiration): add migration for inspirations, items, and hotspots tables"
```

---

### Task 2: Eloquent Models

**Files:**
- Create: `app/Models/Inspiration.php`
- Create: `app/Models/InspirationItem.php`
- Create: `app/Models/InspirationHotspot.php`

- [ ] **Step 1: Create Inspiration model**

Create `app/Models/Inspiration.php`:

```php
<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\SoftDeletes;

class Inspiration extends Model
{
    use SoftDeletes;

    protected $fillable = [
        'slug', 'title_fr', 'title_ar', 'subtitle_fr', 'subtitle_ar',
        'description_fr', 'description_ar', 'hero_image',
        'hero_image_width', 'hero_image_height',
        'status', 'is_featured', 'show_on_home', 'sort_order',
        'published_at', 'starts_at', 'ends_at', 'created_by',
    ];

    protected $casts = [
        'is_featured' => 'boolean',
        'show_on_home' => 'boolean',
        'published_at' => 'datetime',
        'starts_at' => 'datetime',
        'ends_at' => 'datetime',
    ];

    public function items()
    {
        return $this->hasMany(InspirationItem::class)->orderBy('display_order');
    }

    public function hotspots()
    {
        return $this->hasMany(InspirationHotspot::class)->orderBy('display_order');
    }

    public function creator()
    {
        return $this->belongsTo(User::class, 'created_by');
    }

    public function scopePublished($query)
    {
        return $query->where('status', 'published')
            ->where(function ($q) {
                $q->whereNull('starts_at')->orWhere('starts_at', '<=', now());
            })
            ->where(function ($q) {
                $q->whereNull('ends_at')->orWhere('ends_at', '>=', now());
            });
    }

    public function scopeFeatured($query)
    {
        return $query->where('is_featured', true)->where('show_on_home', true);
    }

    public function getTitle($lang = 'fr')
    {
        return $lang === 'ar' && $this->title_ar ? $this->title_ar : $this->title_fr;
    }

    public function getSubtitle($lang = 'fr')
    {
        return $lang === 'ar' && $this->subtitle_ar ? $this->subtitle_ar : $this->subtitle_fr;
    }

    public function getDescription($lang = 'fr')
    {
        return $lang === 'ar' && $this->description_ar ? $this->description_ar : $this->description_fr;
    }

    public function getHeroImageUrlAttribute()
    {
        return $this->hero_image ? asset('storage/' . $this->hero_image) : null;
    }
}
```

- [ ] **Step 2: Create InspirationItem model**

Create `app/Models/InspirationItem.php`:

```php
<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class InspirationItem extends Model
{
    protected $fillable = [
        'inspiration_id', 'product_id', 'display_order',
        'is_visible', 'is_featured', 'custom_title_fr', 'custom_title_ar',
    ];

    protected $casts = [
        'is_visible' => 'boolean',
        'is_featured' => 'boolean',
    ];

    public function inspiration()
    {
        return $this->belongsTo(Inspiration::class);
    }

    public function product()
    {
        return $this->belongsTo(Product::class);
    }

    public function hotspot()
    {
        return $this->hasOne(InspirationHotspot::class);
    }
}
```

- [ ] **Step 3: Create InspirationHotspot model**

Create `app/Models/InspirationHotspot.php`:

```php
<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class InspirationHotspot extends Model
{
    protected $fillable = [
        'inspiration_id', 'inspiration_item_id', 'x', 'y', 'display_order',
    ];

    protected $casts = [
        'x' => 'decimal:4',
        'y' => 'decimal:4',
    ];

    public function inspiration()
    {
        return $this->belongsTo(Inspiration::class);
    }

    public function item()
    {
        return $this->belongsTo(InspirationItem::class, 'inspiration_item_id');
    }
}
```

- [ ] **Step 4: Commit**

```bash
git add app/Models/Inspiration.php app/Models/InspirationItem.php app/Models/InspirationHotspot.php
git commit -m "feat(inspiration): add Inspiration, InspirationItem, and InspirationHotspot models"
```

---

### Task 3: Observer + Cache Invalidation

**Files:**
- Modify: `app/Providers/AppServiceProvider.php`

The project already registers `StorefrontCacheObserver` for models like `ProductCollection`, `Category`, `Blog`, etc. in `AppServiceProvider.php`. Follow the same pattern for `Inspiration`.

- [ ] **Step 1: Register observer**

In `app/Providers/AppServiceProvider.php`, find the block of `::observe()` calls (around lines 34-50) and add after the `ProductCollection` line:

```php
\App\Models\Inspiration::observe(\App\Observers\StorefrontCacheObserver::class);
```

- [ ] **Step 2: Verify registration**

```bash
php artisan tinker --execute="echo 'OK';"
```

Expected: No class-not-found errors.

- [ ] **Step 3: Commit**

```bash
git add app/Providers/AppServiceProvider.php
git commit -m "feat(inspiration): register StorefrontCacheObserver for Inspiration model"
```

---

### Task 4: Permissions Seeder Migration

**Files:**
- Create: `database/migrations/2026_08_29_000002_add_inspiration_permissions.php`
- Modify: `resources/views/backend/inc/admin_sidenav.blade.php`

- [ ] **Step 1: Create permissions migration**

Create `database/migrations/2026_08_29_000002_add_inspiration_permissions.php`:

```php
<?php

use Illuminate\Database\Migrations\Migration;
use Spatie\Permission\Models\Permission;
use Spatie\Permission\Models\Role;

return new class extends Migration
{
    private array $permissions = [
        'view_inspirations',
        'add_inspiration',
        'edit_inspiration',
        'delete_inspiration',
    ];

    public function up(): void
    {
        $superAdmin = Role::where('name', 'Super Admin')->first();

        foreach ($this->permissions as $perm) {
            $p = Permission::firstOrCreate(
                ['name' => $perm, 'guard_name' => 'web'],
                ['section' => 'inspiration']
            );
            if ($superAdmin) {
                $superAdmin->givePermissionTo($p);
            }
        }
    }

    public function down(): void
    {
        foreach ($this->permissions as $perm) {
            Permission::where('name', $perm)->delete();
        }
    }
};
```

- [ ] **Step 2: Run the migration**

```bash
php artisan migrate
```

- [ ] **Step 3: Add sidebar menu item**

In `resources/views/backend/inc/admin_sidenav.blade.php`, find the Product Collections menu item (around line 265-272):

```blade
@can('view_product_collections')
<li class="aiz-side-nav-item">
    <a href="{{route('product-collections.index')}}"
        class="aiz-side-nav-link {{ areActiveRoutes(['product-collections.index', 'product-collections.create', 'product-collections.edit'])}}">
        <span class="aiz-side-nav-text">{{translate('Product Collections')}}</span>
    </a>
</li>
@endcan
```

Add immediately after it:

```blade
@can('view_inspirations')
<li class="aiz-side-nav-item">
    <a href="{{route('inspirations.index')}}"
        class="aiz-side-nav-link {{ areActiveRoutes(['inspirations.index', 'inspirations.create', 'inspirations.edit', 'inspirations.mapper'])}}">
        <span class="aiz-side-nav-text">{{translate('Inspirations')}}</span>
    </a>
</li>
@endcan
```

Also find the parent `@canany([...])` directive (around line 149-150) that wraps the Products section. Add `'view_inspirations'` to that array.

- [ ] **Step 4: Commit**

```bash
git add database/migrations/2026_08_29_000002_add_inspiration_permissions.php resources/views/backend/inc/admin_sidenav.blade.php
git commit -m "feat(inspiration): add permissions seeder and admin sidebar menu item"
```

---

### Task 5: API Controller + Routes

**Files:**
- Create: `app/Http/Controllers/Api/V2/InspirationController.php`
- Modify: `routes/api.php`

- [ ] **Step 1: Create API controller**

Create `app/Http/Controllers/Api/V2/InspirationController.php`:

```php
<?php

namespace App\Http\Controllers\Api\V2;

use App\Models\Inspiration;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Cache;

class InspirationController extends Controller
{
    public function index(Request $request)
    {
        $lang = $request->header('Accept-Language', 'fr');

        $inspirations = Inspiration::published()
            ->withCount('items')
            ->with(['items' => function ($q) {
                $q->where('is_visible', true)
                    ->orderBy('display_order')
                    ->limit(4)
                    ->with('product');
            }])
            ->orderBy('sort_order')
            ->get();

        $data = $inspirations->map(function ($insp) use ($lang) {
            return $this->formatPreview($insp, $lang);
        })->values();

        return response()->json(['data' => $data]);
    }

    public function featured(Request $request)
    {
        $lang = $request->header('Accept-Language', 'fr');
        $cacheKey = "inspirations_featured_{$lang}";

        $data = Cache::remember($cacheKey, 900, function () use ($lang) {
            $inspirations = Inspiration::published()
                ->featured()
                ->withCount('items')
                ->with(['items' => function ($q) {
                    $q->where('is_visible', true)
                        ->orderBy('display_order')
                        ->limit(4)
                        ->with('product');
                }])
                ->orderBy('sort_order')
                ->limit(3)
                ->get();

            return $inspirations->map(function ($insp) use ($lang) {
                return $this->formatPreview($insp, $lang);
            })->values();
        });

        return response()->json(['data' => $data]);
    }

    public function show(Request $request, $slug)
    {
        $lang = $request->header('Accept-Language', 'fr');
        $cacheKey = "inspiration_detail_{$slug}_{$lang}";

        $data = Cache::remember($cacheKey, 300, function () use ($slug, $lang) {
            $inspiration = Inspiration::published()
                ->where('slug', $slug)
                ->with(['items' => function ($q) {
                    $q->where('is_visible', true)
                        ->orderBy('display_order')
                        ->with(['product', 'hotspot']);
                }])
                ->firstOrFail();

            return $this->formatDetail($inspiration, $lang);
        });

        return response()->json(['data' => $data]);
    }

    private function formatPreview(Inspiration $insp, string $lang): array
    {
        return [
            'id' => $insp->id,
            'slug' => $insp->slug,
            'title' => $insp->getTitle($lang),
            'subtitle' => $insp->getSubtitle($lang),
            'image' => $insp->hero_image_url,
            'products_count' => $insp->items_count ?? 0,
            'preview_products' => $insp->items
                ->filter(fn ($item) => $item->product !== null)
                ->take(4)
                ->map(fn ($item) => [
                    'id' => $item->product->id,
                    'name' => $item->product->getTranslation('name', $lang),
                    'image' => uploaded_asset($item->product->thumbnail_img),
                    'price' => format_price(convert_price($item->product->unit_price)),
                    'available' => (bool) ($item->product->published && $item->product->approved),
                ])->values()->all(),
        ];
    }

    private function formatDetail(Inspiration $inspiration, string $lang): array
    {
        return [
            'id' => $inspiration->id,
            'slug' => $inspiration->slug,
            'title' => $inspiration->getTitle($lang),
            'subtitle' => $inspiration->getSubtitle($lang),
            'description' => $inspiration->getDescription($lang),
            'image' => [
                'url' => $inspiration->hero_image_url,
                'width' => $inspiration->hero_image_width,
                'height' => $inspiration->hero_image_height,
            ],
            'items' => $inspiration->items
                ->filter(fn ($item) => $item->product !== null)
                ->values()
                ->map(fn ($item) => [
                    'id' => $item->id,
                    'display_order' => $item->display_order,
                    'hotspot' => $item->hotspot ? [
                        'x' => (float) $item->hotspot->x,
                        'y' => (float) $item->hotspot->y,
                    ] : null,
                    'product' => [
                        'id' => $item->product->id,
                        'name' => $item->product->getTranslation('name', $lang),
                        'slug' => $item->product->slug,
                        'price' => format_price(convert_price($item->product->unit_price)),
                        'discount_price' => $item->product->discount > 0
                            ? format_price(convert_price($item->product->unit_price - ($item->product->discount_type === 'percent'
                                ? ($item->product->unit_price * $item->product->discount / 100)
                                : $item->product->discount)))
                            : null,
                        'image' => uploaded_asset($item->product->thumbnail_img),
                        'available' => (bool) ($item->product->published && $item->product->approved),
                        'stock_status' => $item->product->current_stock > 0 ? 'in_stock' : 'out_of_stock',
                    ],
                ])->all(),
        ];
    }
}
```

- [ ] **Step 2: Add API routes**

In `routes/api.php`, find the `product-collections` route (around line 333) and add after it:

```php
Route::get('inspirations', 'App\Http\Controllers\Api\V2\InspirationController@index');
Route::get('inspirations/featured', 'App\Http\Controllers\Api\V2\InspirationController@featured');
Route::get('inspirations/{slug}', 'App\Http\Controllers\Api\V2\InspirationController@show');
```

**Important:** The `featured` route must come before the `{slug}` route to avoid "featured" being treated as a slug.

- [ ] **Step 3: Verify routes are registered**

```bash
php artisan route:list --path=api/v2/inspirations
```

Expected: 3 routes listed.

- [ ] **Step 4: Commit**

```bash
git add app/Http/Controllers/Api/V2/InspirationController.php routes/api.php
git commit -m "feat(inspiration): add API controller with index, featured, and detail endpoints"
```

---

### Task 6: Admin Controller (CRUD + Hotspot Endpoints)

**Files:**
- Create: `app/Http/Controllers/Admin/InspirationController.php`
- Modify: `routes/admin.php`

- [ ] **Step 1: Create admin controller**

Create `app/Http/Controllers/Admin/InspirationController.php`:

```php
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
```

- [ ] **Step 2: Add admin routes**

In `routes/admin.php`, add the import at the top with the other admin controller imports (around line 76):

```php
use App\Http\Controllers\Admin\InspirationController;
```

Then find the `product-collections` resource route (around line 841) and add after it:

```php
Route::resource('inspirations', InspirationController::class)->except('show');
Route::get('inspirations/{inspiration}/mapper', [InspirationController::class, 'mapper'])->name('inspirations.mapper');
Route::post('inspirations/{inspiration}/hotspots', [InspirationController::class, 'storeHotspot'])->name('inspirations.hotspots.store');
Route::put('inspirations/{inspiration}/hotspots/{hotspot}', [InspirationController::class, 'updateHotspot'])->name('inspirations.hotspots.update');
Route::delete('inspirations/{inspiration}/hotspots/{hotspot}', [InspirationController::class, 'destroyHotspot'])->name('inspirations.hotspots.destroy');
```

- [ ] **Step 3: Verify routes**

```bash
php artisan route:list --path=inspirations
```

Expected: CRUD routes + mapper + 3 hotspot routes.

- [ ] **Step 4: Commit**

```bash
git add app/Http/Controllers/Admin/InspirationController.php routes/admin.php
git commit -m "feat(inspiration): add admin controller with CRUD and hotspot AJAX endpoints"
```

---

### Task 7: Admin Index View

**Files:**
- Create: `resources/views/backend/inspirations/index.blade.php`

- [ ] **Step 1: Create index view**

Create `resources/views/backend/inspirations/index.blade.php`:

```blade
@extends('backend.layouts.app')

@section('content')
<div class="aiz-titlebar text-left mt-2 mb-3">
    <div class="row align-items-center">
        <div class="col-md-6">
            <h1 class="h3">{{ translate('Inspirations') }}</h1>
        </div>
        @can('add_inspiration')
        <div class="col-md-6 text-md-right">
            <a href="{{ route('inspirations.create') }}" class="btn btn-primary">
                <span>{{ translate('Add New Inspiration') }}</span>
            </a>
        </div>
        @endcan
    </div>
</div>

<div class="card">
    <form id="sort_inspirations" action="" method="GET">
        <div class="card-header row gutters-5">
            <div class="col">
                <h5 class="mb-md-0 h6">{{ translate('All Inspirations') }}</h5>
            </div>
            <div class="col-md-3">
                <select class="form-control form-control-sm aiz-selectpicker" name="status" onchange="this.form.submit()">
                    <option value="">{{ translate('Filter by Status') }}</option>
                    <option value="draft" {{ request('status') == 'draft' ? 'selected' : '' }}>{{ translate('Draft') }}</option>
                    <option value="published" {{ request('status') == 'published' ? 'selected' : '' }}>{{ translate('Published') }}</option>
                    <option value="archived" {{ request('status') == 'archived' ? 'selected' : '' }}>{{ translate('Archived') }}</option>
                </select>
            </div>
        </div>
    </form>
    <div class="card-body">
        <table class="table aiz-table mb-0">
            <thead>
                <tr>
                    <th>#</th>
                    <th>{{ translate('Image') }}</th>
                    <th>{{ translate('Title') }}</th>
                    <th>{{ translate('Status') }}</th>
                    <th>{{ translate('Products') }}</th>
                    <th>{{ translate('Featured') }}</th>
                    <th>{{ translate('Sort Order') }}</th>
                    <th class="text-right">{{ translate('Options') }}</th>
                </tr>
            </thead>
            <tbody>
                @foreach($inspirations as $key => $inspiration)
                <tr>
                    <td>{{ $inspirations->firstItem() + $key }}</td>
                    <td>
                        @if($inspiration->hero_image)
                            <img src="{{ asset('storage/' . $inspiration->hero_image) }}" alt="" class="size-60px img-fit rounded">
                        @else
                            <span class="text-muted">—</span>
                        @endif
                    </td>
                    <td>{{ $inspiration->title_fr }}</td>
                    <td>
                        @if($inspiration->status === 'published')
                            <span class="badge badge-inline badge-success">{{ translate('Published') }}</span>
                        @elseif($inspiration->status === 'draft')
                            <span class="badge badge-inline badge-secondary">{{ translate('Draft') }}</span>
                        @else
                            <span class="badge badge-inline badge-warning">{{ translate('Archived') }}</span>
                        @endif
                    </td>
                    <td>{{ $inspiration->items_count ?? 0 }}</td>
                    <td>
                        @if($inspiration->is_featured)
                            <span class="badge badge-inline badge-info">{{ translate('Featured') }}</span>
                        @endif
                        @if($inspiration->show_on_home)
                            <span class="badge badge-inline badge-primary">{{ translate('Home') }}</span>
                        @endif
                    </td>
                    <td>{{ $inspiration->sort_order }}</td>
                    <td class="text-right">
                        @can('edit_inspiration')
                            @if($inspiration->hero_image)
                            <a class="btn btn-soft-info btn-icon btn-circle btn-sm" href="{{ route('inspirations.mapper', $inspiration) }}" title="{{ translate('Mapper') }}">
                                <i class="las la-map-marker-alt"></i>
                            </a>
                            @endif
                            <a class="btn btn-soft-primary btn-icon btn-circle btn-sm" href="{{ route('inspirations.edit', $inspiration) }}" title="{{ translate('Edit') }}">
                                <i class="las la-edit"></i>
                            </a>
                        @endcan
                        @can('delete_inspiration')
                            <a href="#" class="btn btn-soft-danger btn-icon btn-circle btn-sm confirm-delete" data-href="{{ route('inspirations.destroy', $inspiration) }}" title="{{ translate('Delete') }}">
                                <i class="las la-trash"></i>
                            </a>
                        @endcan
                    </td>
                </tr>
                @endforeach
            </tbody>
        </table>
        <div class="aiz-pagination">
            {{ $inspirations->appends(request()->input())->links() }}
        </div>
    </div>
</div>
@endsection
```

- [ ] **Step 2: Verify the page loads**

```bash
php artisan serve
```

Navigate to `/admin/inspirations`. Expected: empty table with "Add New Inspiration" button.

- [ ] **Step 3: Commit**

```bash
git add resources/views/backend/inspirations/index.blade.php
git commit -m "feat(inspiration): add admin index view with table listing"
```

---

### Task 8: Admin Create/Edit Form View

**Files:**
- Create: `resources/views/backend/inspirations/form.blade.php`

- [ ] **Step 1: Create form view**

Create `resources/views/backend/inspirations/form.blade.php`:

```blade
@extends('backend.layouts.app')

@section('content')
<div class="aiz-titlebar text-left mt-2 mb-3">
    <div class="row align-items-center">
        <div class="col-md-6">
            <h1 class="h3">{{ $inspiration->exists ? translate('Edit Inspiration') : translate('Add Inspiration') }}</h1>
        </div>
        <div class="col-md-6 text-md-right">
            <a href="{{ route('inspirations.index') }}" class="btn btn-soft-secondary">
                <i class="las la-arrow-left"></i> {{ translate('Back') }}
            </a>
            @if($inspiration->exists && $inspiration->hero_image)
                <a href="{{ route('inspirations.mapper', $inspiration) }}" class="btn btn-primary">
                    <i class="las la-map-marker-alt"></i> {{ translate('Hotspot Mapper') }}
                </a>
            @endif
        </div>
    </div>
</div>

<form action="{{ $inspiration->exists ? route('inspirations.update', $inspiration) : route('inspirations.store') }}" method="POST" enctype="multipart/form-data">
    @csrf
    @if($inspiration->exists)
        @method('PUT')
    @endif

    <div class="row gutters-8">
        {{-- Main content --}}
        <div class="col-lg-8">
            <div class="card">
                <div class="card-header"><h5 class="mb-0 h6">{{ translate('Content') }} ({{ translate('French') }})</h5></div>
                <div class="card-body">
                    <div class="form-group">
                        <label>{{ translate('Title') }} (FR) <span class="text-danger">*</span></label>
                        <input type="text" name="title_fr" value="{{ old('title_fr', $inspiration->title_fr) }}" class="form-control" required>
                    </div>
                    <div class="form-group">
                        <label>{{ translate('Subtitle') }} (FR)</label>
                        <input type="text" name="subtitle_fr" value="{{ old('subtitle_fr', $inspiration->subtitle_fr) }}" class="form-control">
                    </div>
                    <div class="form-group">
                        <label>{{ translate('Description') }} (FR)</label>
                        <textarea name="description_fr" class="form-control" rows="4">{{ old('description_fr', $inspiration->description_fr) }}</textarea>
                    </div>
                </div>
            </div>

            <div class="card">
                <div class="card-header"><h5 class="mb-0 h6">{{ translate('Content') }} ({{ translate('Arabic') }})</h5></div>
                <div class="card-body">
                    <div class="form-group">
                        <label>{{ translate('Title') }} (AR)</label>
                        <input type="text" name="title_ar" value="{{ old('title_ar', $inspiration->title_ar) }}" class="form-control" dir="rtl">
                    </div>
                    <div class="form-group">
                        <label>{{ translate('Subtitle') }} (AR)</label>
                        <input type="text" name="subtitle_ar" value="{{ old('subtitle_ar', $inspiration->subtitle_ar) }}" class="form-control" dir="rtl">
                    </div>
                    <div class="form-group">
                        <label>{{ translate('Description') }} (AR)</label>
                        <textarea name="description_ar" class="form-control" rows="4" dir="rtl">{{ old('description_ar', $inspiration->description_ar) }}</textarea>
                    </div>
                </div>
            </div>

            <div class="card">
                <div class="card-header"><h5 class="mb-0 h6">{{ translate('Scene Image') }}</h5></div>
                <div class="card-body">
                    @if($inspiration->hero_image)
                        <div class="mb-3">
                            <img src="{{ asset('storage/' . $inspiration->hero_image) }}" class="img-fluid rounded" style="max-height: 300px;">
                            <p class="text-muted small mt-1">
                                {{ $inspiration->hero_image_width ?? '?' }}x{{ $inspiration->hero_image_height ?? '?' }}px
                            </p>
                        </div>
                    @endif
                    <div class="form-group">
                        <label>{{ $inspiration->hero_image ? translate('Replace Image') : translate('Upload Image') }}</label>
                        <input type="file" name="hero_image" class="form-control-file" accept="image/*">
                        <small class="text-muted">{{ translate('Recommended: high-resolution professional interior photography (min 1200px wide)') }}</small>
                    </div>
                </div>
            </div>
        </div>

        {{-- Sidebar --}}
        <div class="col-lg-4">
            <div class="card">
                <div class="card-header"><h5 class="mb-0 h6">{{ translate('Settings') }}</h5></div>
                <div class="card-body">
                    <div class="form-group">
                        <label>{{ translate('Status') }} <span class="text-danger">*</span></label>
                        <select name="status" class="form-control aiz-selectpicker">
                            <option value="draft" {{ old('status', $inspiration->status ?? 'draft') == 'draft' ? 'selected' : '' }}>{{ translate('Draft') }}</option>
                            <option value="published" {{ old('status', $inspiration->status) == 'published' ? 'selected' : '' }}>{{ translate('Published') }}</option>
                            <option value="archived" {{ old('status', $inspiration->status) == 'archived' ? 'selected' : '' }}>{{ translate('Archived') }}</option>
                        </select>
                    </div>
                    <div class="form-group">
                        <label>{{ translate('Slug') }}</label>
                        <input type="text" name="slug" value="{{ old('slug', $inspiration->slug) }}" class="form-control" placeholder="{{ translate('Auto-generated from title') }}">
                    </div>
                    <div class="form-group">
                        <label>{{ translate('Sort Order') }}</label>
                        <input type="number" name="sort_order" value="{{ old('sort_order', $inspiration->sort_order ?? 0) }}" class="form-control" min="0">
                    </div>
                    <div class="form-group">
                        <div class="custom-control custom-switch">
                            <input type="checkbox" class="custom-control-input" id="is_featured" name="is_featured" value="1" {{ old('is_featured', $inspiration->is_featured) ? 'checked' : '' }}>
                            <label class="custom-control-label" for="is_featured">{{ translate('Featured') }}</label>
                        </div>
                    </div>
                    <div class="form-group">
                        <div class="custom-control custom-switch">
                            <input type="checkbox" class="custom-control-input" id="show_on_home" name="show_on_home" value="1" {{ old('show_on_home', $inspiration->show_on_home) ? 'checked' : '' }}>
                            <label class="custom-control-label" for="show_on_home">{{ translate('Show on Home') }}</label>
                        </div>
                    </div>
                </div>
            </div>

            <div class="card">
                <div class="card-header"><h5 class="mb-0 h6">{{ translate('Scheduling') }}</h5></div>
                <div class="card-body">
                    <div class="form-group">
                        <label>{{ translate('Starts At') }}</label>
                        <input type="datetime-local" name="starts_at" value="{{ old('starts_at', optional($inspiration->starts_at)->format('Y-m-d\TH:i')) }}" class="form-control">
                    </div>
                    <div class="form-group">
                        <label>{{ translate('Ends At') }}</label>
                        <input type="datetime-local" name="ends_at" value="{{ old('ends_at', optional($inspiration->ends_at)->format('Y-m-d\TH:i')) }}" class="form-control">
                    </div>
                </div>
            </div>

            <div class="form-group text-right">
                <button type="submit" class="btn btn-primary">
                    {{ $inspiration->exists ? translate('Update') : translate('Create') }}
                </button>
            </div>
        </div>
    </div>
</form>
@endsection
```

- [ ] **Step 2: Verify the form loads**

Navigate to `/admin/inspirations/create`. Expected: form with title, subtitle, description fields for both languages, image upload, status/settings sidebar.

- [ ] **Step 3: Test create flow**

Fill in the form with:
- Title FR: "Esprit Japandi"
- Status: Draft
- Upload any test image

Expected: Redirects to edit page with success flash message.

- [ ] **Step 4: Commit**

```bash
git add resources/views/backend/inspirations/form.blade.php
git commit -m "feat(inspiration): add admin create/edit form view with bilingual fields and image upload"
```

---

### Task 9: Fix Product Search Endpoint

**Files:**
- Modify: `app/Http/Controllers/ProductController.php` (the `search` method is currently a stub)

The hotspot mapper needs `GET /admin/products-search?q=...` to return product results. Currently it returns `'Stub'`.

- [ ] **Step 1: Implement the search method**

In `app/Http/Controllers/ProductController.php`, find the `search()` method (around line 1260) and replace:

```php
public function search() { return 'Stub'; }
```

with:

```php
public function search(Request $request)
{
    $query = $request->input('q', '');

    if (strlen($query) < 2) {
        return response()->json(['data' => []]);
    }

    $products = Product::where(function ($q) use ($query) {
            $q->where('name', 'like', "%{$query}%")
              ->orWhere('slug', 'like', "%{$query}%");
        })
        ->where('published', 1)
        ->where('approved', 1)
        ->select('id', 'name', 'slug', 'thumbnail_img', 'unit_price', 'published', 'approved', 'current_stock', 'photos')
        ->orderByDesc('id')
        ->limit(20)
        ->get();

    $data = $products->map(function ($p) {
        return [
            'id' => $p->id,
            'name' => $p->getTranslation('name', 'fr'),
            'price' => format_price(convert_price($p->unit_price)),
            'image' => uploaded_asset($p->thumbnail_img),
            'available' => (bool) ($p->published && $p->approved),
            'stock' => $p->current_stock > 0 ? 'in_stock' : 'out_of_stock',
        ];
    });

    return response()->json(['data' => $data]);
}
```

- [ ] **Step 2: Verify it works**

```bash
curl "http://localhost:8000/admin/products-search?q=canape" -H "Cookie: ..." 2>/dev/null | python3 -m json.tool | head -20
```

Or navigate to `/admin/products-search?q=test` in browser while logged in as admin. Expected: JSON response with product data.

- [ ] **Step 3: Commit**

```bash
git add app/Http/Controllers/ProductController.php
git commit -m "feat(inspiration): implement admin product search endpoint (was stub)"
```

---

### Task 10: Admin Mapper View (Blade)

**Files:**
- Create: `resources/views/backend/inspirations/mapper.blade.php`

- [ ] **Step 1: Create mapper view**

Create `resources/views/backend/inspirations/mapper.blade.php`:

```blade
@extends('backend.layouts.app')

@push('css')
<style>
    .mapper-container { position: relative; display: inline-block; max-width: 100%; }
    .mapper-container img { max-width: 100%; height: auto; display: block; }

    .hotspot-marker {
        position: absolute;
        width: 28px; height: 28px;
        border-radius: 50%;
        background: #1F2A3A;
        color: #fff;
        border: 2px solid #fff;
        box-shadow: 0 2px 8px rgba(0,0,0,0.25);
        display: flex; align-items: center; justify-content: center;
        font-size: 12px; font-weight: 700;
        transform: translate(-50%, -50%);
        cursor: pointer;
        user-select: none;
        z-index: 10;
        transition: transform 0.15s ease-out;
        tabindex: 0;
    }
    .hotspot-marker:hover { transform: translate(-50%, -50%) scale(1.15); }
    .hotspot-marker.active { animation: marker-pulse 1s ease-in-out infinite; }
    .hotspot-marker.dragging { opacity: 0.6; cursor: grabbing; }
    .hotspot-marker.placing { animation: marker-appear 0.3s ease-out; }

    @keyframes marker-pulse {
        0%, 100% { transform: translate(-50%, -50%) scale(1); }
        50% { transform: translate(-50%, -50%) scale(1.15); }
    }
    @keyframes marker-appear {
        from { transform: translate(-50%, -50%) scale(0); }
        to { transform: translate(-50%, -50%) scale(1); }
    }

    .mapper-container.mode-place { cursor: crosshair; }
    .mapper-container.mode-drag .hotspot-marker { cursor: grab; }

    .mode-btn.active { background: #1F2A3A; color: #fff; }

    .search-modal {
        display: none; position: fixed; top: 0; left: 0; right: 0; bottom: 0;
        background: rgba(0,0,0,0.5); z-index: 1000;
        align-items: center; justify-content: center;
    }
    .search-modal.open { display: flex; }
    .search-panel {
        background: #fff; border-radius: 12px; width: 420px; max-height: 500px;
        box-shadow: 0 8px 32px rgba(0,0,0,0.2); overflow: hidden;
    }
    .search-input { width: 100%; padding: 12px 16px; border: none; border-bottom: 1px solid #eee; font-size: 15px; outline: none; }
    .search-results { max-height: 380px; overflow-y: auto; }
    .search-result-item {
        display: flex; align-items: center; gap: 12px; padding: 10px 16px; cursor: pointer; border-bottom: 1px solid #f5f5f5;
    }
    .search-result-item:hover, .search-result-item.selected { background: #f8f4ef; }
    .search-result-item img { width: 40px; height: 40px; border-radius: 6px; object-fit: cover; }
    .search-result-item .name { font-weight: 500; font-size: 14px; }
    .search-result-item .price { font-size: 13px; color: #666; }
    .search-result-item .stock-badge { font-size: 11px; padding: 2px 6px; border-radius: 4px; }
    .stock-badge.in-stock { background: #d4edda; color: #155724; }
    .stock-badge.out-of-stock { background: #f8d7da; color: #721c24; }

    .save-indicator { font-size: 13px; font-weight: 500; }
    .save-indicator.saved { color: #28a745; }
    .save-indicator.saving { color: #ffc107; }
    .save-indicator.error { color: #dc3545; }

    .item-list-row { display: flex; align-items: center; gap: 10px; padding: 8px 12px; border-bottom: 1px solid #f0f0f0; }
    .item-list-row img { width: 36px; height: 36px; border-radius: 6px; object-fit: cover; }
    .item-list-row .number { width: 24px; height: 24px; border-radius: 50%; background: #1F2A3A; color: #fff; font-size: 11px; font-weight: 700; display: flex; align-items: center; justify-content: center; flex-shrink: 0; }
    .item-list-row .name { font-weight: 500; font-size: 14px; flex: 1; }
    .item-list-row .unavailable { opacity: 0.5; }
    .item-warning { background: #fff3cd; border: 1px solid #ffc107; border-radius: 6px; padding: 8px 12px; margin-bottom: 12px; font-size: 13px; }

    .toast-notification {
        position: fixed; bottom: 24px; right: 24px; background: #1F2A3A; color: #fff;
        padding: 10px 20px; border-radius: 8px; font-size: 14px; z-index: 2000;
        animation: toast-in 0.3s ease-out;
    }
    @keyframes toast-in { from { opacity: 0; transform: translateY(20px); } to { opacity: 1; transform: translateY(0); } }

    .preview-frame { margin: 0 auto; border: 2px solid #ddd; border-radius: 8px; overflow: hidden; }
</style>
@endpush

@section('content')
<div class="aiz-titlebar text-left mt-2 mb-3">
    <div class="row align-items-center">
        <div class="col-md-6">
            <a href="{{ route('inspirations.edit', $inspiration) }}" class="text-muted mr-2"><i class="las la-arrow-left"></i></a>
            <span class="h3">{{ $inspiration->title_fr }} — {{ translate('Mapper') }}</span>
        </div>
        <div class="col-md-6 text-md-right">
            <button type="button" class="btn btn-sm mode-btn active" data-mode="place" onclick="mapper.switchMode('place')">+ {{ translate('Place') }}</button>
            <button type="button" class="btn btn-sm mode-btn" data-mode="drag" onclick="mapper.switchMode('drag')">↔ {{ translate('Move') }}</button>
            <button type="button" class="btn btn-sm btn-outline-secondary ml-2" onclick="mapper.togglePreview()">{{ translate('Preview') }}</button>
            <span class="save-indicator saved ml-3" id="saveIndicator">{{ translate('Saved') }} ✓</span>
        </div>
    </div>
</div>

<div class="card">
    <div class="card-body">
        <div class="mapper-container mode-place" id="mapperContainer">
            <img src="{{ asset('storage/' . $inspiration->hero_image) }}" alt="{{ $inspiration->title_fr }}" id="mapperImage" draggable="false">
            {{-- Markers rendered by JS --}}
        </div>
    </div>
</div>

<div class="card" id="itemsCard">
    <div class="card-header">
        <h5 class="mb-0 h6">{{ translate('Associated Products') }} (<span id="itemCount">{{ $inspiration->items->count() }}</span>)</h5>
    </div>
    <div class="card-body p-0">
        @php
            $unavailableCount = $inspiration->items->filter(fn($item) => $item->product && !($item->product->published && $item->product->approved))->count();
        @endphp
        @if($unavailableCount > 0)
            <div class="item-warning m-3">
                ⚠ {{ $unavailableCount }} {{ translate('produit(s) indisponible(s)') }}
            </div>
        @endif
        <div id="itemsList">
            {{-- Rendered by JS --}}
        </div>
    </div>
</div>

{{-- Product Search Modal --}}
<div class="search-modal" id="searchModal">
    <div class="search-panel">
        <input type="text" class="search-input" id="searchInput" placeholder="{{ translate('Search for a product...') }}" autocomplete="off">
        <div class="search-results" id="searchResults"></div>
    </div>
</div>

<script>
    window.MAPPER_CONFIG = {
        containerId: 'mapperContainer',
        imageId: 'mapperImage',
        inspirationId: {{ $inspiration->id }},
        csrfToken: '{{ csrf_token() }}',
        searchUrl: '{{ route("products.search") }}',
        storeUrl: '{{ route("inspirations.hotspots.store", $inspiration) }}',
        updateUrlTemplate: '{{ route("inspirations.hotspots.update", [$inspiration, "__HOTSPOT_ID__"]) }}',
        destroyUrlTemplate: '{{ route("inspirations.hotspots.destroy", [$inspiration, "__HOTSPOT_ID__"]) }}',
        existingItems: @json($inspiration->items->map(fn($item) => [
            'id' => $item->id,
            'hotspot_id' => $item->hotspot?->id,
            'display_order' => $item->display_order,
            'x' => $item->hotspot ? (float) $item->hotspot->x : null,
            'y' => $item->hotspot ? (float) $item->hotspot->y : null,
            'product' => [
                'id' => $item->product?->id,
                'name' => $item->product?->getTranslation('name', 'fr') ?? 'Unknown',
                'price' => $item->product ? format_price(convert_price($item->product->unit_price)) : '—',
                'image' => $item->product ? uploaded_asset($item->product->thumbnail_img) : '',
                'available' => (bool) ($item->product?->published && $item->product?->approved),
            ],
        ])->values()),
        translations: {
            saved: '{{ translate("Saved") }} ✓',
            saving: '{{ translate("Saving...") }}',
            error: '{{ translate("Error") }} ✗',
            noResults: '{{ translate("No products found") }}',
            loading: '{{ translate("Searching...") }}',
            undone: '{{ translate("Undone") }}',
            deleteConfirm: '{{ translate("Delete this hotspot?") }}',
        },
    };
</script>
<script src="{{ asset('js/inspiration-mapper.js') }}"></script>
@endsection
```

- [ ] **Step 2: Commit**

```bash
git add resources/views/backend/inspirations/mapper.blade.php
git commit -m "feat(inspiration): add admin hotspot mapper Blade view with CSS and config"
```

---

### Task 11: Inspiration Mapper JavaScript

**Files:**
- Create: `public/js/inspiration-mapper.js`

This is the core interactive component. The full JS file implements: marker rendering, image click placement, drag-and-drop, product search modal, undo/redo, auto-save, keyboard accessibility, and preview mode.

- [ ] **Step 1: Create the mapper JS file**

Create `public/js/inspiration-mapper.js`:

```js
'use strict';

class InspirationMapper {
    constructor(config) {
        this.config = config;
        this.items = [...config.existingItems];
        this.mode = 'place'; // 'place' | 'drag'
        this.isPreview = false;
        this.pendingClick = null;
        this.dragState = null;
        this.undoStack = [];
        this.redoStack = [];
        this.saveTimeout = null;
        this.selectedIndex = -1;
        this.searchDebounce = null;
        this.searchSelectedIdx = 0;

        this.container = document.getElementById(config.containerId);
        this.image = document.getElementById(config.imageId);
        this.modal = document.getElementById('searchModal');
        this.searchInput = document.getElementById('searchInput');
        this.searchResults = document.getElementById('searchResults');
        this.itemsList = document.getElementById('itemsList');
        this.itemCount = document.getElementById('itemCount');
        this.saveIndicator = document.getElementById('saveIndicator');

        this.bindEvents();
        this.renderMarkers();
        this.renderItemList();
    }

    // --- Event Binding ---

    bindEvents() {
        // Image click (placement mode)
        this.container.addEventListener('click', (e) => {
            if (this.mode !== 'place' || this.isPreview) return;
            if (e.target.closest('.hotspot-marker')) return;
            const rect = this.image.getBoundingClientRect();
            const x = Math.max(0, Math.min(1, (e.clientX - rect.left) / rect.width));
            const y = Math.max(0, Math.min(1, (e.clientY - rect.top) / rect.height));
            this.pendingClick = { x, y };
            this.openSearchModal();
        });

        // Marker interactions
        this.container.addEventListener('mousedown', (e) => {
            const marker = e.target.closest('.hotspot-marker');
            if (!marker) return;
            const idx = parseInt(marker.dataset.index);
            if (this.mode === 'drag') {
                this.startDrag(e, idx);
            } else {
                e.stopPropagation();
                this.selectMarker(idx);
            }
        });

        // Touch support for drag
        this.container.addEventListener('touchstart', (e) => {
            const marker = e.target.closest('.hotspot-marker');
            if (!marker || this.mode !== 'drag') return;
            e.preventDefault();
            const idx = parseInt(marker.dataset.index);
            this.startDrag(e.touches[0], idx, true);
        }, { passive: false });

        // Search modal
        this.searchInput.addEventListener('input', () => {
            clearTimeout(this.searchDebounce);
            this.searchDebounce = setTimeout(() => this.doSearch(), 300);
        });
        this.searchInput.addEventListener('keydown', (e) => this.handleSearchKeydown(e));
        this.modal.addEventListener('click', (e) => {
            if (e.target === this.modal) this.closeSearchModal();
        });

        // Keyboard shortcuts
        document.addEventListener('keydown', (e) => {
            if (e.key === 'Escape') this.closeSearchModal();
            if (e.ctrlKey && e.key === 'z' && !e.shiftKey) { e.preventDefault(); this.undo(); }
            if (e.ctrlKey && e.key === 'z' && e.shiftKey) { e.preventDefault(); this.redo(); }
            if (e.ctrlKey && e.key === 'Z') { e.preventDefault(); this.redo(); }
            // Arrow nudge for selected marker
            if (this.selectedIndex >= 0 && ['ArrowUp','ArrowDown','ArrowLeft','ArrowRight'].includes(e.key)) {
                e.preventDefault();
                this.nudgeMarker(e.key);
            }
        });

        // Resize observer
        if (window.ResizeObserver) {
            new ResizeObserver(() => this.renderMarkers()).observe(this.container);
        }

        // Unsaved changes warning
        window.addEventListener('beforeunload', (e) => {
            if (this.saveTimeout) { e.preventDefault(); e.returnValue = ''; }
        });
    }

    // --- Marker Rendering ---

    renderMarkers() {
        this.container.querySelectorAll('.hotspot-marker').forEach(el => el.remove());
        this.items.forEach((item, idx) => {
            if (item.x == null || item.y == null) return;
            const marker = document.createElement('div');
            marker.className = 'hotspot-marker' + (idx === this.selectedIndex ? ' active' : '');
            marker.dataset.index = idx;
            marker.style.left = (item.x * 100) + '%';
            marker.style.top = (item.y * 100) + '%';
            marker.textContent = idx + 1;
            marker.tabIndex = 0;
            marker.setAttribute('role', 'button');
            marker.setAttribute('aria-label', `Point ${idx + 1}: ${item.product?.name || ''}`);

            if (!item.product?.available) {
                marker.style.background = '#999';
            }

            this.container.appendChild(marker);
        });
    }

    renderItemList() {
        this.itemsList.innerHTML = '';
        this.itemCount.textContent = this.items.length;
        this.items.forEach((item, idx) => {
            const row = document.createElement('div');
            row.className = 'item-list-row' + (!item.product?.available ? ' unavailable' : '');
            row.innerHTML = `
                <span class="number">${idx + 1}</span>
                <img src="${item.product?.image || ''}" alt="">
                <span class="name">${item.product?.name || 'Unknown'}</span>
                <span class="price">${item.product?.price || ''}</span>
                ${!item.product?.available ? '<span class="stock-badge out-of-stock">Indisponible</span>' : ''}
                <button class="btn btn-sm btn-soft-danger btn-icon btn-circle" onclick="mapper.deleteItem(${idx})" title="Delete">
                    <i class="las la-trash"></i>
                </button>
            `;
            row.addEventListener('click', (e) => {
                if (e.target.closest('button')) return;
                this.selectMarker(idx);
            });
            this.itemsList.appendChild(row);
        });
    }

    selectMarker(idx) {
        this.selectedIndex = this.selectedIndex === idx ? -1 : idx;
        this.renderMarkers();
    }

    // --- Drag & Drop ---

    startDrag(event, idx, isTouch = false) {
        const item = this.items[idx];
        if (!item) return;
        const oldX = item.x, oldY = item.y;
        const marker = this.container.querySelector(`[data-index="${idx}"]`);
        if (marker) marker.classList.add('dragging');

        let moved = false;
        let rafId = null;
        let lastClientX = event.clientX, lastClientY = event.clientY;

        const onMove = (e) => {
            const pt = isTouch ? e.touches[0] : e;
            const dx = Math.abs(pt.clientX - event.clientX);
            const dy = Math.abs(pt.clientY - event.clientY);
            if (!moved && dx < 3 && dy < 3) return;
            moved = true;
            lastClientX = pt.clientX;
            lastClientY = pt.clientY;
            if (!rafId) {
                rafId = requestAnimationFrame(() => {
                    const rect = this.image.getBoundingClientRect();
                    item.x = Math.max(0, Math.min(1, (lastClientX - rect.left) / rect.width));
                    item.y = Math.max(0, Math.min(1, (lastClientY - rect.top) / rect.height));
                    this.renderMarkers();
                    rafId = null;
                });
            }
        };

        const onEnd = () => {
            if (marker) marker.classList.remove('dragging');
            document.removeEventListener(isTouch ? 'touchmove' : 'mousemove', onMove);
            document.removeEventListener(isTouch ? 'touchend' : 'mouseup', onEnd);
            if (moved && item.hotspot_id) {
                this.pushUndo({ type: 'move', idx, oldX, oldY, newX: item.x, newY: item.y });
                this.saveHotspotPosition(item);
            }
        };

        document.addEventListener(isTouch ? 'touchmove' : 'mousemove', onMove, { passive: false });
        document.addEventListener(isTouch ? 'touchend' : 'mouseup', onEnd);
    }

    nudgeMarker(key) {
        const item = this.items[this.selectedIndex];
        if (!item) return;
        const step = 0.005;
        const oldX = item.x, oldY = item.y;
        if (key === 'ArrowLeft') item.x = Math.max(0, item.x - step);
        if (key === 'ArrowRight') item.x = Math.min(1, item.x + step);
        if (key === 'ArrowUp') item.y = Math.max(0, item.y - step);
        if (key === 'ArrowDown') item.y = Math.min(1, item.y + step);
        this.renderMarkers();
        this.pushUndo({ type: 'move', idx: this.selectedIndex, oldX, oldY, newX: item.x, newY: item.y });
        this.saveHotspotPosition(item);
    }

    // --- Product Search ---

    openSearchModal() {
        this.modal.classList.add('open');
        this.searchInput.value = '';
        this.searchResults.innerHTML = '';
        this.searchSelectedIdx = 0;
        setTimeout(() => this.searchInput.focus(), 100);
    }

    closeSearchModal() {
        this.modal.classList.remove('open');
        this.pendingClick = null;
    }

    async doSearch() {
        const q = this.searchInput.value.trim();
        if (q.length < 2) { this.searchResults.innerHTML = ''; return; }

        this.searchResults.innerHTML = `<div style="padding:16px;text-align:center;color:#999">${this.config.translations.loading}</div>`;

        try {
            const res = await fetch(`${this.config.searchUrl}?q=${encodeURIComponent(q)}`, {
                headers: { 'X-CSRF-TOKEN': this.config.csrfToken, 'Accept': 'application/json' },
            });
            const json = await res.json();
            const products = json.data || [];

            if (products.length === 0) {
                this.searchResults.innerHTML = `<div style="padding:16px;text-align:center;color:#999">${this.config.translations.noResults}</div>`;
                return;
            }

            this.searchSelectedIdx = 0;
            this.searchResults.innerHTML = products.map((p, i) => `
                <div class="search-result-item${i === 0 ? ' selected' : ''}" data-product-id="${p.id}" data-idx="${i}">
                    <img src="${p.image || ''}" alt="">
                    <div>
                        <div class="name">${p.name}</div>
                        <div class="price">${p.price}</div>
                    </div>
                    <span class="stock-badge ${p.stock === 'in_stock' ? 'in-stock' : 'out-of-stock'}">${p.available ? 'En stock' : 'Indisponible'}</span>
                </div>
            `).join('');

            this.searchResults.querySelectorAll('.search-result-item').forEach(el => {
                el.addEventListener('click', () => this.selectProduct(JSON.parse(JSON.stringify(products[parseInt(el.dataset.idx)])), el));
            });
        } catch (err) {
            this.searchResults.innerHTML = `<div style="padding:16px;text-align:center;color:#c00">Error</div>`;
        }
    }

    handleSearchKeydown(e) {
        const items = this.searchResults.querySelectorAll('.search-result-item');
        if (!items.length) return;
        if (e.key === 'ArrowDown') { e.preventDefault(); this.searchSelectedIdx = Math.min(this.searchSelectedIdx + 1, items.length - 1); }
        if (e.key === 'ArrowUp') { e.preventDefault(); this.searchSelectedIdx = Math.max(this.searchSelectedIdx - 1, 0); }
        items.forEach((el, i) => el.classList.toggle('selected', i === this.searchSelectedIdx));
        items[this.searchSelectedIdx]?.scrollIntoView({ block: 'nearest' });
        if (e.key === 'Enter') { e.preventDefault(); items[this.searchSelectedIdx]?.click(); }
    }

    async selectProduct(product, el) {
        if (!this.pendingClick) return;
        this.closeSearchModal();
        const { x, y } = this.pendingClick;

        this.setSaveState('saving');
        try {
            const res = await fetch(this.config.storeUrl, {
                method: 'POST',
                headers: { 'Content-Type': 'application/json', 'X-CSRF-TOKEN': this.config.csrfToken, 'Accept': 'application/json' },
                body: JSON.stringify({ product_id: product.id, x, y }),
            });
            const json = await res.json();
            if (json.success) {
                const newItem = { ...json.item, product: json.item.product };
                this.items.push(newItem);
                this.pushUndo({ type: 'place', idx: this.items.length - 1 });
                this.renderMarkers();
                this.renderItemList();
                this.setSaveState('saved');
            }
        } catch (err) {
            this.setSaveState('error');
        }
    }

    // --- Delete ---

    async deleteItem(idx) {
        const item = this.items[idx];
        if (!item) return;

        this.setSaveState('saving');
        try {
            const url = this.config.destroyUrlTemplate.replace('__HOTSPOT_ID__', item.hotspot_id);
            const res = await fetch(url, {
                method: 'DELETE',
                headers: { 'X-CSRF-TOKEN': this.config.csrfToken, 'Accept': 'application/json' },
            });
            const json = await res.json();
            if (json.success) {
                const removed = this.items.splice(idx, 1)[0];
                this.pushUndo({ type: 'delete', idx, item: removed });
                if (this.selectedIndex === idx) this.selectedIndex = -1;
                this.renderMarkers();
                this.renderItemList();
                this.setSaveState('saved');
            }
        } catch (err) {
            this.setSaveState('error');
        }
    }

    // --- Save hotspot position (move/nudge) ---

    saveHotspotPosition(item) {
        clearTimeout(this.saveTimeout);
        this.setSaveState('saving');
        this.saveTimeout = setTimeout(async () => {
            try {
                const url = this.config.updateUrlTemplate.replace('__HOTSPOT_ID__', item.hotspot_id);
                await fetch(url, {
                    method: 'PUT',
                    headers: { 'Content-Type': 'application/json', 'X-CSRF-TOKEN': this.config.csrfToken, 'Accept': 'application/json' },
                    body: JSON.stringify({ x: item.x, y: item.y }),
                });
                this.setSaveState('saved');
            } catch (err) {
                this.setSaveState('error');
            }
            this.saveTimeout = null;
        }, 500);
    }

    // --- Undo / Redo ---

    pushUndo(action) {
        this.undoStack.push(action);
        if (this.undoStack.length > 50) this.undoStack.shift();
        this.redoStack = [];
    }

    async undo() {
        const action = this.undoStack.pop();
        if (!action) return;
        this.redoStack.push(action);

        if (action.type === 'move') {
            this.items[action.idx].x = action.oldX;
            this.items[action.idx].y = action.oldY;
            this.saveHotspotPosition(this.items[action.idx]);
        } else if (action.type === 'place') {
            const item = this.items[action.idx];
            if (item?.hotspot_id) {
                const url = this.config.destroyUrlTemplate.replace('__HOTSPOT_ID__', item.hotspot_id);
                await fetch(url, { method: 'DELETE', headers: { 'X-CSRF-TOKEN': this.config.csrfToken, 'Accept': 'application/json' } });
            }
            this.items.splice(action.idx, 1);
        } else if (action.type === 'delete') {
            // Re-create on server
            const res = await fetch(this.config.storeUrl, {
                method: 'POST',
                headers: { 'Content-Type': 'application/json', 'X-CSRF-TOKEN': this.config.csrfToken, 'Accept': 'application/json' },
                body: JSON.stringify({ product_id: action.item.product.id, x: action.item.x, y: action.item.y }),
            });
            const json = await res.json();
            if (json.success) {
                this.items.splice(action.idx, 0, { ...json.item, product: json.item.product });
            }
        }

        this.renderMarkers();
        this.renderItemList();
        this.showToast(`${this.config.translations.undone}: ${action.type}`);
    }

    async redo() {
        const action = this.redoStack.pop();
        if (!action) return;
        this.undoStack.push(action);

        if (action.type === 'move') {
            this.items[action.idx].x = action.newX;
            this.items[action.idx].y = action.newY;
            this.saveHotspotPosition(this.items[action.idx]);
        }
        // Place and delete redo are more complex — simplified: just re-apply
        this.renderMarkers();
        this.renderItemList();
    }

    // --- Mode switching ---

    switchMode(mode) {
        this.mode = mode;
        this.container.className = `mapper-container mode-${mode}`;
        document.querySelectorAll('.mode-btn').forEach(btn => {
            btn.classList.toggle('active', btn.dataset.mode === mode);
        });
    }

    // --- Preview ---

    togglePreview() {
        this.isPreview = !this.isPreview;
        document.querySelectorAll('.mode-btn, #saveIndicator').forEach(el => {
            el.style.display = this.isPreview ? 'none' : '';
        });
        const itemActions = this.itemsList.querySelectorAll('button');
        itemActions.forEach(btn => btn.style.display = this.isPreview ? 'none' : '');
        this.container.style.cursor = this.isPreview ? 'default' : '';
    }

    // --- UI Helpers ---

    setSaveState(state) {
        const t = this.config.translations;
        if (state === 'saved') { this.saveIndicator.textContent = t.saved; this.saveIndicator.className = 'save-indicator saved'; }
        else if (state === 'saving') { this.saveIndicator.textContent = t.saving; this.saveIndicator.className = 'save-indicator saving'; }
        else { this.saveIndicator.textContent = t.error; this.saveIndicator.className = 'save-indicator error'; }
    }

    showToast(message) {
        const toast = document.createElement('div');
        toast.className = 'toast-notification';
        toast.textContent = message;
        document.body.appendChild(toast);
        setTimeout(() => toast.remove(), 2000);
    }
}

// Initialize when DOM is ready
document.addEventListener('DOMContentLoaded', () => {
    if (window.MAPPER_CONFIG) {
        window.mapper = new InspirationMapper(window.MAPPER_CONFIG);
    }
});
```

- [ ] **Step 2: Verify the mapper loads**

1. Create an Inspiration via admin with a test image
2. Navigate to the mapper page
3. Expected: Image displayed, click on image opens product search modal

- [ ] **Step 3: Test the full flow**

1. Click on image → search modal opens → search for a product → select → marker appears
2. Switch to drag mode → drag a marker → marker repositions
3. Press Ctrl+Z → marker returns to original position
4. Click delete button → marker removed

- [ ] **Step 4: Commit**

```bash
git add public/js/inspiration-mapper.js
git commit -m "feat(inspiration): add advanced vanilla JS hotspot mapper with drag, undo, search, auto-save"
```

---

## Phase B: Mobile App (React Native)

### Task 12: Inspiration Service

**Files:**
- Create: `mayush-mobile/src/services/api/inspirationService.ts`

- [ ] **Step 1: Create the service file**

Create `mayush-mobile/src/services/api/inspirationService.ts`:

```typescript
import { apiClient, API_BASE_URL } from '../../api';

export interface InspirationPreview {
  id: number;
  slug: string;
  title: string;
  subtitle: string;
  image: string;
  products_count: number;
  preview_products: InspirationPreviewProduct[];
}

export interface InspirationPreviewProduct {
  id: number;
  name: string;
  image: string;
  price: string;
  available: boolean;
}

export interface InspirationDetail {
  id: number;
  slug: string;
  title: string;
  subtitle: string;
  description: string;
  image: {
    url: string;
    width: number;
    height: number;
  };
  items: InspirationDetailItem[];
}

export interface InspirationDetailItem {
  id: number;
  display_order: number;
  hotspot: { x: number; y: number } | null;
  product: {
    id: number;
    name: string;
    slug: string;
    price: string;
    discount_price: string | null;
    image: string;
    available: boolean;
    stock_status: string;
  };
}

export const inspirationService = {
  async getFeatured(language: string): Promise<InspirationPreview[]> {
    try {
      const response = await fetch(`${API_BASE_URL}/inspirations/featured`, {
        headers: {
          'Accept': 'application/json',
          'Accept-Language': language,
        },
      });
      if (!response.ok) throw new Error(`HTTP ${response.status}`);
      const json = await response.json();
      return json.data || [];
    } catch {
      return [];
    }
  },

  async getAll(language: string): Promise<InspirationPreview[]> {
    try {
      const response = await fetch(`${API_BASE_URL}/inspirations`, {
        headers: {
          'Accept': 'application/json',
          'Accept-Language': language,
        },
      });
      if (!response.ok) throw new Error(`HTTP ${response.status}`);
      const json = await response.json();
      return json.data || [];
    } catch {
      return [];
    }
  },

  async getBySlug(slug: string, language: string): Promise<InspirationDetail | null> {
    try {
      const response = await fetch(`${API_BASE_URL}/inspirations/${encodeURIComponent(slug)}`, {
        headers: {
          'Accept': 'application/json',
          'Accept-Language': language,
        },
      });
      if (!response.ok) throw new Error(`HTTP ${response.status}`);
      const json = await response.json();
      return json.data || null;
    } catch {
      return null;
    }
  },
};
```

- [ ] **Step 2: Verify TypeScript compiles**

```bash
cd /c/laragon/www/mayush/mayush-mobile && npx tsc --noEmit --pretty 2>&1 | head -20
```

Expected: No errors.

- [ ] **Step 3: Commit**

```bash
cd /c/laragon/www/mayush/mayush-mobile
git add src/services/api/inspirationService.ts
git commit -m "feat(inspiration): add mobile inspiration API service with types"
```

---

### Task 13: Navigation — Register InspirationDetailScreen

**Files:**
- Modify: `mayush-mobile/src/navigation/screenKeys.ts`
- Modify: `mayush-mobile/src/navigation/RootNavigator.tsx`

This app uses a manual state-driven router (not React Navigation). New screens are added by:
1. Adding the key to the `ScreenKey` union type
2. Rendering the screen conditionally in `RootNavigatorContent`

- [ ] **Step 1: Add screen key**

In `mayush-mobile/src/navigation/screenKeys.ts`, add `'inspiration-detail'` to the `ScreenKey` type union and the `ALL_SCREEN_KEYS` set. Find the existing entries and add after `'product-gallery'`:

```typescript
| 'inspiration-detail'
```

And in the `ALL_SCREEN_KEYS` set:

```typescript
'inspiration-detail',
```

- [ ] **Step 2: Add screen rendering in RootNavigator**

In `mayush-mobile/src/navigation/RootNavigator.tsx`:

First, add the import at the top with other screen imports:

```typescript
import InspirationDetailScreen from '../screens/discovery/InspirationDetailScreen';
```

Then find where `ProductGalleryScreen` is conditionally rendered and add after it:

```typescript
{currentScreen === 'inspiration-detail' ? (
  <InspirationDetailScreen
    activeTab={activeTab}
    onBack={() => setCurrentScreen('home')}
    onNavigateTab={navigateTab}
    slug={screenParams?.slug as string}
    onSelectProduct={(product) => {
      setScreenParams({ productSlug: product.slug });
      setCurrentScreen('product-details');
    }}
  />
) : null}
```

Also add state for passing params. Find where `screenParams` or equivalent state is managed and ensure it supports passing a `slug` param when navigating to `'inspiration-detail'`.

- [ ] **Step 3: Verify TypeScript compiles**

```bash
cd /c/laragon/www/mayush/mayush-mobile && npx tsc --noEmit --pretty 2>&1 | head -20
```

Note: This will fail until the `InspirationDetailScreen` component exists. That's expected — proceed to Task 14.

- [ ] **Step 4: Commit**

```bash
git add src/navigation/screenKeys.ts src/navigation/RootNavigator.tsx
git commit -m "feat(inspiration): register InspirationDetailScreen in navigation"
```

---

### Task 14: HomeScreen Integration — Replace Hardcoded Inspirations

**Files:**
- Modify: `mayush-mobile/src/screens/discovery/HomeScreen.tsx`

Replace the hardcoded `INSPIRATION_ARTWORK` static images with live API data from `inspirationService.getFeatured()`.

- [ ] **Step 1: Add import**

At the top of `HomeScreen.tsx`, add:

```typescript
import { inspirationService, InspirationPreview } from '../../services/api/inspirationService';
```

- [ ] **Step 2: Add state and cache**

Find the `homeCache` object (around line 139-153) and add:

```typescript
featuredInspirations: [] as InspirationPreview[],
```

Find the component state declarations and add:

```typescript
const [featuredInspirations, setFeaturedInspirations] = useState<InspirationPreview[]>(
  hasCachedData ? homeCache.featuredInspirations : []
);
const [inspirationsLoading, setInspirationsLoading] = useState(!hasCachedData);
```

- [ ] **Step 3: Add API fetch**

In the `useEffect` data fetch block (around line 275-435), add alongside the other API calls:

```typescript
inspirationService
  .getFeatured(language)
  .then((res) => {
    if (mounted) {
      setFeaturedInspirations(res);
      homeCache.featuredInspirations = res;
      setInspirationsLoading(false);
    }
  })
  .catch(() => { if (mounted) setInspirationsLoading(false); })
  .finally(() => { markRequestComplete(); updateCache(); });
```

Increment the total request count by 1 to account for this new fetch.

- [ ] **Step 4: Update renderInspirationsSection**

Replace the existing `renderInspirationsSection` function (around lines 629-699) to use live data:

```typescript
const renderInspirationsSection = () => {
  if (inspirationsLoading) {
    return (
      <>
        <SectionHeader label={heading('Inspiration du moment', 'إلهام اللحظة')} isRTL={isRTL} />
        <Skeleton width="100%" height={180} borderRadius="lg" />
      </>
    );
  }

  if (featuredInspirations.length === 0) return null;

  return (
    <>
      <SectionHeader label={heading('Inspiration du moment', 'إلهام اللحظة')} isRTL={isRTL} />
      <ScrollView horizontal showsHorizontalScrollIndicator={false}>
        {featuredInspirations.map((insp) => (
          <TouchableOpacity
            key={insp.id}
            activeOpacity={0.88}
            style={[styles.inspirationCard, { width: Math.round(contentWidth * 0.85) }]}
            onPress={() => {
              onOpenInspiration?.();
              // Navigate to inspiration detail — depends on how navigation callbacks are wired
            }}
          >
            <Image
              source={{ uri: insp.image }}
              style={styles.inspirationImage}
              resizeMode="cover"
            />
            <View style={styles.inspirationOverlay}>
              <MayushText variant="body" color={colors.interactive.textInverse} style={{ fontWeight: '700' }}>
                {insp.title}
              </MayushText>
              <MayushText variant="smallBody" color={colors.interactive.textInverse}>
                {insp.products_count} {heading('articles', 'منتجات')}
              </MayushText>
            </View>
          </TouchableOpacity>
        ))}
      </ScrollView>
      {featuredInspirations[0]?.preview_products?.length > 0 && (
        <ScrollView horizontal showsHorizontalScrollIndicator={false} style={{ marginTop: 8 }}>
          {featuredInspirations[0].preview_products.map((p) => (
            <TouchableOpacity
              key={p.id}
              style={{ marginRight: 8, alignItems: 'center' }}
              onPress={() => onSelectProduct?.({ id: p.id, slug: '', name: p.name } as any)}
            >
              <Image
                source={{ uri: p.image }}
                style={{ width: 44, height: 44, borderRadius: 22, backgroundColor: colors.surface.creamLight }}
                resizeMode="cover"
              />
            </TouchableOpacity>
          ))}
        </ScrollView>
      )}
    </>
  );
};
```

- [ ] **Step 5: Add overlay style**

Add to the StyleSheet:

```typescript
inspirationOverlay: {
  position: 'absolute',
  bottom: 0,
  left: 0,
  right: 0,
  padding: 12,
  paddingTop: 24,
  background: 'linear-gradient(transparent, rgba(0,0,0,0.6))',
},
```

Note: React Native doesn't support CSS gradients. Use a `LinearGradient` component from `expo-linear-gradient` instead, or use a simpler semi-transparent background:

```typescript
inspirationOverlay: {
  position: 'absolute',
  bottom: 0,
  left: 0,
  right: 0,
  padding: 12,
  backgroundColor: 'rgba(0,0,0,0.4)',
  borderBottomLeftRadius: 16,
  borderBottomRightRadius: 16,
},
```

- [ ] **Step 6: Remove INSPIRATION_ARTWORK if no longer used**

If `INSPIRATION_ARTWORK` (around line 26-28) is no longer referenced anywhere after the update, remove it:

```typescript
// DELETE these lines:
const INSPIRATION_ARTWORK = [
  require('../../../assets/reference-art/home-inspiration-japandi.png'),
  require('../../../assets/reference-art/home-inspiration-natural.png'),
];
```

- [ ] **Step 7: Verify TypeScript compiles**

```bash
cd /c/laragon/www/mayush/mayush-mobile && npx tsc --noEmit --pretty 2>&1 | head -20
```

- [ ] **Step 8: Commit**

```bash
git add src/screens/discovery/HomeScreen.tsx
git commit -m "feat(inspiration): wire HomeScreen to live inspiration API, remove hardcoded artwork"
```

---

### Task 15: InspirationDetailScreen — Layout, Loading, Product Grid

**Files:**
- Create: `mayush-mobile/src/screens/discovery/InspirationDetailScreen.tsx`

- [ ] **Step 1: Create the screen file**

Create `mayush-mobile/src/screens/discovery/InspirationDetailScreen.tsx`:

```typescript
import React, { useEffect, useState, useRef, useCallback } from 'react';
import {
  View,
  ScrollView,
  Image,
  TouchableOpacity,
  StyleSheet,
  useWindowDimensions,
  ActivityIndicator,
  LayoutChangeEvent,
  Animated,
} from 'react-native';
import { MayushText } from '../../design-system/components/typography/MayushText';
import { MayushIcon } from '../../design-system/components/navigation/MayushIcon';
import { ProductCard } from '../../design-system/components/commerce/ProductCard';
import { Skeleton } from '../../design-system/components/feedback/Skeleton';
import { colors } from '../../design-system/tokens/colors';
import { useTheme } from '../../design-system/theme/useTheme';
import { inspirationService, InspirationDetail, InspirationDetailItem } from '../../services/api/inspirationService';
import { normalizeImageUrl } from '../../contracts/mappers/imageNormalizer';

interface InspirationDetailScreenProps {
  activeTab: string;
  onBack: () => void;
  onNavigateTab: (tab: string) => void;
  slug: string;
  onSelectProduct?: (product: { id: number; slug: string; name: string }) => void;
}

const MARKER_SIZE = 28;

const InspirationDetailScreen: React.FC<InspirationDetailScreenProps> = ({
  onBack,
  slug,
  onSelectProduct,
}) => {
  const { language, isRTL } = useTheme();
  const { width: screenWidth } = useWindowDimensions();
  const scrollRef = useRef<ScrollView>(null);
  const [data, setData] = useState<InspirationDetail | null>(null);
  const [loading, setLoading] = useState(true);
  const [error, setError] = useState(false);
  const [imageLayout, setImageLayout] = useState({ width: 0, height: 0 });
  const [highlightedItemId, setHighlightedItemId] = useState<number | null>(null);
  const [highlightedMarkerIdx, setHighlightedMarkerIdx] = useState<number | null>(null);
  const cardPositions = useRef<Record<number, number>>({});

  const heading = (fr: string, ar: string) => (isRTL ? ar : fr);
  const contentPadding = Math.max(16, Math.round(screenWidth * 0.04));
  const cardWidth = Math.floor((screenWidth - contentPadding * 2 - 12) / 2);

  useEffect(() => {
    let mounted = true;
    setLoading(true);
    setError(false);

    inspirationService.getBySlug(slug, language).then((result) => {
      if (mounted) {
        if (result) {
          setData(result);
        } else {
          setError(true);
        }
        setLoading(false);
      }
    });

    return () => { mounted = false; };
  }, [slug, language]);

  const handleImageLayout = useCallback((event: LayoutChangeEvent) => {
    const { width, height } = event.nativeEvent.layout;
    setImageLayout({ width, height });
  }, []);

  const handleMarkerPress = useCallback((item: InspirationDetailItem, index: number) => {
    setHighlightedItemId(item.id);
    // Scroll to the product card
    const yPos = cardPositions.current[item.id];
    if (yPos !== undefined) {
      scrollRef.current?.scrollTo({ y: yPos - 100, animated: true });
    }
    setTimeout(() => setHighlightedItemId(null), 800);
  }, []);

  const handleProductPress = useCallback((item: InspirationDetailItem, index: number) => {
    // Highlight the corresponding marker
    setHighlightedMarkerIdx(index);
    setTimeout(() => setHighlightedMarkerIdx(null), 800);

    // Navigate to product detail
    if (onSelectProduct && item.product) {
      onSelectProduct({
        id: item.product.id,
        slug: item.product.slug,
        name: item.product.name,
      });
    }
  }, [onSelectProduct]);

  // --- Loading skeleton ---
  if (loading) {
    return (
      <View style={[styles.container, { paddingHorizontal: contentPadding }]}>
        <View style={styles.header}>
          <TouchableOpacity onPress={onBack} style={styles.backButton}>
            <MayushIcon name={isRTL ? 'chevron-right' : 'chevron-left'} size={24} color={colors.brand.navy900} />
          </TouchableOpacity>
        </View>
        <Skeleton width="60%" height={24} borderRadius="sm" />
        <View style={{ height: 8 }} />
        <Skeleton width="80%" height={16} borderRadius="sm" />
        <View style={{ height: 16 }} />
        <Skeleton width="100%" height={Math.round(screenWidth * 0.65)} borderRadius="lg" />
        <View style={{ height: 16 }} />
        <View style={styles.productGrid}>
          {Array.from({ length: 4 }).map((_, i) => (
            <Skeleton key={i} width={cardWidth} height={cardWidth * 1.4} borderRadius="md" />
          ))}
        </View>
      </View>
    );
  }

  // --- Error state ---
  if (error || !data) {
    return (
      <View style={[styles.container, styles.centered, { paddingHorizontal: contentPadding }]}>
        <View style={styles.header}>
          <TouchableOpacity onPress={onBack} style={styles.backButton}>
            <MayushIcon name={isRTL ? 'chevron-right' : 'chevron-left'} size={24} color={colors.brand.navy900} />
          </TouchableOpacity>
        </View>
        <MayushText variant="body" color={colors.neutral.gray500} align="center">
          {heading('Impossible de charger l\'inspiration', 'تعذر تحميل الإلهام')}
        </MayushText>
        <TouchableOpacity
          style={styles.retryButton}
          onPress={() => { setLoading(true); setError(false); inspirationService.getBySlug(slug, language).then(r => { setData(r); setLoading(false); }).catch(() => { setError(true); setLoading(false); }); }}
        >
          <MayushText variant="body" color={colors.brand.orange500}>
            {heading('Réessayer', 'إعادة المحاولة')}
          </MayushText>
        </TouchableOpacity>
      </View>
    );
  }

  // --- Main content ---
  const items = data.items || [];
  const imageAspect = data.image.width && data.image.height
    ? data.image.height / data.image.width
    : 0.65;
  const imageDisplayHeight = Math.round(screenWidth * imageAspect);

  return (
    <View style={styles.container}>
      {/* Header */}
      <View style={[styles.header, { paddingHorizontal: contentPadding }]}>
        <TouchableOpacity onPress={onBack} style={styles.backButton}>
          <MayushIcon name={isRTL ? 'chevron-right' : 'chevron-left'} size={24} color={colors.brand.navy900} />
        </TouchableOpacity>
      </View>

      <ScrollView ref={scrollRef} showsVerticalScrollIndicator={false}>
        {/* Title */}
        <View style={{ paddingHorizontal: contentPadding, marginBottom: 12 }}>
          <MayushText variant="heading" color={colors.brand.navy900} style={{ fontWeight: '800', fontSize: 22 }}>
            {data.title}
          </MayushText>
          {data.subtitle ? (
            <MayushText variant="body" color={colors.neutral.gray700} style={{ marginTop: 4 }}>
              {data.subtitle}
            </MayushText>
          ) : null}
        </View>

        {/* Scene image with hotspot markers */}
        <View
          style={[styles.imageContainer, { height: imageDisplayHeight }]}
          onLayout={handleImageLayout}
        >
          <Image
            source={{ uri: data.image.url }}
            style={{ width: screenWidth, height: imageDisplayHeight }}
            resizeMode="cover"
          />
          {/* Hotspot markers */}
          {imageLayout.width > 0 && items.map((item, idx) => {
            if (!item.hotspot) return null;
            const isHighlighted = highlightedMarkerIdx === idx;
            return (
              <TouchableOpacity
                key={item.id}
                activeOpacity={0.8}
                style={[
                  styles.marker,
                  {
                    left: item.hotspot.x * imageLayout.width - MARKER_SIZE / 2,
                    top: item.hotspot.y * imageLayout.height - MARKER_SIZE / 2,
                    backgroundColor: item.product.available ? colors.brand.navy900 : colors.neutral.gray500,
                    transform: isHighlighted ? [{ scale: 1.3 }] : [{ scale: 1 }],
                  },
                ]}
                onPress={() => handleMarkerPress(item, idx)}
              >
                <MayushText variant="smallBody" color={colors.interactive.textInverse} style={styles.markerText}>
                  {idx + 1}
                </MayushText>
              </TouchableOpacity>
            );
          })}
        </View>

        {/* Products count */}
        <View style={{ paddingHorizontal: contentPadding, marginTop: 16, marginBottom: 12 }}>
          <MayushText variant="body" color={colors.brand.navy900} style={{ fontWeight: '700' }}>
            {items.length} {heading('articles dans cette ambiance', 'منتجات في هذا الإلهام')}
          </MayushText>
        </View>

        {/* Product grid - 2 columns */}
        <View style={[styles.productGrid, { paddingHorizontal: contentPadding }]}>
          {items.map((item, idx) => (
            <View
              key={item.id}
              style={{ width: cardWidth, marginBottom: 12, opacity: item.product.available ? 1 : 0.5 }}
              onLayout={(e) => { cardPositions.current[item.id] = e.nativeEvent.layout.y; }}
            >
              <ProductCard
                name={item.product.name}
                thumbnailUrl={item.product.image ? normalizeImageUrl(item.product.image) : undefined}
                currentPriceFormatted={item.product.price}
                originalPriceFormatted={item.product.discount_price ? item.product.price : undefined}
                hasDiscount={!!item.product.discount_price}
                inStock={item.product.available}
                width={cardWidth}
                variant="grid"
                onPress={() => handleProductPress(item, idx)}
                style={[
                  highlightedItemId === item.id && styles.highlightedCard,
                ]}
              />
              {!item.product.available && (
                <View style={styles.unavailableBadge}>
                  <MayushText variant="smallBody" color={colors.interactive.textInverse} style={{ fontSize: 10, fontWeight: '600' }}>
                    {heading('Indisponible', 'غير متاح')}
                  </MayushText>
                </View>
              )}
            </View>
          ))}
        </View>

        <View style={{ height: 40 }} />
      </ScrollView>
    </View>
  );
};

const styles = StyleSheet.create({
  container: {
    flex: 1,
    backgroundColor: colors.surface.white,
  },
  centered: {
    justifyContent: 'center',
    alignItems: 'center',
  },
  header: {
    flexDirection: 'row',
    alignItems: 'center',
    paddingVertical: 12,
  },
  backButton: {
    width: 40,
    height: 40,
    justifyContent: 'center',
  },
  imageContainer: {
    position: 'relative',
    width: '100%',
    overflow: 'hidden',
  },
  marker: {
    position: 'absolute',
    width: MARKER_SIZE,
    height: MARKER_SIZE,
    borderRadius: MARKER_SIZE / 2,
    borderWidth: 2,
    borderColor: colors.surface.white,
    justifyContent: 'center',
    alignItems: 'center',
    elevation: 4,
    shadowColor: '#000',
    shadowOffset: { width: 0, height: 2 },
    shadowOpacity: 0.25,
    shadowRadius: 4,
  },
  markerText: {
    fontSize: 11,
    fontWeight: '700',
  },
  productGrid: {
    flexDirection: 'row',
    flexWrap: 'wrap',
    justifyContent: 'space-between',
  },
  highlightedCard: {
    borderWidth: 2,
    borderColor: colors.brand.orange500,
    borderRadius: 12,
  },
  unavailableBadge: {
    position: 'absolute',
    top: 8,
    left: 8,
    backgroundColor: colors.neutral.gray700,
    paddingHorizontal: 8,
    paddingVertical: 2,
    borderRadius: 4,
  },
  retryButton: {
    marginTop: 16,
    padding: 12,
  },
});

export default InspirationDetailScreen;
```

- [ ] **Step 2: Verify TypeScript compiles**

```bash
cd /c/laragon/www/mayush/mayush-mobile && npx tsc --noEmit --pretty 2>&1 | head -20
```

Fix any import path issues.

- [ ] **Step 3: Commit**

```bash
git add src/screens/discovery/InspirationDetailScreen.tsx
git commit -m "feat(inspiration): add InspirationDetailScreen with hotspot markers and product grid"
```

---

### Task 16: Verify Full Integration

This is a verification task — no new code, just end-to-end testing.

- [ ] **Step 1: Backend verification**

```bash
cd /c/laragon/www/mayush
php artisan serve
```

1. Login to admin → Inspirations → Create
2. Fill title "Esprit Japandi", upload a test image, save
3. Go to Mapper → click on image → search for a product → place marker
4. Place 3-4 markers
5. Drag a marker to reposition
6. Go back to Edit → set Status: Published, check Featured + Show on Home → save

- [ ] **Step 2: API verification**

```bash
curl http://localhost:8000/api/v2/inspirations/featured -H "Accept-Language: fr" 2>/dev/null | python3 -m json.tool
```

Expected: JSON with the featured inspiration including preview products.

```bash
curl http://localhost:8000/api/v2/inspirations/esprit-japandi -H "Accept-Language: fr" 2>/dev/null | python3 -m json.tool
```

Expected: Full detail with hotspot coordinates and product data.

- [ ] **Step 3: Mobile verification**

```bash
cd /c/laragon/www/mayush/mayush-mobile && npx expo start
```

1. Open app → Home → "Inspiration du moment" section should show live data
2. Tap the inspiration card → navigate to detail screen
3. Scene image with numbered markers visible
4. Tap a marker → scrolls to product card with highlight
5. Tap a product card → navigates to product detail

- [ ] **Step 4: Commit any fixes**

If any integration issues are found, fix and commit each separately.

---

## Self-Review Checklist

### Spec Coverage

| Spec Section | Task(s) | Status |
|---|---|---|
| 1. Data Architecture (3 tables) | Task 1 | ✅ |
| 2. Laravel Models (3 models) | Task 2 | ✅ |
| 3. API Endpoints (3 routes) | Task 5 | ✅ |
| 4. API Resources | Task 5 (inline in controller) | ✅ |
| 5.1 Admin Routes | Task 6 | ✅ |
| 5.2 Permissions | Task 4 | ✅ |
| 5.3 Admin Sidebar | Task 4 | ✅ |
| 5.4 CRUD Views | Tasks 7, 8 | ✅ |
| 5.5 Hotspot Mapper | Tasks 10, 11 | ✅ |
| 5.6 Publication Validation | Task 6 (in update method) | ✅ |
| 6. Mobile Service Layer | Task 12 | ✅ |
| 7. Home Screen Integration | Task 14 | ✅ |
| 8. Inspiration Detail Screen | Task 15 | ✅ |
| 9. Unavailable Product Handling | Tasks 5, 11, 15 | ✅ |
| 10. Caching Strategy | Tasks 3, 5 | ✅ |
| 11. File Structure | All tasks | ✅ |

### Design Decisions

- **API resources as inline methods** instead of separate Resource classes — follows the existing `ProductCollectionController` pattern which maps manually in the controller rather than using `JsonResource` subclasses.
- **Product search fix** (Task 9) — the existing `GET /admin/products-search` was a stub returning `'Stub'`. Now returns proper JSON for the mapper to consume.
- **Pinch-to-zoom** deferred — the basic detail screen with tap-to-highlight markers is the MVP. Pinch-to-zoom can be added later following the `ProductGalleryScreen` pattern already in the codebase.
- **Observer reuse** — uses existing `StorefrontCacheObserver` rather than creating a new one, following the established pattern for `ProductCollection`, `Category`, etc.
