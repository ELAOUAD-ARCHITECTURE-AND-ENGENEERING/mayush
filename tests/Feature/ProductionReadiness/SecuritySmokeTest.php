<?php

namespace Tests\Feature\ProductionReadiness;

use Tests\TestCase;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Http\UploadedFile;
use Illuminate\Support\Facades\Storage;
use Tests\Traits\SeedsAppConfigs;

class SecuritySmokeTest extends TestCase
{
    use RefreshDatabase, SeedsAppConfigs;

    protected function setUp(): void
    {
        parent::setUp();
        $this->seedConfigs();
    }

    /** @test */
    public function admin_routes_require_authentication(): void
    {
        $response = $this->get(route('admin.dashboard'));
        $response->assertRedirect();
    }

    /** @test */
    public function seller_routes_require_authentication(): void
    {
        $response = $this->get(route('seller.dashboard'));
        $this->assertContains($response->status(), [302, 404]);
    }

    /** @test */
    public function checkout_post_routes_reject_unauthenticated_access(): void
    {
        $response = $this->post(route('payment.checkout'), [
            'payment_option' => 'cash_on_delivery'
        ]);

        // Should redirect or reject
        $this->assertContains($response->status(), [302, 400, 404]);
    }

    /** @test */
    public function dangerous_debug_routes_are_not_publicly_accessible(): void
    {
        // Check common debug routes
        $response = $this->get('/debug');
        $this->assertNotEquals(200, $response->status());

        $response = $this->get('/_debugbar/open');
        $this->assertNotEquals(200, $response->status());
    }

    /** @test */
    public function file_upload_routes_validate_file_type(): void
    {
        Storage::fake();
        $user = User::factory()->customer()->create();

        // 1. Post a valid image file
        $response = $this->actingAs($user)->post('/aiz-uploader/upload', [
            'aiz_file' => UploadedFile::fake()->image('test.png')
        ]);
        $response->assertStatus(200);

        // 2. Post an invalid/malicious file (e.g. .exe)
        $response2 = $this->actingAs($user)->post('/aiz-uploader/upload', [
            'aiz_file' => UploadedFile::fake()->create('malicious.exe', 100)
        ]);
        
        // Since .exe is not in the allowed types array, the controller should not save it in uploads table.
        $this->assertDatabaseMissing('uploads', [
            'file_original_name' => 'malicious'
        ]);
    }
}