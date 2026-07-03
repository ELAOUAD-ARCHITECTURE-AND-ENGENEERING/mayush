<?php

namespace App\Console\Commands;

use Illuminate\Console\Command;
use Illuminate\Support\Facades\File;

class RestoreVerifyCommand extends Command
{
    protected $signature = 'mayush:restore:verify
        {--dump= : Path to a SQL dump to validate}
        {--database= : Disposable local/test database name}
        {--confirm : Confirm intentional local/test restore verification}
        {--manual : Print guarded manual restore instructions without executing anything}';

    protected $description = 'Validate a guarded restore verification plan without restoring over the current database';

    public function handle(): int
    {
        $environment = (string) config('app.env', app()->environment());

        if ($environment === 'production' || app()->environment('production')) {
            $this->error('Refusing restore verification in production.');

            return self::FAILURE;
        }

        if (! $this->hasLocalOrExplicitGuard($environment)) {
            $this->error('APP_ENV must be local/testing, or MAYUSH_ALLOW_RESTORE_TEST=true must be set for a disposable restore verification.');

            return self::FAILURE;
        }

        if (! $this->option('confirm')) {
            $this->error('Refusing restore verification without --confirm.');

            return self::FAILURE;
        }

        $dump = (string) $this->option('dump');

        if ($dump === '' || ! File::exists($dump)) {
            $this->error('Backup dump file is missing.');

            return self::FAILURE;
        }

        $dumpPath = realpath($dump);
        $publicPath = realpath(public_path());

        if ($dumpPath === false || $publicPath === false) {
            $this->error('Unable to resolve dump or public path.');

            return self::FAILURE;
        }

        if ($this->isPathInside($dumpPath, $publicPath)) {
            $this->error('Refusing dump files stored inside the public web directory.');

            return self::FAILURE;
        }

        $targetDatabase = (string) $this->option('database');
        $currentDatabase = (string) config('database.connections.'.config('database.default').'.database');

        if ($targetDatabase === '') {
            $this->error('A disposable --database target is required.');

            return self::FAILURE;
        }

        if ($targetDatabase === $currentDatabase) {
            $this->error('Refusing to verify restore against the current configured database.');

            return self::FAILURE;
        }

        $this->warn('RESTORE VERIFICATION PLAN ONLY. NO RESTORE COMMANDS WERE RUN.');
        $this->line('Environment: '.$environment);
        $this->line('Dump file: '.$dumpPath);
        $this->line('Disposable target database: '.$targetDatabase);
        $this->line('Current configured database: '.$currentDatabase);
        $this->newLine();
        $this->info('Manual local/staging verification steps:');
        $this->line('1. Create the disposable database with a local/test database user.');
        $this->line('2. Import the dump into only that disposable database.');
        $this->line('3. Point a local/test .env file at the disposable database.');
        $this->line('4. Run integrity checks for users, products, orders, uploads, payment_attempts, and cmi_callback_logs.');
        $this->line('5. Discard the disposable database only after human confirmation.');
        $this->newLine();
        $this->warn('Secrets are intentionally omitted. This command does not print DB passwords, APP_KEY, SMTP/API/payment keys, or cloud credentials.');

        return self::SUCCESS;
    }

    private function hasLocalOrExplicitGuard(string $environment): bool
    {
        if (in_array($environment, ['local', 'testing'], true)) {
            return true;
        }

        $allow = config('mayush_backup_restore.allow_restore_test', env('MAYUSH_ALLOW_RESTORE_TEST', false));

        return filter_var($allow, FILTER_VALIDATE_BOOLEAN);
    }

    private function isPathInside(string $path, string $directory): bool
    {
        $normalizedPath = rtrim(str_replace('\\', '/', $path), '/');
        $normalizedDirectory = rtrim(str_replace('\\', '/', $directory), '/').'/';

        return str_starts_with($normalizedPath.'/', $normalizedDirectory);
    }
}
