<?php

namespace Tests\Feature;

use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Http\UploadedFile;
use Spatie\Permission\Models\Permission;
use Tests\TestCase;
use Tests\Traits\SeedsAppConfigs;

class ProductionDebugArtifactTest extends TestCase
{
    use RefreshDatabase, SeedsAppConfigs;

    protected function setUp(): void
    {
        parent::setUp();
        app(\Spatie\Permission\PermissionRegistrar::class)->forgetCachedPermissions();
        $this->seedConfigs();
    }

    public function test_production_paths_do_not_contain_active_debug_stops(): void
    {
        $paths = [
            app_path(),
            base_path('routes'),
            resource_path('views'),
        ];

        $offenders = [];
        foreach ($paths as $path) {
            foreach (new \RecursiveIteratorIterator(new \RecursiveDirectoryIterator($path)) as $file) {
                if (!$file->isFile() || !in_array($file->getExtension(), ['php'], true)) {
                    continue;
                }

                $contents = file_get_contents($file->getPathname());
                if (preg_match('/(?<![A-Za-z0-9_])(dd|dump|ray|var_dump)\s*\(/', $contents)) {
                    $offenders[] = $file->getPathname();
                }
            }
        }

        $this->assertSame([], $offenders);
    }

    public function test_invalid_addon_archive_returns_response_instead_of_debug_stop(): void
    {
        Permission::findOrCreate('manage_addons', 'web');
        $admin = User::factory()->admin()->create();
        $admin->givePermissionTo('manage_addons');

        $response = $this->actingAs($admin)->post(route('addons.store'), [
            'addon_zip' => UploadedFile::fake()->create('addon.zip', 1, 'application/zip'),
        ]);

        $response->assertRedirect(route('addons.index'));
    }
}
