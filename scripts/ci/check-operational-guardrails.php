<?php

$root = dirname(__DIR__, 2);
$failed = false;

function fail(string $message): void
{
    global $failed;
    $failed = true;
    fwrite(STDERR, "[FAIL] {$message}\n");
}

function pass_check(string $message): void
{
    fwrite(STDOUT, "[OK] {$message}\n");
}

function relative_path(string $path): string
{
    return str_replace('\\', '/', $path);
}

function read_file_required(string $path): string
{
    global $root;

    $absolute = $root.DIRECTORY_SEPARATOR.str_replace('/', DIRECTORY_SEPARATOR, $path);
    if (!is_file($absolute)) {
        fail("Required guardrail file is missing: {$path}");
        return '';
    }

    pass_check("Required file exists: {$path}");
    return (string) file_get_contents($absolute);
}

function contains_all(string $path, string $contents, array $needles): void
{
    foreach ($needles as $needle) {
        if (!str_contains($contents, $needle)) {
            fail("{$path} is missing required guardrail token: {$needle}");
        }
    }
}

function scan_forbidden_patterns(array $patterns): void
{
    global $root;

    $rii = new RecursiveIteratorIterator(
        new RecursiveDirectoryIterator($root, FilesystemIterator::SKIP_DOTS)
    );

    $allowedPrefixes = [
        'app/Http/Controllers/',
        'resources/views/',
    ];

    foreach ($rii as $file) {
        if (!$file->isFile()) {
            continue;
        }

        $path = relative_path(substr($file->getPathname(), strlen($root) + 1));

        $shouldScan = false;
        foreach ($allowedPrefixes as $prefix) {
            if (str_starts_with($path, $prefix)) {
                $shouldScan = true;
                break;
            }
        }

        if (!$shouldScan) {
            continue;
        }

        $contents = (string) file_get_contents($file->getPathname());
        foreach ($patterns as $pattern => $reason) {
            if (str_contains($contents, $pattern)) {
                fail("Forbidden pattern found in {$path}: {$reason}");
            }
        }
    }
}

function check_destructive_diff(): void
{
    global $root;

    $base = getenv('GUARDRAIL_BASE') ?: '';
    $head = getenv('GUARDRAIL_HEAD') ?: 'HEAD';

    if ($base === '' || preg_match('/^0+$/', $base)) {
        pass_check('No diff base provided; skipped destructive-change diff check.');
        return;
    }

    $command = 'git diff --name-status --find-renames '.escapeshellarg($base).' '.escapeshellarg($head).' --';
    $lines = [];
    $code = 0;
    exec($command, $lines, $code);

    if ($code !== 0) {
        fail("Unable to inspect git diff from {$base} to {$head}.");
        return;
    }

    $protectedDeletes = [
        '.github/workflows/deploy.yml',
        '.github/workflows/quality-gates.yml',
        '.github/workflows/restoration-guardrails.yml',
        'app/Console/Commands/PreflightRestoreGuard.php',
        'composer.json',
        'composer.lock',
        'package.json',
        'package-lock.json',
        'scripts/ci/check-operational-guardrails.php',
        'scripts/maintenance/backup-database.ps1',
        'scripts/maintenance/smoke-check.ps1',
        'docs/operational-safety-runbook.md',
        '.env.example',
    ];

    $protectedDeletePrefixes = [
        'database/migrations/',
        'sqlupdates/',
        'routes/',
        'config/',
        'app/Http/Middleware/',
    ];

    $deleteCount = 0;
    foreach ($lines as $line) {
        $parts = preg_split('/\s+/', trim($line));
        if (!$parts || count($parts) < 2) {
            continue;
        }

        $status = $parts[0];
        $path = relative_path(end($parts));

        if (!str_starts_with($status, 'D')) {
            continue;
        }

        $deleteCount++;

        if (in_array($path, $protectedDeletes, true)) {
            fail("Protected file deletion detected: {$path}");
        }

        foreach ($protectedDeletePrefixes as $prefix) {
            if (str_starts_with($path, $prefix)) {
                fail("Protected path deletion detected: {$path}");
            }
        }
    }

    if ($deleteCount > 25 && getenv('ALLOW_DESTRUCTIVE_CHANGES') !== 'true') {
        fail("Mass deletion detected: {$deleteCount} deleted files. Set ALLOW_DESTRUCTIVE_CHANGES=true only for an approved emergency.");
    }

    pass_check("Destructive-change diff check inspected {$deleteCount} deletions.");
}

chdir($root);

$deploy = read_file_required('.github/workflows/deploy.yml');
$restoration = read_file_required('.github/workflows/restoration-guardrails.yml');
$quality = read_file_required('.github/workflows/quality-gates.yml');
read_file_required('app/Console/Commands/PreflightRestoreGuard.php');
read_file_required('scripts/maintenance/backup-database.ps1');
read_file_required('scripts/maintenance/smoke-check.ps1');
read_file_required('docs/operational-safety-runbook.md');

contains_all('.github/workflows/deploy.yml', $deploy, [
    'mysqldump --single-transaction --quick --routines --triggers',
    'php8.2 artisan app:preflight-restore --require-redis --allow-pending-migrations --no-ansi',
    'php8.2 artisan db:seed --class=BlogNavigationSeeder --force',
    'php8.2 artisan app:preflight-restore --require-redis --require-blog-navigation --no-ansi',
]);

contains_all('.github/workflows/restoration-guardrails.yml', $restoration, [
    'php scripts/ci/check-operational-guardrails.php',
    'php artisan app:preflight-restore --skip-db --no-ansi',
]);

contains_all('.github/workflows/quality-gates.yml', $quality, [
    'composer validate --no-check-publish',
    'composer audit --locked --format=json',
    'npm audit --omit=dev --audit-level=high',
]);

scan_forbidden_patterns([
    "include('header.' .get_element_type_by_id(get_setting('header_element')))" => 'Use safe_header_view() instead.',
    "auth.'.get_setting('authentication_layout_select')" => 'Use safe_auth_layout_select() instead.',
    "frontend.' . get_setting('homepage_select')" => 'Use safe_homepage_select() instead.',
]);

check_destructive_diff();

if ($failed) {
    fwrite(STDERR, "Operational guardrails failed.\n");
    exit(1);
}

pass_check('Operational guardrails passed.');
