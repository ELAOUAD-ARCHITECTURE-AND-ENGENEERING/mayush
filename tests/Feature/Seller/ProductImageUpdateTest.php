<?php

namespace Tests\Feature\Seller;

use App\Models\Product;
use App\Models\User;
use App\Services\ProductService;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class ProductImageUpdateTest extends TestCase
{
    use RefreshDatabase;

    public function test_product_create_persists_gallery_thumbnail_and_meta_image(): void
    {
        $admin = User::factory()->admin()->create();
        $this->actingAs($admin);

        $product = app(ProductService::class)->store([
            'name' => 'Image Product',
            'description' => 'Image product description',
            'tags' => ['decor'],
            'category_id' => 1,
            'brand_id' => null,
            'unit' => 'pcs',
            'unit_price' => 100,
            'purchase_price' => 50,
            'current_stock' => 5,
            'min_qty' => 1,
            'digital' => 0,
            'auction_product' => 0,
            'wholesale_product' => 0,
            'shipping_type' => 'flat_rate',
            'flat_shipping_cost' => 0,
            'photos' => '11,12',
            'thumbnail_img' => 13,
            'meta_img' => null,
            'button' => 'publish',
        ]);

        $this->assertSame('11,12', $product->photos);
        $this->assertSame(13, (int) $product->thumbnail_img);
        $this->assertSame(13, (int) $product->meta_img);
    }

    public function test_product_update_preserves_existing_images_when_not_changed(): void
    {
        $product = Product::factory()->create([
            'photos' => '21,22',
            'thumbnail_img' => 23,
            'meta_img' => 23,
        ]);

        (new ProductService())->update([
            'name' => $product->name,
            'slug' => $product->slug,
            'description' => 'Updated description',
            'tags' => ['updated'],
            'unit' => 'pcs',
            'unit_price' => 120,
            'purchase_price' => 60,
            'current_stock' => 5,
            'min_qty' => 1,
            'digital' => 0,
            'auction_product' => 0,
            'wholesale_product' => 0,
            'shipping_type' => 'flat_rate',
            'flat_shipping_cost' => 0,
            'meta_img' => $product->meta_img,
        ], $product);

        $product->refresh();

        $this->assertSame('21,22', $product->photos);
        $this->assertSame(23, (int) $product->thumbnail_img);
        $this->assertSame(23, (int) $product->meta_img);
    }

    public function test_product_update_replaces_images_when_new_ids_are_submitted(): void
    {
        $product = Product::factory()->create([
            'photos' => '31,32',
            'thumbnail_img' => 33,
            'meta_img' => 33,
        ]);

        (new ProductService())->update([
            'name' => $product->name,
            'slug' => $product->slug,
            'description' => $product->description,
            'tags' => ['updated'],
            'unit' => 'pcs',
            'unit_price' => 120,
            'purchase_price' => 60,
            'current_stock' => 5,
            'min_qty' => 1,
            'digital' => 0,
            'auction_product' => 0,
            'wholesale_product' => 0,
            'shipping_type' => 'flat_rate',
            'flat_shipping_cost' => 0,
            'photos' => '41,42',
            'thumbnail_img' => 43,
            'meta_img' => null,
        ], $product);

        $product->refresh();

        $this->assertSame('41,42', $product->photos);
        $this->assertSame(43, (int) $product->thumbnail_img);
        $this->assertSame(43, (int) $product->meta_img);
    }
}
