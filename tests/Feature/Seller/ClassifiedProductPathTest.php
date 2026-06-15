<?php

namespace Tests\Feature\Seller;

use App\Models\BusinessSetting;
use App\Models\Category;
use App\Models\CustomerProduct;
use App\Models\Shop;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Facades\DB;
use Tests\TestCase;
use Tests\Traits\SeedsAppConfigs;

class ClassifiedProductPathTest extends TestCase
{
    use RefreshDatabase, SeedsAppConfigs;

    protected function setUp(): void
    {
        parent::setUp();
        $this->seedConfigs();

        BusinessSetting::updateOrCreate(
            ['type' => 'classified_product'],
            ['value' => '1']
        );
    }

    public function test_seller_classified_products_page_exposes_working_add_product_link(): void
    {
        $seller = $this->sellerWithShop(['remaining_uploads' => 3]);
        $this->assertEquals(3, $seller->fresh()->remaining_uploads);

        $response = $this->actingAs($seller)->get(route('seller.promoted_products'));

        $response->assertOk()
            ->assertSee('Classified Products')
            ->assertSee(route('customer_products.create'), false);

        $this->actingAs($seller)
            ->get(route('customer_products.create'))
            ->assertOk()
            ->assertSee('Add Your Product');
    }

    public function test_seller_can_store_valid_classified_product(): void
    {
        $seller = $this->sellerWithShop(['remaining_uploads' => 2]);
        $category = Category::factory()->create(['parent_id' => 0, 'digital' => 0]);

        $response = $this->actingAs($seller)->post(route('customer_products.store'), [
            'added_by' => 'seller',
            'name' => 'Seller Classified Chair',
            'category_id' => $category->id,
            'conditon' => 'new',
            'location' => 'Casablanca',
            'unit' => 'pc',
            'unit_price' => 1250,
            'description' => 'A carefully described seller classified product.',
            'tags' => [json_encode([
                ['value' => 'chair'],
                ['value' => 'design'],
            ])],
        ]);

        $response->assertSessionHasNoErrors();
        $response->assertRedirect(route('seller.promoted_products'));

        $this->assertDatabaseHas('customer_products', [
            'user_id' => $seller->id,
            'name' => 'Seller Classified Chair',
            'tags' => 'chair,design',
        ]);
        $this->assertSame(1, $seller->fresh()->remaining_uploads);
    }

    public function test_invalid_classified_product_submission_fails_validation(): void
    {
        $seller = $this->sellerWithShop(['remaining_uploads' => 2]);

        $response = $this->actingAs($seller)
            ->from(route('customer_products.create'))
            ->post(route('customer_products.store'), [
                'name' => '',
                'unit_price' => -10,
            ]);

        $response->assertRedirect(route('customer_products.create'));
        $response->assertSessionHasErrors(['name', 'category_id', 'conditon', 'location', 'unit', 'unit_price', 'description']);
        $this->assertSame(0, CustomerProduct::count());
    }

    public function test_seller_without_upload_credits_cannot_store_classified_product(): void
    {
        $seller = $this->sellerWithShop(['remaining_uploads' => 0]);
        $category = Category::factory()->create(['parent_id' => 0, 'digital' => 0]);

        $response = $this->actingAs($seller)
            ->from(route('customer_products.create'))
            ->post(route('customer_products.store'), [
                'added_by' => 'seller',
                'name' => 'Blocked Classified Product',
                'category_id' => $category->id,
                'conditon' => 'new',
                'location' => 'Casablanca',
                'unit' => 'pc',
                'unit_price' => 1250,
                'description' => 'A valid payload that should still be blocked by upload credits.',
            ]);

        $response->assertRedirect(route('customer_products.create'));
        $this->assertSame(0, CustomerProduct::count());
    }

    private function sellerWithShop(array $attributes = []): User
    {
        $seller = User::factory()->seller()->create();
        if ($attributes !== []) {
            DB::table('users')->where('id', $seller->id)->update($attributes);
            $seller->refresh();
        }
        Shop::factory()->create([
            'user_id' => $seller->id,
            'registration_approval' => 1,
            'approval_status' => 'approved',
        ]);

        return $seller;
    }
}
