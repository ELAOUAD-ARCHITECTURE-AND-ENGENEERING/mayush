<?php

use Illuminate\Database\Migrations\Migration;
use Spatie\Permission\Models\Permission;
use Spatie\Permission\Models\Role;

return new class extends Migration
{
    private array $permissions = [
        'view_inspirations',
        'add_inspiration',
        'edit_inspiration',
        'delete_inspiration',
    ];

    public function up(): void
    {
        $superAdmin = Role::where('name', 'Super Admin')->first();

        foreach ($this->permissions as $perm) {
            $p = Permission::firstOrCreate(
                ['name' => $perm, 'guard_name' => 'web'],
                ['section' => 'inspiration']
            );
            if ($superAdmin) {
                $superAdmin->givePermissionTo($p);
            }
        }
    }

    public function down(): void
    {
        foreach ($this->permissions as $perm) {
            Permission::where('name', $perm)->delete();
        }
    }
};
