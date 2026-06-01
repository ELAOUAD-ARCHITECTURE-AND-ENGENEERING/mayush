<?php

namespace Tests\Feature\Frontend;

use App\Models\BusinessSetting;
use App\Models\Blog;
use App\Models\BlogCategory;
use App\Models\Category;
use App\Models\Language;
use App\Models\Product;
use App\Models\Upload;
use App\Services\HomeLayoutService;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Facades\Cache;
use Illuminate\Support\Facades\DB;
use Tests\TestCase;

class HomepageRenderingTest extends TestCase
{
    use RefreshDatabase;

    protected function setUp(): void
    {
        parent::setUp();

        Cache::flush();
        $this->setting('homepage_select', 'classic');
        $this->setting('vendor_system_activation', '0');
        $this->setting('best_selling', '0');
    }

    public function test_guest_can_view_homepage_with_empty_database(): void
    {
        $this->get(route('home'))
            ->assertOk()
            ->assertSee('home-banner-area', false);
    }

    public function test_homepage_renders_with_minimal_seeded_category_settings(): void
    {
        $category = Category::factory()->create([
            'name' => 'Living Room',
            'slug' => 'living-room',
            'featured' => 1,
            'hot_category' => 1,
        ]);

        Cache::flush();

        $this->get(route('home'))
            ->assertOk()
            ->assertSee('Living Room');

        $data = app(HomeLayoutService::class)->getHomepageData();

        $this->assertTrue($data['featured_categories']->contains('id', $category->id));
        $this->assertTrue($data['hot_categories']->contains('id', $category->id));
        $this->assertSame('en', $data['lang']);
    }

    public function test_homepage_ajax_sections_accept_the_methods_used_by_frontend_scripts(): void
    {
        foreach ([
            'home.section.featured',
            'home.section.best_selling',
            'home.section.home_categories',
            'home.section.best_sellers',
            'home.section.auction_products',
            'load-elite-artisans-section',
        ] as $routeName) {
            $this->post(route($routeName))->assertOk();
        }

        foreach ([
            'home.section.todays_deal',
            'home.section.newest_products',
            'home.section.preorder_products',
        ] as $routeName) {
            $this->get(route($routeName))->assertOk();
        }
    }

    public function test_metro_homepage_renders_admin_configured_hero_overlay(): void
    {
        $upload = Upload::create([
            'file_original_name' => 'hero.jpg',
            'file_name' => 'uploads/test-hero.jpg',
            'extension' => 'jpg',
            'type' => 'image',
            'file_size' => 1024,
        ]);

        $this->setting('homepage_select', 'metro');
        $this->setting('home_slider_images', json_encode([$upload->id]));
        $this->setting('home_slider_links', json_encode(['https://example.test/legacy-slide-link']));
        $this->setting('home_slider_titles', json_encode(['Design <span style="color: red;"><strong>Your Living Room</strong></span><script>alert("x")</script>']));
        $this->setting('home_slider_descriptions', json_encode(['Curated furniture and decor from Mayush sellers.']));
        $this->setting('home_slider_cta_texts', json_encode(['Shop the Collection']));
        $this->setting('home_slider_cta_links', json_encode(['https://example.test/collections/living-room']));

        Cache::flush();

        $this->get(route('home'))
            ->assertOk()
            ->assertSee('metro-hero-slide has-content', false)
            ->assertSee('Design <span style="color: red;"><strong>Your Living Room</strong></span>', false)
            ->assertDontSee('alert("x")', false)
            ->assertSee('Curated furniture and decor from Mayush sellers.')
            ->assertSee('Shop the Collection')
            ->assertSee('href="https://example.test/collections/living-room"', false);
    }

    public function test_metro_homepage_cta_button_falls_back_to_search_when_link_is_empty(): void
    {
        $upload = Upload::create([
            'file_original_name' => 'hero.jpg',
            'file_name' => 'uploads/test-hero.jpg',
            'extension' => 'jpg',
            'type' => 'image',
            'file_size' => 1024,
        ]);

        $this->setting('homepage_select', 'metro');
        $this->setting('home_slider_images', json_encode([$upload->id]));
        $this->setting('home_slider_links', json_encode([null]));
        $this->setting('home_slider_titles', json_encode(['Et si votre interieur refletait qui vous etes ?']));
        $this->setting('home_slider_descriptions', json_encode(['Mobilier et decoration design selectionnes.']));
        $this->setting('home_slider_cta_texts', json_encode(['Decouvrir nos collections']));
        $this->setting('home_slider_cta_links', json_encode([null]));

        Cache::flush();

        $this->get(route('home'))
            ->assertOk()
            ->assertSee('Decouvrir nos collections')
            ->assertSee('href="' . route('search') . '"', false);
    }

    public function test_metro_featured_categories_appear_directly_after_hero_with_h2_heading(): void
    {
        $this->setting('homepage_select', 'metro');
        Category::factory()->create([
            'name' => 'Featured Dining',
            'slug' => 'featured-dining',
            'featured' => 1,
        ]);

        Cache::flush();

        $response = $this->get(route('home'))->assertOk();
        $content = $response->getContent();

        $this->assertStringContainsString('<h2 class="fs-16 fs-md-20 fw-700 mb-2 mb-sm-0">', $content);
        $this->assertStringContainsString('Featured Categories', $content);
        $this->assertStringNotContainsString('<h3 class="fs-16 fs-md-20 fw-700 mb-2 mb-sm-0">', $content);
        $this->assertLessThan(
            strpos($content, '<h2 class="fs-16 fs-md-20 fw-700 mb-2 mb-sm-0">'),
            strpos($content, 'home-banner-area')
        );
        $this->assertLessThan(
            strpos($content, 'id="todays_deal_section"'),
            strpos($content, 'Featured Categories')
        );
    }

    public function test_metro_todays_deal_section_has_daily_countdown_and_product_grid(): void
    {
        $this->setting('homepage_select', 'metro');

        Product::factory()->count(8)->create([
            'todays_deal' => 1,
            'published' => 1,
            'approved' => 1,
            'auction_product' => 0,
            'wholesale_product' => 0,
        ]);

        Cache::flush();

        $this->get(route('home.section.todays_deal'))
            ->assertOk()
            ->assertSee('todays-deal-header-row', false)
            ->assertSee('data-metro-todays-countdown', false)
            ->assertSee('data-countdown-part="days"', false)
            ->assertSee('data-countdown-part="hours"', false)
            ->assertSee('data-countdown-part="minutes"', false)
            ->assertSee('data-countdown-part="seconds"', false)
            ->assertSee('todays-deal-yellow-section', false)
            ->assertSee('todays-deal-carousel aiz-carousel', false)
            ->assertSee('data-items="6"', false)
            ->assertSee('-webkit-line-clamp: 2', false)
            ->assertSee('border: 1px solid #f97316', false)
            ->assertSee('color: #111827', false)
            ->assertSee('Specially Made For U');
    }

    public function test_metro_category_icon_navigation_can_be_hidden_from_home_settings(): void
    {
        Category::factory()->create([
            'name' => 'Hot Category',
            'slug' => 'hot-category',
            'hot_category' => 1,
        ]);

        $this->setting('homepage_select', 'metro');
        $this->setting('category_icon_navigation_status', '0');
        Cache::flush();

        $this->get(route('home'))
            ->assertOk()
            ->assertDontSee('Category Navigation');
    }

    public function test_metro_home_categories_section_can_be_hidden_from_home_settings(): void
    {
        $this->setting('homepage_select', 'metro');
        $this->setting('home_categories_section_status', '0');
        Cache::flush();

        $this->get(route('home'))
            ->assertOk()
            ->assertDontSee('id="section_home_categories"', false)
            ->assertDontSee("loadSection('" . route('home.section.home_categories') . "', '#section_home_categories')", false);

        $sectionResponse = $this->get(route('home.section.home_categories'))->assertOk();

        $this->assertSame('', $sectionResponse->getContent());
    }

    public function test_metro_collections_split_section_can_be_hidden_from_home_settings(): void
    {
        $this->setting('homepage_select', 'metro');
        $this->setting('metro_collections_section_status', '0');
        Cache::flush();

        $this->get(route('home'))
            ->assertOk()
            ->assertDontSee('id="metro_collections_section"', false)
            ->assertDontSee("loadSection('" . route('home.section.best_selling') . "', '#section_best_selling')", false)
            ->assertDontSee("loadSection('" . route('home.section.newest_products') . "', '#section_newest')", false);
    }

    public function test_metro_collection_ajax_sections_render_merged_section_headings(): void
    {
        $this->setting('homepage_select', 'metro');
        Cache::flush();

        $this->get(route('home.section.newest_products'))
            ->assertOk()
            ->assertSee('Nouvelles collections')
            ->assertSee('Découvrez une sélection exclusive de mobilier et décoration où design contemporain, confort et raffinement se rencontrent.')
            ->assertSee('metro-collection-products aiz-carousel', false)
            ->assertSee('data-autoplay="true"', false);

        $this->get(route('home.section.best_selling'))
            ->assertOk()
            ->assertSee("L’art de vivre commence chez vous")
            ->assertSee('Les meilleures ventes qui font la tendance cette saison.');
    }

    public function test_metro_inspiration_section_defaults_to_latest_six_published_blogs(): void
    {
        $this->setting('homepage_select', 'metro');
        $category = BlogCategory::create([
            'category_name' => 'Conseils',
            'slug' => 'conseils',
            'status' => 1,
        ]);

        foreach (range(1, 7) as $index) {
            Blog::create([
                'category_id' => $category->id,
                'title' => 'Inspiration Article ' . $index,
                'slug' => 'inspiration-article-' . $index,
                'short_description' => 'Short inspiration summary ' . $index,
                'description' => '<p>Article content</p>',
                'status' => 1,
                'published_at' => now()->subDays(7 - $index),
            ]);
        }

        Blog::create([
            'category_id' => $category->id,
            'title' => 'Hidden Draft Article',
            'slug' => 'hidden-draft-article',
            'short_description' => 'Draft summary',
            'description' => '<p>Draft content</p>',
            'status' => 0,
            'published_at' => now(),
        ]);

        Cache::flush();

        $response = $this->get(route('home'))->assertOk();

        $response
            ->assertSee('home_inspiration_articles_section', false)
            ->assertSee('inspiration-articles-carousel', false)
            ->assertSee('data-items="4"', false)
            ->assertSee('Inspiration &amp; Conseils', false)
            ->assertSee('read more')
            ->assertSee(route('blog'), false)
            ->assertSee('Inspiration Article 7')
            ->assertSee('Inspiration Article 2')
            ->assertDontSee('Inspiration Article 1')
            ->assertDontSee('Hidden Draft Article');
    }

    public function test_metro_inspiration_section_uses_admin_selected_articles(): void
    {
        $this->setting('homepage_select', 'metro');
        $category = BlogCategory::create([
            'category_name' => 'Conseils',
            'slug' => 'conseils',
            'status' => 1,
        ]);

        $selected = Blog::create([
            'category_id' => $category->id,
            'title' => 'Selected Inspiration',
            'slug' => 'selected-inspiration',
            'short_description' => 'Selected summary',
            'description' => '<p>Selected content</p>',
            'status' => 1,
            'published_at' => now()->subDays(10),
        ]);

        Blog::create([
            'category_id' => $category->id,
            'title' => 'Latest Inspiration',
            'slug' => 'latest-inspiration',
            'short_description' => 'Latest summary',
            'description' => '<p>Latest content</p>',
            'status' => 1,
            'published_at' => now(),
        ]);

        $this->setting('home_inspiration_blog_ids', json_encode([$selected->id]));
        Cache::flush();

        $this->get(route('home'))
            ->assertOk()
            ->assertSee('Selected Inspiration')
            ->assertDontSee('Latest Inspiration');
    }

    public function test_header_language_switcher_moves_to_cart_strip_and_currency_respects_admin_toggle(): void
    {
        Language::factory()->create([
            'name' => 'English',
            'code' => 'en',
            'app_lang_code' => 'en',
            'status' => 1,
        ]);

        DB::table('currencies')->insert([
            'id' => 1,
            'name' => 'Moroccan Dirham',
            'symbol' => 'Dhs',
            'exchange_rate' => 1,
            'code' => 'MAD',
            'status' => 1,
            'created_at' => now(),
            'updated_at' => now(),
        ]);

        $this->setting('homepage_select', 'metro');
        $this->setting('header_element', '1');
        $this->setting('show_language_switcher', 'on');
        $this->setting('show_currency_switcher', 'on');
        $this->setting('system_default_currency', '1');
        Cache::flush();

        $content = $this->get(route('home'))->assertOk()->getContent();

        $this->assertStringContainsString('header-cart-switchers', $content);
        $this->assertStringContainsString('id="lang-change"', $content);
        $this->assertStringContainsString('id="mobile-lang-change"', $content);
        $this->assertStringContainsString('id="currency-change"', $content);
        $this->assertStringContainsString('id="mobile-currency-change"', $content);
        $this->assertSame(1, substr_count($content, 'id="lang-change"'));
        $this->assertSame(1, substr_count($content, 'id="currency-change"'));

        $this->setting('show_currency_switcher', '0');
        Cache::flush();

        $content = $this->get(route('home'))->assertOk()->getContent();

        $this->assertStringContainsString('id="lang-change"', $content);
        $this->assertStringNotContainsString('id="currency-change"', $content);
        $this->assertStringNotContainsString('id="mobile-currency-change"', $content);
    }

    private function setting(string $type, string $value): void
    {
        BusinessSetting::updateOrCreate(['type' => $type], ['value' => $value]);
    }
}
