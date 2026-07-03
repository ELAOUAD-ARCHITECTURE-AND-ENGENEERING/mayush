<?php

namespace Tests\Feature;

use App\Jobs\OptimizeStaticImageJob;
use App\Jobs\OptimizeUploadedImageJob;
use App\Models\ImageOptimizationState;
use App\Models\Upload;
use App\Services\ImageOptimizationService;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Http\UploadedFile;
use Illuminate\Support\Facades\Queue;
use Illuminate\Support\Facades\Storage;
use Intervention\Image\Facades\Image;
use Tests\TestCase;

class ImageOptimizationPipelineTest extends TestCase
{
    use RefreshDatabase;

    protected function setUp(): void
    {
        parent::setUp();

        Storage::fake('local');
        config([
            'filesystems.default' => 'local',
            'image-optimization.disk' => 'local',
            'image-optimization.static_disk' => 'local',
            'image-optimization.recipe_version' => 'test-v1',
        ]);
    }

    /** @test */
    public function it_generates_webp_derivatives_without_replacing_the_source(): void
    {
        $source = UploadedFile::fake()->image('office.png', 1000, 800)->getContent();
        Storage::disk('local')->put('assets/office.png', $source);

        $state = app(ImageOptimizationService::class)->optimizeStaticAsset('assets/office.png');

        Storage::disk('local')->assertExists('assets/office.png');
        Storage::disk('local')->assertExists('assets/office.webp');
        Storage::disk('local')->assertExists('assets/office_small.webp');
        Storage::disk('local')->assertExists('assets/office_thumb.webp');
        Storage::disk('local')->assertExists('assets/office_card.webp');
        Storage::disk('local')->assertExists('assets/office_medium.webp');
        Storage::disk('local')->assertExists('assets/office_large.webp');
        $this->assertSame($source, Storage::disk('local')->get('assets/office.png'));
        $this->assertSame('optimized', $state->status);
    }

    /** @test */
    public function it_is_idempotent_until_the_recipe_version_changes(): void
    {
        Storage::disk('local')->put('assets/logo.png', UploadedFile::fake()->image('logo.png', 400, 300)->getContent());
        $optimizer = app(ImageOptimizationService::class);

        $optimizer->optimizeStaticAsset('assets/logo.png');
        $initial = ImageOptimizationState::firstOrFail();
        $optimizer->optimizeStaticAsset('assets/logo.png');
        $this->assertSame('test-v1', $initial->fresh()->recipe_version);

        config(['image-optimization.recipe_version' => 'test-v2']);
        $optimizer->optimizeStaticAsset('assets/logo.png');

        $this->assertSame('test-v2', $initial->fresh()->recipe_version);
    }

    /** @test */
    public function it_preserves_a_webp_source_while_generating_responsive_variants(): void
    {
        $source = (string) Image::make(UploadedFile::fake()->image('source.png', 600, 400)->getContent())->encode('webp', 90);
        Storage::disk('local')->put('assets/source.webp', $source);

        app(ImageOptimizationService::class)->optimizeStaticAsset('assets/source.webp');

        $this->assertSame($source, Storage::disk('local')->get('assets/source.webp'));
        Storage::disk('local')->assertExists('assets/source_thumb.webp');
    }

    /** @test */
    public function it_uses_the_configured_cloud_disk_for_upload_derivatives(): void
    {
        Storage::fake('aws');
        config([
            'filesystems.default' => 'aws',
            'image-optimization.disk' => 'aws',
        ]);
        Storage::disk('aws')->put('uploads/all/product.png', UploadedFile::fake()->image('product.png', 800, 800)->getContent());
        $upload = Upload::withoutEvents(fn () => Upload::create([
            'file_original_name' => 'product',
            'file_name' => 'uploads/all/product.png',
            'extension' => 'png',
            'type' => 'image',
            'file_size' => 123,
        ]));

        app(ImageOptimizationService::class)->optimizeUpload($upload);

        Storage::disk('aws')->assertExists('uploads/all/product.png');
        Storage::disk('aws')->assertExists('uploads/all/product_medium.webp');
    }

    /** @test */
    public function creating_an_image_upload_dispatches_the_optimizer_job(): void
    {
        Queue::fake();

        $upload = Upload::create([
            'file_original_name' => 'queued',
            'file_name' => 'uploads/all/queued.png',
            'extension' => 'png',
            'type' => 'image',
            'file_size' => 123,
        ]);

        Queue::assertPushedOn('images', OptimizeUploadedImageJob::class, fn ($job) => $job->uploadId === $upload->id);
    }

    /** @test */
    public function repair_audit_queues_upload_and_static_repairs(): void
    {
        Queue::fake();
        Storage::disk('local')->put('uploads/all/audit.png', UploadedFile::fake()->image('audit.png', 500, 500)->getContent());
        $upload = Upload::withoutEvents(fn () => Upload::create([
            'file_original_name' => 'audit',
            'file_name' => 'uploads/all/audit.png',
            'extension' => 'png',
            'type' => 'image',
            'file_size' => 123,
        ]));
        config(['image-optimization.static_assets' => ['assets/static.png']]);
        Storage::disk('local')->put('assets/static.png', UploadedFile::fake()->image('static.png', 500, 500)->getContent());

        $this->artisan('images:audit', ['--repair' => true, '--include-static' => true, '--limit' => 1])
            ->assertSuccessful();

        Queue::assertPushed(OptimizeUploadedImageJob::class, fn ($job) => $job->uploadId === $upload->id);
        Queue::assertPushed(OptimizeStaticImageJob::class, fn ($job) => $job->path === 'assets/static.png');
    }

    /** @test */
    public function responsive_srcsets_include_only_real_unique_derivatives_and_queue_one_repair(): void
    {
        Queue::fake();
        Storage::disk('local')->put('uploads/all/hero.png', UploadedFile::fake()->image('hero.png', 1600, 720)->getContent());
        $upload = Upload::withoutEvents(fn () => Upload::create([
            'file_original_name' => 'hero',
            'file_name' => 'uploads/all/hero.png',
            'extension' => 'png',
            'type' => 'image',
            'file_size' => 123,
        ]));

        $this->assertSame('', uploaded_asset_srcset($upload, ['medium', 'large']));
        $this->assertSame('', uploaded_asset_srcset($upload, ['medium', 'large']));
        Queue::assertPushed(OptimizeUploadedImageJob::class, 1);

        Storage::disk('local')->put('uploads/all/hero_medium.webp', 'medium');
        $srcset = uploaded_asset_srcset($upload, ['medium', 'large']);

        $this->assertStringContainsString('hero_medium.webp', $srcset);
        $this->assertStringNotContainsString('hero.png 1200w', $srcset);
    }
}
