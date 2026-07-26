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

    public function test_rate_limit_stops_the_run_without_rescheduling_background_work(): void
    {
        Queue::fake();
        config(['product_translation.max_item_attempts' => 3]);
        $product = Product::factory()->create(['draft' => 0]);
        $run = ProductTranslationRun::create(['active_key' => 'global', 'status' => 'queued', 'total_candidates' => 1, 'pending_count' => 1]);
        $item = ProductTranslationRunItem::create(['run_id' => $run->id, 'product_id' => $product->id, 'status' => 'pending']);

        $this->mock(ProductTranslationRepairService::class, function ($mock) {
            $mock->shouldReceive('repair')->once()->andReturn([
                'status' => 'failed',
                'error_code' => 'rate_limit',
                'retry_after' => 1,
                'failed_fields' => ['name'],
                'errors' => ['name' => 'rate_limit'],
                'translated_fields' => [],
                'translated_characters' => 0,
            ]);
        });

        (new ProcessProductTranslationRunJob($run->id))->handle(app(ProductTranslationRepairService::class));

        $this->assertDatabaseHas('product_translation_runs', ['id' => $run->id, 'status' => 'paused', 'active_key' => 'global']);
        $this->assertNotNull($run->fresh()->next_retry_at);
        $this->assertDatabaseHas('product_translation_run_items', ['id' => $item->id, 'status' => 'failed']);
        Queue::assertNotPushed(ProcessProductTranslationRunJob::class);
    }

    public function test_product_failure_stops_the_run_and_does_not_process_the_next_product(): void
    {
        Queue::fake();
        $firstProduct = Product::factory()->create(['draft' => 0]);
        $secondProduct = Product::factory()->create(['draft' => 0]);
        $run = ProductTranslationRun::create(['active_key' => 'global', 'status' => 'queued', 'total_candidates' => 2, 'pending_count' => 2]);
        ProductTranslationRunItem::create(['run_id' => $run->id, 'product_id' => $firstProduct->id, 'status' => 'pending']);
        ProductTranslationRunItem::create(['run_id' => $run->id, 'product_id' => $secondProduct->id, 'status' => 'pending']);

        $this->mock(ProductTranslationRepairService::class, function ($mock) {
            $mock->shouldReceive('repair')->once()->andReturn(
                ['status' => 'failed', 'error_code' => 'malformed_response', 'translated_fields' => [], 'translated_characters' => 0]
            );
        });

        (new ProcessProductTranslationRunJob($run->id))->handle(app(ProductTranslationRepairService::class));
        $this->assertDatabaseHas('product_translation_runs', ['id' => $run->id, 'status' => 'failed', 'failed_count' => 1, 'active_key' => null]);
        $this->assertDatabaseHas('product_translation_run_items', ['run_id' => $run->id, 'product_id' => $firstProduct->id, 'status' => 'failed']);
        $this->assertDatabaseHas('product_translation_run_items', ['run_id' => $run->id, 'product_id' => $secondProduct->id, 'status' => 'pending']);
        Queue::assertNotPushed(ProcessProductTranslationRunJob::class);
    }
}
