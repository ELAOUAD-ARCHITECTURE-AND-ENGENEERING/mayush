<?php

namespace Tests\Feature\Admin;

use App\Jobs\PrepareProductTranslationRunJob;
use App\Models\Product;
use App\Models\ProductTranslationRun;
use App\Models\User;
use App\Services\ProductTranslationRateLimitGuard;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Facades\Queue;
use Spatie\Permission\Models\Permission;
use Tests\TestCase;

class ProductTranslationDiagnosticsControllerTest extends TestCase
{
    use RefreshDatabase;

    private User $admin;

    protected function setUp(): void
    {
        parent::setUp();
        Permission::findOrCreate('product_edit', 'web');
        $this->admin = User::factory()->create(['user_type' => 'admin', 'email_verified_at' => now()]);
        $this->admin->givePermissionTo('product_edit');
    }

    public function test_product_manager_can_start_only_one_active_translation_run(): void
    {
        Queue::fake();

        $first = $this->actingAs($this->admin)->postJson(route('admin.product_translation_diagnostics.start'));
        $first->assertOk()->assertJsonPath('run.status', 'queued');
        Queue::assertPushed(PrepareProductTranslationRunJob::class, 1);

        $second = $this->actingAs($this->admin)->postJson(route('admin.product_translation_diagnostics.start'));
        $second->assertStatus(409)->assertJsonPath('run.status', 'queued');
        $this->assertSame(1, ProductTranslationRun::count());
    }

    public function test_start_returns_a_paused_run_without_a_conflict(): void
    {
        $run = ProductTranslationRun::create([
            'user_id' => $this->admin->id,
            'active_key' => 'global',
            'status' => 'paused',
            'total_candidates' => 2,
            'pending_count' => 2,
            'failure_reason' => 'The previous run was paused.',
        ]);

        $this->actingAs($this->admin)
            ->postJson(route('admin.product_translation_diagnostics.start'))
            ->assertOk()
            ->assertJsonPath('run.id', $run->id)
            ->assertJsonPath('run.status', 'paused');
    }

    public function test_customer_cannot_access_translation_diagnostics(): void
    {
        $customer = User::factory()->create(['user_type' => 'customer']);

        $this->assertContains(
            $this->actingAs($customer)->get(route('admin.product_translation_diagnostics'))->status(),
            [403, 404]
        );
    }

    public function test_authorized_user_can_poll_a_run_without_loading_all_items(): void
    {
        $run = ProductTranslationRun::create([
            'user_id' => $this->admin->id,
            'status' => 'failed',
            'total_candidates' => 1,
            'processed_count' => 1,
            'failed_count' => 1,
            'failure_reason' => 'La limite temporaire du service de traduction a été atteinte.',
            'finished_at' => now(),
        ]);

        $this->actingAs($this->admin)
            ->getJson(route('admin.product_translation_diagnostics.progress', ['run' => $run->id]))
            ->assertOk()
            ->assertJsonPath('run.id', $run->id)
            ->assertJsonPath('run.status', 'failed');
    }

    public function test_rate_limited_run_cannot_be_retried_during_cooldown(): void
    {
        $run = ProductTranslationRun::create([
            'user_id' => $this->admin->id,
            'active_key' => 'global',
            'status' => 'paused',
            'total_candidates' => 2,
            'pending_count' => 1,
            'failed_count' => 1,
            'failure_reason' => 'rate_limit',
            'next_retry_at' => now()->addMinutes(5),
        ]);

        $this->actingAs($this->admin)
            ->postJson(route('admin.product_translation_diagnostics.retry_failed', ['run' => $run->id]))
            ->assertStatus(429)
            ->assertJsonPath('run.status', 'paused')
            ->assertHeader('Retry-After');
    }

    public function test_one_by_one_translation_respects_the_shared_rate_limit_cooldown(): void
    {
        $product = Product::factory()->create(['draft' => 0]);
        $retryAfter = app(ProductTranslationRateLimitGuard::class)->block(120);

        $this->actingAs($this->admin)
            ->postJson(route('admin.product_translation_diagnostics.repair', ['product' => $product->id]))
            ->assertStatus(429)
            ->assertJsonPath('result.error_code', 'rate_limit')
            ->assertHeader('Retry-After', (string) $retryAfter);
    }

    public function test_start_accepts_and_stores_batch_limit(): void
    {
        Queue::fake();

        $response = $this->actingAs($this->admin)->postJson(route('admin.product_translation_diagnostics.start'), ['limit' => 25]);
        $response->assertOk()
            ->assertJsonPath('run.status', 'queued')
            ->assertJsonPath('run.limit_count', 25);

        $this->assertSame(25, ProductTranslationRun::first()->limit_count);
    }

    public function test_authorized_user_can_stop_running_translation(): void
    {
        $run = ProductTranslationRun::create([
            'user_id' => $this->admin->id,
            'active_key' => 'global',
            'status' => 'running',
            'total_candidates' => 10,
            'pending_count' => 5,
            'processed_count' => 5,
        ]);

        $response = $this->actingAs($this->admin)->postJson(route('admin.product_translation_diagnostics.stop', ['run' => $run->id]));
        $response->assertOk()
            ->assertJsonPath('run.status', 'failed')
            ->assertJsonPath('run.failure_reason', 'Traduction interrompue par l’utilisateur.');

        $this->assertNull(ProductTranslationRun::find($run->id)->active_key);
    }
}
