# Mayush Inspiration Feature — Design Spec

> **Scope:** Backend (Laravel) + Admin Dashboard + Mobile App (React Native/Expo)
>
> **Goal:** Replace the hardcoded "Inspiration du moment" section with a fully dynamic, admin-managed Inspiration system featuring interactive hotspot-to-product mapping on professionally photographed interior scenes.
>
> **Key principle:** The admin uploads pre-shot professional photography, clicks directly on products in the image to place hotspots, and links each hotspot to a real Mayush catalog product. No product data is duplicated — prices, stock, images are always loaded live from the catalog.

---

## 1. Data Architecture

Three new tables, independent from the existing `product_collections` system:

```
inspirations
    ├── inspiration_items  (product link + display order)
    │       └── product_id → products
    └── inspiration_hotspots  (normalized x/y on the scene image)
            └── inspiration_item_id → inspiration_items
```

### 1.1 `inspirations` table

| Column | Type | Notes |
|---|---|---|
| id | bigint PK | |
| slug | string unique | URL-friendly identifier |
| title_fr | string | French title |
| title_ar | string nullable | Arabic title |
| subtitle_fr | string nullable | French subtitle |
| subtitle_ar | string nullable | Arabic subtitle |
| description_fr | text nullable | French description |
| description_ar | text nullable | Arabic description |
| hero_image | string | Storage path to uploaded scene image |
| hero_image_width | int nullable | Original image width in px (stored on upload) |
| hero_image_height | int nullable | Original image height in px (stored on upload) |
| status | enum: draft, published, archived | Default: draft |
| is_featured | boolean | Default: false |
| show_on_home | boolean | Default: false |
| sort_order | int | Default: 0 |
| published_at | timestamp nullable | |
| starts_at | timestamp nullable | Time-based visibility start |
| ends_at | timestamp nullable | Time-based visibility end |
| created_by | bigint nullable FK → users | |
| created_at | timestamp | |
| updated_at | timestamp | |
| deleted_at | timestamp nullable | Soft deletes |

### 1.2 `inspiration_items` table

| Column | Type | Notes |
|---|---|---|
| id | bigint PK | |
| inspiration_id | bigint FK → inspirations | CASCADE delete |
| product_id | bigint FK → products | |
| display_order | int | Default: 0 |
| is_visible | boolean | Default: true |
| is_featured | boolean | Default: false |
| custom_title_fr | string nullable | Override product name if needed |
| custom_title_ar | string nullable | Override product name if needed |
| created_at | timestamp | |
| updated_at | timestamp | |

**No duplicated product data.** Price, stock, images, availability are always loaded live from the `products` table via eager loading.

### 1.3 `inspiration_hotspots` table

| Column | Type | Notes |
|---|---|---|
| id | bigint PK | |
| inspiration_id | bigint FK → inspirations | CASCADE delete |
| inspiration_item_id | bigint FK → inspiration_items | CASCADE delete |
| x | decimal(6,4) | Normalized 0.0000–1.0000 |
| y | decimal(6,4) | Normalized 0.0000–1.0000 |
| display_order | int | Default: 0 |
| created_at | timestamp | |
| updated_at | timestamp | |

**Coordinates are normalized (0–1), not pixels.** Frontend renders as `left: x*100%; top: y*100%`. This ensures hotspots work at any image display size — mobile (360px), tablet (768px), desktop (1440px).

---

## 2. Laravel Models

### 2.1 `Inspiration`

```php
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

    public function items() { return $this->hasMany(InspirationItem::class)->orderBy('display_order'); }
    public function hotspots() { return $this->hasMany(InspirationHotspot::class)->orderBy('display_order'); }
    public function creator() { return $this->belongsTo(User::class, 'created_by'); }

    public function scopePublished($query) {
        return $query->where('status', 'published')
            ->where(function ($q) {
                $q->whereNull('starts_at')->orWhere('starts_at', '<=', now());
            })
            ->where(function ($q) {
                $q->whereNull('ends_at')->orWhere('ends_at', '>=', now());
            });
    }

    public function scopeFeatured($query) {
        return $query->where('is_featured', true)->where('show_on_home', true);
    }

    // Bilingual accessor
    public function getTitle($lang = 'fr') {
        return $lang === 'ar' && $this->title_ar ? $this->title_ar : $this->title_fr;
    }

    public function getSubtitle($lang = 'fr') {
        return $lang === 'ar' && $this->subtitle_ar ? $this->subtitle_ar : $this->subtitle_fr;
    }
}
```

### 2.2 `InspirationItem`

```php
class InspirationItem extends Model
{
    protected $fillable = [
        'inspiration_id', 'product_id', 'display_order',
        'is_visible', 'is_featured', 'custom_title_fr', 'custom_title_ar',
    ];

    protected $casts = ['is_visible' => 'boolean', 'is_featured' => 'boolean'];

    public function inspiration() { return $this->belongsTo(Inspiration::class); }
    public function product() { return $this->belongsTo(Product::class); }
    public function hotspot() { return $this->hasOne(InspirationHotspot::class); }
}
```

### 2.3 `InspirationHotspot`

```php
class InspirationHotspot extends Model
{
    protected $fillable = [
        'inspiration_id', 'inspiration_item_id', 'x', 'y', 'display_order',
    ];

    protected $casts = ['x' => 'decimal:4', 'y' => 'decimal:4'];

    public function inspiration() { return $this->belongsTo(Inspiration::class); }
    public function item() { return $this->belongsTo(InspirationItem::class, 'inspiration_item_id'); }
}
```

---

## 3. API Endpoints

### 3.1 `GET /api/v2/inspirations`

Returns all published inspirations with preview data.

**Response:**
```json
{
    "data": [
        {
            "id": 12,
            "slug": "esprit-japandi",
            "title": "Esprit Japandi",
            "subtitle": "Un salon chaleureux et naturel",
            "image": "https://mayush.ma/storage/inspirations/esprit-japandi.webp",
            "products_count": 6,
            "preview_products": [
                {
                    "id": 381,
                    "name": "Canape Solis",
                    "image": "https://...",
                    "price": "1 890,00",
                    "available": true
                }
            ]
        }
    ]
}
```

### 3.2 `GET /api/v2/inspirations/featured`

Same structure as above but filtered to `is_featured = true` and `show_on_home = true`. Ordered by `sort_order`. Limited to 3 results. Used by Home screen.

**Caching:** 15 minutes, invalidated on publish/unpublish/edit.

### 3.3 `GET /api/v2/inspirations/{slug}`

Full detail with hotspot coordinates and complete product data.

**Response:**
```json
{
    "id": 12,
    "slug": "esprit-japandi",
    "title": "Esprit Japandi",
    "subtitle": "Un salon chaleureux et naturel",
    "description": "Un espace de vie...",
    "image": {
        "url": "https://mayush.ma/storage/inspirations/esprit-japandi.webp",
        "width": 1800,
        "height": 1200
    },
    "items": [
        {
            "id": 41,
            "display_order": 1,
            "hotspot": {
                "x": 0.2810,
                "y": 0.6320
            },
            "product": {
                "id": 381,
                "name": "Canape Solis",
                "slug": "canape-solis",
                "price": "1 890,00",
                "discount_price": null,
                "image": "https://...",
                "available": true,
                "stock_status": "in_stock"
            }
        }
    ]
}
```

**Caching:** 5 minutes per slug.

Product data is loaded live via eager loading (`items.product`) using existing Product model relationships and pricing logic. The `product` object in the response reuses the existing `ProductResource` / `ProductMiniCollection` format.

---

## 4. API Resources

### 4.1 `InspirationResource` (listing/featured)

```php
class InspirationResource extends JsonResource
{
    public function toArray($request)
    {
        $lang = $request->header('Accept-Language', 'fr');
        return [
            'id' => $this->id,
            'slug' => $this->slug,
            'title' => $this->getTitle($lang),
            'subtitle' => $this->getSubtitle($lang),
            'image' => $this->hero_image ? asset('storage/' . $this->hero_image) : null,
            'products_count' => $this->items_count ?? $this->items()->count(),
            'preview_products' => $this->whenLoaded('items', function () {
                return $this->items->take(4)->map(fn ($item) => [
                    'id' => $item->product->id,
                    'name' => $item->product->getTranslatedName(),
                    'image' => $item->product->thumbnail_img,
                    'price' => format_price($item->product->unit_price),
                    'available' => $item->product->isAvailable(),
                ]);
            }),
        ];
    }
}
```

### 4.2 `InspirationDetailResource` (detail with hotspots)

```php
class InspirationDetailResource extends JsonResource
{
    public function toArray($request)
    {
        $lang = $request->header('Accept-Language', 'fr');
        return [
            'id' => $this->id,
            'slug' => $this->slug,
            'title' => $this->getTitle($lang),
            'subtitle' => $this->getSubtitle($lang),
            'description' => $lang === 'ar' && $this->description_ar
                ? $this->description_ar : $this->description_fr,
            'image' => [
                'url' => $this->hero_image ? asset('storage/' . $this->hero_image) : null,
                'width' => $this->hero_image_width,
                'height' => $this->hero_image_height,
            ],
            'items' => $this->items
                ->filter(fn ($item) => $item->is_visible && $item->product)
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
                        'name' => $item->product->getTranslatedName(),
                        'slug' => $item->product->slug,
                        'price' => format_price($item->product->unit_price),
                        'discount_price' => $item->product->discount > 0
                            ? format_price($item->product->discounted_price) : null,
                        'image' => $item->product->thumbnail_img,
                        'available' => $item->product->isAvailable(),
                        'stock_status' => $item->product->stock_status ?? 'in_stock',
                    ],
                ]),
        ];
    }
}
```

---

## 5. Admin Dashboard

### 5.1 Routes

```
GET    /admin/inspirations                    → index (list all)
GET    /admin/inspirations/create             → create form
POST   /admin/inspirations                    → store
GET    /admin/inspirations/{id}/edit          → edit form
PUT    /admin/inspirations/{id}              → update
DELETE /admin/inspirations/{id}              → destroy (soft delete)
GET    /admin/inspirations/{id}/mapper       → hotspot mapper page
POST   /admin/inspirations/{id}/hotspots     → add hotspot (AJAX)
PUT    /admin/inspirations/{id}/hotspots/{h} → move/reassign hotspot (AJAX)
DELETE /admin/inspirations/{id}/hotspots/{h} → remove hotspot (AJAX)
GET    /admin/products-search                → AJAX product search (EXISTING route, reuse)
```

### 5.2 Permissions

Following the existing pattern from `product_collection`:
- `view_inspiration`
- `add_inspiration`
- `edit_inspiration`
- `delete_inspiration`

### 5.3 Admin Sidebar

New menu item "Inspirations" in the Commerce/Catalog section, next to Collections.

### 5.4 Standard CRUD Views (Blade)

**Index:** Table with columns: Image thumbnail, Title, Status badge, Products count, Featured toggle, Sort order, Actions (edit/mapper/delete). Filterable by status.

**Create/Edit:** Standard form with:
- Title FR / AR
- Subtitle FR / AR
- Description FR / AR (textarea)
- Hero image upload (with preview, stores dimensions on upload)
- Status dropdown (draft/published/archived)
- Checkboxes: is_featured, show_on_home
- Sort order input
- Date range: starts_at / ends_at
- Slug (auto-generated from title, editable)

### 5.5 Hotspot Mapper Page (Blade + Advanced Vanilla JS)

This is the core interactive admin feature. Accessed via "Mapper" button on the edit page after an image is uploaded.

**Layout:**
```
┌──────────────────────────────────────────────────────┐
│  ← Retour    Esprit Japandi — Mapper    [Apercu]     │
├──────────────────────────────────────────────────────┤
│                                                      │
│  ┌────────────────────────────────────────────────┐  │
│  │                                                │  │
│  │            UPLOADED SCENE IMAGE                │  │
│  │                                                │  │
│  │     ①                          ③               │  │
│  │                                                │  │
│  │              ②                                 │  │
│  │                            ④                   │  │
│  └────────────────────────────────────────────────┘  │
│                                                      │
│  Mode: [+ Placer]  [↔ Deplacer]     Enregistre ✓    │
│                                                      │
│  ─────────────────────────────────────────────────── │
│  ARTICLES ASSOCIES (4)                               │
│                                                      │
│  ① Canape Solis        1 890 DH    ✏️  🗑️           │
│  ② Table basse Eve       479 DH    ✏️  🗑️           │
│  ③ Suspension Nori       199 DH    ✏️  🗑️           │
│  ④ Vase Brume            129 DH    ✏️  🗑️           │
│                                                      │
│  ⚠ Suspension Nori — Produit indisponible           │
└──────────────────────────────────────────────────────┘
```

**The `InspirationMapper` JS class provides:**

#### Visual Quality
- Markers use CSS transition `transform: scale(0) → scale(1)` on creation (300ms ease-out spring)
- Active/selected marker pulses with `@keyframes` CSS animation (subtle scale 1.0 → 1.15 breathing)
- Hover tooltip on each marker shows product name + small thumbnail (positioned above marker, CSS-only with `::after` pseudo-element + `data-` attributes, falls back to JS tooltip for image)
- Cursor changes contextually: `crosshair` in placement mode, `grab`/`grabbing` in drag mode, `pointer` on marker hover
- Marker numbered circles use the Mayush brand navy color with white text, 28px diameter, `border: 2px solid white`, `box-shadow: 0 2px 8px rgba(0,0,0,0.25)`

#### Drag & Drop
- Smooth 60fps dragging via `requestAnimationFrame` loop (not raw mousemove)
- Semi-transparent ghost marker follows cursor during drag (`opacity: 0.6`)
- Boundary clamping: markers cannot be placed or dragged outside the image bounds (x/y clamped to 0.0–1.0)
- Touch support: `touchstart/touchmove/touchend` events with `touch.clientX/Y` extraction, works on tablets
- Minimum drag threshold: 3px movement before entering drag state (prevents click→drag confusion)
- On drop: animated snap to final position (150ms ease-out)

#### Product Search Panel
- Modal overlay opens on image click (placement mode)
- Debounced AJAX search input: 300ms debounce, calls `GET /admin/products-search?q={query}`
- Results show: product thumbnail (40x40), name, formatted price, stock status badge (green/red)
- Keyboard navigation: Arrow Up/Down to move selection, Enter to confirm, Escape to cancel
- "Recents" section at top: last 5 products used in this inspiration session (stored in `sessionStorage`)
- Loading spinner during search, empty state message ("Aucun produit trouve")
- Clicking a result: closes modal, creates hotspot + item via `POST`, renders marker on image

#### Undo/Redo System
- Action history stack: stores `{ type: 'place'|'move'|'delete'|'reassign', data: {...}, inverse: {...} }`
- `Ctrl+Z` triggers undo, `Ctrl+Shift+Z` triggers redo
- Visual toast notification on undo/redo: "Annule: suppression de (1)" (auto-dismiss 2s)
- Stack limited to 50 actions to prevent memory issues
- History resets on page reload (not persisted)

#### Responsive Image Handling
- Image rendered `max-width: 100%` with natural aspect ratio (`height: auto`)
- Container uses `position: relative`, markers use `position: absolute` with `left: {x*100}%`, `top: {y*100}%`, `transform: translate(-50%, -50%)`
- `ResizeObserver` on the image container: recalculates marker positions on window resize
- Coordinates always normalized (0–1), never stored or transmitted as pixels

#### Auto-Save & State
- All changes auto-save via AJAX: debounced 500ms after last action
- Save indicator in toolbar: "Enregistre ✓" (green) / "Enregistrement..." (amber spinner) / "Erreur ✗" (red, with retry button)
- Optimistic UI: markers update instantly in DOM, revert position/state on server error with toast notification
- `beforeunload` event listener: warns "Modifications non enregistrees" if pending saves exist
- CSRF token included in all AJAX requests via Laravel's `meta[name=csrf-token]`

#### Accessibility
- Markers are focusable: `tabindex="0"`, `role="button"`, `aria-label="Point 1: Canape Solis"`
- Keyboard activation: Enter/Space on focused marker opens context menu
- Arrow keys: nudge selected marker by 0.5% (0.005) per keypress for pixel-precise positioning
- Screen reader: `aria-live="polite"` region announces actions ("Point 3 place", "Point 2 supprime")
- Focus trap inside product search modal
- All interactive elements meet WCAG 2.1 AA contrast requirements

#### Preview Mode
- Toggle button switches between Edit and Preview mode
- Preview hides all edit controls (mode buttons, product list actions, save indicator)
- Preview renders the scene exactly as mobile/web would: numbered markers on image, product cards below
- Device frame selector: Mobile (390px), Tablet (768px), Desktop (1440px)
- Preview container uses `max-width` + `margin: auto` to simulate device width
- Markers and product cards render at simulated device proportions

#### Code Structure
- Single `InspirationMapper` class in `public/js/inspiration-mapper.js`
- Constructor takes: `{ containerId, imageUrl, inspirationId, csrfToken, existingHotspots[] }`
- Methods organized by concern: `bindEvents()`, `handleImageClick()`, `handleMarkerDrag()`, `openProductSearch()`, `saveHotspot()`, `undo()`, `redo()`, `renderMarkers()`, `switchMode()`, `togglePreview()`
- ~500-600 lines of well-structured JS
- No external dependencies — vanilla DOM APIs + Fetch API
- Loaded via `<script src="{{ asset('js/inspiration-mapper.js') }}"></script>` in the mapper Blade view

### 5.6 Publication Validation

Server-side validation when changing status to `published` (`PUT /admin/inspirations/{id}`):

| Rule | Error Message |
|---|---|
| Hero image must exist | "L'image de la scene est requise" |
| Title FR must exist | "Le titre (francais) est requis" |
| At least 1 item with valid product | "Au moins un produit doit etre associe" |
| All items must have hotspots | "N produits ne sont pas positionnes dans l'image" |
| All referenced products must exist | "N produits references n'existent plus dans le catalogue" |

Validation errors prevent the status change and return a structured error response with all failing rules.

---

## 6. Mobile App — Service Layer

### 6.1 New file: `src/services/api/inspirationService.ts`

```typescript
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
    items: InspirationItem[];
}

export interface InspirationItem {
    id: number;
    display_order: number;
    hotspot: {
        x: number;
        y: number;
    } | null;
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
        // GET /api/v2/inspirations/featured
        // Header: Accept-Language: {language}
    },

    async getAll(language: string): Promise<InspirationPreview[]> {
        // GET /api/v2/inspirations
    },

    async getBySlug(slug: string, language: string): Promise<InspirationDetail> {
        // GET /api/v2/inspirations/{slug}
    },
};
```

### 6.2 DTO additions in `src/contracts/api/dto.ts`

Add `InspirationPreview`, `InspirationDetail`, `InspirationItem` types (matching the service interfaces above).

---

## 7. Mobile App — Home Screen Integration

### 7.1 Replace hardcoded inspirations

Remove:
- `INSPIRATION_ARTWORK` static array (2 hardcoded PNG requires)
- Any hardcoded inspiration rendering

Add:
- `inspirationService.getFeatured(language)` call in `useEffect` data fetch
- Result stored in `homeCache.inspirations`
- Loading state: skeleton card (same height as inspiration card, shimmer animation)

### 7.2 Home Inspiration Section

```
Inspiration du moment                    Voir tout →

┌────────────────────────────────────────────┐
│                                            │
│         PROFESSIONAL ROOM IMAGE            │
│                                            │
│  Esprit Japandi                            │
│  6 articles                                │
└────────────────────────────────────────────┘

[ Product1 ] [ Product2 ] [ Product3 ] →
```

- Full-width card with scene image, title overlay at bottom-left, products count badge
- Below: horizontal ScrollView with preview product thumbnails (small circles, ~44px)
- Tap card → navigate to `InspirationDetailScreen` with `{ slug }`
- Tap product thumbnail → navigate to `ProductDetailScreen`
- No hotspot markers on Home — keep it lightweight for discovery
- If multiple featured inspirations: horizontal pager (same pattern as hero slider)

---

## 8. Mobile App — Inspiration Detail Screen

### 8.1 New file: `src/screens/discovery/InspirationDetailScreen.tsx`

**Screen layout (scrollable):**

```
← Retour

Esprit Japandi
Un salon chaleureux et naturel

┌────────────────────────────────────────────┐
│                                            │
│             ④                              │
│                                            │
│      ①                        ③            │
│                                            │
│               ②                            │
│                          ⑤                 │
└────────────────────────────────────────────┘

6 articles dans cette ambiance

┌────────────┐ ┌────────────┐
│  Canape    │ │   Table    │
│  Solis     │ │   Eve      │
│ 1 890 DH   │ │ 479 DH     │
│ ♡          │ │ ♡          │
└────────────┘ └────────────┘

┌────────────┐ ┌────────────┐
│  Suspension│ │   Vase     │
│  Nori      │ │   Brume    │
│ 199 DH     │ │ 129 DH     │
│ ♡          │ │ ♡          │
└────────────┘ └────────────┘
```

### 8.2 Interactive Hotspot Image

- Full-width image with natural aspect ratio preserved (`resizeMode="contain"`)
- Image wrapped in a `View` with `position: relative`
- `onLayout` callback stores rendered image dimensions
- Hotspot markers rendered as absolutely-positioned `TouchableOpacity` views:
  ```
  left: hotspot.x * imageWidth
  top: hotspot.y * imageHeight
  transform: [{ translateX: -14 }, { translateY: -14 }]  // center the 28px marker
  ```
- Markers: 28px navy circles with white number, white border, elevation shadow
- Markers reposition on orientation change via `onLayout` recalculation

### 8.3 Hotspot ↔ Product Interaction

**Tap marker → scroll to product:**
- Tap ① marker on image
- Marker animates: brief scale pulse (1.0 → 1.3 → 1.0, 300ms)
- `scrollTo` the corresponding product card in the grid below
- Product card highlights: brief border color change (navy → orange → navy, 600ms)
- Uses `useRef` for ScrollView + measured card positions

**Tap product card → highlight marker:**
- Tap a product card in the grid
- Corresponding marker on the image pulses (same animation as above)
- If marker is off-screen (user scrolled down), scroll the image into view first

### 8.4 Pinch-to-Zoom on Scene Image

- Reuse the existing gesture handler pattern from the product gallery
- Markers scale inversely during zoom so they remain readable:
  ```
  markerScale = 1 / currentZoomLevel  (clamped to 0.5–1.5)
  ```
- Double-tap resets zoom to 1x

### 8.5 Product Grid

- 2-column `FlatList` or manual grid below the image
- Reuse existing `ProductCard` component for each item
- Same wishlist toggle, cart, and navigation behavior as everywhere else
- Unavailable products: card shown with 50% opacity + "Indisponible" overlay badge
- Tap any card → navigate to `ProductDetailScreen`
- Hotspot marker on unavailable product uses muted gray color instead of navy

### 8.6 Loading State

- Skeleton loader while API responds:
  - Image placeholder: full-width rectangle with shimmer
  - 6 product card placeholders in 2-column grid with shimmer
- Error state: "Impossible de charger l'inspiration" with retry button

### 8.7 Navigation

- Registered in the navigation stack with params: `{ slug: string }`
- Deep link support: `mayush://inspiration/{slug}`
- Back button returns to previous screen (Home or future listing)

---

## 9. Unavailable Product Handling

Products go in/out of stock over time. Inspirations are long-lived. The system handles this gracefully:

### 9.1 Backend
- Products are eager-loaded live — current price/stock/availability always reflected
- API response always includes unavailable products with `available: false`
- Products are never automatically removed from an inspiration when they go out of stock

### 9.2 Mobile
- Product card renders with reduced opacity (0.5) and "Indisponible" badge
- Tap still opens ProductDetailScreen (user can see details, request restock notification)
- Hotspot marker uses muted gray color
- Product count text says "6 articles" regardless of availability (it's about the scene composition)

### 9.3 Admin
- Warning icon (⚠) next to unavailable products in the mapper product list
- Yellow banner at top: "⚠ N produit(s) indisponible(s)" when applicable
- Admin can swap the product (change hotspot association) or leave it as-is

---

## 10. Caching Strategy

### 10.1 Backend (Laravel)
- `GET /api/v2/inspirations/featured`: cached 15 minutes, key: `inspirations_featured_{lang}`
- `GET /api/v2/inspirations/{slug}`: cached 5 minutes, key: `inspiration_detail_{slug}_{lang}`
- Cache invalidated on: inspiration create/update/delete, item add/remove, hotspot change, publish/unpublish
- Cache invalidation via model events (Observer pattern)

### 10.2 Mobile
- Featured inspirations stored in `homeCache.inspirations` (same in-memory cache pattern as categories, products)
- Detail data not cached beyond Expo's standard HTTP/image caching
- Scene images cached by Expo Image component (standard URI caching)

---

## 11. File Structure

### 11.1 Backend (new files)

```
app/Models/Inspiration.php
app/Models/InspirationItem.php
app/Models/InspirationHotspot.php
app/Observers/InspirationObserver.php
database/migrations/xxxx_create_inspirations_tables.php
app/Http/Controllers/Api/V2/InspirationController.php
app/Http/Controllers/Admin/InspirationController.php
app/Http/Resources/V2/InspirationResource.php
app/Http/Resources/V2/InspirationDetailResource.php
resources/views/backend/inspirations/index.blade.php
resources/views/backend/inspirations/create.blade.php
resources/views/backend/inspirations/edit.blade.php
resources/views/backend/inspirations/mapper.blade.php
public/js/inspiration-mapper.js
routes/api.php                              (modify — add 3 routes)
routes/admin.php or routes/web.php          (modify — add admin routes)
```

### 11.2 Mobile (new/modified files)

```
src/services/api/inspirationService.ts                  (new)
src/screens/discovery/InspirationDetailScreen.tsx        (new)
src/contracts/api/dto.ts                                 (modify — add types)
src/screens/discovery/HomeScreen.tsx                     (modify — replace hardcoded)
src/navigation/...                                       (modify — add route)
```

---

## 12. Out of Scope (Future Phases)

These are explicitly **not** part of this spec but are designed to be addable later without restructuring:

- **Inspiration listing screen** (`/inspirations` page on mobile) — currently Home shows featured only
- **"Recreer cette ambiance"** — multi-select products + batch add-to-cart
- **Web frontend** — Blade views for web inspiration pages
- **Analytics** — tracking inspiration views, hotspot clicks, conversions
- **Multiple scenes per inspiration** — current schema supports one hero image; multiple scenes would need a `inspiration_scenes` table
- **Video scenes** — using video instead of static photography
- **Social sharing** — share inspiration with deep link
