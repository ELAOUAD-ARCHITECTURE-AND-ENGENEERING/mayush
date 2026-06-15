<?php

namespace Tests\Feature\Maintenance;

use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Facades\DB;
use Tests\TestCase;

class BackupRestoreVerificationTest extends TestCase
{
    use RefreshDatabase;

    private array $temporaryFiles = [];

    protected function tearDown(): void
    {
        foreach ($this->temporaryFiles as $file) {
            if (is_file($file)) {
                @unlink($file);
            }
        }

        parent::tearDown();
    }

    public function test_backup_audit_command_runs_successfully(): void
    {
        $this->artisan('mayush:backup:audit')
            ->expectsOutputToContain('READ-ONLY BACKUP AUDIT MODE')
            ->expectsOutputToContain('Custom DB backup script')
            ->expectsOutputToContain('Restore verification command')
            ->assertSuccessful();
    }

    public function test_backup_audit_command_does_not_expose_secrets(): void
    {
        config([
            'app.key' => 'base64:secret-app-key-for-test',
            'database.connections.sqlite.password' => 'secret-db-password-for-test',
            'mail.mailers.smtp.password' => 'secret-smtp-password-for-test',
            'services.stripe.secret' => 'secret-stripe-key-for-test',
            'cmi.store_key' => 'secret-cmi-store-key-for-test',
        ]);

        $this->artisan('mayush:backup:audit')
            ->expectsOutputToContain('READ-ONLY BACKUP AUDIT MODE')
            ->doesntExpectOutputToContain('secret-app-key-for-test')
            ->doesntExpectOutputToContain('secret-db-password-for-test')
            ->doesntExpectOutputToContain('secret-smtp-password-for-test')
            ->doesntExpectOutputToContain('secret-stripe-key-for-test')
            ->doesntExpectOutputToContain('secret-cmi-store-key-for-test')
            ->assertSuccessful();
    }

    public function test_backup_audit_command_does_not_delete_or_update_data(): void
    {
        DB::table('failed_jobs')->insert([
            'uuid' => 'backup-audit-failed-job',
            'connection' => 'sync',
            'queue' => 'default',
            'payload' => '{}',
            'exception' => 'test',
            'failed_at' => now(),
        ]);

        DB::table('payment_attempts')->insert([
            'user_id' => null,
            'combined_order_id' => null,
            'order_id' => null,
            'payment_method' => 'cmi',
            'gateway' => 'cmi',
            'gateway_reference' => 'CMI-BACKUP-AUDIT',
            'merchant_reference' => 'ORDER-BACKUP-AUDIT',
            'amount' => 100,
            'currency' => 'MAD',
            'status' => 'completed',
            'request_payload_hash' => 'request-hash',
            'response_payload_hash' => 'response-hash',
            'initiated_at' => now(),
            'completed_at' => now(),
            'failed_at' => null,
            'metadata' => json_encode(['source' => 'backup-audit-test']),
            'created_at' => now(),
            'updated_at' => now(),
        ]);

        $failedJobsBefore = DB::table('failed_jobs')->count();
        $paymentAttemptBefore = (array) DB::table('payment_attempts')
            ->where('merchant_reference', 'ORDER-BACKUP-AUDIT')
            ->first();

        $this->artisan('mayush:backup:audit')->assertSuccessful();

        $paymentAttemptAfter = (array) DB::table('payment_attempts')
            ->where('merchant_reference', 'ORDER-BACKUP-AUDIT')
            ->first();

        $this->assertSame($failedJobsBefore, DB::table('failed_jobs')->count());
        $this->assertDatabaseHas('failed_jobs', ['uuid' => 'backup-audit-failed-job']);
        $this->assertSame($paymentAttemptBefore, $paymentAttemptAfter);
    }

    public function test_restore_verification_refuses_production_environment(): void
    {
        config(['app.env' => 'production']);

        $this->artisan('mayush:restore:verify', [
            '--dump' => $this->fakeDump(),
            '--database' => 'mayush_restore_verify',
            '--confirm' => true,
        ])
            ->expectsOutputToContain('Refusing restore verification in production.')
            ->assertExitCode(1);
    }

    public function test_restore_verification_refuses_current_database_as_target(): void
    {
        config(['app.env' => 'testing']);
        $currentDatabase = (string) config('database.connections.'.config('database.default').'.database');

        $this->artisan('mayush:restore:verify', [
            '--dump' => $this->fakeDump(),
            '--database' => $currentDatabase,
            '--confirm' => true,
        ])
            ->expectsOutputToContain('Refusing to verify restore against the current configured database.')
            ->assertExitCode(1);
    }

    public function test_restore_verification_requires_explicit_local_or_test_guard(): void
    {
        config(['app.env' => 'staging']);

        $this->artisan('mayush:restore:verify', [
            '--dump' => $this->fakeDump(),
            '--database' => 'mayush_restore_verify',
            '--confirm' => true,
        ])
            ->expectsOutputToContain('APP_ENV must be local/testing')
            ->assertExitCode(1);
    }

    public function test_restore_verification_refuses_missing_dump_path(): void
    {
        config(['app.env' => 'testing']);

        $this->artisan('mayush:restore:verify', [
            '--dump' => sys_get_temp_dir().DIRECTORY_SEPARATOR.'missing-mayush-dump.sql',
            '--database' => 'mayush_restore_verify',
            '--confirm' => true,
        ])
            ->expectsOutputToContain('Backup dump file is missing.')
            ->assertExitCode(1);
    }

    public function test_restore_verification_does_not_create_backup_files_in_repo(): void
    {
        config(['app.env' => 'testing']);

        $beforeRootDumps = glob(base_path('*.sql')) ?: [];
        $beforeBackupDumps = glob(storage_path('app/backups/*.sql')) ?: [];

        $this->artisan('mayush:backup:audit')->assertSuccessful();
        $this->artisan('mayush:restore:verify', [
            '--dump' => $this->fakeDump(),
            '--database' => 'mayush_restore_verify',
            '--confirm' => true,
        ])->assertSuccessful();

        $afterRootDumps = glob(base_path('*.sql')) ?: [];
        $afterBackupDumps = glob(storage_path('app/backups/*.sql')) ?: [];

        $this->assertSame($beforeRootDumps, $afterRootDumps);
        $this->assertSame($beforeBackupDumps, $afterBackupDumps);
    }

    public function test_critical_table_list_includes_required_business_payment_and_media_tables(): void
    {
        $this->artisan('mayush:backup:audit')
            ->expectsOutputToContain('users')
            ->expectsOutputToContain('products')
            ->expectsOutputToContain('orders')
            ->expectsOutputToContain('payment_attempts')
            ->expectsOutputToContain('cmi_callback_logs')
            ->expectsOutputToContain('uploads')
            ->expectsOutputToContain('refunds')
            ->expectsOutputToContain('sellers')
            ->expectsOutputToContain('shops')
            ->assertSuccessful();
    }

    public function test_documentation_exists(): void
    {
        $path = base_path('docs/BACKUP_RESTORE_VERIFICATION.md');

        $this->assertFileExists($path);

        $documentation = file_get_contents($path);

        $this->assertStringContainsString('## 1. Purpose', $documentation);
        $this->assertStringContainsString('## 15. Production Checklist Before Pruning', $documentation);
        $this->assertStringContainsString('php artisan mayush:backup:audit', $documentation);
        $this->assertStringContainsString('php artisan mayush:restore:verify', $documentation);
        $this->assertStringContainsString('php artisan test', $documentation);
    }

    private function fakeDump(): string
    {
        $file = tempnam(sys_get_temp_dir(), 'mayush_restore_');
        file_put_contents($file, "-- sanitized local test dump\nSELECT 1;\n");

        $this->temporaryFiles[] = $file;

        return $file;
    }
}
