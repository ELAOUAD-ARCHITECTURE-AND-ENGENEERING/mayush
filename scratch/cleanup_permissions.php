<?php

require 'vendor/autoload.php';
$app = require_once 'bootstrap/app.php';
$kernel = $app->make(Illuminate\Contracts\Console\Kernel::class);
$kernel->bootstrap();

use Illuminate\Support\Facades\DB;

try {
    DB::transaction(function() {
        $duplicates = ['view_all_product_conversations', 'reply_to_product_conversations', 'delete_product_conversations', 'select_header'];
        foreach ($duplicates as $name) {
            $recs = DB::table('permissions')->where('name', $name)->orderBy('id')->get();
            if ($recs->count() > 1) {
                echo "Cleaning up duplicates for: $name\n";
                $keepId = $recs[0]->id;
                $removeIds = $recs->slice(1)->pluck('id')->toArray();
                
                echo "Keeping ID: $keepId, Removing IDs: " . implode(', ', $removeIds) . "\n";
                
                foreach ($removeIds as $removeId) {
                    // Find roles that have BOTH the keeId and the removeId
                    $duplicateRoles = DB::table('role_has_permissions')
                        ->where('permission_id', $removeId)
                        ->whereIn('role_id', function($query) use ($keepId) {
                            $query->select('role_id')->from('role_has_permissions')->where('permission_id', $keepId);
                        })
                        ->pluck('role_id');

                    if ($duplicateRoles->count() > 0) {
                        echo "  Deleting redundant pivot records for roles: " . $duplicateRoles->implode(', ') . "\n";
                        DB::table('role_has_permissions')->where('permission_id', $removeId)->whereIn('role_id', $duplicateRoles)->delete();
                    }

                    // Now safe to update the rest
                    DB::table('role_has_permissions')->where('permission_id', $removeId)->update(['permission_id' => $keepId]);
                    
                    // Do the same for model_has_permissions
                    DB::table('model_has_permissions')->where('permission_id', $removeId)->update(['permission_id' => $keepId]);
                }
                
                // Final cleanup of the permissions table
                DB::table('permissions')->whereIn('id', $removeIds)->delete();
            }
        }
    });
    echo "Deduplication complete!\n";
} catch (\Exception $e) {
    echo "ERROR: " . $e->getMessage() . "\n";
}
