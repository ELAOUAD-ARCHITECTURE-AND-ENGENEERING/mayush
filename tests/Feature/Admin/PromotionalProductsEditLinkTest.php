<?php

namespace Tests\Feature\Admin;

use App\Models\Product;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Spatie\Permission\Models\Permission;
use Tests\TestCase;

class PromotionalProductsEditLinkTest extends TestCase
{
    use RefreshDatabase;

    private User $admin;

    protected function setUp(): void
    {
        parent::setUp();

        Permission::findOrCreate('view_promotional_product', 'web');
        Permission::findOrCreate('product_edit', 'web');

        $this->admin = User::factory()->create([
            'user_type' => 'admin',
            'email_verified_at' => now(),
        ]);
        $this->admin->givePermissionTo('view_promotional_product');
    }

    public function test_edit_link_is_hidden_without_product_edit_permission(): void
    {
        // Create a promotional product
        $product = Product::factory()->create([
            'promotional' => 1,
            'auction_product' => 0,
            'wholesale_product' => 0,
            'digital' => 0,
            'added_by' => 'admin',
        ]);

        $response = $this->actingAs($this->admin)->get(route('promotional_products.filter'));

        $response->assertOk();
        $html = $response->json('html');

        $this->assertStringNotContainsString(route('products.admin.edit', ['id' => $product->id, 'lang' => env('DEFAULT_LANGUAGE')]), $html);
    }

    public function test_edit_links_are_visible_and_correct_for_different_product_types(): void
    {
        $this->admin->givePermissionTo('product_edit');

        // 1. Digital product
        $digitalProduct = Product::factory()->create([
            'promotional' => 1,
            'auction_product' => 0,
            'wholesale_product' => 0,
            'digital' => 1,
            'added_by' => 'admin',
        ]);

        // 2. Seller physical product
        $sellerProduct = Product::factory()->create([
            'promotional' => 1,
            'auction_product' => 0,
            'wholesale_product' => 0,
            'digital' => 0,
            'added_by' => 'seller',
        ]);

        // 3. Admin (inhouse) physical product
        $adminProduct = Product::factory()->create([
            'promotional' => 1,
            'auction_product' => 0,
            'wholesale_product' => 0,
            'digital' => 0,
            'added_by' => 'admin',
        ]);

        $response = $this->actingAs($this->admin)->get(route('promotional_products.filter'));
        $response->assertOk();
        $html = $response->json('html');

        // Assert correct edit URLs are rendered
        $this->assertStringContainsString(
            route('digitalproducts.edit', ['digitalproduct' => $digitalProduct->id, 'lang' => env('DEFAULT_LANGUAGE')]),
            $html
        );
        $this->assertStringContainsString(
            route('products.seller.edit', ['id' => $sellerProduct->id, 'lang' => env('DEFAULT_LANGUAGE')]),
            $html
        );
        $this->assertStringContainsString(
            route('products.admin.edit', ['id' => $adminProduct->id, 'lang' => env('DEFAULT_LANGUAGE')]),
            $html
        );
    }
}
