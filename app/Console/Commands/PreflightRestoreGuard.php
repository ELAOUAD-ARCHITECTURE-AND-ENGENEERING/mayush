<?php

namespace App\Console\Commands;

use App\Models\Currency;
use Illuminate\Console\Command;
use Illuminate\Support\Facades\Artisan;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Redis;
use Illuminate\Support\Facades\Schema;
use Throwable;

class PreflightRestoreGuard extends Command
{
    protected $signature = 'app:preflight-restore
        {--require-redis : Fail if Redis is not reachable, even when the queue driver is not redis}
        {--require-blog-navigation : Fail if the public Blog header link is missing}
        {--allow-pending-migrations : Do not fail when pending migrations exist}
        {--skip-db : Skip database-backed checks}';

    protected $description = 'Validate deployment, migration, and runtime prerequisites before risky operations';

    public function handle(): int
    {
        $failed = false;

        $this->info('Mayush restoration preflight');
        $this->line('Environment: '.app()->environment());
        $this->line('Database: '.config('database.default'));
        $this->line('Queue: '.config('queue.default'));
        $this->line('Cache: '.config('cache.default'));
        $this->newLine();

        if (!$this->option('skip-db')) {
            $failed = !$this->checkDatabase() || $failed;
            $failed = !$this->checkRequiredSettings() || $failed;
            $failed = !$this->checkHeaderMenu() || $failed;
            $failed = !$this->checkDefaultCurrency() || $failed;
            $failed = !$this->checkMigrations() || $failed;
        }

        $failed = !$this->checkRedis() || $failed;

        if ($failed) {
            $this->error('Preflight failed. Stop deployment or migration until the errors above are fixed.');
            return self::FAILURE;
        }

        $this->info('Preflight passed.');
        return self::SUCCESS;
    }

    private function checkDatabase(): bool
    {
        try {
            DB::connection()->getPdo();
            $this->info('[OK] Database connection works.');
            return true;
        } catch (Throwable $exception) {
            $this->error('[FAIL] Database connection failed: '.$exception->getMessage());
            return false;
        }
    }

    private function checkRequiredSettings(): bool
    {
        if (!Schema::hasTable('business_settings')) {
            $this->error('[FAIL] business_settings table is missing.');
            return false;
        }

        $required = [
            'homepage_select',
            'header_element',
            'authentication_layout_select',
            'system_default_currency',
            'no_of_decimals',
            'decimal_separator',
            'header_menu_labels',
            'header_menu_links',
        ];

        $ok = true;
        foreach ($required as $key) {
            $value = get_setting($key);
            if ($value === null || $value === '') {
                $this->error("[FAIL] Required business setting is missing or empty: {$key}");
                $ok = false;
            } else {
                $this->info("[OK] {$key}={$value}");
            }
        }

        $homepage = safe_homepage_select();
        if (!view()->exists("frontend.{$homepage}.index")) {
            $this->error("[FAIL] Homepage view does not exist for homepage_select={$homepage}");
            $ok = false;
        }

        $authLayout = safe_auth_layout_select();
        if (!view()->exists("auth.{$authLayout}.admin_login")) {
            $this->error("[FAIL] Auth login view does not exist for authentication_layout_select={$authLayout}");
            $ok = false;
        }

        $headerView = safe_header_view();
        if (!view()->exists($headerView)) {
            $this->error("[FAIL] Header view does not exist: {$headerView}");
            $ok = false;
        }

        return $ok;
    }

    private function checkHeaderMenu(): bool
    {
        $labels = $this->decodeBusinessSettingArray('header_menu_labels');
        $links = $this->decodeBusinessSettingArray('header_menu_links');

        if ($labels === null || $links === null) {
            $this->error('[FAIL] Header menu labels/links must be valid JSON arrays.');
            return false;
        }

        if (count($labels) !== count($links)) {
            $this->error('[FAIL] Header menu labels and links have different item counts.');
            return false;
        }

        $blogIndex = collect($labels)->search(function ($label) {
            return strtolower(trim((string) $label)) === 'blog';
        });

        $hasBlogLink = $blogIndex !== false && ($links[$blogIndex] ?? null) === '/blog';

        if (!$hasBlogLink && $this->option('require-blog-navigation')) {
            $this->error('[FAIL] Public Blog header navigation is missing or does not point to /blog.');
            return false;
        }

        if (!$hasBlogLink) {
            $this->warn('[WARN] Public Blog header navigation is missing. Run BlogNavigationSeeder if this release includes blog access.');
            return true;
        }

        $this->info('[OK] Public Blog header navigation points to /blog.');
        return true;
    }

    private function checkDefaultCurrency(): bool
    {
        if (!Schema::hasTable('currencies')) {
            $this->error('[FAIL] currencies table is missing.');
            return false;
        }

        $currencyId = get_setting('system_default_currency');
        $currency = $currencyId ? Currency::find($currencyId) : null;

        if (!$currency) {
            $this->error('[FAIL] system_default_currency does not point to an existing currency.');
            return false;
        }

        $this->info("[OK] Default currency: {$currency->code}");
        return true;
    }

    private function decodeBusinessSettingArray(string $type): ?array
    {
        $value = get_setting($type);
        $decoded = json_decode($value ?: '[]', true);

        return is_array($decoded) ? $decoded : null;
    }

    private function checkMigrations(): bool
    {
        try {
            Artisan::call('migrate:status');
            $output = Artisan::output();
        } catch (Throwable $exception) {
            $this->error('[FAIL] Unable to read migration status: '.$exception->getMessage());
            return false;
        }

        if (str_contains($output, 'Pending') && !$this->option('allow-pending-migrations')) {
            $this->error('[FAIL] Pending migrations detected. Run migrations on a backed-up database first.');
            return false;
        }

        if (str_contains($output, 'Pending')) {
            $this->warn('[WARN] Pending migrations detected. Allowed by --allow-pending-migrations.');
            return true;
        }

        $this->info('[OK] No pending migrations.');
        return true;
    }

    private function checkRedis(): bool
    {
        $redisRequired = $this->option('require-redis')
            || config('queue.default') === 'redis'
            || config('cache.default') === 'redis'
            || config('session.driver') === 'redis';

        if (!$redisRequired) {
            $this->warn('[WARN] Redis check skipped because queue/cache/session are not configured for Redis.');
            return true;
        }

        try {
            Redis::connection()->ping();
            $this->info('[OK] Redis is reachable.');
            return true;
        } catch (Throwable $exception) {
            $this->error('[FAIL] Redis is required but unreachable: '.$exception->getMessage());
            return false;
        }
    }
}
