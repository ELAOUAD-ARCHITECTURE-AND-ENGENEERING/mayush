<?php

namespace Tests\Feature\V109;

use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;
use Spatie\Permission\Models\Role;
use Tests\TestCase;
use Tests\Traits\SeedsAppConfigs;

class V109SchemaMigrationTest extends TestCase
{
    use RefreshDatabase, SeedsAppConfigs;

    protected function setUp(): void
    {
        parent::setUp();
        $this->seedConfigs();
    }

    public function test_v109_schema_and_settings_are_available(): void
    {
        $this->assertTrue(Schema::hasColumn('products', 'promotional'));
        $this->assertTrue(Schema::hasColumn('orders', 'invoice_number'));
        $this->assertTrue(Schema::hasColumn('orders', 'order_note'));
        $this->assertTrue(Schema::hasColumn('wallets', 'added_by'));
        $this->assertTrue(Schema::hasColumn('permissions', 'section'));
        $this->assertTrue(Schema::hasTable('payment_informations'));
        $this->assertTrue(Schema::hasTable('ai_prompts'));
        $this->assertTrue(Schema::hasTable('ai_usage_logs'));

        foreach (['invoice_config', 'shipping_label', 'thermal_printer', 'ai_activation', 'openrouter_model'] as $type) {
            $this->assertDatabaseHas('business_settings', ['type' => $type]);
        }

        $this->assertDatabaseHas('permissions', [
            'name' => 'view_promotion_and_offers_dashboard',
            'section' => 'promotion_and_offers',
        ]);
    }

    public function test_v109_permissions_are_assigned_to_existing_admin_roles(): void
    {
        DB::table('roles')->updateOrInsert(
            ['name' => 'Admin', 'guard_name' => 'web'],
            ['updated_at' => now(), 'created_at' => now()]
        );

        DB::table('roles')->updateOrInsert(
            ['name' => 'Super Admin', 'guard_name' => 'web'],
            ['updated_at' => now(), 'created_at' => now()]
        );

        $migration = include database_path('migrations/2026_05_19_000001_assign_v109_permissions_to_admin_roles.php');
        $migration->up();

        $permissionId = DB::table('permissions')
            ->where('name', 'view_promotion_and_offers_dashboard')
            ->value('id');

        foreach (['Admin', 'Super Admin'] as $roleName) {
            $roleId = DB::table('roles')->where('name', $roleName)->value('id');

            $this->assertDatabaseHas('role_has_permissions', [
                'role_id' => $roleId,
                'permission_id' => $permissionId,
            ]);
        }
    }

    public function test_admin_role_can_access_imported_v109_admin_routes(): void
    {
        $this->withoutExceptionHandling();

        $adminRole = Role::findOrCreate('Admin', 'web');

        $migration = include database_path('migrations/2026_05_19_000001_assign_v109_permissions_to_admin_roles.php');
        $migration->up();

        $admin = User::factory()->admin()->create();
        $admin->assignRole($adminRole);

        $this->actingAs($admin)->get(route('admin.dashboard'))
            ->assertOk()
            ->assertSee('Promotion &amp; Offers', false)
            ->assertSee('AI Configuration');

        $this->actingAs($admin)->get(route('promotion_and_offers.index'))
            ->assertOk()
            ->assertSee('Promotion & Offers');

        $this->actingAs($admin)->get(route('ai-config'))
            ->assertOk()
            ->assertSee('OpenRouter');
    }
}
