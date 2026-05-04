<?php

namespace Tests\Integration\Controllers\Frontend;

use App\Models\Blog;
use App\Models\BlogCategory;
use App\Models\BlogSubscriberLog;
use App\Models\BusinessSetting;
use App\Models\Product;
use App\Services\Blog\BlogContentSanitizerService;
use App\Services\Blog\BlogProductMatcherService;
use App\Services\Blog\BlogSettingsService;
use App\Services\Blog\BlogTocService;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Facades\Cache;
use Illuminate\Support\Facades\Schema;
use Tests\TestCase;
use Tests\Traits\SeedsAppConfigs;

class BlogConversionFoundationTest extends TestCase
{
    use RefreshDatabase, SeedsAppConfigs;

    protected function setUp(): void
    {
        parent::setUp();
        $this->seedConfigs();
    }

    /** @test */
    public function conversion_schema_extends_existing_blog_without_duplicate_article_table(): void
    {
        $this->assertTrue(Schema::hasTable('blogs'));
        $this->assertTrue(Schema::hasTable('blog_product'));
        $this->assertTrue(Schema::hasTable('blog_subscriber_logs'));
        $this->assertFalse(Schema::hasTable('blog_articles'));
        $this->assertFalse(class_exists('App\\Models\\BlogArticle'));

        foreach ([
            'hero_image',
            'badge_type',
            'custom_badge_text',
            'read_time_minutes',
            'is_featured',
            'canonical_url',
            'schema_enabled',
            'shop_id',
            'vendor_quote',
        ] as $column) {
            $this->assertTrue(Schema::hasColumn('blogs', $column), "Missing blogs.{$column}");
        }
    }

    /** @test */
    public function blog_product_matcher_returns_only_safe_manual_products(): void
    {
        $blog = $this->createPublishedBlog();
        $safeProduct = Product::factory()->create(['name' => 'Safe Lamp', 'current_stock' => 5, 'min_qty' => 1]);
        $unpublished = Product::factory()->unpublished()->create(['name' => 'Draft Lamp']);
        $unapproved = Product::factory()->unapproved()->create(['name' => 'Unapproved Lamp']);
        $outOfStock = Product::factory()->outOfStock()->create(['name' => 'Empty Lamp']);

        $blog->products()->attach($safeProduct->id, ['placement' => 'manual', 'sort_order' => 1]);
        $blog->products()->attach($unpublished->id, ['placement' => 'manual', 'sort_order' => 2]);
        $blog->products()->attach($unapproved->id, ['placement' => 'manual', 'sort_order' => 3]);
        $blog->products()->attach($outOfStock->id, ['placement' => 'manual', 'sort_order' => 4]);

        $products = app(BlogProductMatcherService::class)->productsFor($blog, 'manual', 10);

        $this->assertCount(1, $products);
        $this->assertTrue($products->contains('id', $safeProduct->id));
        $this->assertFalse($products->contains('id', $unpublished->id));
        $this->assertFalse($products->contains('id', $unapproved->id));
        $this->assertFalse($products->contains('id', $outOfStock->id));
    }

    /** @test */
    public function blog_content_sanitizer_removes_scripts_and_unsafe_attributes(): void
    {
        $html = '<h2 onclick="alert(1)">Lighting</h2><p>Safe</p><script>alert(1)</script><a href="javascript:alert(1)">Bad</a>';

        $sanitized = app(BlogContentSanitizerService::class)->sanitize($html);

        $this->assertStringContainsString('Lighting', $sanitized);
        $this->assertStringContainsString('Safe', $sanitized);
        $this->assertStringNotContainsString('<script', $sanitized);
        $this->assertStringNotContainsString('onclick', $sanitized);
        $this->assertStringNotContainsString('javascript:', $sanitized);
    }

    /** @test */
    public function subscriber_logs_can_reference_a_blog_without_requiring_one(): void
    {
        $blog = $this->createPublishedBlog();

        $withBlog = BlogSubscriberLog::create([
            'email' => 'reader@example.com',
            'placement' => 'post_read',
            'blog_id' => $blog->id,
            'blog_title' => $blog->title,
            'provider' => 'local',
            'provider_status' => 'logged',
            'subscribed_at' => now(),
        ]);

        $withoutBlog = BlogSubscriberLog::create([
            'email' => 'listing@example.com',
            'placement' => 'listing_inline',
            'provider' => 'local',
            'provider_status' => 'logged',
            'subscribed_at' => now(),
        ]);

        $this->assertTrue($withBlog->blog->is($blog));
        $this->assertNull($withoutBlog->blog);
    }

    /** @test */
    public function blog_detail_renders_sanitized_content_and_safe_product_embed(): void
    {
        $blog = $this->createPublishedBlog('safe-product-guide');
        $blog->description = '<h2 onclick="steal()">Lighting Ideas</h2><p>Use warm lamps.</p><script>maliciousArticlePayload()</script>';
        $blog->save();

        $safeProduct = Product::factory()->create([
            'name' => 'Warm Table Lamp',
            'slug' => 'warm-table-lamp',
            'current_stock' => 5,
            'min_qty' => 1,
        ]);
        $unsafeProduct = Product::factory()->unpublished()->create([
            'name' => 'Hidden Table Lamp',
            'slug' => 'hidden-table-lamp',
            'current_stock' => 5,
            'min_qty' => 1,
        ]);

        $blog->products()->attach($safeProduct->id, ['placement' => 'manual', 'sort_order' => 1]);
        $blog->products()->attach($unsafeProduct->id, ['placement' => 'manual', 'sort_order' => 2]);

        $response = $this->get(route('blog.details', $blog->slug));

        $response->assertStatus(200);
        $response->assertSee('Lighting Ideas');
        $response->assertSee('Use warm lamps.');
        $response->assertDontSee('steal()', false);
        $response->assertDontSee('maliciousArticlePayload', false);
        $response->assertSee('Shop this guide');
        $response->assertSee(route('blog.subscribe'));
        $response->assertSee('Warm Table Lamp');
        $response->assertSee(route('product', $safeProduct->slug));
        $response->assertDontSee('Hidden Table Lamp');
    }

    /** @test */
    public function blog_subscription_logs_locally_with_safe_metadata(): void
    {
        $blog = $this->createPublishedBlog('subscribe-guide');

        $response = $this->post(route('blog.subscribe'), [
            'email' => 'READER@example.test',
            'placement' => 'post_read',
            'blog_id' => $blog->id,
            'website' => '',
        ]);

        $response->assertRedirect();
        $response->assertSessionHas('blog_subscribe_success');
        $this->assertDatabaseHas('blog_subscriber_logs', [
            'email' => 'reader@example.test',
            'placement' => 'post_read',
            'blog_id' => $blog->id,
            'blog_title' => $blog->title,
            'provider' => 'local',
            'provider_status' => 'logged',
        ]);
    }

    /** @test */
    public function blog_subscription_honeypot_blocks_spam_without_logging(): void
    {
        $response = $this->from(route('blog'))->post(route('blog.subscribe'), [
            'email' => 'spam@example.test',
            'placement' => 'listing_inline',
            'website' => 'filled-by-bot',
        ]);

        $response->assertRedirect(route('blog'));
        $response->assertSessionHasErrors('website');
        $this->assertDatabaseMissing('blog_subscriber_logs', [
            'email' => 'spam@example.test',
        ]);
    }

    /** @test */
    public function blog_toc_service_injects_stable_heading_ids(): void
    {
        $result = app(BlogTocService::class)->parse('<h2>Lighting Plan</h2><p>Intro</p><h3>Lighting Plan</h3>');

        $this->assertStringContainsString('id="lighting-plan"', $result['content']);
        $this->assertStringContainsString('id="lighting-plan-2"', $result['content']);
        $this->assertSame([
            ['id' => 'lighting-plan', 'text' => 'Lighting Plan', 'level' => 2],
            ['id' => 'lighting-plan-2', 'text' => 'Lighting Plan', 'level' => 3],
        ], $result['toc']);
    }

    /** @test */
    public function blog_detail_renders_table_of_contents_from_sanitized_headings(): void
    {
        $blog = $this->createPublishedBlog('toc-guide');
        $blog->description = '<h2>Lighting Plan</h2><p>Start here.</p><h3>Ambient Light</h3>';
        $blog->save();

        $response = $this->get(route('blog.details', $blog->slug));

        $response->assertStatus(200);
        $response->assertSee('On this page');
        $response->assertSee('href="#lighting-plan"', false);
        $response->assertSee('id="lighting-plan"', false);
        $response->assertSee('Ambient Light');
    }

    /** @test */
    public function blog_conversion_settings_can_disable_optional_article_features(): void
    {
        foreach ([
            'blog_enable_product_embeds',
            'blog_email_enable_sidebar',
            'blog_email_enable_post_read',
            'blog_enable_table_of_contents',
        ] as $type) {
            BusinessSetting::updateOrCreate(['type' => $type], ['value' => '0']);
        }
        Cache::forget('business_settings');

        $blog = $this->createPublishedBlog('settings-guide');
        $blog->description = '<h2>Lighting Plan</h2><p>Start here.</p>';
        $blog->save();

        $product = Product::factory()->create([
            'name' => 'Muted Floor Lamp',
            'slug' => 'muted-floor-lamp',
            'current_stock' => 5,
            'min_qty' => 1,
        ]);
        $blog->products()->attach($product->id, ['placement' => 'manual', 'sort_order' => 1]);

        $response = $this->get(route('blog.details', $blog->slug));

        $response->assertStatus(200);
        $response->assertSee('Lighting Plan');
        $response->assertDontSee('Shop this guide');
        $response->assertDontSee('Muted Floor Lamp');
        $response->assertDontSee(route('blog.subscribe'));
        $response->assertDontSee('On this page');
    }

    /** @test */
    public function blog_settings_service_returns_safe_defaults(): void
    {
        Cache::forget('business_settings');

        $settings = app(BlogSettingsService::class)->all();

        $this->assertTrue($settings['product_embeds_enabled']);
        $this->assertTrue($settings['email_listing_inline_enabled']);
        $this->assertTrue($settings['email_sidebar_enabled']);
        $this->assertTrue($settings['email_post_read_enabled']);
        $this->assertTrue($settings['table_of_contents_enabled']);
        $this->assertTrue($settings['product_schema_enabled']);
        $this->assertSame(4, $settings['products_per_embed']);
    }

    /** @test */
    public function blog_products_api_returns_only_safe_serialized_products(): void
    {
        Cache::flush();
        $blog = $this->createPublishedBlog('api-products-guide');
        $safeProduct = Product::factory()->create([
            'name' => 'API Safe Lamp',
            'slug' => 'api-safe-lamp',
            'current_stock' => 5,
            'min_qty' => 1,
        ]);
        $hiddenProduct = Product::factory()->unpublished()->create([
            'name' => 'API Hidden Lamp',
            'slug' => 'api-hidden-lamp',
            'current_stock' => 5,
            'min_qty' => 1,
        ]);

        $blog->products()->attach($safeProduct->id, ['placement' => 'manual', 'sort_order' => 1]);
        $blog->products()->attach($hiddenProduct->id, ['placement' => 'manual', 'sort_order' => 2]);

        $response = $this->getJson('/api/blog/products?blog_id=' . $blog->id . '&placement=manual&count=4');

        $response->assertOk()
            ->assertJsonPath('success', true)
            ->assertJsonCount(1, 'data')
            ->assertJsonPath('data.0.id', $safeProduct->id)
            ->assertJsonPath('data.0.name', 'API Safe Lamp')
            ->assertJsonPath('data.0.url', route('product', $safeProduct->slug))
            ->assertJsonPath('data.0.badge', 'Available on Mayush');

        $this->assertStringNotContainsString('API Hidden Lamp', $response->getContent());
    }

    /** @test */
    public function blog_products_api_respects_product_embed_setting(): void
    {
        BusinessSetting::updateOrCreate(['type' => 'blog_enable_product_embeds'], ['value' => '0']);
        Cache::flush();
        $blog = $this->createPublishedBlog('api-disabled-guide');
        $product = Product::factory()->create([
            'name' => 'Disabled API Lamp',
            'slug' => 'disabled-api-lamp',
            'current_stock' => 5,
            'min_qty' => 1,
        ]);
        $blog->products()->attach($product->id, ['placement' => 'manual', 'sort_order' => 1]);

        $response = $this->getJson('/api/blog/products?blog_id=' . $blog->id);

        $response->assertOk()
            ->assertJsonPath('success', true)
            ->assertJsonCount(0, 'data');
    }

    /** @test */
    public function blog_detail_outputs_product_schema_for_embedded_products(): void
    {
        Cache::flush();
        $blog = $this->createPublishedBlog('schema-products-guide');
        $product = Product::factory()->create([
            'name' => 'Schema Floor Lamp',
            'slug' => 'schema-floor-lamp',
            'current_stock' => 5,
            'min_qty' => 1,
        ]);
        $blog->products()->attach($product->id, ['placement' => 'manual', 'sort_order' => 1]);

        $response = $this->get(route('blog.details', $blog->slug));

        $response->assertStatus(200);
        $response->assertSee('"@type": "Product"', false);
        $response->assertSee('Schema Floor Lamp');
        $response->assertSee(route('product', $product->slug));
    }

    private function createPublishedBlog(string $slug = 'conversion-guide'): Blog
    {
        $category = BlogCategory::firstOrCreate(
            ['slug' => 'lighting'],
            ['category_name' => 'Lighting', 'status' => 1]
        );

        return Blog::create([
            'category_id' => $category->id,
            'title' => 'Lighting Conversion Guide',
            'slug' => $slug,
            'short_description' => 'Choose lighting that fits your home.',
            'description' => '<h2>Lighting Ideas</h2><p>Choose lighting that fits your home.</p>',
            'status' => 1,
            'published_at' => now(),
        ]);
    }
}
