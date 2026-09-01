<?php

namespace Tests\Feature;

use Database\Seeders\InspirationsSeeder;
use App\Models\Inspiration;
use App\Models\InspirationHotspot;
use App\Models\InspirationItem;
use App\Models\Product;
use App\Models\ProductStock;
use App\Models\ProductTax;
use App\Models\ProductTranslation;
use App\Models\User;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Database\QueryException;
use Illuminate\Support\Facades\Cache;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Storage;
use Illuminate\Http\UploadedFile;
use Spatie\Permission\Models\Permission;
use Tests\TestCase;
use Tests\Traits\SeedsAppConfigs;

class InspirationFeatureTest extends TestCase
{
    use RefreshDatabase;
    use SeedsAppConfigs;

    protected function setUp(): void
    {
        parent::setUp();
        $this->seedConfigs();
        Storage::fake('public');
        Storage::disk('public')->put('inspirations/scene.webp', 'scene');
    }

    public function test_public_endpoints_only_return_currently_published_inspirations(): void
    {
        $visible = $this->inspiration(['slug' => 'visible', 'sort_order' => 2]);
        $this->attachProduct($visible);
        $this->inspiration(['slug' => 'draft', 'status' => 'draft']);
        $this->inspiration(['slug' => 'future', 'starts_at' => now()->addHour()]);
        $this->inspiration(['slug' => 'expired', 'ends_at' => now()->subHour()]);

        $response = $this->getJson('/api/v2/inspirations', [
            'Accept-Language' => 'fr',
        ]);

        $response->assertOk()
            ->assertJsonCount(1, 'data')
            ->assertJsonPath('data.0.slug', 'visible');
    }

    public function test_detail_keeps_out_of_stock_products_and_reports_live_availability(): void
    {
        $inspiration = $this->inspiration(['slug' => 'long-lived']);
        $product = Product::factory()->outOfStock()->create([
            'rating' => 4.25,
            'num_of_sale' => 17,
        ]);
        $this->attachProduct($inspiration, $product, 0.281, 0.632);

        $response = $this->getJson('/api/v2/inspirations/long-lived');

        $response->assertOk()
            ->assertJsonCount(1, 'data.items')
            ->assertJsonPath('data.items.0.product.id', $product->id)
            ->assertJsonPath('data.items.0.product.available', false)
            ->assertJsonPath('data.items.0.product.stock_status', 'out_of_stock')
            ->assertJsonPath('data.items.0.product.rating', 4.25)
            ->assertJsonPath('data.items.0.product.review_count', 0)
            ->assertJsonPath('data.items.0.product.sales', 17)
            ->assertJsonPath('data.items.0.product.links.details', route('api.products.show', $product->id))
            ->assertJsonPath('data.items.0.hotspot.x', 0.281)
            ->assertJsonPath('data.items.0.hotspot.y', 0.632);
    }

    public function test_arabic_locale_and_custom_product_title_fall_back_correctly(): void
    {
        $inspiration = $this->inspiration([
            'slug' => 'localized',
            'title_fr' => 'Salon naturel',
            'title_ar' => 'صالون طبيعي',
            'subtitle_fr' => 'Sous-titre',
            'subtitle_ar' => null,
        ]);
        $item = $this->attachProduct($inspiration);
        $item->update(['custom_title_ar' => 'منتج مختار']);

        $response = $this->getJson('/api/v2/inspirations/localized', [
            'Accept-Language' => 'ar-MA',
        ]);

        $response->assertOk()
            ->assertJsonPath('data.title', 'صالون طبيعي')
            ->assertJsonPath('data.subtitle', 'Sous-titre')
            ->assertJsonPath('data.items.0.product.name', 'منتج مختار');
    }

    public function test_featured_endpoint_is_ordered_limited_and_cache_is_invalidated_by_item_changes(): void
    {
        foreach ([4, 1, 3, 2] as $sortOrder) {
            $inspiration = $this->inspiration([
                'slug' => 'featured-'.$sortOrder,
                'sort_order' => $sortOrder,
                'is_featured' => true,
                'show_on_home' => true,
            ]);
            $this->attachProduct($inspiration);
        }

        $first = $this->getJson('/api/v2/inspirations/featured');
        $first->assertOk()->assertJsonCount(3, 'data');
        $this->assertSame(
            ['featured-1', 'featured-2', 'featured-3'],
            collect($first->json('data'))->pluck('slug')->all()
        );
        $this->assertTrue(Cache::has('inspirations_featured_fr'));

        InspirationItem::query()->first()->update(['display_order' => 99]);

        $this->assertFalse(Cache::has('inspirations_featured_fr'));
    }

    public function test_featured_endpoint_ignores_caller_limit_and_uses_only_canonical_cache_key(): void
    {
        foreach (range(1, 5) as $sortOrder) {
            $inspiration = $this->inspiration([
                'slug' => 'fixed-limit-'.$sortOrder,
                'sort_order' => $sortOrder,
                'is_featured' => true,
                'show_on_home' => true,
            ]);
            $this->attachProduct($inspiration);
        }

        $this->getJson('/api/v2/inspirations/featured?limit=20')
            ->assertOk()
            ->assertJsonCount(3, 'data');

        $this->assertTrue(Cache::has('inspirations_featured_fr'));
        $this->assertFalse(Cache::has('inspirations_featured_fr_20'));
    }

    public function test_product_and_hotspot_changes_invalidate_cached_live_data(): void
    {
        $inspiration = $this->inspiration([
            'slug' => 'cache-live-data',
            'is_featured' => true,
            'show_on_home' => true,
        ]);
        $product = Product::factory()->create(['current_stock' => 5]);
        $item = $this->attachProduct($inspiration, $product);

        $this->getJson('/api/v2/inspirations/featured')->assertOk();
        $this->getJson('/api/v2/inspirations/cache-live-data')->assertOk();
        $this->assertTrue(Cache::has('inspirations_featured_fr'));
        $this->assertTrue(Cache::has('inspiration_detail_cache-live-data_fr'));

        $product->update(['current_stock' => 0]);
        $this->assertFalse(Cache::has('inspirations_featured_fr'));
        $this->assertFalse(Cache::has('inspiration_detail_cache-live-data_fr'));

        $this->getJson('/api/v2/inspirations/cache-live-data')->assertOk();
        $item->hotspot->update(['x' => 0.5]);
        $this->assertFalse(Cache::has('inspiration_detail_cache-live-data_fr'));
    }

    public function test_product_cache_invalidation_is_scoped_and_includes_tax_translation_and_stock(): void
    {
        $linked = $this->inspiration([
            'slug' => 'linked-cache',
            'is_featured' => true,
            'show_on_home' => true,
        ]);
        $unlinked = $this->inspiration(['slug' => 'unlinked-cache']);
        $linkedProduct = Product::factory()->create();
        $this->attachProduct($linked, $linkedProduct);
        $this->attachProduct($unlinked);

        $this->getJson('/api/v2/inspirations/featured')->assertOk();
        $this->getJson('/api/v2/inspirations/linked-cache')->assertOk();
        $this->getJson('/api/v2/inspirations/unlinked-cache')->assertOk();

        $linkedProduct->update(['unit_price' => 321]);
        $this->assertFalse(Cache::has('inspirations_featured_fr'));
        $this->assertFalse(Cache::has('inspiration_detail_linked-cache_fr'));
        $this->assertTrue(Cache::has('inspiration_detail_unlinked-cache_fr'));

        foreach (['tax', 'translation', 'stock'] as $mutation) {
            $this->getJson('/api/v2/inspirations/linked-cache')->assertOk();
            $this->assertTrue(Cache::has('inspiration_detail_linked-cache_fr'));

            if ($mutation === 'tax') {
                $tax = new ProductTax();
                $tax->product_id = $linkedProduct->id;
                $tax->tax = 20;
                $tax->tax_type = 'percent';
                $tax->save();
            } elseif ($mutation === 'translation') {
                ProductTranslation::create([
                    'product_id' => $linkedProduct->id,
                    'lang' => 'ar',
                    'name' => 'منتج مترجم',
                ]);
            } else {
                ProductStock::factory()->create([
                    'product_id' => $linkedProduct->id,
                    'qty' => 2,
                ]);
            }

            $this->assertFalse(Cache::has('inspiration_detail_linked-cache_fr'));
            $this->assertTrue(Cache::has('inspiration_detail_unlinked-cache_fr'));
        }
    }

    public function test_product_availability_matches_checkout_for_digital_variant_and_legacy_stock(): void
    {
        $digital = Product::factory()->digital()->outOfStock()->create();
        $legacy = Product::factory()->create(['current_stock' => 4]);
        $variantAvailable = Product::factory()->outOfStock()->create();
        $variantUnavailable = Product::factory()->create(['current_stock' => 99]);
        $unpublished = Product::factory()->digital()->unpublished()->create();

        ProductStock::factory()->create(['product_id' => $variantAvailable->id, 'qty' => 3]);
        ProductStock::factory()->create(['product_id' => $variantUnavailable->id, 'qty' => 0]);

        $this->assertTrue($digital->isAvailable());
        $this->assertTrue($legacy->isAvailable());
        $this->assertTrue($variantAvailable->fresh()->load('stocks')->isAvailable());
        $this->assertFalse($variantUnavailable->fresh()->load('stocks')->isAvailable());
        $this->assertFalse($unpublished->isAvailable());
    }

    public function test_inspiration_serialization_does_not_lazy_load_product_relations(): void
    {
        $inspiration = $this->inspiration(['slug' => 'no-lazy-loading']);
        foreach (range(1, 4) as $order) {
            $this->attachProduct($inspiration, null, 0.1 * $order, 0.1 * $order);
        }

        Model::preventLazyLoading();
        try {
            $this->getJson('/api/v2/inspirations/no-lazy-loading')
                ->assertOk()
                ->assertJsonCount(4, 'data.items');
        } finally {
            Model::preventLazyLoading(false);
        }
    }

    public function test_unpublished_and_unknown_details_return_not_found(): void
    {
        $this->inspiration(['slug' => 'private-draft', 'status' => 'draft']);

        $this->getJson('/api/v2/inspirations/private-draft')->assertNotFound();
        $this->getJson('/api/v2/inspirations/not-real')->assertNotFound();
    }

    public function test_publishing_returns_all_structured_validation_failures(): void
    {
        $admin = User::factory()->admin()->create();
        Permission::findOrCreate('edit_inspiration', 'web');
        $admin->givePermissionTo('edit_inspiration');
        $inspiration = $this->inspiration([
            'status' => 'draft',
            'hero_image' => 'inspirations/missing.webp',
        ]);
        $inspiration->items()->create([
            'product_id' => Product::factory()->create()->id,
            'display_order' => 1,
        ]);

        $response = $this->actingAs($admin)->from(route('inspirations.edit', $inspiration))
            ->put(route('inspirations.update', $inspiration), $this->adminPayload([
                'status' => 'published',
            ]));

        $response->assertRedirect(route('inspirations.edit', $inspiration))
            ->assertSessionHasErrors(['hero_image', 'hotspots']);
        $this->assertSame('draft', $inspiration->fresh()->status);
    }

    public function test_admin_index_uses_the_specified_view_permission(): void
    {
        $authorized = User::where('user_type', 'admin')->firstOrFail();
        Permission::findOrCreate('view_inspiration', 'web');
        $authorized->givePermissionTo('view_inspiration');

        $this->actingAs($authorized)->get(route('inspirations.index'))
            ->assertOk()
            ->assertViewIs('backend.inspirations.index');
    }

    public function test_admin_without_view_inspiration_permission_is_forbidden(): void
    {
        $unauthorized = User::factory()->admin()->create(['email_verified_at' => now()]);
        Permission::findOrCreate('view_inspiration', 'web');

        $this->actingAs($unauthorized)->get(route('inspirations.index'))->assertForbidden();
    }

    public function test_view_only_admin_cannot_add_edit_or_delete_inspirations(): void
    {
        foreach (['view_inspiration', 'add_inspiration', 'edit_inspiration', 'delete_inspiration'] as $permission) {
            Permission::findOrCreate($permission, 'web');
        }

        $inspiration = $this->inspiration(['status' => 'draft']);
        $viewOnly = User::factory()->admin()->create();
        $viewOnly->givePermissionTo('view_inspiration');
        $this->actingAs($viewOnly)->get(route('inspirations.index'))->assertOk();
        $this->actingAs($viewOnly)->get(route('inspirations.create'))->assertForbidden();
        $this->actingAs($viewOnly)->get(route('inspirations.edit', $inspiration))->assertForbidden();
        $this->actingAs($viewOnly)->delete(route('inspirations.destroy', $inspiration))->assertForbidden();
    }

    public function test_admin_with_add_permission_can_open_the_create_form(): void
    {
        Permission::findOrCreate('add_inspiration', 'web');
        $adder = User::factory()->admin()->create();
        $adder->givePermissionTo('add_inspiration');
        $this->actingAs($adder)->get(route('inspirations.create'))->assertOk();
    }

    public function test_admin_with_edit_permission_can_open_edit_and_mapper(): void
    {
        Permission::findOrCreate('edit_inspiration', 'web');
        $inspiration = $this->inspiration(['status' => 'draft']);
        $editor = User::factory()->admin()->create();
        $editor->givePermissionTo('edit_inspiration');
        $this->actingAs($editor)->get(route('inspirations.edit', $inspiration))->assertOk();
        $this->actingAs($editor)->get(route('inspirations.mapper', $inspiration))->assertOk();
    }

    public function test_admin_with_delete_permission_can_soft_delete_an_inspiration(): void
    {
        Permission::findOrCreate('delete_inspiration', 'web');
        $inspiration = $this->inspiration(['status' => 'draft']);
        $deleter = User::factory()->admin()->create();
        $deleter->givePermissionTo('delete_inspiration');
        $this->actingAs($deleter)->delete(route('inspirations.destroy', $inspiration))->assertRedirect();
        $this->assertSoftDeleted('inspirations', ['id' => $inspiration->id]);
    }

    public function test_admin_upload_create_replace_soft_delete_and_force_delete_lifecycle(): void
    {
        foreach (['add_inspiration', 'edit_inspiration', 'delete_inspiration'] as $permission) {
            Permission::findOrCreate($permission, 'web');
        }
        $admin = User::factory()->admin()->create();
        $admin->givePermissionTo(['add_inspiration', 'edit_inspiration', 'delete_inspiration']);

        $this->actingAs($admin)->post(route('inspirations.store'), $this->adminPayload([
            'slug' => 'uploaded-scene',
            'hero_image' => UploadedFile::fake()->image('scene.jpg', 1200, 800),
        ]))->assertRedirect();

        $inspiration = Inspiration::where('slug', 'uploaded-scene')->firstOrFail();
        $originalPath = $inspiration->hero_image;
        Storage::disk('public')->assertExists($originalPath);
        $this->assertSame(1200, $inspiration->hero_image_width);
        $this->assertSame(800, $inspiration->hero_image_height);

        $this->actingAs($admin)->put(route('inspirations.update', $inspiration), $this->adminPayload([
            'slug' => 'uploaded-scene',
            'hero_image' => UploadedFile::fake()->image('replacement.jpg', 1600, 900),
        ]))->assertRedirect();

        $inspiration->refresh();
        Storage::disk('public')->assertMissing($originalPath);
        Storage::disk('public')->assertExists($inspiration->hero_image);
        $this->assertSame(1600, $inspiration->hero_image_width);
        $this->assertSame(900, $inspiration->hero_image_height);

        $currentPath = $inspiration->hero_image;
        $this->actingAs($admin)->delete(route('inspirations.destroy', $inspiration))->assertRedirect();
        Storage::disk('public')->assertExists($currentPath);
        $inspiration->forceDelete();
        Storage::disk('public')->assertMissing($currentPath);
    }

    public function test_each_publication_rule_is_reported_without_changing_status(): void
    {
        $admin = User::factory()->admin()->create();
        Permission::findOrCreate('edit_inspiration', 'web');
        $admin->givePermissionTo('edit_inspiration');

        $missingImage = $this->inspiration([
            'slug' => 'publish-missing-image',
            'status' => 'draft',
            'hero_image' => 'inspirations/not-on-disk.webp',
        ]);
        $this->attachProduct($missingImage);
        $this->actingAs($admin)->put(route('inspirations.update', $missingImage), $this->adminPayload([
            'slug' => $missingImage->slug,
            'status' => 'published',
        ]))->assertSessionHasErrors('hero_image');

        $noItems = $this->inspiration(['slug' => 'publish-no-items', 'status' => 'draft']);
        $this->actingAs($admin)->put(route('inspirations.update', $noItems), $this->adminPayload([
            'slug' => $noItems->slug,
            'status' => 'published',
        ]))->assertSessionHasErrors('items');

        $missingHotspot = $this->inspiration(['slug' => 'publish-no-hotspot', 'status' => 'draft']);
        $missingHotspot->items()->create([
            'product_id' => Product::factory()->create()->id,
            'display_order' => 1,
        ]);
        $this->actingAs($admin)->put(route('inspirations.update', $missingHotspot), $this->adminPayload([
            'slug' => $missingHotspot->slug,
            'status' => 'published',
        ]))->assertSessionHasErrors('hotspots');

        $this->assertSame('draft', $missingImage->fresh()->status);
        $this->assertSame('draft', $noItems->fresh()->status);
        $this->assertSame('draft', $missingHotspot->fresh()->status);
    }

    public function test_hotspot_endpoints_validate_coordinates_duplicates_and_parent_scope(): void
    {
        $admin = User::factory()->admin()->create();
        Permission::findOrCreate('edit_inspiration', 'web');
        $admin->givePermissionTo('edit_inspiration');
        $first = $this->inspiration(['slug' => 'first']);
        $second = $this->inspiration(['slug' => 'second']);
        $product = Product::factory()->create();

        $created = $this->actingAs($admin)->postJson(
            route('inspirations.hotspots.store', $first),
            ['product_id' => $product->id, 'x' => 0.25, 'y' => 0.75]
        );
        $created->assertCreated()->assertJsonPath('item.product.id', $product->id);

        $this->actingAs($admin)->postJson(
            route('inspirations.hotspots.store', $first),
            ['product_id' => $product->id, 'x' => 0.4, 'y' => 0.4]
        )->assertUnprocessable()->assertJsonValidationErrors('product_id');

        $this->actingAs($admin)->postJson(
            route('inspirations.hotspots.store', $second),
            ['product_id' => Product::factory()->create()->id, 'x' => 1.01, 'y' => -0.01]
        )->assertUnprocessable()->assertJsonValidationErrors(['x', 'y']);

        $hotspot = InspirationHotspot::findOrFail($created->json('item.hotspot_id'));
        $this->actingAs($admin)->putJson(
            route('inspirations.hotspots.update', [$second, $hotspot]),
            ['x' => 0.5, 'y' => 0.5]
        )->assertNotFound();

        $this->actingAs($admin)->putJson(
            route('inspirations.hotspots.update', [$first, $hotspot]),
            []
        )->assertUnprocessable()->assertJsonValidationErrors('hotspot');
    }

    public function test_replacing_and_force_deleting_scene_images_cleans_storage_safely(): void
    {
        Storage::disk('public')->put('inspirations/old.webp', 'old');
        Storage::disk('public')->put('inspirations/new.webp', 'new');
        $inspiration = $this->inspiration(['hero_image' => 'inspirations/old.webp']);

        $inspiration->update(['hero_image' => 'inspirations/new.webp']);
        $this->assertFalse(Storage::disk('public')->exists('inspirations/old.webp'));
        $this->assertTrue(Storage::disk('public')->exists('inspirations/new.webp'));

        $inspiration->delete();
        $this->assertTrue(Storage::disk('public')->exists('inspirations/new.webp'));
        $inspiration->forceDelete();
        $this->assertFalse(Storage::disk('public')->exists('inspirations/new.webp'));
    }

    public function test_mapper_contains_complete_save_history_preview_and_accessibility_contracts(): void
    {
        $script = file_get_contents(public_path('js/inspiration-mapper.js'));
        $view = file_get_contents(resource_path('views/backend/inspirations/mapper.blade.php'));

        foreach (['response.ok', 'requestAnimationFrame', 'sessionStorage', 'beforeunload',
            'async undo()', 'async redo()', 'reassignProduct', 'trapModalFocus',
            'setPreviewWidth', 'aria-label', 'this.moveSaves = new Map()',
            'const optimisticItem'] as $contract) {
            $this->assertStringContainsString($contract, $script);
        }
        $this->assertStringNotContainsString('simplified', strtolower($script));
        $this->assertStringContainsString('aria-live="polite"', $view);
        $this->assertStringContainsString('data-width="390"', $view);
        $this->assertStringContainsString('data-width="768"', $view);
        $this->assertStringContainsString('data-width="1440"', $view);
    }

    public function test_sqlite_schema_enforces_inspiration_integrity_for_direct_database_writes(): void
    {
        $creator = User::factory()->admin()->create();
        $inspiration = $this->inspiration(['created_by' => $creator->id]);
        $product = Product::factory()->create();
        $item = $this->attachProduct($inspiration, $product);
        $this->assertSame(1, (int) DB::selectOne('PRAGMA foreign_keys')->foreign_keys);
        $this->assertNotEmpty(DB::select('PRAGMA foreign_key_list(inspirations)'));

        $this->assertDatabaseWriteRejected(fn () => DB::table('inspiration_items')->insert([
            'inspiration_id' => $inspiration->id,
            'product_id' => $product->id,
        ]));
        $this->assertDatabaseWriteRejected(fn () => DB::table('inspiration_hotspots')->insert([
            'inspiration_id' => $inspiration->id,
            'inspiration_item_id' => $item->id,
            'x' => 0.2,
            'y' => 0.2,
        ]));

        $secondItem = $inspiration->items()->create([
            'product_id' => Product::factory()->create()->id,
            'display_order' => 2,
        ]);
        $this->assertDatabaseWriteRejected(fn () => DB::table('inspiration_hotspots')->insert([
            'inspiration_id' => $inspiration->id,
            'inspiration_item_id' => $secondItem->id,
            'x' => 1.01,
            'y' => 0.5,
        ]));
        $this->assertDatabaseWriteRejected(fn () => DB::table('inspirations')->insert([
            'slug' => 'invalid-status',
            'title_fr' => 'Invalid status',
            'hero_image' => 'inspirations/scene.webp',
            'status' => 'invalid',
        ]));
        $this->assertDatabaseWriteRejected(fn () => DB::table('inspirations')->insert([
            'slug' => 'missing-image',
            'title_fr' => 'Missing image',
            'hero_image' => null,
            'status' => 'draft',
        ]));

        DB::table('users')->where('id', $creator->id)->delete();
        $this->assertNull($inspiration->fresh()->created_by);
        $this->assertDatabaseWriteRejected(fn () => DB::table('products')->where('id', $product->id)->delete());

        DB::table('inspirations')->where('id', $inspiration->id)->delete();
        $this->assertDatabaseMissing('inspiration_items', ['inspiration_id' => $inspiration->id]);
        $this->assertDatabaseMissing('inspiration_hotspots', ['inspiration_id' => $inspiration->id]);
    }

    public function test_release_readiness_seeder_uses_real_images_and_all_publication_scenarios(): void
    {
        Product::factory()->count(4)->create([
            'published' => 1,
            'thumbnail_img' => 'catalog/product.webp',
        ]);

        $this->seed(InspirationsSeeder::class);

        $this->assertSame(7, Inspiration::withTrashed()->count());
        $this->assertSame(3, Inspiration::published()->featured()->count());
        $this->assertDatabaseHas('inspirations', ['status' => 'draft']);
        $this->assertDatabaseHas('inspirations', ['status' => 'archived']);
        $this->assertTrue(Inspiration::where('starts_at', '>', now())->exists());
        $this->assertTrue(Inspiration::where('ends_at', '<', now())->exists());

        Inspiration::each(function (Inspiration $inspiration): void {
            Storage::disk('public')->assertExists($inspiration->hero_image);
            $this->assertGreaterThan(0, Storage::disk('public')->size($inspiration->hero_image));
            $this->assertSame(4, $inspiration->items()->count());
            $this->assertSame(4, $inspiration->hotspots()->count());
        });
    }

    private function inspiration(array $attributes = []): Inspiration
    {
        return Inspiration::create(array_merge([
            'slug' => 'scene-'.uniqid(),
            'title_fr' => 'Inspiration test',
            'title_ar' => null,
            'subtitle_fr' => null,
            'subtitle_ar' => null,
            'description_fr' => null,
            'description_ar' => null,
            'hero_image' => 'inspirations/scene.webp',
            'hero_image_width' => 1800,
            'hero_image_height' => 1200,
            'status' => 'published',
            'is_featured' => false,
            'show_on_home' => false,
            'sort_order' => 0,
            'published_at' => now(),
            'starts_at' => null,
            'ends_at' => null,
        ], $attributes));
    }

    private function attachProduct(
        Inspiration $inspiration,
        ?Product $product = null,
        float $x = 0.25,
        float $y = 0.75
    ): InspirationItem {
        $item = $inspiration->items()->create([
            'product_id' => ($product ?? Product::factory()->create())->id,
            'display_order' => 1,
        ]);
        $item->hotspot()->create([
            'inspiration_id' => $inspiration->id,
            'x' => $x,
            'y' => $y,
            'display_order' => 1,
        ]);

        return $item;
    }

    private function adminPayload(array $overrides = []): array
    {
        return array_merge([
            'title_fr' => 'Inspiration test',
            'title_ar' => '',
            'subtitle_fr' => '',
            'subtitle_ar' => '',
            'description_fr' => '',
            'description_ar' => '',
            'slug' => 'scene-admin',
            'status' => 'draft',
            'sort_order' => 0,
            'starts_at' => null,
            'ends_at' => null,
        ], $overrides);
    }

    private function assertDatabaseWriteRejected(callable $write): void
    {
        try {
            $write();
            $this->fail('Expected the database schema to reject the invalid Inspiration write.');
        } catch (QueryException) {
            $this->addToAssertionCount(1);
        }
    }
}
