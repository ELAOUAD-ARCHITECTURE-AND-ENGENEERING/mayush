<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;
use Spatie\Permission\PermissionRegistrar;

return new class extends Migration
{
    public function up(): void
    {
        if (!Schema::hasTable('roles') || !Schema::hasTable('permissions') || !Schema::hasTable('role_has_permissions')) {
            return;
        }

        $permissionIds = DB::table('permissions')
            ->whereIn('name', $this->v109PermissionNames())
            ->pluck('id');

        if ($permissionIds->isEmpty()) {
            return;
        }

        $roleIds = DB::table('roles')
            ->whereIn('name', ['Super Admin', 'Admin'])
            ->pluck('id');

        foreach ($roleIds as $roleId) {
            foreach ($permissionIds as $permissionId) {
                DB::table('role_has_permissions')->updateOrInsert([
                    'permission_id' => $permissionId,
                    'role_id' => $roleId,
                ]);
            }
        }

        app(PermissionRegistrar::class)->forgetCachedPermissions();
    }

    public function down(): void
    {
        app(PermissionRegistrar::class)->forgetCachedPermissions();
    }

    private function v109PermissionNames(): array
    {
        return [
            'can_download_and_print_shipping_label',
            'view_promotion_and_offers_dashboard',
            'view_promotional_product',
            'add_promotional_products',
            'remove_from_promotional',
            'remove_from_todays_deal',
            'add_todays_deal_products',
            'can_set_category_wise_discount',
            'customer_delete',
            'manage_ai_configuration',
        ];
    }
};
