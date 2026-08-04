<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    /**
     * Run the migrations.
     */
    public function up(): void
    {
        $permissions = [
            'view_product_collections' => 'product_collection',
            'add_product_collection' => 'product_collection',
            'edit_product_collection' => 'product_collection',
            'delete_product_collection' => 'product_collection',
        ];

        foreach ($permissions as $name => $section) {
            $permission = \Spatie\Permission\Models\Permission::firstOrCreate([
                'name' => $name,
                'guard_name' => 'web'
            ]);

            $permission->section = $section;
            $permission->save();

            // Assign to Super Admin role if it exists
            $role = \Spatie\Permission\Models\Role::where('name', 'Super Admin')->first();
            if ($role) {
                $role->givePermissionTo($permission);
            }
        }
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        $names = [
            'view_product_collections',
            'add_product_collection',
            'edit_product_collection',
            'delete_product_collection',
        ];

        \Spatie\Permission\Models\Permission::whereIn('name', $names)->delete();
    }
};
