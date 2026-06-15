<?php

require 'vendor/autoload.php';
$app = require_once 'bootstrap/app.php';
$kernel = $app->make(Illuminate\Contracts\Console\Kernel::class);
$kernel->bootstrap();

use Spatie\Permission\Models\Role;
use App\Models\RoleTranslation;
use Illuminate\Http\Request;

try {
    $id = 2;
    $role = Role::findOrFail($id);
    echo "Found Role 2: " . $role->name . " (Guard: " . $role->guard_name . ")\n";

    // Mocking request data
    $name = "Admin Updated";
    $permissions = [1, 2]; // Assuming IDs or Names. In Spatie, syncPermissions can take both but usually IDs if passed from a form.
    $lang = "fr";

    echo "Attempting to sync permissions...\n";
    // $role->syncPermissions($permissions); 
    // Wait, let's see what permissions are available
    $allStoredPermissions = Spatie\Permission\Models\Permission::all()->pluck('name')->toArray();
    echo "Total permissions in DB: " . count($allStoredPermissions) . "\n";

    echo "Attempting RoleTranslation...\n";
    $role_translation = RoleTranslation::firstOrNew(['lang' => $lang, 'role_id' => $role->id]);
    $role_translation->name = $name;
    // $role_translation->save();
    echo "RoleTranslation mock success!\n";

    echo "Diagnosis complete. No immediate PHP-level crash in mock script.\n";

} catch (\Exception $e) {
    echo "ERROR CAUGHT: " . $e->getMessage() . "\n";
    echo "FILE: " . $e->getFile() . ":" . $e->getLine() . "\n";
    echo $e->getTraceAsString();
}
