<?php

use Illuminate\Database\Migrations\Migration;
use Spatie\Permission\Models\Permission;

return new class extends Migration
{
    public function up(): void
    {
        $old = Permission::where('name', 'view_inspirations')
            ->where('guard_name', 'web')
            ->first();
        $permission = Permission::firstOrCreate(
            ['name' => 'view_inspiration', 'guard_name' => 'web'],
            ['section' => 'inspiration']
        );

        if ($old) {
            foreach ($old->roles as $role) {
                $role->givePermissionTo($permission);
            }
            $old->delete();
        }

        app(\Spatie\Permission\PermissionRegistrar::class)->forgetCachedPermissions();
    }

    public function down(): void
    {
        $current = Permission::where('name', 'view_inspiration')
            ->where('guard_name', 'web')
            ->first();
        $old = Permission::firstOrCreate(
            ['name' => 'view_inspirations', 'guard_name' => 'web'],
            ['section' => 'inspiration']
        );

        if ($current) {
            foreach ($current->roles as $role) {
                $role->givePermissionTo($old);
            }
            $current->delete();
        }

        app(\Spatie\Permission\PermissionRegistrar::class)->forgetCachedPermissions();
    }
};
