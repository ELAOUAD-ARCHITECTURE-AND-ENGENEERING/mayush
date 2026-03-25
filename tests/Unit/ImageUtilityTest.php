<?php

namespace Tests\Unit;

use Tests\TestCase;
use App\Utility\ImageUtility;
use Illuminate\Support\Facades\File;
use Intervention\Image\Facades\Image;

class ImageUtilityTest extends TestCase
{
    /** @test */
    public function it_skips_conversion_if_file_does_not_exist()
    {
        $result = ImageUtility::convertToWebp('non_existent.jpg');
        $this->assertFalse($result);
    }

    /** @test */
    public function it_returns_true_path_if_already_webp()
    {
        // Mocking can be complex with static methods and File facade, 
        // but let's test the logic for early return.
        $path = 'test.webp';
        $fullPath = public_path($path);
        
        File::shouldReceive('exists')->with($fullPath)->andReturn(true);
        
        $result = ImageUtility::convertToWebp($path);
        $this->assertEquals($fullPath, $result);
    }
}
