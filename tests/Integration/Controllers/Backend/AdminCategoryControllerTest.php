<?php

namespace Tests\Integration\Controllers\Backend;

use Tests\TestCase;
use App\Models\User;
use App\Models\Category;
use App\Models\Language;
use App\Models\BusinessSetting;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Spatie\Permission\Models\Permission;

class AdminCategoryControllerTest extends TestCase
{
    use RefreshDatabase;

    protected function setUp(): void
    {
        parent::setUp();
        Language::factory()->create(['code' => 'en']);
        BusinessSetting::factory()->create(['type' => 'site_name', 'value' => 'Mayush']);
        
        $this->admin = User::factory()->create(['user_type' => 'admin']);
        
        // Setup permissions
        Permission::findOrCreate('view_product_categories', 'web');
        Permission::findOrCreate('add_product_category', 'web');
        Permission::findOrCreate('edit_product_category', 'web');
        Permission::findOrCreate('delete_product_category', 'web');
        
        $this->admin->givePermissionTo([
            'view_product_categories',
            'add_product_category',
            'edit_product_category',
            'delete_product_category'
        ]);
    }

    /** @test */
    public function admin_can_view_categories_index()
    {
        $response = $this->actingAs($this->admin)->get(route('categories.index'));
        $response->assertStatus(200);
        $response->assertViewIs('backend.product.categories.index');
    }

    /** @test */
    public function admin_can_view_create_category_page()
    {
        $response = $this->actingAs($this->admin)->get(route('categories.create'));
        $response->assertStatus(200);
    }

    /** @test */
    public function admin_can_store_new_category()
    {
        $categoryData = [
            'name' => 'Test Category',
            'digital' => 0,
            'slug' => 'test-category',
            'parent_id' => 0,
            'order_level' => 1
        ];

        $response = $this->actingAs($this->admin)->post(route('categories.store'), $categoryData);

        $response->assertJson(['success' => true]);
        $this->assertDatabaseHas('categories', [
            'name' => 'Test Category',
            'slug' => 'test-category'
        ]);
    }

    /** @test */
    public function admin_can_edit_category()
    {
        $category = Category::factory()->create();
        
        $response = $this->actingAs($this->admin)->get(route('categories.edit', $category->id));
        
        $response->assertStatus(200);
        $response->assertViewHas('category', $category);
    }

    /** @test */
    public function admin_can_update_category()
    {
        $category = Category::factory()->create(['name' => 'Old Name']);
        
        $updatedData = [
            'name' => 'New Name',
            'digital' => 0,
            'slug' => 'new-name',
            'parent_id' => 0,
            'lang' => 'en'
        ];

        $response = $this->actingAs($this->admin)->put(route('categories.update', $category->id), $updatedData);

        $response->assertJson(['success' => true]);
        $this->assertDatabaseHas('categories', [
            'id' => $category->id,
            'name' => 'New Name'
        ]);
    }

    /** @test */
    public function admin_can_delete_category()
    {
        $category = Category::factory()->create();
        
        $response = $this->actingAs($this->admin)->delete(route('categories.destroy', $category->id));
        
        $response->assertStatus(200);
        $this->assertDatabaseMissing('categories', ['id' => $category->id]);
    }

    /** @test */
    public function non_authorized_user_cannot_access_categories()
    {
        $user = User::factory()->create(['user_type' => 'customer']);
        
        $response = $this->actingAs($user)->get(route('categories.index'));
        $this->assertContains($response->getStatusCode(), [403, 404]);
    }
}
