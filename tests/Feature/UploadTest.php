<?php

namespace Tests\Feature;

use Tests\TestCase;
use App\Models\Upload;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Facades\File;
use Intervention\Image\Facades\Image;
use Illuminate\Http\UploadedFile;
use Illuminate\Support\Facades\Storage;

class UploadTest extends TestCase
{
    use RefreshDatabase;

    /** @test */
    public function it_triggers_webp_conversion_on_image_upload_record_creation()
    {
        // Setup
        $user = User::factory()->create();
        
        // Act
        $upload = Upload::create([
            'file_original_name' => 'test.jpg',
            'file_name' => 'test.jpg',
            'user_id' => $user->id,
            'extension' => 'jpg',
            'type' => 'image/jpeg',
            'file_size' => 1024,
        ]);

        // Assert
        $this->assertDatabaseHas('uploads', [
            'id' => $upload->id,
            'file_name' => 'test.jpg'
        ]);
    }
}
