<?php

namespace Tests\Integration\Controllers\Frontend;

use App\Models\Blog;
use App\Models\BlogCategory;
use App\Models\BlogTranslation;
use App\Models\Tag;
use App\Models\Translation;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Facades\App;
use Illuminate\Support\Facades\Cache;
use Tests\TestCase;
use Tests\Traits\SeedsAppConfigs;

class BlogPlatformTest extends TestCase
{
    use RefreshDatabase, SeedsAppConfigs;

    private int $baseOutputBufferLevel = 0;

    protected function setUp(): void
    {
        parent::setUp();
        $this->baseOutputBufferLevel = ob_get_level();
        App::setLocale('en');
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
    public function blog_translation_falls_back_to_base_content(): void
    {
        $blog = $this->createPublishedBlog();

        App::setLocale('fr');

        $this->assertEquals('Guide canape', $blog->fresh()->getTranslation('title'));

        App::setLocale('es');

        $this->assertEquals('Sofa Guide', $blog->fresh()->getTranslation('title'));
    }

    /** @test */
    public function blog_listing_renders_published_translated_posts(): void
    {
        $this->createPublishedBlog();
        App::setLocale('fr');

        $response = $this->withSession(['locale' => 'fr'])->get(route('blog'));

        $response->assertStatus(200);
        $response->assertSee('Guide canape');
    }

    /** @test */
    public function blog_listing_renders_category_names_in_the_active_language(): void
    {
        $this->createPublishedBlog();

        Translation::create([
            'lang' => 'en',
            'lang_key' => 'guides',
            'lang_value' => 'Guides',
        ]);
        Translation::create([
            'lang' => 'fr',
            'lang_key' => 'guides',
            'lang_value' => 'Guides en francais',
        ]);
        Cache::forget('translations-en');
        Cache::forget('translations-fr');

        $response = $this->withSession(['locale' => 'fr'])->get(route('blog'));

        $response->assertStatus(200);
        $response->assertSeeText('Guides en francais');
        $this->assertDoesNotMatchRegularExpression('/>\s*Guides\s*<\/a>/', $response->getContent());
    }

    /** @test */
    public function blog_detail_renders_related_posts(): void
    {
        $blog = $this->createPublishedBlog();
        $related = $this->createPublishedBlog('lighting-guide', 'Lighting Guide');
        $tag = Tag::firstOrCreate(['slug' => 'decor'], ['name' => 'decor']);
        $blog->tags()->sync([$tag->id]);
        $related->tags()->sync([$tag->id]);

        $response = $this->withSession(['locale' => 'en'])->get(route('blog.details', $blog->slug));

        $response->assertStatus(200);
        $response->assertSee('Related Posts');
        $response->assertSee('Lighting Guide');
    }

    /** @test */
    public function homepage_includes_latest_blog_section_when_posts_exist(): void
    {
        $this->createPublishedBlog();

        $response = $this->withSession(['locale' => 'en'])->get(route('home'));

        $response->assertStatus(200);
        $response->assertSee('Latest from Blog');
        $response->assertSee('Sofa Guide');
    }

    private function createPublishedBlog(string $slug = 'sofa-guide', string $title = 'Sofa Guide'): Blog
    {
        $category = BlogCategory::firstOrCreate(
            ['slug' => 'guides'],
            ['category_name' => 'Guides', 'status' => 1]
        );

        $blog = Blog::create([
            'category_id' => $category->id,
            'title' => $title,
            'slug' => $slug,
            'short_description' => 'Choose the right sofa.',
            'description' => '<p>Choose the right sofa.</p>',
            'status' => 1,
            'published_at' => now(),
        ]);

        BlogTranslation::create([
            'blog_id' => $blog->id,
            'lang' => 'fr',
            'title' => 'Guide canape',
            'short_description' => 'Choisir le bon canape.',
            'description' => '<p>Choisir le bon canape.</p>',
        ]);

        return $blog;
    }
}
