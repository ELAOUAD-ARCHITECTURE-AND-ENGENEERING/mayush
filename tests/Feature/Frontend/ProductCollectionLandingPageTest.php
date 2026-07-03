<?php

namespace Tests\Feature\Frontend;

use App\Models\BusinessSetting;
use App\Models\LastViewedProduct;
use App\Models\Product;
use App\Models\ProductCollection;
use App\Models\Upload;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Facades\Cache;
use Tests\TestCase;

class ProductCollectionLandingPageTest extends TestCase
{
    use RefreshDatabase;

    protected function setUp(): void
    {
        parent::setUp();

        $this->setting('vendor_system_activation', '0');
        $this->setting('homepage_select', 'metro');
        Cache::flush();
    }

    public function test_collection_page_renders_grid_before_bottom_recommendations(): void
    {
        $customer = User::factory()->customer()->create();
        $popular = Product::factory()->create(['name' => 'Popular Collection Lamp', 'num_of_sale' => 120]);
        $recent = Product::factory()->create(['name' => 'Recently Viewed Collection Chair', 'num_of_sale' => 5]);
        $collection = ProductCollection::create([
            'name' => 'Living Room Edit',
            'slug' => 'living-room-edit',
            'mode' => 'manual',
            'status' => true,
        ]);
        $collection->products()->sync([$popular->id, $recent->id]);
        LastViewedProduct::create(['user_id' => $customer->id, 'product_id' => $recent->id]);

        $content = $this->actingAs($customer)
            ->get(route('product-collections.show', $collection->slug))
            ->assertOk()
            ->assertSee('Living Room Edit')
            ->assertSee('Collection Products')
            ->assertSee('Most Buying Products')
            ->assertSee('Recently Viewed Products')
            ->getContent();

        $this->assertLessThan(strpos($content, 'Most Buying Products'), strpos($content, 'Collection Products'));
        $this->assertLessThan(strpos($content, 'Recently Viewed Products'), strpos($content, 'Most Buying Products'));
    }

    public function test_recently_viewed_section_is_hidden_for_guests(): void
    {
        $product = Product::factory()->create(['name' => 'Guest Collection Product']);
        $collection = ProductCollection::create([
            'name' => 'Guest Edit',
            'slug' => 'guest-edit',
            'mode' => 'manual',
            'status' => true,
        ]);
        $collection->products()->sync([$product->id]);

        $this->get(route('product-collections.show', $collection->slug))
            ->assertOk()
            ->assertDontSee('Recently Viewed Products');
    }

    public function test_metro_banner_collection_destination_overrides_custom_url(): void
    {
        $upload = Upload::create([
            'file_original_name' => 'collection-promo.jpg',
            'file_name' => 'uploads/collection-promo.jpg',
            'extension' => 'jpg',
            'type' => 'image',
            'file_size' => 1024,
        ]);
        $collection = ProductCollection::create([
            'name' => 'Banner Collection',
            'slug' => 'banner-collection',
            'mode' => 'dynamic',
            'status' => true,
        ]);

        $this->setting('home_banner1_images', json_encode([$upload->id]));
        $this->setting('home_banner1_links', json_encode(['https://example.test/fallback']));
        $this->setting('home_banner1_collection_ids', json_encode([$collection->id]));
        $this->setting('home_banner1_titles', json_encode(['Banner Collection CTA']));
        Cache::flush();

        $this->get(route('home'))
            ->assertOk()
            ->assertSee('href="' . route('product-collections.show', $collection->slug) . '"', false)
            ->assertDontSee('https://example.test/fallback', false);
    }

    private function setting(string $type, string $value): void
    {
        BusinessSetting::where('type', $type)->delete();
        BusinessSetting::create(['type' => $type, 'value' => $value]);
    }
}
