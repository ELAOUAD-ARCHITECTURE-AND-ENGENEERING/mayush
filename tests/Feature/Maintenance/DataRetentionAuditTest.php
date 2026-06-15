<?php

namespace Tests\Feature\Maintenance;

use App\Services\Maintenance\DatabaseRetentionAuditService;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Facades\DB;
use Tests\TestCase;

class DataRetentionAuditTest extends TestCase
{
    use RefreshDatabase;

    public function test_retention_audit_command_runs_successfully(): void
    {
        $this->artisan('mayush:retention-audit')
            ->expectsOutputToContain('READ-ONLY AUDIT MODE. NO DATA WILL BE DELETED.')
            ->expectsOutputToContain('PROTECTED_FOREVER')
            ->expectsOutputToContain('ARCHIVE_BEFORE_PRUNE')
            ->expectsOutputToContain('DIRECT_PRUNE_CANDIDATE')
            ->expectsOutputToContain('UNKNOWN_PROTECTED')
            ->assertSuccessful();
    }

    public function test_retention_audit_command_deletes_nothing(): void
    {
        DB::table('failed_jobs')->insert([
            'uuid' => 'retention-audit-failed-job',
            'connection' => 'sync',
            'queue' => 'default',
            'payload' => '{}',
            'exception' => 'test',
            'failed_at' => now(),
        ]);

        $before = DB::table('failed_jobs')->count();

        $this->artisan('mayush:retention-audit')->assertSuccessful();

        $this->assertSame($before, DB::table('failed_jobs')->count());
        $this->assertDatabaseHas('failed_jobs', ['uuid' => 'retention-audit-failed-job']);
    }

    public function test_retention_audit_command_updates_nothing(): void
    {
        DB::table('payment_attempts')->insert([
            'user_id' => null,
            'combined_order_id' => null,
            'order_id' => null,
            'payment_method' => 'cmi',
            'gateway' => 'cmi',
            'gateway_reference' => 'CMI-READ-ONLY',
            'merchant_reference' => 'ORDER-READ-ONLY',
            'amount' => 100,
            'currency' => 'MAD',
            'status' => 'completed',
            'request_payload_hash' => 'request-hash',
            'response_payload_hash' => 'response-hash',
            'initiated_at' => now(),
            'completed_at' => now(),
            'failed_at' => null,
            'metadata' => json_encode(['source' => 'retention-audit-test']),
            'created_at' => now(),
            'updated_at' => now(),
        ]);

        $before = (array) DB::table('payment_attempts')
            ->where('merchant_reference', 'ORDER-READ-ONLY')
            ->first();

        $this->artisan('mayush:retention-audit')->assertSuccessful();

        $after = (array) DB::table('payment_attempts')
            ->where('merchant_reference', 'ORDER-READ-ONLY')
            ->first();

        $this->assertSame($before, $after);
    }

    public function test_pruning_is_disabled_by_default(): void
    {
        $this->assertFalse(config('mayush_retention.pruning_enabled'));
    }

    public function test_orders_are_protected(): void
    {
        $this->assertProtected('orders');
    }

    public function test_users_are_protected(): void
    {
        $this->assertProtected('users');
    }

    public function test_products_are_protected(): void
    {
        $this->assertProtected('products');
    }

    public function test_uploads_are_protected(): void
    {
        $this->assertProtected('uploads');
    }

    public function test_refunds_are_protected(): void
    {
        $this->assertProtected('refund_requests');
    }

    public function test_successful_payment_attempts_are_protected_by_table_blacklist(): void
    {
        $this->assertProtected('payment_attempts');
    }

    public function test_successful_cmi_callback_logs_are_protected_by_table_blacklist(): void
    {
        $this->assertProtected('cmi_callback_logs');
    }

    public function test_failed_jobs_are_only_a_candidate_and_not_deleted(): void
    {
        $classification = app(DatabaseRetentionAuditService::class)->classify('failed_jobs');

        $this->assertSame(DatabaseRetentionAuditService::DIRECT_PRUNE_CANDIDATE, $classification['category']);
        $this->assertNotContains('failed_jobs', config('mayush_retention.protected_tables'));
    }

    public function test_unknown_tables_default_to_unknown_protected(): void
    {
        $classification = app(DatabaseRetentionAuditService::class)->classify('future_unreviewed_table');

        $this->assertSame(DatabaseRetentionAuditService::UNKNOWN_PROTECTED, $classification['category']);
    }

    public function test_no_execute_pruning_command_exists(): void
    {
        $commands = $this->app[\Illuminate\Contracts\Console\Kernel::class]->all();

        $this->assertArrayHasKey('mayush:retention-audit', $commands);
        $this->assertArrayNotHasKey('mayush:retention-prune', $commands);
        $this->assertArrayNotHasKey('mayush:retention-prune-execute', $commands);
        $this->assertFalse($commands['mayush:retention-audit']->getDefinition()->hasOption('execute'));
    }

    public function test_retention_audit_is_not_scheduled_to_delete_data(): void
    {
        $kernel = file_get_contents(app_path('Console/Kernel.php'));

        $this->assertStringNotContainsString('mayush:retention-prune', $kernel);
        $this->assertStringNotContainsString('mayush:retention-audit --execute', $kernel);
    }

    private function assertProtected(string $table): void
    {
        $classification = app(DatabaseRetentionAuditService::class)->classify($table);

        $this->assertSame(DatabaseRetentionAuditService::PROTECTED_FOREVER, $classification['category']);
    }
}
