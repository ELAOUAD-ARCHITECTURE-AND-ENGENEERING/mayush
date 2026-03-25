<?php

namespace Tests\Feature;

use Tests\TestCase;
use Illuminate\Foundation\Testing\RefreshDatabase;
use App\Models\User;
use Spatie\Permission\Models\Role;
use Spatie\Permission\Models\Permission;

abstract class CrudTestCase extends TestCase
{
    use RefreshDatabase;

    protected $adminUser;

    protected function setUp(): void
    {
        parent::setUp();
        
        // Ensure we are using a clean state
        $this->setUpAdmin();
    }

    /**
     * Set up an admin user for authenticated requests
     */
    protected function setUpAdmin()
    {
        $this->adminUser = User::updateOrCreate(
            ['email' => 'admin@test.com'],
            [
                'name' => 'Admin User',
                'password' => bcrypt('password'),
                'user_type' => 'admin',
                'email_verified_at' => now(),
            ]
        );

        // Seed default language
        \App\Models\Language::updateOrCreate(
            ['code' => 'en'],
            [
                'name' => 'English',
                'app_lang_code' => 'en',
                'rtl' => 0,
            ]
        );

        // Seed basic permissions for Category CRUD
        $permissions = [
            'view_product_categories',
            'add_product_category',
            'edit_product_category',
            'delete_product_category',
        ];

        foreach ($permissions as $permission) {
            Permission::findOrCreate($permission, 'web');
        }

        $adminRole = Role::findOrCreate('admin', 'web');
        $adminRole->givePermissionTo($permissions);
        
        $this->adminUser->assignRole($adminRole);
    }

    /**
     * Profile a request to measure execution time and memory usage
     *
     * @param \Closure $callback
     * @return array
     */
    protected function profileBlock(\Closure $callback)
    {
        $startTime = microtime(true);
        $startMemory = memory_get_usage();

        $result = $callback();

        $endTime = microtime(true);
        $endMemory = memory_get_peak_usage();

        $duration = ($endTime - $startTime) * 1000; // ms
        $memory = ($endMemory - $startMemory) / 1024 / 1024; // MB

        return [
            'result' => $result,
            'metrics' => [
                'duration_ms' => round($duration, 2),
                'memory_mb' => round($memory, 2),
            ]
        ];
    }

    /**
     * Log performance metrics for reporting
     */
    protected function logPerformance($operation, $metrics)
    {
        $logFile = base_path('tests/performance_log.json');
        $currentLogs = [];
        
        if (file_exists($logFile)) {
            $currentLogs = json_decode(file_get_contents($logFile), true) ?: [];
        }

        $currentLogs[] = [
            'test' => get_class($this) . '::' . $this->name(),
            'operation' => $operation,
            'duration_ms' => $metrics['duration_ms'],
            'memory_mb' => $metrics['memory_mb'],
            'timestamp' => now()->toDateTimeString(),
        ];

        file_put_contents($logFile, json_encode($currentLogs, JSON_PRETTY_PRINT));
    }
}
