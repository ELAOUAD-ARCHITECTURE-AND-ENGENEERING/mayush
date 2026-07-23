<?php

namespace Tests\Feature\Seo;

use App\Models\Brand;
use App\Models\BusinessSetting;
use App\Models\Category;
use App\Models\Product;
use App\Models\ProductStock;
use App\Models\Shop;
use App\Models\User;
use App\Services\IndexNowService;
use App\Services\SeoService;
use Database\Seeders\BladeTranslationSeeder;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Facades\Http;
use Tests\TestCase;
use Tests\Traits\SeedsAppConfigs;

class AdvancedSeoSchemaTest extends TestCase
{
    use RefreshDatabase, SeedsAppConfigs;

    protected function setUp(): void
    {
        parent::setUp();

        $this->seedConfigs();
        $this->seed(BladeTranslationSeeder::class);
        $this->app->setLocale('fr');
        config(['app.url' => 'https://mayushdesign.test']);
    }

    public function test_product_schema_includes_return_policy_and_shipping_details(): void
    {
        BusinessSetting::updateOrCreate(['type' => 'refund_request_time'], ['value' => 12]);
        $category = Category::factory()->create();
        $brand = Brand::factory()->create(['name' => 'Atlas Design']);
        $product = Product::factory()->create([
            'category_id' => $category->id,
            'brand_id' => $brand->id,
            'digital' => 0,
            'flat_shipping_cost' => 35,
            'est_shipping_days' => 5,
        ]);

        $schema = SeoService::productSchema($product->fresh(['brand', 'reviews', 'main_category']), 'InStock');

        $this->assertSame('Product', $schema['@type']);
        $this->assertSame('MerchantReturnPolicy', $schema['offers']['hasMerchantReturnPolicy']['@type']);
        $this->assertSame(12, $schema['offers']['hasMerchantReturnPolicy']['merchantReturnDays']);
        $this->assertSame('OfferShippingDetails', $schema['offers']['shippingDetails']['@type']);
        $this->assertSame('MA', $schema['offers']['shippingDetails']['shippingDestination']['addressCountry']);
        $this->assertSame('35.00', $schema['offers']['shippingDetails']['shippingRate']['value']);
    }

    public function test_category_listing_renders_collection_and_faq_schema(): void
    {
        $category = Category::factory()->create([
            'name' => 'Office Furniture',
            'slug' => 'office-furniture',
            'meta_title' => 'Office Furniture Morocco Modern Workspace Collection',
            'meta_description' => 'Modern office furniture for Moroccan workspaces.',
        ]);
        $product = Product::factory()->create([
            'category_id' => $category->id,
            'published' => 1,
            'approved' => 1,
        ]);
        ProductStock::factory()->create(['product_id' => $product->id]);

        $this->get(route('products.category', $category->slug))
            ->assertOk()
            ->assertSee('"@type": "CollectionPage"', false)
            ->assertSee('"@type": "FAQPage"', false)
            ->assertSee('Office Furniture Morocco Modern Workspace Collection', false)
            ->assertSee('Comment optimiser un espace de bureau ou coworking au Maroc ?', false)
            ->assertSee('id="search-form"', false)
            ->assertSee('Explorez une sélection Mayush vérifiée pour comparer les styles, les prix et les options de livraison au Maroc.', false)
            ->assertSee('class="fs-13 text-secondary mb-0 mt-2 pt-2"', false)
            ->assertDontSee('<h1 class="fs-24 fs-md-28 fw-700 text-dark mb-2">Office Furniture au Maroc</h1>', false)
            ->assertDontSee('Derniere mise a jour', false)
            ->assertDontSee('geo-expert-note', false);
    }

    public function test_homepage_and_product_pages_render_mayushseo_visible_geo_content(): void
    {
        $category = Category::factory()->create([
            'name' => 'Tables a manger',
            'slug' => 'tables-a-manger',
        ]);
        $brand = Brand::factory()->create(['name' => 'Mayush Artisans']);
        $product = Product::factory()->create([
            'name' => 'Table a manger scandinave',
            'slug' => 'table-a-manger-scandinave',
            'category_id' => $category->id,
            'brand_id' => $brand->id,
            'published' => 1,
            'approved' => 1,
            'photos' => '1',
            'thumbnail_img' => 1,
            'est_shipping_days' => 4,
        ]);
        ProductStock::factory()->create(['product_id' => $product->id, 'qty' => 3]);

        $this->get(route('home'))
            ->assertOk()
            ->assertSee('max-image-preview:large, max-snippet:-1, max-video-preview:-1', false)
            ->assertSee('"@type": "ItemList"', false)
            ->assertSee('Que peut-on acheter sur Mayush au Maroc ?', false)
            ->assertSee('Mayush Marketplace', false)
            ->assertSee('Marketplace de Mobilier & Décoration au Maroc')
            ->assertSee('marketplace marocaine de mobilier', false);

        $this->get(route('product', $product->slug))
            ->assertOk()
            ->assertSee('geo-direct-answer', false)
            ->assertSee('geo-specs-table', false)
            ->assertSee('geo-expert-note', false)
            ->assertSee('Table a manger scandinave est un produit', false)
            ->assertSee('Estimation 4 jours au Maroc', false)
            ->assertSee('Casablanca, Rabat, Tanger, Marrakech', false)
            ->assertSee('Livraison Maroc', false);
    }

    public function test_global_organization_schema_includes_marketplace_entity_details(): void
    {
        $schema = SeoService::organizationSchema([
            'site_name' => 'MayushTest',
            'description' => 'Fallback description',
        ]);

        $this->assertSame('Mayush Marketplace', $schema['alternateName']);
        $this->assertSame('Maroc', $schema['foundingLocation']['name']);
        $this->assertContains('marketplace mobilier', $schema['knowsAbout']);
    }

    public function test_seller_profile_renders_mayushseo_eeat_content_and_store_schema(): void
    {
        $shop = Shop::factory()->create([
            'user_id' => User::factory()->seller()->create()->id,
            'name' => 'Atelier Atlas',
            'slug' => 'atelier-atlas',
            'address' => 'Casablanca, Maroc',
            'verification_status' => 1,
            'approval_status' => 'approved',
        ]);
        Product::factory()->create([
            'user_id' => $shop->user_id,
            'published' => 1,
            'approved' => 1,
        ]);

        $this->get(route('shop.visit', $shop->slug))
            ->assertOk()
            ->assertSee('"@type": "Store"', false)
            ->assertSee('Atelier Atlas - vendeur Mayush au Maroc', false)
            ->assertSee('Vendeur vérifié Mayush', false)
            ->assertSee('1 produits publiés', false)
            ->assertSee('Casablanca, Maroc', false);
    }

    public function test_indexnow_service_noops_without_configuration(): void
    {
        config(['seo.indexnow.enabled' => false, 'seo.indexnow.key' => null]);

        $result = app(IndexNowService::class)->submitUrl('https://mayushdesign.test/product/table');

        $this->assertFalse($result['submitted']);
        $this->assertSame(1, $result['url_count']);
        $this->assertStringContainsString('disabled', $result['reason']);
    }

    public function test_indexnow_service_submits_expected_payload_when_configured(): void
    {
        Http::fake(fn () => Http::response('', 200));
        config([
            'seo.indexnow.enabled' => true,
            'seo.indexnow.key' => 'test-indexnow-key',
            'seo.indexnow.endpoint' => 'https://api.indexnow.org/indexnow',
            'seo.indexnow.key_location' => 'https://mayushdesign.test/test-indexnow-key.txt',
        ]);

        $result = app(IndexNowService::class)->submitUrls([
            'https://mayushdesign.test/product/table',
            '/category/office-furniture',
        ]);

        $this->assertTrue($result['submitted']);
        $this->assertSame(2, $result['url_count']);

        Http::assertSent(function ($request) {
            $payload = json_decode($request->body(), true) ?: [];

            return $request->url() === 'https://api.indexnow.org/indexnow'
                && $payload['key'] === 'test-indexnow-key'
                && $payload['host'] === 'mayushdesign.test'
                && in_array('https://mayushdesign.test/product/table', $payload['urlList'], true)
                && in_array('https://mayushdesign.test/category/office-furniture', $payload['urlList'], true);
        });
    }
}
