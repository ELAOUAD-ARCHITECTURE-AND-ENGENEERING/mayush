<?php

namespace Tests\Feature\Queue;

use App\Jobs\ProcessProductTranslationRunJob;
use App\Models\Product;
use App\Models\ProductTranslationRun;
use App\Models\ProductTranslationRunItem;
use App\Services\ProductTranslationRepairService;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Facades\Queue;
use Tests\TestCase;

class ProductTranslationRunJobsTest extends TestCase
{
    use RefreshDatabase;

    public function test_rate_limit_stops_the_run_completely_and_does_not_dispatch_the_next_product(): void
    {
        Queue::fake();
        $product = Product::factory()->create(['draft' => 0]);
        $run = ProductTranslationRun::create([
            'active_key' => 'global',
            'status' => 'queued',
            'total_candidates' => 1,
            'pending_count' => 1,
        ]);
        ProductTranslationRunItem::create([
            'run_id' => $run->id,
            'product_id' => $product->id,
            'status' => 'pending',
        ]);

        $this->mock(ProductTranslationRepairService::class, function ($mock) {
            $mock->shouldReceive('repair')->once()->andReturn([
                'status' => 'failed',
                'error_code' => 'rate_limit',
                'failed_fields' => ['name'],
                'errors' => ['name' => 'rate_limit'],
                'translated_fields' => [],
                'azure_characters' => 0,
            ]);
        });

        (new ProcessProductTranslationRunJob($run->id))->handle(app(ProductTranslationRepairService::class));

        $this->assertDatabaseHas('product_translation_runs', [
            'id' => $run->id,
            'status' => 'failed',
            'active_key' => null,
        ]);
        Queue::assertNotPushed(ProcessProductTranslationRunJob::class);
    }

    public function test_any_other_translation_failure_also_stops_the_run_completely(): void
    {
        Queue::fake();
        $product = Product::factory()->create(['draft' => 0]);
        $run = ProductTranslationRun::create(['active_key' => 'global', 'status' => 'queued', 'total_candidates' => 1, 'pending_count' => 1]);
        ProductTranslationRunItem::create(['run_id' => $run->id, 'product_id' => $product->id, 'status' => 'pending']);

        $this->mock(ProductTranslationRepairService::class, function ($mock) {
            $mock->shouldReceive('repair')->once()->andReturn([
                'status' => 'failed',
                'error_code' => 'request_failed',
                'error' => 'Azure request failed',
                'translated_fields' => [],
                'azure_characters' => 0,
            ]);
        });

        (new ProcessProductTranslationRunJob($run->id))->handle(app(ProductTranslationRepairService::class));

        $this->assertDatabaseHas('product_translation_runs', ['id' => $run->id, 'status' => 'failed', 'active_key' => null]);
        Queue::assertNotPushed(ProcessProductTranslationRunJob::class);
    }
}
