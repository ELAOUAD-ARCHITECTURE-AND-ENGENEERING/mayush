<?php

namespace Tests\Feature;

use Tests\TestCase;
use App\Models\User;
use App\Models\Product;
use App\Models\Category;
use App\Models\CategoryTranslation;
use App\Models\Language;
use App\Models\Translation;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Facades\Cache;


class PromotedCategoryTest extends TestCase
{
    use RefreshDatabase;

    protected $admin;
    protected $customer;
    protected $category;

    protected function setUp(): void
    {
        parent::setUp();

        $this->admin = User::factory()->create(['user_type' => 'admin']);
        $this->customer = User::factory()->create(['user_type' => 'customer']);
        $this->category = Category::factory()->create(['name' => 'Test Promo Category']);
        Cache::forget('business_settings');
        app(\App\Services\StorefrontCacheService::class)->bump();
    }

    /** @test */
    public function admin_can_fetch_products_by_category()
    {
        $product = Product::factory()->create([
            'category_id' => $this->category->id,
            'published' => 1,
            'approved' => 1,
            'unit_price' => 100,
            'discount' => 10,
            'discount_type' => 'percent',
        ]);

        $response = $this->actingAs($this->admin)
            ->post(route('promotional_category.products'), [
                'category_id' => $this->category->id,
            ]);

        $response->assertStatus(200);
        $response->assertSee($product->name);
    }

    /** @test */
    public function admin_can_update_product_discount()
    {
        $product = Product::factory()->create([
            'category_id' => $this->category->id,
            'published' => 1,
            'approved' => 1,
            'unit_price' => 200,
            'discount' => 0,
            'discount_type' => 'amount',
        ]);

        $response = $this->actingAs($this->admin)
            ->post(route('promotional_category.update_discounts'), [
                'product_id' => $product->id,
                'discount' => 25,
                'discount_type' => 'percent',
            ]);

        $response->assertStatus(200);
        $response->assertJson(['success' => true]);

        $product->refresh();
        $this->assertEquals(25, $product->discount);
        $this->assertEquals('percent', $product->discount_type);
    }

    /** @test */
    public function non_admin_cannot_access_promotional_endpoints()
    {
        $response = $this->actingAs($this->customer)
            ->post(route('promotional_category.products'), [
                'category_id' => $this->category->id,
            ]);

        // Should be redirected (302), forbidden (403), or not found (404 - used by IsAdmin middleware)
        $this->assertTrue(in_array($response->getStatusCode(), [302, 403, 404]));
    }

    /** @test */
    public function guest_cannot_access_promotional_endpoints()
    {
        $response = $this->post(route('promotional_category.products'), [
            'category_id' => $this->category->id,
        ]);

        $response->assertStatus(302); // Redirect to login
    }

    /** @test */
    public function empty_category_returns_no_products_message()
    {
        $emptyCategory = Category::factory()->create(['name' => 'Empty Category']);

        $response = $this->actingAs($this->admin)
            ->post(route('promotional_category.products'), [
                'category_id' => $emptyCategory->id,
            ]);

        $response->assertStatus(200);
        $response->assertSee('No published products found');
    }

    /** @test */
    public function promoted_section_hidden_when_disabled()
    {
        // Ensure the setting is off
        \App\Models\BusinessSetting::updateOrCreate(
            ['type' => 'promoted_category_status'],
            ['value' => '0']
        );
        Cache::forget('business_settings');

        $response = $this->get('/');
        $response->assertDontSee('<section class="promoted-category-section');
    }

    /** @test */
    public function promoted_section_shows_when_enabled_with_discounted_products()
    {
        $product = Product::factory()->create([
            'category_id' => $this->category->id,
            'published' => 1,
            'approved' => 1,
            'unit_price' => 100,
            'discount' => 15,
            'discount_type' => 'percent',
        ]);

        \App\Models\BusinessSetting::updateOrCreate(
            ['type' => 'promoted_category_status'],
            ['value' => '1']
        );
        \App\Models\BusinessSetting::updateOrCreate(
            ['type' => 'promoted_category_id'],
            ['value' => $this->category->id]
        );
        Cache::forget('business_settings');

        $response = $this->get('/');

        $response->assertSee('promoted-category-section');
    }

    /** @test */
    public function promoted_section_renders_category_title_as_h2_and_configured_subtitle_as_h3()
    {
        $this->category->update([
            'name' => 'Mobilier de Bureau',
            'slug' => 'mobilier-de-bureau',
        ]);

        Product::factory()->create([
            'category_id' => $this->category->id,
            'published' => 1,
            'approved' => 1,
            'unit_price' => 100,
            'discount' => 15,
            'discount_type' => 'percent',
        ]);

        $subtitle = 'Des espaces inspirants pour plus d’efficacité Découvrez notre sélection exclusive de mobilier de bureau alliant design, confort et fonctionnalité.';

        \App\Models\BusinessSetting::updateOrCreate(
            ['type' => 'homepage_select'],
            ['value' => 'metro']
        );
        \App\Models\BusinessSetting::updateOrCreate(
            ['type' => 'promoted_category_status'],
            ['value' => '1']
        );
        \App\Models\BusinessSetting::updateOrCreate(
            ['type' => 'promoted_category_id'],
            ['value' => $this->category->id]
        );
        \App\Models\BusinessSetting::updateOrCreate(
            ['type' => 'promoted_category_subtitle', 'lang' => 'en'],
            ['value' => $subtitle]
        );
        Cache::forget('business_settings');

        $response = $this->get('/');

        $response->assertSee('<h2 class="promoted-category-title', false);
        $response->assertSee('Mobilier de Bureau');
        $response->assertSee('<h3 class="promoted-category-subtitle', false);
        $response->assertSee($subtitle);
        $this->assertLessThan(
            strpos($response->getContent(), '<h3 class="promoted-category-subtitle'),
            strpos($response->getContent(), '<h2 class="promoted-category-title')
        );
    }

    /** @test */
    public function promoted_section_uses_selected_language_category_translation_instead_of_english_base_name()
    {
        $this->category->update([
            'name' => 'Unique Promo Chair',
            'slug' => 'unique-promo-chair',
        ]);

        Language::create([
            'name' => 'French',
            'code' => 'fr',
            'rtl' => 0,
            'status' => 1,
        ]);

        CategoryTranslation::create([
            'category_id' => $this->category->id,
            'lang' => 'fr',
            'name' => 'Mobilier de Bureau',
        ]);

        Product::factory()->create([
            'category_id' => $this->category->id,
            'published' => 1,
            'approved' => 1,
            'unit_price' => 100,
            'discount' => 15,
            'discount_type' => 'percent',
        ]);

        \App\Models\BusinessSetting::updateOrCreate(
            ['type' => 'homepage_select'],
            ['value' => 'metro']
        );
        \App\Models\BusinessSetting::updateOrCreate(
            ['type' => 'promoted_category_status'],
            ['value' => '1']
        );
        \App\Models\BusinessSetting::updateOrCreate(
            ['type' => 'promoted_category_id'],
            ['value' => $this->category->id]
        );
        Cache::forget('business_settings');

        $this->withSession(['locale' => 'fr'])
            ->get('/')
            ->assertOk()
            ->assertSee('Mobilier de Bureau')
            ->assertDontSee('Unique Promo Chair');
    }

    /** @test */
    public function promoted_section_uses_ui_translation_when_category_translation_repeats_base_english_name()
    {
        $this->category->update([
            'name' => 'Unique Promo Desk',
            'slug' => 'unique-promo-desk',
        ]);

        Language::create([
            'name' => 'French',
            'code' => 'fr',
            'rtl' => 0,
            'status' => 1,
        ]);

        CategoryTranslation::create([
            'category_id' => $this->category->id,
            'lang' => 'fr',
            'name' => 'Unique Promo Desk',
        ]);

        Translation::create([
            'lang' => 'fr',
            'lang_key' => 'unique_promo_desk',
            'lang_value' => 'Mobilier de bureau',
        ]);

        Product::factory()->create([
            'category_id' => $this->category->id,
            'published' => 1,
            'approved' => 1,
            'unit_price' => 100,
            'discount' => 15,
            'discount_type' => 'percent',
        ]);

        \App\Models\BusinessSetting::updateOrCreate(
            ['type' => 'homepage_select'],
            ['value' => 'metro']
        );
        \App\Models\BusinessSetting::updateOrCreate(
            ['type' => 'promoted_category_status'],
            ['value' => '1']
        );
        \App\Models\BusinessSetting::updateOrCreate(
            ['type' => 'promoted_category_id'],
            ['value' => $this->category->id]
        );
        Cache::forget('business_settings');
        Cache::forget('translations-fr');

        $this->withSession(['locale' => 'fr'])
            ->get('/')
            ->assertOk()
            ->assertSee('Mobilier de bureau')
            ->assertDontSee('Unique Promo Desk');
    }

    /** @test */
    public function promoted_section_uses_default_h3_subtitle_when_admin_has_not_saved_one()
    {
        Product::factory()->create([
            'category_id' => $this->category->id,
            'published' => 1,
            'approved' => 1,
            'unit_price' => 100,
            'discount' => 15,
            'discount_type' => 'percent',
        ]);

        \App\Models\BusinessSetting::updateOrCreate(
            ['type' => 'homepage_select'],
            ['value' => 'metro']
        );
        \App\Models\BusinessSetting::updateOrCreate(
            ['type' => 'promoted_category_status'],
            ['value' => '1']
        );
        \App\Models\BusinessSetting::updateOrCreate(
            ['type' => 'promoted_category_id'],
            ['value' => $this->category->id]
        );
        Cache::forget('business_settings');

        $this->get('/')
            ->assertOk()
            ->assertSee('<h3 class="promoted-category-subtitle', false)
            ->assertSee('Des espaces inspirants pour plus d’efficacité');
    }
}
