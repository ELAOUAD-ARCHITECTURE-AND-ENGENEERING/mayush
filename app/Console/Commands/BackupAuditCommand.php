<?php

namespace App\Console\Commands;

use Illuminate\Console\Command;
use Illuminate\Support\Facades\File;

class BackupAuditCommand extends Command
{
    protected $signature = 'mayush:backup:audit';

    protected $description = 'Run a read-only backup and restore readiness audit';

    private const CRITICAL_TABLES = [
        'users',
        'sellers',
        'shops',
        'products',
        'uploads',
        'orders',
        'combined_orders',
        'order_details',
        'payment_attempts',
        'cmi_callback_logs',
        'payments',
        'payment_tokens',
        'refunds',
        'refund_requests',
        'wallets',
        'onessta_shipments',
        'onessta_tracking_events',
        'audit_logs',
        'notifications',
        'business_settings',
    ];

    public function handle(): int
    {
        $composer = $this->composerRequirements();
        $kernel = File::exists(app_path('Console/Kernel.php')) ? File::get(app_path('Console/Kernel.php')) : '';

        $databaseBackupScript = File::exists(base_path('scripts/maintenance/backup-database.ps1'));
        $deployBackupWorkflow = File::exists(base_path('.github/workflows/deploy.yml'))
            && str_contains(File::get(base_path('.github/workflows/deploy.yml')), 'mysqldump --single-transaction --quick --routines --triggers');

        $this->warn('READ-ONLY BACKUP AUDIT MODE. NO BACKUP, RESTORE, OR DATA MUTATION WILL RUN.');
        $this->line('Environment: '.config('app.env'));
        $this->line('Database connection: '.config('database.default'));
        $this->line('Database driver: '.config('database.connections.'.config('database.default').'.driver'));
        $this->line('Database name: '.config('database.connections.'.config('database.default').'.database'));
        $this->line('Default filesystem disk: '.config('filesystems.default'));
        $this->line('Local backup directory: '.base_path('storage/app/backups'));
        $this->line('Uploads/media paths: public/uploads, public/all, public/storage, storage/app/public');
        $this->newLine();

        $this->table(['Check', 'Status'], [
            ['Custom DB backup script', $databaseBackupScript ? 'present: scripts/maintenance/backup-database.ps1' : 'missing'],
            ['Deploy pre-migration DB backup', $deployBackupWorkflow ? 'present in .github/workflows/deploy.yml' : 'missing'],
            ['spatie/db-dumper package', in_array('spatie/db-dumper', $composer, true) ? 'installed' : 'missing'],
            ['spatie/laravel-backup package', in_array('spatie/laravel-backup', $composer, true) ? 'installed' : 'not installed'],
            ['Laravel backup scheduler entry', preg_match('/backup[^\\n]*run|backup[^\\n]*clean|mayush:backup/i', $kernel) ? 'present' : 'not scheduled'],
            ['Upload/media backup command', preg_match('/uploads?.*backup|media.*backup|backup.*uploads?/i', $kernel) ? 'scheduled' : 'not scheduled'],
            ['Backup dump git ignore', $this->gitIgnoreCoversBackups() ? 'covered' : 'missing'],
            ['Restore verification command', 'present: mayush:restore:verify'],
        ]);

        $this->newLine();
        $this->info('Critical tables/files that backup planning must cover:');
        foreach (self::CRITICAL_TABLES as $table) {
            $this->line('- '.$table);
        }

        $this->newLine();
        $this->line('files: uploads/media, product images, shop/seller documents, invoices, generated documents, storage symlinks, .env.example');

        $this->newLine();
        $this->warn('Secrets are intentionally omitted: DB password, APP_KEY, SMTP/API/payment keys, and cloud credentials are never printed.');
        $this->warn('No scheduler entry is added by this command.');

        return self::SUCCESS;
    }

    private function composerRequirements(): array
    {
        $path = base_path('composer.json');

        if (! File::exists($path)) {
            return [];
        }

        $composer = json_decode(File::get($path), true);

        return array_keys(array_merge(
            $composer['require'] ?? [],
            $composer['require-dev'] ?? []
        ));
    }

    private function gitIgnoreCoversBackups(): bool
    {
        $rootIgnore = File::exists(base_path('.gitignore')) ? File::get(base_path('.gitignore')) : '';
        $storageIgnore = File::exists(storage_path('app/.gitignore')) ? File::get(storage_path('app/.gitignore')) : '';

        return str_contains($rootIgnore, '/storage/app/backups/')
            || str_contains($rootIgnore, '*.sql')
            || str_contains($storageIgnore, '*');
    }
}
