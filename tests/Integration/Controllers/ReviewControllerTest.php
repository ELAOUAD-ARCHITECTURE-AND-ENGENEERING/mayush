<?php

namespace Tests\Integration\Controllers;

use Tests\TestCase;
use App\Models\User;
use App\Models\Product;
use App\Models\Review;
use App\Models\Language;
use App\Models\BusinessSetting;
use Illuminate\Foundation\Testing\RefreshDatabase;

class ReviewControllerTest extends TestCase
{
    use RefreshDatabase;

    protected function setUp(): void
    {
        parent::setUp();
        Language::factory()->create(['code' => 'en']);
        BusinessSetting::factory()->create(['type' => 'site_name', 'value' => 'Mayush']);
    }

    /** @test */
    public function guest_cannot_submit_review()
    {
        $product = Product::factory()->create();
        
        $response = $this->post(route('reviews.store'), [
            'product_id' => $product->id,
            'rating' => 5,
            'comment' => 'Great product!'
        ]);

        $response->assertRedirect(route('login'));
    }

    /** @test */
    public function authenticated_customer_can_submit_review()
    {
        $user = User::factory()->create(['user_type' => 'customer']);
        $product = Product::factory()->create();
        
        $response = $this->actingAs($user)->post(route('reviews.store'), [
            'product_id' => $product->id,
            'rating' => 5,
            'comment' => 'Great product!',
            'photos' => []
        ]);

        $response->assertStatus(302); // Redirect back
        $this->assertDatabaseHas('reviews', [
            'product_id' => $product->id,
            'user_id' => $user->id,
            'rating' => 5,
            'comment' => 'Great product!'
        ]);
    }

    /** @test */
    public function admin_can_view_reviews_index()
    {
        $admin = User::factory()->create(['user_type' => 'admin']);
        $admin->givePermissionTo('view_product_reviews'); // Assuming Spatie Permissions

        $response = $this->actingAs($admin)->get(route('reviews.index'));
        $response->assertStatus(200);
    }

    /** @test */
    public function admin_can_publish_review()
    {
        $admin = User::factory()->create(['user_type' => 'admin']);
        $admin->givePermissionTo('publish_product_review');
        
        $review = Review::factory()->create(['status' => 0]);

        $response = $this->actingAs($admin)->post(route('reviews.published'), [
            'id' => $review->id,
            'status' => 1
        ]);

        $response->assertStatus(200);
        $this->assertEquals(1, $review->fresh()->status);
    }

    /** @test */
    public function admin_can_submit_custom_review()
    {
        $admin = User::factory()->create(['user_type' => 'admin']);
        $product = Product::factory()->create();
        
        $response = $this->actingAs($admin)->post(route('reviews.store'), [
            'product_id' => $product->id,
            'rating' => 4,
            'comment' => 'Custom review by admin',
            'custom_reviewer_name' => 'John Doe',
            'custom_reviewer_image' => null,
            'photos' => []
        ]);

        $this->assertDatabaseHas('reviews', [
            'product_id' => $product->id,
            'type' => 'custom',
            'custom_reviewer_name' => 'John Doe',
            'comment' => 'Custom review by admin'
        ]);
    }
}
