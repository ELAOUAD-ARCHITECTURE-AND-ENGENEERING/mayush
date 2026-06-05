<?php

namespace Tests\Feature\Seo;

use App\Models\Brand;
use App\Models\BusinessSetting;
use App\Models\Category;
use App\Models\Product;
use App\Models\ProductStock;
use App\Services\IndexNowService;
use App\Services\SeoService;
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
            ->assertSee('Office Furniture Morocco Modern Workspace Collection', false);
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
