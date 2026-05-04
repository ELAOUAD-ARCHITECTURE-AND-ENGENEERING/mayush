<?php

namespace Tests\Integration\Controllers\Frontend;

use App\Models\Blog;
use App\Models\BlogCategory;
use App\Models\BlogSubscriberLog;
use App\Models\BusinessSetting;
use App\Models\Product;
use App\Models\Shop;
use App\Models\User;
use App\Services\Blog\BlogContentSanitizerService;
use App\Services\Blog\BlogProductMatcherService;
use App\Services\Blog\BlogSettingsService;
use App\Services\Blog\BlogTocService;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Facades\Cache;
use Illuminate\Support\Facades\Http;
use Illuminate\Support\Facades\Schema;
use Spatie\Permission\Models\Permission;
use Tests\TestCase;
use Tests\Traits\SeedsAppConfigs;

class BlogConversionFoundationTest extends TestCase
{
    use RefreshDatabase, SeedsAppConfigs;

    private int $baseOutputBufferLevel = 0;

    protected function setUp(): void
    {
        parent::setUp();
        $this->baseOutputBufferLevel = ob_get_level();
        $this->seedConfigs();
    }

    protected function tearDown(): void
    {
        while (ob_get_level() > $this->baseOutputBufferLevel) {
            ob_end_clean();
        }

        parent::tearDown();
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
        $response->assertSee('assets/blog/css/blog-conversion.css');
        $response->assertSee('assets/blog/js/blog-conversion.js');
        $response->assertSee(route('blog.subscribe'));
        $response->assertSee('Warm Table Lamp');
        $response->assertSee(route('product', $safeProduct->slug));
        $response->assertDontSee('Hidden Table Lamp');
    }

    /** @test */
    public function blog_listing_loads_conversion_assets(): void
    {
        $this->createPublishedBlog('listing-assets-guide');

        $response = $this->get(route('blog'));

        $response->assertStatus(200);
        $response->assertSee('assets/blog/css/blog-conversion.css');
        $response->assertSee('assets/blog/js/blog-conversion.js');
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
        $this->assertTrue($settings['share_bar_enabled']);
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

    /** @test */
    public function blog_detail_renders_vendor_spotlight_when_configured(): void
    {
        $blog = $this->createPublishedBlog('vendor-spotlight-guide');
        $shop = Shop::factory()->create([
            'name' => 'Atlas Atelier',
            'slug' => 'atlas-atelier',
        ]);
        $blog->shop_id = $shop->id;
        $blog->vendor_quote = 'Handmade lighting changes the entire room.';
        $blog->save();

        $response = $this->get(route('blog.details', $blog->slug));

        $response->assertStatus(200);
        $response->assertSee('Vendor spotlight');
        $response->assertSee('Atlas Atelier');
        $response->assertSee('Handmade lighting changes the entire room.');
        $response->assertSee(route('shop.visit', $shop->slug));
    }

    /** @test */
    public function blog_detail_renders_share_bar_by_default(): void
    {
        $blog = $this->createPublishedBlog('share-bar-guide');

        $response = $this->get(route('blog.details', $blog->slug));

        $response->assertStatus(200);
        $response->assertSee('WhatsApp');
        $response->assertSee('data-blog-copy-link', false);
        $response->assertSee(rawurlencode(route('blog.details', $blog->slug)));
    }

    /** @test */
    public function admin_can_update_blog_conversion_settings(): void
    {
        $admin = $this->adminWithBlogPermissions();

        $response = $this->actingAs($admin)->post(route('blog.conversion-settings.update'), [
            'blog_enable_product_embeds' => '1',
            'blog_products_per_embed' => '6',
            'blog_product_embed_cache_minutes' => '30',
            'blog_enable_product_schema' => '1',
            'blog_enable_table_of_contents' => '0',
            'blog_email_enable_listing_inline' => '1',
            'blog_email_enable_sidebar' => '0',
            'blog_email_enable_post_read' => '1',
            'blog_email_provider' => 'local',
            'blog_email_success_message' => 'Welcome to Mayush design notes.',
        ]);

        $response->assertRedirect();
        $this->assertDatabaseHas('business_settings', [
            'type' => 'blog_products_per_embed',
            'value' => '6',
        ]);
        $this->assertDatabaseHas('business_settings', [
            'type' => 'blog_enable_table_of_contents',
            'value' => '0',
        ]);
        $this->assertDatabaseHas('business_settings', [
            'type' => 'blog_email_success_message',
            'value' => 'Welcome to Mayush design notes.',
        ]);
    }

    /** @test */
    public function blog_subscription_can_deliver_to_configured_webhook_with_local_log(): void
    {
        Http::fake([
            'https://hooks.example.test/blog' => Http::response(['ok' => true], 200),
        ]);
        BusinessSetting::updateOrCreate(['type' => 'blog_email_provider'], ['value' => 'webhook']);
        BusinessSetting::updateOrCreate(['type' => 'blog_webhook_url'], ['value' => 'https://hooks.example.test/blog']);
        Cache::flush();

        $response = $this->post(route('blog.subscribe'), [
            'email' => 'webhook-reader@example.test',
            'placement' => 'listing_inline',
            'website' => '',
        ]);

        $response->assertRedirect();
        Http::assertSent(fn ($request) => $request->url() === 'https://hooks.example.test/blog'
            && $request['email'] === 'webhook-reader@example.test');
        $this->assertDatabaseHas('blog_subscriber_logs', [
            'email' => 'webhook-reader@example.test',
            'provider' => 'webhook',
            'provider_status' => 'delivered',
        ]);
    }

    /** @test */
    public function admin_can_filter_and_export_blog_subscriber_logs(): void
    {
        $admin = $this->adminWithBlogPermissions();
        BlogSubscriberLog::create([
            'email' => 'reader@example.test',
            'placement' => 'post_read',
            'blog_title' => 'Lighting Guide',
            'provider' => 'local',
            'provider_status' => 'logged',
            'subscribed_at' => now(),
        ]);
        BlogSubscriberLog::create([
            'email' => 'other@example.test',
            'placement' => 'sidebar',
            'blog_title' => 'Sofa Guide',
            'provider' => 'local',
            'provider_status' => 'logged',
            'subscribed_at' => now(),
        ]);

        $index = $this->actingAs($admin)->get(route('blog.conversion-subscribers', ['email' => 'reader']));
        $index->assertStatus(200);
        $index->assertSee('reader@example.test');
        $index->assertDontSee('other@example.test');

        $export = $this->actingAs($admin)->get(route('blog.conversion-subscribers.export', ['email' => 'reader']));
        $export->assertStatus(200);
        $export->assertHeader('Content-Type', 'text/csv; charset=UTF-8');
        $this->assertStringContainsString('reader@example.test', $export->getContent());
        $this->assertStringNotContainsString('other@example.test', $export->getContent());
    }

    /** @test */
    public function admin_can_store_blog_conversion_fields_and_manual_products(): void
    {
        $admin = $this->adminWithBlogPermissions();
        $category = $this->createBlogCategory();
        $safeProduct = Product::factory()->create([
            'name' => 'Admin Safe Lamp',
            'slug' => 'admin-safe-lamp',
            'current_stock' => 5,
            'min_qty' => 1,
        ]);
        $hiddenProduct = Product::factory()->unpublished()->create([
            'name' => 'Admin Hidden Lamp',
            'slug' => 'admin-hidden-lamp',
            'current_stock' => 5,
            'min_qty' => 1,
        ]);

        $response = $this->actingAs($admin)->post(route('blog.store'), [
            'category_id' => $category->id,
            'title' => 'Admin Conversion Guide',
            'slug' => 'admin-conversion-guide',
            'short_description' => 'A guide for admin managed content.',
            'description' => '<h2>Guide</h2>',
            'badge_type' => 'buying_guide',
            'is_featured' => '1',
            'schema_enabled' => '1',
            'canonical_url' => 'https://example.test/blog/admin-conversion-guide',
            'product_ids' => [$safeProduct->id, $hiddenProduct->id],
        ]);

        $response->assertRedirect(route('blog.index'));
        $blog = Blog::where('slug', 'admin-conversion-guide')->firstOrFail();
        $this->assertSame('buying_guide', $blog->badge_type);
        $this->assertSame(1, (int) $blog->is_featured);
        $this->assertSame('https://example.test/blog/admin-conversion-guide', $blog->canonical_url);
        $this->assertTrue($blog->products()->where('products.id', $safeProduct->id)->exists());
        $this->assertFalse($blog->products()->where('products.id', $hiddenProduct->id)->exists());
    }

    /** @test */
    public function admin_can_update_blog_conversion_fields_and_manual_products(): void
    {
        $admin = $this->adminWithBlogPermissions();
        $blog = $this->createPublishedBlog('admin-update-guide');
        $product = Product::factory()->create([
            'name' => 'Updated Admin Lamp',
            'slug' => 'updated-admin-lamp',
            'current_stock' => 5,
            'min_qty' => 1,
        ]);

        $response = $this->actingAs($admin)->patch(route('blog.update', $blog->id), [
            'category_id' => $blog->category_id,
            'title' => 'Updated Admin Conversion Guide',
            'slug' => 'admin-update-guide',
            'short_description' => 'Updated short description.',
            'description' => '<h2>Updated Guide</h2>',
            'badge_type' => 'expert_pick',
            'custom_badge_text' => null,
            'schema_enabled' => '1',
            'product_ids' => [$product->id],
        ]);

        $response->assertRedirect(route('blog.index'));
        $blog->refresh();
        $this->assertSame('expert_pick', $blog->badge_type);
        $this->assertSame('Updated Admin Conversion Guide', $blog->title);
        $this->assertTrue($blog->products()->where('products.id', $product->id)->exists());
    }

    private function createPublishedBlog(string $slug = 'conversion-guide'): Blog
    {
        $category = $this->createBlogCategory();

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

    private function createBlogCategory(): BlogCategory
    {
        $category = BlogCategory::where('slug', 'lighting')->first();
        if (!$category) {
            $category = new BlogCategory();
            $category->slug = 'lighting';
            $category->category_name = 'Lighting';
            $category->status = 1;
            $category->save();
        }

        return $category;
    }

    private function adminWithBlogPermissions(): User
    {
        $admin = User::factory()->create(['user_type' => 'admin']);

        foreach (['view_blogs', 'add_blog', 'edit_blog'] as $permission) {
            Permission::findOrCreate($permission, 'web');
            $admin->givePermissionTo($permission);
        }

        return $admin;
    }
}
