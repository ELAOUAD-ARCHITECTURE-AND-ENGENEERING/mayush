<?php

namespace Tests\Feature\Frontend;

use App\Models\BusinessSetting;
use App\Models\FlashDeal;
use App\Models\FlashDealProduct;
use App\Models\Product;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Facades\Cache;
use Illuminate\Support\Facades\Schema;
use Tests\TestCase;
use Tests\Traits\SeedsAppConfigs;

class FlashDealsLandingPageTest extends TestCase
{
    use RefreshDatabase, SeedsAppConfigs;

    protected function setUp(): void
    {
        parent::setUp();

        $this->seedConfigs();
        if (! Schema::hasTable('flash_deal_translations')) {
            Schema::create('flash_deal_translations', function ($table) {
                $table->id();
                $table->unsignedBigInteger('flash_deal_id');
                $table->string('title')->nullable();
                $table->string('lang', 10);
                $table->timestamps();
            });
        }
        $this->setting('homepage_select', 'metro');
        $this->setting('vendor_system_activation', '0');
        Cache::flush();
    }

    public function test_flash_deals_page_uses_shared_metro_cards_and_seo_structure(): void
    {
        $deal = FlashDeal::factory()->active()->create([
            'title' => 'Living Room Flash Edit',
            'slug' => 'living-room-flash-edit',
        ]);
        $visibleProduct = Product::factory()->create([
            'name' => 'Visible Flash Deal Sofa',
            'slug' => 'visible-flash-deal-sofa',
        ]);
        $hiddenProduct = Product::factory()->unpublished()->create([
            'name' => 'Hidden Draft Flash Deal Sofa',
            'slug' => 'hidden-draft-flash-deal-sofa',
        ]);
        $unapprovedProduct = Product::factory()->unapproved()->create([
            'name' => 'Hidden Unapproved Flash Deal Sofa',
            'slug' => 'hidden-unapproved-flash-deal-sofa',
        ]);

        foreach ([$visibleProduct, $hiddenProduct, $unapprovedProduct] as $product) {
            FlashDealProduct::create([
                'flash_deal_id' => $deal->id,
                'product_id' => $product->id,
            ]);
        }

        $response = $this->get(route('flash-deals'))
            ->assertOk()
            ->assertSee('<link rel="canonical" href="' . route('flash-deals') . '">', false)
            ->assertSee('<h1 class="flash-deals-title">', false)
            ->assertSee('Active flash deals')
            ->assertSee('<h3 class="flash-deal-card__title">', false)
            ->assertSee('Living Room Flash Edit')
            ->assertSee('Visible Flash Deal Sofa')
            ->assertSee('aiz-card-box h-auto bg-white py-3 hov-scale-img', false)
            ->assertSee('"@type": "BreadcrumbList"', false)
            ->assertSee('"@type": "ItemList"', false)
            ->assertDontSee('Hidden Draft Flash Deal Sofa')
            ->assertDontSee('Hidden Unapproved Flash Deal Sofa')
            ->assertDontSee('public/css/modern.css', false);

        $this->assertSame(1, substr_count($response->getContent(), '<h1'));
    }

    public function test_flash_deals_grid_only_returns_approved_published_products(): void
    {
        $deal = FlashDeal::factory()->active()->create();
        $visibleProduct = Product::factory()->create(['name' => 'Visible Grid Product']);
        $hiddenProduct = Product::factory()->unpublished()->create(['name' => 'Hidden Grid Product']);

        FlashDealProduct::create(['flash_deal_id' => $deal->id, 'product_id' => $visibleProduct->id]);
        FlashDealProduct::create(['flash_deal_id' => $deal->id, 'product_id' => $hiddenProduct->id]);

        $this->get(route('flash-deals-grid'))
            ->assertOk()
            ->assertSee('Visible Grid Product')
            ->assertDontSee('Hidden Grid Product')
            ->assertSee('aiz-card-box h-auto bg-white py-3 hov-scale-img', false);
    }

    public function test_flash_deals_empty_state_uses_shared_cards_without_populated_grid_script(): void
    {
        Product::factory()->create(['name' => 'Fallback Popular Product']);

        $this->get(route('flash-deals'))
            ->assertOk()
            ->assertSee('New flash deals are coming soon')
            ->assertSee('Fallback Popular Product')
            ->assertSee('aiz-card-box h-auto bg-white py-3 hov-scale-img', false)
            ->assertDontSee('initFilterAndSort', false)
            ->assertDontSee('public/css/modern.css', false);
    }

    public function test_expired_and_disabled_flash_deal_pages_return_not_found(): void
    {
        $expired = FlashDeal::factory()->expired()->create(['slug' => 'expired-flash-deal']);
        $disabled = FlashDeal::factory()->inactive()->create(['slug' => 'disabled-flash-deal']);

        foreach ([$expired, $disabled] as $deal) {
            $product = Product::factory()->create();
            FlashDealProduct::create(['flash_deal_id' => $deal->id, 'product_id' => $product->id]);
        }

        $this->get(route('flash-deal-details', $expired->slug))->assertNotFound();
        $this->get(route('flash-deal-details-grid', $expired->slug))->assertNotFound();
        $this->get(route('flash-deal-details', $disabled->slug))->assertNotFound();
        $this->get(route('flash-deal-details-grid', $disabled->slug))->assertNotFound();
    }

    private function setting(string $type, string $value): void
    {
        BusinessSetting::updateOrCreate(['type' => $type], ['value' => $value]);
    }
}
