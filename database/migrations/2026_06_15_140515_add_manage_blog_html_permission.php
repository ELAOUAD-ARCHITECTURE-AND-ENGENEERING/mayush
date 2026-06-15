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
        $permission = \Spatie\Permission\Models\Permission::firstOrCreate([
            'name' => 'manage_blog_html',
            'guard_name' => 'web'
        ]);
        
        // Try to assign it to a super_admin role if one exists, otherwise don't crash
        $role = \Spatie\Permission\Models\Role::where('name', 'super_admin')->first();
        if ($role) {
            $role->givePermissionTo($permission);
        }
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        \Spatie\Permission\Models\Permission::where('name', 'manage_blog_html')->delete();
    }
};
