<?php

declare(strict_types=1);

const SHARD_COUNT = 8;

$projectRoot = dirname(__DIR__);
$testsRoot = $projectRoot.DIRECTORY_SEPARATOR.'tests';

if (!is_dir($testsRoot)) {
    fwrite(STDERR, "Tests directory not found: {$testsRoot}".PHP_EOL);
    exit(2);
}

$testFiles = discoverTestFiles($testsRoot, $projectRoot);
if ($testFiles === []) {
    fwrite(STDERR, 'No tests/**/*Test.php files were found.'.PHP_EOL);
    exit(2);
}

$shards = array_fill(1, SHARD_COUNT, []);
foreach ($testFiles as $index => $testFile) {
    $shards[($index % SHARD_COUNT) + 1][] = $testFile;
}

verifyManifest($testFiles, $shards);

$selection = parseSelection($argv);
$selectedShardNumbers = $selection === 'all'
    ? range(1, SHARD_COUNT)
    : [$selection];

$manifestHash = hash('sha256', implode("\n", $testFiles));
$totals = [
    'files' => 0,
    'tests' => 0,
    'assertions' => 0,
    'failures' => 0,
    'errors' => 0,
    'skipped' => 0,
    'failed_shards' => [],
];

foreach ($selectedShardNumbers as $shardNumber) {
    $files = $shards[$shardNumber];
    fwrite(STDOUT, PHP_EOL."=== Laravel test shard {$shardNumber}/".SHARD_COUNT.' ('.count($files).' files) ==='.PHP_EOL);

    $result = runShard($projectRoot, $shardNumber, $files);
    $totals['files'] += count($files);
    foreach (['tests', 'assertions', 'failures', 'errors', 'skipped'] as $metric) {
        $totals[$metric] += $result[$metric];
    }
    if ($result['exit_code'] !== 0) {
        $totals['failed_shards'][] = $shardNumber;
    }

    fwrite(
        STDOUT,
        "Shard {$shardNumber}/".SHARD_COUNT." summary: ".count($files)." files, {$result['tests']} tests, "
        ."{$result['assertions']} assertions, exit {$result['exit_code']}".PHP_EOL
    );
}

$selectedLabel = $selection === 'all' ? 'all 8 shards' : "shard {$selection}/".SHARD_COUNT;
fwrite(STDOUT, PHP_EOL.'=== Canonical Laravel suite summary ==='.PHP_EOL);
fwrite(STDOUT, "Selection: {$selectedLabel}".PHP_EOL);
fwrite(STDOUT, "Manifest: {$manifestHash}".PHP_EOL);
fwrite(STDOUT, "Files: {$totals['files']} / ".count($testFiles).PHP_EOL);
fwrite(STDOUT, "Tests: {$totals['tests']}".PHP_EOL);
fwrite(STDOUT, "Assertions: {$totals['assertions']}".PHP_EOL);
fwrite(STDOUT, "Failures: {$totals['failures']}; Errors: {$totals['errors']}; Skipped: {$totals['skipped']}".PHP_EOL);

if ($totals['failed_shards'] !== []) {
    fwrite(STDERR, 'Failed shards: '.implode(', ', $totals['failed_shards']).PHP_EOL);
    exit(1);
}

exit(0);

/**
 * @return list<string>
 */
function discoverTestFiles(string $testsRoot, string $projectRoot): array
{
    $filesByCanonicalPath = [];
    $iterator = new RecursiveIteratorIterator(
        new RecursiveDirectoryIterator($testsRoot, FilesystemIterator::SKIP_DOTS)
    );

    foreach ($iterator as $fileInfo) {
        if (!$fileInfo->isFile() || !str_ends_with($fileInfo->getFilename(), 'Test.php')) {
            continue;
        }

        $realPath = $fileInfo->getRealPath();
        if ($realPath === false) {
            throw new RuntimeException('Unable to resolve test path: '.$fileInfo->getPathname());
        }

        $relativePath = substr($realPath, strlen($projectRoot) + 1);
        $relativePath = str_replace(DIRECTORY_SEPARATOR, '/', $relativePath);
        $filesByCanonicalPath[strtolower(str_replace('\\', '/', $realPath))] = $relativePath;
    }

    $files = array_values($filesByCanonicalPath);
    sort($files, SORT_STRING);

    return $files;
}

/**
 * @param list<string> $manifest
 * @param array<int, list<string>> $shards
 */
function verifyManifest(array $manifest, array $shards): void
{
    $assigned = [];
    foreach ($shards as $files) {
        foreach ($files as $file) {
            if (isset($assigned[$file])) {
                throw new RuntimeException("Test file assigned more than once: {$file}");
            }
            $assigned[$file] = true;
        }
    }

    $assignedFiles = array_keys($assigned);
    sort($assignedFiles, SORT_STRING);
    if ($assignedFiles !== $manifest) {
        throw new RuntimeException('Shard manifest is incomplete or contains an unexpected file.');
    }
}

/**
 * @return int|'all'
 */
function parseSelection(array $arguments): int|string
{
    $options = array_slice($arguments, 1);
    if ($options === ['--all']) {
        return 'all';
    }

    if (count($options) === 1 && preg_match('/^--shard=([1-8])\/8$/', $options[0], $matches) === 1) {
        return (int) $matches[1];
    }

    fwrite(STDERR, 'Usage: php scripts/run-test-shards.php --all | --shard=N/8'.PHP_EOL);
    exit(2);
}

/**
 * @param list<string> $files
 * @return array{exit_code:int,tests:int,assertions:int,failures:int,errors:int,skipped:int}
 */
function runShard(string $projectRoot, int $shardNumber, array $files): array
{
    $junitPath = tempnam(sys_get_temp_dir(), "mayush-shard-{$shardNumber}-");
    if ($junitPath === false) {
        throw new RuntimeException('Unable to allocate the temporary JUnit report.');
    }

    $command = [
        PHP_BINARY,
        'artisan',
        'test',
        '--without-tty',
        '--log-junit='.$junitPath,
        ...$files,
    ];
    $descriptors = [
        0 => ['file', 'php://stdin', 'r'],
        1 => ['file', 'php://stdout', 'a'],
        2 => ['file', 'php://stderr', 'a'],
    ];

    $process = proc_open($command, $descriptors, $pipes, $projectRoot, null, ['bypass_shell' => true]);
    if (!is_resource($process)) {
        @unlink($junitPath);
        throw new RuntimeException("Unable to start Laravel test shard {$shardNumber}.");
    }

    $exitCode = proc_close($process);
    $metrics = readJunitMetrics($junitPath);
    @unlink($junitPath);

    return ['exit_code' => $exitCode, ...$metrics];
}

/**
 * @return array{tests:int,assertions:int,failures:int,errors:int,skipped:int}
 */
function readJunitMetrics(string $junitPath): array
{
    $empty = ['tests' => 0, 'assertions' => 0, 'failures' => 0, 'errors' => 0, 'skipped' => 0];
    if (!is_file($junitPath) || filesize($junitPath) === 0 || !function_exists('simplexml_load_file')) {
        return $empty;
    }

    $xml = @simplexml_load_file($junitPath);
    if ($xml === false) {
        return $empty;
    }

    $topLevelSuites = $xml->xpath('/testsuites/testsuite');
    if ($topLevelSuites === false || $topLevelSuites === []) {
        $topLevelSuites = [$xml];
    }

    $metrics = $empty;
    foreach ($topLevelSuites as $suite) {
        $attributes = $suite->attributes();
        foreach (array_keys($metrics) as $metric) {
            $metrics[$metric] += (int) ($attributes[$metric] ?? 0);
        }
    }

    return $metrics;
}
