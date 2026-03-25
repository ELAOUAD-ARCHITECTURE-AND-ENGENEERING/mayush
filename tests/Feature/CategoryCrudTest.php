<?php

namespace Tests\Feature;

use App\Models\Category;
use Illuminate\Support\Str;

class CategoryCrudTest extends CrudTestCase
{
    /** @test */
    public function admin_can_create_category()
    {
        $data = [
            'name' => 'Test Category',
            'digital' => 0,
            'order_level' => 1,
        ];

        $response = $this->profileBlock(function () use ($data) {
            return $this->actingAs($this->adminUser)
                ->post(route('categories.store'), $data);
        });

        if ($response['result']->getStatusCode() == 302) {
            fwrite(STDERR, "Redirect Location: " . $response['result']->headers->get('Location') . "\n");
        }
        $response['result']->assertStatus(200);
        $response['result']->assertJson(['success' => true]);
        $this->assertDatabaseHas('categories', ['name' => 'Test Category']);
        
        $this->logPerformance('Create Category', $response['metrics']);
    }

    /** @test */
    public function admin_can_read_category_list()
    {
        Category::create(['name' => 'Cat 1', 'slug' => 'cat-1', 'digital' => 0]);
        Category::create(['name' => 'Cat 2', 'slug' => 'cat-2', 'digital' => 0]);

        $response = $this->profileBlock(function () {
            return $this->actingAs($this->adminUser)
                ->get(route('categories.filter', ['category_type' => 'all_categories']));
        });

        $response['result']->assertStatus(200);
        $response['result']->assertJsonPath('html', fn ($html) => str_contains($html, 'Cat 1'));
        $response['result']->assertJsonPath('html', fn ($html) => str_contains($html, 'Cat 2'));

        $this->logPerformance('Read Category List', $response['metrics']);
    }

    /** @test */
    public function admin_can_update_category()
    {
        $category = Category::create(['name' => 'Old Name', 'slug' => 'old-name', 'digital' => 0]);

        $data = [
            'name' => 'New Name',
            'digital' => 1,
            'lang' => 'en',
        ];

        $response = $this->profileBlock(function () use ($category, $data) {
            return $this->actingAs($this->adminUser)
                ->put(route('categories.update', $category->id), $data);
        });

        $response['result']->assertStatus(200);
        $response['result']->assertJson(['success' => true]);
        $this->assertDatabaseHas('categories', [
            'id' => $category->id,
            'name' => 'New Name',
            'digital' => 1
        ]);

        $this->logPerformance('Update Category', $response['metrics']);
    }

    /** @test */
    public function admin_can_delete_category()
    {
        $category = Category::create(['name' => 'To Delete', 'slug' => 'to-delete', 'digital' => 0]);

        $response = $this->profileBlock(function () use ($category) {
            return $this->actingAs($this->adminUser)
                ->get(route('categories.destroy', $category->id));
        });

        $response['result']->assertStatus(200);
        $response['result']->assertSee('1');
        $this->assertDatabaseMissing('categories', ['id' => $category->id]);

        $this->logPerformance('Delete Category', $response['metrics']);
    }

    /** @test */
    public function guest_cannot_access_category_crud()
    {
        $this->get(route('categories.index'))->assertRedirect(route('login'));
        $this->post(route('categories.store'), [])->assertRedirect(route('login'));
    }

    /** @test */
    public function invalid_category_creation_fails()
    {
        $response = $this->actingAs($this->adminUser)
            ->from(route('categories.create'))
            ->post(route('categories.store'), [
                'name' => '', // Required
                'digital' => 3, // Must be 0 or 1
            ]);

        $response->assertSessionHasErrors(['name', 'digital']);
    }
}
