<?php

namespace Tests\Integration\Controllers\Backend;

use App\Models\Upload;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class WebsiteHeaderControllerTest extends TestCase
{
    use RefreshDatabase;

    public function test_admin_can_fetch_header_upload_file_name(): void
    {
        $admin = User::factory()->create(['user_type' => 'admin']);
        $upload = Upload::create([
            'file_original_name' => 'logo',
            'file_name' => 'uploads/all/logo.png',
            'user_id' => $admin->id,
            'extension' => 'png',
            'type' => 'image',
            'file_size' => 1024,
        ]);

        $this->actingAs($admin)
            ->postJson(route('website.get-upload-file-name'), ['id' => $upload->id])
            ->assertOk()
            ->assertJson([
                'success' => true,
                'file_name' => 'uploads/all/logo.png',
            ])
            ->assertJsonStructure(['success', 'file_name', 'url']);
    }
}
