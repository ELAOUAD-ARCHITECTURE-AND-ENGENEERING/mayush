<?php

namespace Tests\Feature\Admin;

use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Spatie\Permission\Models\Permission;
use Tests\TestCase;

class ProductCollectionAccessTest extends TestCase
{
    use RefreshDatabase;

    private User $admin;
    private User $unauthorizedAdmin;

    protected function setUp(): void
    {
        parent::setUp();

        // Create permissions
        Permission::findOrCreate('view_product_collections', 'web');
        Permission::findOrCreate('add_product_collection', 'web');
        Permission::findOrCreate('edit_product_collection', 'web');
        Permission::findOrCreate('delete_product_collection', 'web');

        // Create authorized admin
        $this->admin = User::factory()->create([
            'user_type' => 'admin',
            'email_verified_at' => now()
        ]);
        $this->admin->givePermissionTo('view_product_collections');

        // Create unauthorized admin
        $this->unauthorizedAdmin = User::factory()->create([
            'user_type' => 'admin',
            'email_verified_at' => now()
        ]);
    }

    public function test_guest_cannot_access_product_collections_index(): void
    {
        $response = $this->get(route('product-collections.index'));
        $response->assertRedirect(route('login'));
    }

    public function test_unauthorized_staff_cannot_access_product_collections_index(): void
    {
        $response = $this->actingAs($this->unauthorizedAdmin)->get(route('product-collections.index'));
        $response->assertStatus(403);
    }

    public function test_authorized_staff_can_access_product_collections_index(): void
    {
        $response = $this->actingAs($this->admin)->get(route('product-collections.index'));
        $response->assertOk();
        $response->assertViewIs('backend.product_collections.index');
    }

    public function test_unauthorized_staff_cannot_access_create_collection(): void
    {
        $response = $this->actingAs($this->unauthorizedAdmin)->get(route('product-collections.create'));
        $response->assertStatus(403);
    }

    public function test_authorized_staff_can_access_create_collection(): void
    {
        $this->admin->givePermissionTo('add_product_collection');
        $response = $this->actingAs($this->admin)->get(route('product-collections.create'));
        $response->assertOk();
    }

    public function test_sidebar_includes_product_collections_link_only_for_authorized_staff(): void
    {
        // For unauthorized staff, the link should not be present
        $this->actingAs($this->unauthorizedAdmin);
        $html1 = view('backend.inc.admin_sidenav')->render();
        $this->assertStringNotContainsString(route('product-collections.index'), $html1);

        // For authorized staff, the link should be present
        $this->actingAs($this->admin);
        $html2 = view('backend.inc.admin_sidenav')->render();
        $this->assertStringContainsString(route('product-collections.index'), $html2);
    }
}
