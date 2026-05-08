<?php

namespace Tests\Feature\Seo;

use App\Models\Brand;
use App\Models\Category;
use App\Models\Product;
use App\Models\ProductStock;
use App\Models\Shop;
use App\Models\User;
use DOMDocument;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;
use Tests\Traits\SeedsAppConfigs;

class SitemapSeoTest extends TestCase
{
    use RefreshDatabase, SeedsAppConfigs;

    private string $sitemapPath;
    private ?string $originalSitemap = null;

    protected function setUp(): void
    {
        parent::setUp();
        $this->seedConfigs();

        $this->sitemapPath = public_path('sitemap.xml');
        $this->originalSitemap = file_exists($this->sitemapPath) ? file_get_contents($this->sitemapPath) : null;
    }

    protected function tearDown(): void
    {
        if ($this->originalSitemap === null) {
            if (file_exists($this->sitemapPath)) {
                unlink($this->sitemapPath);
            }
        } else {
            file_put_contents($this->sitemapPath, $this->originalSitemap);
        }

        parent::tearDown();
    }

    public function test_sitemap_command_generates_valid_xml_and_filters_private_records(): void
    {
        $category = Category::factory()->create(['slug' => 'lighting']);
        $brand = Brand::factory()->create(['slug' => 'atlas-brand', 'status' => 1]);
        $inactiveBrand = Brand::factory()->create(['slug' => 'hidden-brand', 'status' => 0]);
        $publishedProduct = Product::factory()->create([
            'slug' => 'published-lamp',
            'category_id' => $category->id,
            'brand_id' => $brand->id,
            'published' => 1,
            'approved' => 1,
        ]);
        Product::factory()->create([
            'slug' => 'draft-lamp',
            'published' => 0,
            'approved' => 1,
        ]);
        Product::factory()->create([
            'slug' => 'unapproved-lamp',
            'published' => 1,
            'approved' => 0,
        ]);
        ProductStock::factory()->create(['product_id' => $publishedProduct->id]);

        $verifiedSeller = User::factory()->seller()->create(['banned' => 0]);
        Shop::factory()->create([
            'user_id' => $verifiedSeller->id,
            'slug' => 'verified-shop',
            'verification_status' => 1,
        ]);
        $unverifiedSeller = User::factory()->seller()->create(['banned' => 0]);
        Shop::factory()->create([
            'user_id' => $unverifiedSeller->id,
            'slug' => 'hidden-shop',
            'verification_status' => 0,
        ]);

        $this->artisan('app:generate-sitemap', ['--base-url' => 'https://mayushdesign.test'])
            ->assertExitCode(0);

        $contents = file_get_contents($this->sitemapPath);
        $xml = new DOMDocument();

        $this->assertTrue($xml->loadXML($contents));
        $this->assertStringContainsString('https://mayushdesign.test', $contents);
        $this->assertStringContainsString('/category/lighting', $contents);
        $this->assertStringContainsString('/brand/atlas-brand', $contents);
        $this->assertStringContainsString('/product/published-lamp', $contents);
        $this->assertStringContainsString('/shop/verified-shop', $contents);
        $this->assertStringNotContainsString('hidden-brand', $contents);
        $this->assertStringNotContainsString('draft-lamp', $contents);
        $this->assertStringNotContainsString('unapproved-lamp', $contents);
        $this->assertStringNotContainsString('hidden-shop', $contents);
    }

    public function test_public_sitemap_and_robots_routes_return_expected_content_types(): void
    {
        file_put_contents($this->sitemapPath, '<?xml version="1.0" encoding="UTF-8"?><urlset xmlns="http://www.sitemaps.org/schemas/sitemap/0.9"><url><loc>https://mayushdesign.test</loc></url></urlset>');

        $this->get('/sitemap.xml')
            ->assertOk()
            ->assertHeader('Content-Type', 'application/xml; charset=UTF-8')
            ->assertSee('<urlset', false);

        $this->get('/robots.txt')
            ->assertOk()
            ->assertHeader('Content-Type', 'text/plain; charset=UTF-8')
            ->assertSee('Sitemap:', false);
    }

    public function test_admin_can_view_and_trigger_sitemap_generation(): void
    {
        $admin = User::factory()->admin()->create();

        $this->actingAs($admin)
            ->get(route('sitemap_generator'))
            ->assertOk()
            ->assertSee('Generate Sitemap');

        $this->actingAs($admin)
            ->post(route('generate_sitemap'), ['base_url' => 'https://mayushdesign.test'])
            ->assertRedirect(route('sitemap_generator'));

        $this->assertFileExists($this->sitemapPath);
        $this->assertStringContainsString('https://mayushdesign.test', file_get_contents($this->sitemapPath));
    }

    public function test_non_admin_cannot_trigger_sitemap_generation(): void
    {
        $customer = User::factory()->create(['user_type' => 'customer']);

        $this->actingAs($customer)
            ->post(route('generate_sitemap'), ['base_url' => 'https://mayushdesign.test'])
            ->assertNotFound();
    }

    public function test_product_and_category_pages_render_basic_seo_metadata(): void
    {
        $category = Category::factory()->create([
            'name' => 'SEO Category',
            'slug' => 'seo-category',
            'meta_title' => 'SEO Category Title',
            'meta_description' => 'SEO Category Description',
        ]);
        $brand = Brand::factory()->create();
        $product = Product::factory()->create([
            'slug' => 'seo-product',
            'category_id' => $category->id,
            'brand_id' => $brand->id,
            'published' => 1,
            'approved' => 1,
            'meta_title' => 'SEO Product Title',
            'meta_description' => 'SEO Product Description',
        ]);
        ProductStock::factory()->create(['product_id' => $product->id]);

        $this->get(route('products.category', $category->slug))
            ->assertOk()
            ->assertSee('SEO Category')
            ->assertSee('<meta name="description"', false);

        $this->get(route('product', $product->slug))
            ->assertOk()
            ->assertSee('SEO Product Title')
            ->assertSee('SEO Product Description');
    }
}
