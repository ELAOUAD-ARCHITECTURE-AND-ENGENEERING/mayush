<?php

namespace Tests\Integration\Controllers;

use Tests\TestCase;
use App\Models\User;
use App\Models\Product;
use App\Models\Review;
use App\Models\Order;
use App\Models\OrderDetail;
use App\Models\Language;
use App\Models\BusinessSetting;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Spatie\Permission\Models\Role;
use Spatie\Permission\Models\Permission;

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
    use RefreshDatabase;

    protected function setUp(): void
    {
        parent::setUp();
        app()->make(\Spatie\Permission\PermissionRegistrar::class)->forgetCachedPermissions();
        
        Language::updateOrCreate(
            ['code' => 'en'],
            ['name' => 'English', 'app_lang_code' => 'en', 'rtl' => 0]
        );

        $settings = [
            'site_name' => 'MayushTest',
            'language' => 'en',
            'set_point_for_product_review' => 10,
        ];
        foreach ($settings as $key => $value) {
            BusinessSetting::updateOrCreate(['type' => $key], ['value' => $value]);
        }
    }

    /**
     * Helper to create an admin with specific Spatie permissions.
     */
    protected function createAdminWithPermission(string $permissionName): User
    {
        Permission::findOrCreate($permissionName, 'web');
        $role = Role::findOrCreate('Super Admin', 'web');
        $admin = User::factory()->admin()->create();
        $admin->givePermissionTo($permissionName);
        $admin->assignRole($role);
        return $admin;
    }

    // ─── Admin Endpoints ─────────────────────────────────────────────────────

    /** @test */
    public function admin_can_view_reviews_index_with_permission()
    {
        $admin = $this->createAdminWithPermission('view_product_reviews');
        Product::factory()->create(); // Ensure at least one product exists

        $response = $this->actingAs($admin)->get(route('reviews.index'));

        $response->assertStatus(200);
        $response->assertViewIs('backend.product.reviews.index');
    }

    /** @test */
    public function admin_cannot_view_reviews_index_without_permission()
    {
        $admin = User::factory()->admin()->create(); // No Spatie permissions assigned

        $response = $this->actingAs($admin)->get(route('reviews.index'));

        // Spatie middleware blocks it (usually 403)
        $response->assertStatus(403);
    }

    /** @test */
    public function admin_can_update_review_published_status()
    {
        $admin = $this->createAdminWithPermission('publish_product_review');
        $review = Review::factory()->create(['status' => 0]);

        $response = $this->actingAs($admin)->post(route('reviews.published'), [
            'id' => $review->id,
            'status' => 1
        ]);

        $response->assertStatus(200);
        $this->assertEquals(1, $response->getContent());
        $this->assertDatabaseHas('reviews', [
            'id' => $review->id,
            'status' => 1
        ]);
    }

    /** @test */
    public function admin_can_create_custom_review()
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

    // ─── Customer Endpoints ──────────────────────────────────────────────────

    /** @test */
    public function customer_can_submit_review()
    {
        $customer = User::factory()->customer()->create();
        $product = Product::factory()->create();
        
        // Mock an order so the controller marks the order detail as reviewed
        $order = Order::factory()->create(['user_id' => $customer->id]);
        OrderDetail::factory()->create([
            'order_id' => $order->id,
            'product_id' => $product->id,
            'reviewed' => 0
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
        
        // Ensure order detail is marked as reviewed
        $this->assertDatabaseHas('order_details', [
            'order_id' => $order->id,
            'product_id' => $product->id,
            'reviewed' => 1
        ]);
    }
}
