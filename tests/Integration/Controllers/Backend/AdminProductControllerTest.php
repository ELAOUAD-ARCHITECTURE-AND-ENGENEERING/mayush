<?php

namespace Tests\Integration\Controllers\Backend;

use Tests\TestCase;
use App\Models\User;
use App\Models\Product;
use App\Models\Category;
use App\Models\Brand;
use App\Models\Language;
use App\Models\BusinessSetting;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Spatie\Permission\Models\Permission;

class AdminProductControllerTest extends TestCase
{
    use RefreshDatabase;

    protected function setUp(): void
    {
        parent::setUp();
        Language::factory()->create(['code' => 'en']);
        BusinessSetting::factory()->create(['type' => 'site_name', 'value' => 'Mayush']);
        // Seed admin user
        $this->admin = User::factory()->create(['user_type' => 'admin']);
        Permission::findOrCreate('add_new_product', 'web');
        Permission::findOrCreate('show_in_house_products', 'web');
        Permission::findOrCreate('product_edit', 'web');
        Permission::findOrCreate('product_delete', 'web');

        $this->admin->givePermissionTo([
            'add_new_product',
            'show_in_house_products',
            'product_edit',
            'product_delete',
        ]);
    }

    /** @test */
    public function admin_can_view_admin_products_list()
    {
        $response = $this->actingAs($this->admin)->get(route('products.admin'));
        $response->assertStatus(200);
        $response->assertViewIs('backend.product.products.index');
    }

    /** @test */
    public function admin_can_view_create_product_page()
    {
        $response = $this->actingAs($this->admin)->get(route('products.create'));
        $response->assertStatus(200);
    }

    /** @test */
    public function admin_can_store_new_product()
    {
        $category = Category::factory()->create();
        $brand = Brand::factory()->create();

        $productData = [
            'name' => 'Admin Test Product',
            'category_ids' => [$category->id],
            'category_id' => $category->id,
            'brand_id' => $brand->id,
            'unit' => 'pcs',
            'min_qty' => 1,
            'tags' => [json_encode([['value' => 'test']])],
            'unit_price' => 100,
            'current_stock' => 10,
            'thumbnail_img' => null,
            'description' => 'Test description'
        ];

        $response = $this->actingAs($this->admin)->post(route('products.store'), $productData);

        $response->assertRedirect(route('products.admin'));
        $this->assertDatabaseHas('products', [
            'name' => 'Admin Test Product',
            'added_by' => 'admin'
        ]);
    }

    /** @test */
    public function admin_can_edit_product()
    {
        $product = Product::factory()->create(['added_by' => 'admin']);
        
        $response = $this->actingAs($this->admin)->get(route('products.admin.edit', $product->id));
        
        $response->assertStatus(200);
        $response->assertViewHas('product', $product);
    }

    /** @test */
    public function admin_can_delete_product()
    {
        $product = Product::factory()->create();
        
        $response = $this->actingAs($this->admin)->get(route('products.destroy', $product->id));
        
        $this->assertDatabaseMissing('products', ['id' => $product->id]);
    }
}
