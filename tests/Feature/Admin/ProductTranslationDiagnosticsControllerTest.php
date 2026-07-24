<?php

namespace Tests\Feature\Admin;

use App\Jobs\PrepareProductTranslationRunJob;
use App\Models\ProductTranslationRun;
use App\Models\User;
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

    public function test_customer_cannot_access_translation_diagnostics(): void
    {
        $customer = User::factory()->create(['user_type' => 'customer']);

        $this->assertContains(
            $this->actingAs($customer)->get(route('admin.product_translation_diagnostics'))->status(),
            [403, 404]
        );
    }
}
