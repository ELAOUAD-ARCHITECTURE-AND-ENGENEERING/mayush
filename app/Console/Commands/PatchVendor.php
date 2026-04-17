<?php

namespace App\Console\Commands;

use Illuminate\Console\Command;
use Illuminate\Support\Facades\Log;

/**
 * PatchVendor — Resilient Vendor File Patching
 *
 * Automatically re-applies critical patches to vendor files that would
 * otherwise be lost on `composer update` or `composer install`.
 *
 * Currently patches:
 *  - CoreComponentRepository: Disables aggressive redirect()->send() calls
 *    that deadlock the admin dashboard navigation.
 */
class PatchVendor extends Command
{
    protected $signature = 'app:patch-vendor {--dry-run : Show what would be changed without modifying files}';

    protected $description = 'Apply critical patches to vendor files to prevent admin dashboard deadlocks';

    /**
     * The patches to apply. Each patch targets a specific file and defines
     * search/replace pairs.
     */
    protected array $patches = [
        [
            'file' => 'vendor/mehedi-iitdu/core-component-repository/src/CoreComponentRepository.php',
            'label' => 'CoreComponentRepository: Disable activation redirect in finalizeRepository()',
            'search' => "return redirect('https://activeitzone.com/activation/')->send();",
            'replace' => "// return redirect('https://activeitzone.com/activation/')->send(); // PATCHED by app:patch-vendor",
        ],
        [
            'file' => 'vendor/mehedi-iitdu/core-component-repository/src/CoreComponentRepository.php',
            'label' => 'CoreComponentRepository: Disable addon redirect in finalizeCache()',
            'search' => "return redirect()->route('addons.index')->send();",
            'replace' => "// return redirect()->route('addons.index')->send(); // PATCHED by app:patch-vendor",
        ],
    ];

    public function handle(): int
    {
        $dryRun = $this->option('dry-run');
        $applied = 0;
        $skipped = 0;
        $failed = 0;

        $this->info('');
        $this->info('╔══════════════════════════════════════════════╗');
        $this->info('║     Mayush Vendor Patch System v1.0          ║');
        $this->info('╚══════════════════════════════════════════════╝');
        $this->info('');

        if ($dryRun) {
            $this->warn('  [DRY RUN] No files will be modified.');
            $this->info('');
        }

        foreach ($this->patches as $index => $patch) {
            $filePath = base_path($patch['file']);
            $label = $patch['label'];

            $this->line("  [{$index}] {$label}");

            // Check if file exists
            if (!file_exists($filePath)) {
                $this->warn("      ⚠ File not found: {$patch['file']}");
                $failed++;
                continue;
            }

            $contents = file_get_contents($filePath);

            // Check if already patched (the replacement text is already present)
            if (str_contains($contents, $patch['replace'])) {
                $this->info("      ✓ Already patched. Skipping.");
                $skipped++;
                continue;
            }

            // Check if the original (unpatched) line exists
            if (!str_contains($contents, $patch['search'])) {
                $this->warn("      ⚠ Target code not found. File may have been updated upstream.");
                $failed++;
                continue;
            }

            // Apply the patch
            if (!$dryRun) {
                $patched = str_replace($patch['search'], $patch['replace'], $contents);
                file_put_contents($filePath, $patched);
                $this->info("      ✓ Patch applied successfully.");

                Log::info("[PatchVendor] Applied patch: {$label}");
            } else {
                $this->info("      → Would apply patch.");
            }

            $applied++;
        }

        $this->info('');
        $this->info("  Summary: {$applied} applied, {$skipped} already patched, {$failed} failed.");
        $this->info('');

        if ($failed > 0) {
            $this->error('  Some patches could not be applied. Review the output above.');
            return Command::FAILURE;
        }

        return Command::SUCCESS;
    }
}
