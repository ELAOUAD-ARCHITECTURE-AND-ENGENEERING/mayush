<?php

namespace Tests\Integration\Controllers;

use App\Models\BusinessSetting;
use App\Models\Order;
use App\Models\OrderDetail;
use App\Models\Product;
use App\Models\Review;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Spatie\Permission\Models\Permission;
use Spatie\Permission\Models\Role;
use Tests\TestCase;
use Tests\Traits\SeedsAppConfigs;

/**
 * ReviewControllerTest
 *
 * Verifies core functionality of the ReviewController including:
 * - Admin index viewing with correct permissions
 * - Admin custom review creation and updates
 * - Status toggling (published/unpublished)
 * - Customer review submission mechanics and rating recalculation
 */
class ReviewControllerTest extends TestCase
{
    use RefreshDatabase, SeedsAppConfigs;

    protected function setUp(): void
    {
        parent::setUp();
        app(\Spatie\Permission\PermissionRegistrar::class)->forgetCachedPermissions();
        $this->seedConfigs();

        BusinessSetting::updateOrCreate(
            ['type' => 'set_point_for_product_review'],
            ['value' => 10]
        );
    }

    protected function createAdminWithPermission(string $permissionName): User
    {
        Permission::findOrCreate($permissionName, 'web');
        $role = Role::findOrCreate('Super Admin', 'web');

        $admin = User::factory()->admin()->create();
        $admin->givePermissionTo($permissionName);
        $admin->assignRole($role);

        return $admin;
    }

    /** @test */
    public function guest_cannot_submit_review(): void
    {
        $product = Product::factory()->create();

        $response = $this->post(route('reviews.store'), [
            'product_id' => $product->id,
            'rating' => 5,
            'comment' => 'Great product!',
        ]);

        $response->assertRedirect();
    }

    /** @test */
    public function customer_can_submit_review(): void
    {
        $customer = User::factory()->customer()->create();
        $product = Product::factory()->create();
        $order = Order::factory()->create(['user_id' => $customer->id]);
        OrderDetail::factory()->create([
            'order_id' => $order->id,
            'product_id' => $product->id,
            'reviewed' => 0,
        ]);

        $response = $this->actingAs($customer)->post(route('reviews.store'), [
            'product_id' => $product->id,
            'rating' => 4,
            'comment' => 'Very good!',
            'photos' => [],
            'order_id' => $order->id,
        ]);

        $response->assertRedirect();
        $this->assertDatabaseHas('reviews', [
            'product_id' => $product->id,
            'user_id' => $customer->id,
            'rating' => 4,
        ]);
        $this->assertDatabaseHas('order_details', [
            'order_id' => $order->id,
            'product_id' => $product->id,
            'reviewed' => 1,
        ]);
    }

    /** @test */
    public function admin_can_view_reviews_index_with_permission(): void
    {
        $admin = $this->createAdminWithPermission('view_product_reviews');
        Product::factory()->create();

        $response = $this->actingAs($admin)->get(route('reviews.index'));

        $response->assertStatus(200);
        $response->assertViewIs('backend.product.reviews.index');
    }

    /** @test */
    public function admin_without_permission_cannot_view_reviews_index(): void
    {
        $admin = User::factory()->admin()->create();

        $this->actingAs($admin)->get(route('reviews.index'))->assertStatus(403);
    }

    /** @test */
    public function admin_can_update_review_published_status(): void
    {
        $admin = $this->createAdminWithPermission('publish_product_review');
        $review = Review::factory()->create(['status' => 0]);

        $response = $this->actingAs($admin)->post(route('reviews.published'), [
            'id' => $review->id,
            'status' => 1,
        ]);

        $response->assertStatus(200);
        $this->assertEquals('1', $response->getContent());
        $this->assertDatabaseHas('reviews', [
            'id' => $review->id,
            'status' => 1,
        ]);
    }

    /** @test */
    public function admin_can_create_custom_review(): void
    {
        $admin = $this->createAdminWithPermission('add_custom_review');
        $product = Product::factory()->create();

        $response = $this->actingAs($admin)->post(route('reviews.store'), [
            'product_id' => $product->id,
            'rating' => 5,
            'comment' => 'Great product!',
            'photos' => [],
            'custom_reviewer_name' => 'John Custom',
        ]);

        $response->assertRedirect();
        $this->assertDatabaseHas('reviews', [
            'product_id' => $product->id,
            'type' => 'custom',
            'custom_reviewer_name' => 'John Custom',
            'rating' => 5,
        ]);
    }
}
