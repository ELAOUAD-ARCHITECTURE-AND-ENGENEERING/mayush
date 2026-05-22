<?php

namespace Tests\Integration\Controllers\Seller;

use Tests\TestCase;
use App\Models\User;
use App\Models\Product;
use App\Models\Category;
use App\Models\Shop;
use App\Models\Attribute;
use App\Models\ProductStock;
use App\Models\ProductTranslation;
use App\Models\Language;
use App\Models\BusinessSetting;
use Illuminate\Foundation\Testing\RefreshDatabase;

class SellerProductControllerTest extends TestCase
{
    use RefreshDatabase;

    protected function setUp(): void
    {
        parent::setUp();
        Language::factory()->create(['code' => 'en']);
        BusinessSetting::factory()->create(['type' => 'site_name', 'value' => 'Mayush']);
        
        $this->admin = User::factory()->create(['user_type' => 'admin']);
        $this->seller = User::factory()->create(['user_type' => 'seller']);
        $this->shop = Shop::factory()->create([
            'user_id' => $this->seller->id,
            'approval_status' => 'approved'
        ]);
    }

    /** @test */
    public function seller_can_view_their_products_list()
    {
        $response = $this->actingAs($this->seller)->get(route('seller.products'));
        $response->assertStatus(200);
        $response->assertViewIs('seller.product.products.index');
    }

    /** @test */
    public function seller_can_view_create_product_page()
    {
        $this->withoutExceptionHandling();
        $response = $this->actingAs($this->seller)->get(route('seller.products.create'));
        $response->assertStatus(200);
    }

    /** @test */
    public function seller_can_store_product()
    {
        $category = Category::factory()->create();
        
        $productData = [
            'name' => 'Seller Test Product',
            'category_ids' => [$category->id],
            'category_id' => $category->id,
            'unit' => 'pcs',
            'min_qty' => 1,
            'tags' => [json_encode([['value' => 'seller-test']])],
            'unit_price' => 50,
            'current_stock' => 20,
            'description' => 'Seller test description',
            'button' => 'publish',
            'date_range' => now()->format('Y-m-d') . ' to ' . now()->addDays(7)->format('Y-m-d'),
        ];

        $this->withoutExceptionHandling();
        $response = $this->actingAs($this->seller)->post(route('seller.products.store'), $productData);

        $response->assertRedirect(route('seller.products'));
        $this->assertDatabaseHas('products', [
            'name' => 'Seller Test Product',
            'user_id' => $this->seller->id,
            'added_by' => 'seller'
        ]);
    }

    /** @test */
    public function seller_can_persist_a_dimension_variant_added_from_the_sku_table()
    {
        $category = Category::factory()->create();
        $dimension = $this->dimensionAttribute();

        $response = $this->actingAs($this->seller)->post(route('seller.products.store'), [
            'name' => 'Table Dimension Product',
            'category_ids' => [$category->id],
            'category_id' => $category->id,
            'lang' => 'en',
            'unit' => 'pcs',
            'min_qty' => 1,
            'unit_price' => 50,
            'description' => 'SKU table variant row',
            'current_stock' => 0,
            'choice_no' => [$dimension->id],
            'choice_options_' . $dimension->id => ['1-100cm', '+1000cm'],
            'price_1-100cm' => 50,
            'sku_1-100cm' => 'DIM-BASE',
            'qty_1-100cm' => 3,
            'img_1-100cm' => null,
            'price_+1000cm' => 75,
            'sku_+1000cm' => 'DIM-TABLE',
            'qty_+1000cm' => 4,
            'img_+1000cm' => null,
            'button' => 'publish',
        ]);

        $response->assertRedirect(route('seller.products'));

        $product = Product::where('name', 'Table Dimension Product')->firstOrFail();
        $this->assertStringContainsString('+1000cm', $product->choice_options);
        $this->assertDatabaseHas('product_stocks', [
            'product_id' => $product->id,
            'variant' => '+1000cm',
            'sku' => 'DIM-TABLE',
        ]);
    }

    /** @test */
    public function seller_cannot_save_duplicate_dimension_sku_table_variants()
    {
        $category = Category::factory()->create();
        $dimension = $this->dimensionAttribute();

        $response = $this->from(route('seller.products.create'))
            ->actingAs($this->seller)
            ->post(route('seller.products.store'), $this->dimensionProductData($category, $dimension, [
                '1-100cm',
                '1 - 100 CM',
            ]));

        $response->assertRedirect(route('seller.products.create'));
        $response->assertSessionHasErrors('choice_options_' . $dimension->id);
        $this->assertDatabaseMissing('products', ['name' => 'Invalid Dimension Table Product']);
    }

    /** @test */
    public function seller_can_save_duplicate_variant_names_with_different_prices_or_dimensions_when_spaces_are_present()
    {
        $category = Category::factory()->create();
        $dimension = $this->dimensionAttribute();

        $response = $this->actingAs($this->seller)->post(route('seller.products.store'), [
            'name' => 'Space Dimension Product',
            'category_ids' => [$category->id],
            'category_id' => $category->id,
            'lang' => 'en',
            'unit' => 'pcs',
            'min_qty' => 1,
            'unit_price' => 50,
            'description' => 'Duplicate space variant name with distinct price/dimensions',
            'current_stock' => 0,
            'choice_no' => [$dimension->id],
            'choice_options_' . $dimension->id => ['1 - 100 cm', '1 - 100 cm'],
            'price_1-100cm' => [50, 75],
            'sku_1-100cm' => ['DIM-SPACE-1', 'DIM-SPACE-2'],
            'qty_1-100cm' => [3, 4],
            'img_1-100cm' => [null, null],
            'length_1-100cm' => [10, 20],
            'width_1-100cm' => [15, 25],
            'height_1-100cm' => [5, 8],
            'unit_1-100cm' => ['cm', 'cm'],
            'button' => 'publish',
        ]);

        $response->assertRedirect(route('seller.products'));

        $product = Product::where('name', 'Space Dimension Product')->firstOrFail();
        $this->assertDatabaseHas('product_stocks', [
            'product_id' => $product->id,
            'variant' => '1-100cm',
            'price' => 50,
            'length' => 10,
        ]);
        $this->assertDatabaseHas('product_stocks', [
            'product_id' => $product->id,
            'variant' => '1-100cm',
            'price' => 75,
            'length' => 20,
        ]);
    }

    /** @test */
    public function seller_cannot_save_an_invalid_dimension_sku_table_variant()
    {
        $category = Category::factory()->create();
        $dimension = $this->dimensionAttribute();

        $response = $this->from(route('seller.products.create'))
            ->actingAs($this->seller)
            ->post(route('seller.products.store'), $this->dimensionProductData($category, $dimension, [
                'large cabinet',
            ]));

        $response->assertRedirect(route('seller.products.create'));
        $response->assertSessionHasErrors('choice_options_' . $dimension->id);
        $this->assertDatabaseMissing('products', ['name' => 'Invalid Dimension Table Product']);
    }

    /** @test */
    public function seller_dimension_update_without_lang_uses_the_default_translation_language()
    {
        $category = Category::factory()->create();
        $dimension = $this->dimensionAttribute();
        $product = Product::factory()->create([
            'name' => 'Default Language Dimension Product',
            'user_id' => $this->seller->id,
            'category_id' => $category->id,
            'colors' => json_encode([]),
            'attributes' => json_encode([$dimension->id]),
            'choice_options' => json_encode([[
                'attribute_id' => $dimension->id,
                'values' => ['1-100cm'],
            ]]),
        ]);

        ProductStock::create(['product_id' => $product->id, 'variant' => '1-100cm', 'price' => 50, 'qty' => 2]);

        $this->actingAs($this->seller)
            ->get(route('seller.products.edit', $product))
            ->assertOk()
            ->assertSee('name="lang" value="en"', false);

        $response = $this->actingAs($this->seller)->post(route('seller.products.update', $product), [
            'name' => 'Default Language Dimension Product Updated',
            'category_ids' => [$category->id],
            'category_id' => $category->id,
            'unit' => 'pcs',
            'min_qty' => 1,
            'unit_price' => 50,
            'current_stock' => 0,
            'description' => 'Dimension update without a language query value.',
            'meta_img' => null,
            'thumbnail_img' => null,
            'choice_no' => [$dimension->id],
            'choice_options_' . $dimension->id => ['1-100cm'],
            'price_1-100cm' => [50],
            'sku_1-100cm' => ['DIM-DEFAULT-LANG'],
            'qty_1-100cm' => [2],
            'img_1-100cm' => [null],
        ]);

        $response->assertRedirect();
        $this->assertSame(1, ProductTranslation::where('product_id', $product->id)->where('lang', 'en')->count());
        $this->assertDatabaseHas('product_translations', [
            'product_id' => $product->id,
            'lang' => 'en',
            'name' => 'Default Language Dimension Product Updated',
        ]);
    }

    /** @test */
    public function seller_can_remove_a_dimension_sku_table_variant_on_update()
    {
        $category = Category::factory()->create();
        $dimension = $this->dimensionAttribute();
        $product = Product::factory()->create([
            'name' => 'Editable Table Dimension Product',
            'user_id' => $this->seller->id,
            'category_id' => $category->id,
            'attributes' => json_encode([$dimension->id]),
            'choice_options' => json_encode([[
                'attribute_id' => $dimension->id,
                'values' => ['1-100cm', '+1000cm'],
            ]]),
        ]);

        ProductStock::create(['product_id' => $product->id, 'variant' => '1-100cm', 'price' => 50, 'qty' => 2]);
        ProductStock::create(['product_id' => $product->id, 'variant' => '+1000cm', 'price' => 75, 'qty' => 3]);

        $response = $this->actingAs($this->seller)->post(route('seller.products.update', $product), [
            'name' => 'Editable Table Dimension Product',
            'category_ids' => [$category->id],
            'category_id' => $category->id,
            'lang' => 'en',
            'unit' => 'pcs',
            'min_qty' => 1,
            'unit_price' => 50,
            'current_stock' => 0,
            'description' => 'Remove SKU table variant',
            'meta_img' => null,
            'thumbnail_img' => null,
            'choice_no' => [$dimension->id],
            'choice_options_' . $dimension->id => ['1-100cm', '+1000cm'],
            'removed_sku_variants' => ['+1000cm'],
            'price_1-100cm' => 50,
            'sku_1-100cm' => 'DIM-BASE',
            'qty_1-100cm' => 2,
            'img_1-100cm' => null,
            'price_+1000cm' => 75,
            'sku_+1000cm' => 'DIM-REMOVE',
            'qty_+1000cm' => 3,
            'img_+1000cm' => null,
        ]);

        $response->assertRedirect();
        $this->assertDatabaseMissing('product_stocks', ['product_id' => $product->id, 'variant' => '+1000cm']);
        $this->assertDatabaseHas('product_stocks', ['product_id' => $product->id, 'variant' => '1-100cm']);
        $this->assertStringNotContainsString('+1000cm', $product->fresh()->choice_options);
    }

    /** @test */
    public function seller_can_persist_custom_dimension_rows_with_updated_prices_on_update()
    {
        $category = Category::factory()->create();
        $dimension = $this->dimensionAttribute();
        $product = Product::factory()->create([
            'name' => 'Custom Row Dimension Product',
            'user_id' => $this->seller->id,
            'category_id' => $category->id,
            'attributes' => json_encode([$dimension->id]),
            'choice_options' => json_encode([[
                'attribute_id' => $dimension->id,
                'values' => ['1-100cm', '+1000cm'],
            ]]),
        ]);

        ProductStock::create(['product_id' => $product->id, 'variant' => '1-100cm', 'price' => 50, 'qty' => 2]);
        ProductStock::create(['product_id' => $product->id, 'variant' => '+1000cm', 'price' => 75, 'qty' => 3]);

        // Simulate form submission with custom row: duplicate +1000cm with different price
        $response = $this->actingAs($this->seller)->post(route('seller.products.update', $product), [
            'name' => 'Custom Row Dimension Product',
            'category_ids' => [$category->id],
            'category_id' => $category->id,
            'lang' => 'en',
            'unit' => 'pcs',
            'min_qty' => 1,
            'unit_price' => 50,
            'current_stock' => 0,
            'description' => 'Updated with custom row',
            'meta_img' => null,
            'thumbnail_img' => null,
            'choice_no' => [$dimension->id],
            'choice_options_' . $dimension->id => ['1-100cm', '+1000cm', '+1000cm'],
            'price_1-100cm' => [50],
            'sku_1-100cm' => ['DIM-BASE'],
            'qty_1-100cm' => [2],
            'img_1-100cm' => [null],
            'price_+1000cm' => [75, 95],
            'sku_+1000cm' => ['DIM-OLD', 'DIM-CUSTOM'],
            'qty_+1000cm' => [3, 5],
            'img_+1000cm' => [null, null],
            'length_+1000cm' => [0, 100],
            'width_+1000cm' => [0, 50],
            'height_+1000cm' => [0, 30],
            'unit_+1000cm' => ['cm', 'cm'],
        ]);

        $response->assertRedirect();

        $product->refresh();
        $stocks = $product->stocks()->orderBy('id')->get();

        $this->assertCount(3, $stocks);
        $this->assertDatabaseHas('product_stocks', [
            'product_id' => $product->id,
            'variant' => '1-100cm',
            'price' => 50,
        ]);
        $this->assertDatabaseHas('product_stocks', [
            'product_id' => $product->id,
            'variant' => '+1000cm',
            'price' => 75,
        ]);
        $this->assertDatabaseHas('product_stocks', [
            'product_id' => $product->id,
            'variant' => '+1000cm',
            'price' => 95,
            'length' => 100,
            'width' => 50,
            'height' => 30,
        ]);

        $this->assertStringContainsString('+1000cm', $product->choice_options);
        $this->assertStringContainsString('+1000cm', $product->choice_options, 'Custom row variant should persist in choice_options');
    }

    /** @test */
    public function sku_combination_edit_renders_all_stock_entries_for_duplicate_variants()
    {
        $category = Category::factory()->create();
        $dimension = $this->dimensionAttribute();
        $product = Product::factory()->create([
            'name' => 'Multi-Row Dimension Product',
            'user_id' => $this->seller->id,
            'category_id' => $category->id,
            'attributes' => json_encode([$dimension->id]),
            'choice_options' => json_encode([[
                'attribute_id' => $dimension->id,
                'values' => ['1-100cm', '+1000cm', '+1000cm'],
            ]]),
        ]);

        ProductStock::create(['product_id' => $product->id, 'variant' => '1-100cm', 'price' => 50, 'qty' => 2, 'sku' => 'SKU-1']);
        ProductStock::create(['product_id' => $product->id, 'variant' => '+1000cm', 'price' => 75, 'qty' => 3, 'sku' => 'SKU-2']);
        ProductStock::create(['product_id' => $product->id, 'variant' => '+1000cm', 'price' => 95, 'qty' => 5, 'sku' => 'SKU-3']);

        $response = $this->actingAs($this->seller)->post(route('seller.products.sku_combination_edit'), [
            'id' => $product->id,
            'colors_active' => 0,
            'choice_no' => [$dimension->id],
            // The edit-page multi-select cannot serialize the saved duplicate option twice.
            'choice_options_' . $dimension->id => ['1-100cm', '+1000cm'],
            'unit_price' => 50,
            'name' => 'Multi-Row Dimension Product',
        ]);

        $response->assertOk();
        $html = $response->content();

        $this->assertStringContainsString('SKU-1', $html);
        $this->assertStringContainsString('SKU-2', $html);
        $this->assertStringContainsString('SKU-3', $html, 'Custom row SKU should be rendered in edit view');
    }

    /** @test */
    public function seller_cannot_edit_another_sellers_product()
    {
        $otherSeller = User::factory()->create(['user_type' => 'seller']);
        $product = Product::factory()->create(['user_id' => $otherSeller->id]);

        $response = $this->actingAs($this->seller)->get(route('seller.products.edit', $product->id));

        $response->assertStatus(403);
    }

    /** @test */
    public function seller_can_delete_their_own_product()
    {
        $this->withoutExceptionHandling();
        $product = Product::factory()->create(['user_id' => $this->seller->id]);

        $response = $this->actingAs($this->seller)->delete(route('seller.products.destroy', $product->id));

        $this->assertDatabaseMissing('products', ['id' => $product->id]);
    }

    private function dimensionAttribute(): Attribute
    {
        $attribute = new Attribute();
        $attribute->name = 'Dimension';
        $attribute->save();

        return $attribute;
    }

    private function dimensionProductData(Category $category, Attribute $dimension, array $values): array
    {
        return [
            'name' => 'Invalid Dimension Table Product',
            'category_ids' => [$category->id],
            'category_id' => $category->id,
            'unit' => 'pcs',
            'min_qty' => 1,
            'unit_price' => 50,
            'current_stock' => 0,
            'description' => 'Duplicate Dimension SKU table rows should not save.',
            'choice_no' => [$dimension->id],
            'choice_options_' . $dimension->id => $values,
            'button' => 'publish',
        ];
    }

}
