<?php

namespace App\Console\Commands;

use App\Models\SellerDocument;
use Illuminate\Console\Command;
use Illuminate\Support\Facades\File;
use Illuminate\Support\Facades\Storage;

class CleanupLegacySellerDocuments extends Command
{
    protected $signature = 'seller-documents:cleanup-legacy
        {--dry-run : Report verified cleanup candidates without deleting anything}
        {--confirm= : Must be DELETE_LEGACY_SELLER_DOCUMENTS to permit deletion}';

    protected $description = 'Remove legacy public seller-document copies after private storage has been verified';

    public function handle(): int
    {
        $documents = SellerDocument::query()
            ->whereNotNull('legacy_file_path')
            ->get()
            ->unique('legacy_file_path');

        $candidates = 0;
        $failures = 0;
        $confirmed = $this->option('confirm') === 'DELETE_LEGACY_SELLER_DOCUMENTS';
        $dryRun = (bool) $this->option('dry-run');

        foreach ($documents as $document) {
            $source = $this->safePublicFilePath($document->legacy_file_path);
            $privatePath = $document->safeStoragePath();
            $privateExists = $privatePath !== null
                && Storage::disk('seller_documents')->exists($privatePath);

            if ($source === null || !$privateExists) {
                $this->warn("Skipped document {$document->id}: private copy or safe legacy source was not verified.");
                continue;
            }

            $privateRealPath = Storage::disk('seller_documents')->path($privatePath);
            $sourceSize = (int) @filesize($source);
            $privateSize = (int) @filesize($privateRealPath);
            $sourceHash = is_file($source) ? hash_file('sha256', $source) : null;
            $privateHash = is_file($privateRealPath) ? hash_file('sha256', $privateRealPath) : null;

            if ($sourceSize !== $privateSize || $sourceHash === false || $sourceHash !== $privateHash) {
                $failures++;
                $this->error("Skipped document {$document->id}: legacy and private copies failed size or SHA-256 verification.");
                continue;
            }

            $candidates++;
            $this->line("Verified cleanup candidate: document {$document->id} ({$document->legacy_file_path})");

            if ($dryRun || !$confirmed) {
                continue;
            }

            if (!File::delete($source)) {
                $failures++;
                $this->error("Could not remove legacy source for document {$document->id}.");
                continue;
            }

            $this->info("Removed legacy source for document {$document->id}.");
        }

        if (!$confirmed && !$dryRun && $candidates > 0) {
            $this->warn('No files were deleted. Re-run with --dry-run or the exact --confirm=DELETE_LEGACY_SELLER_DOCUMENTS option after approval.');
        }

        if ($failures > 0) {
            return self::FAILURE;
        }

        $this->info("Verified cleanup candidates: {$candidates}.");
        return self::SUCCESS;
    }

    private function safePublicFilePath(?string $relativePath): ?string
    {
        $path = str_replace('\\', '/', trim((string) $relativePath));

        if ($path === '' || str_starts_with($path, '/')
            || preg_match('/^[A-Za-z]:\//', $path)
            || preg_match('/[\x00-\x1F\x7F]/', $path)
            || in_array('..', explode('/', ltrim($path, '/')), true)) {
            return null;
        }

        $publicRoot = realpath(public_path());
        $sourceRealPath = realpath(public_path(ltrim($path, '/')));

        if ($publicRoot === false || $sourceRealPath === false || !File::isFile($sourceRealPath)) {
            return null;
        }

        $rootPrefix = strtolower(rtrim($publicRoot, DIRECTORY_SEPARATOR) . DIRECTORY_SEPARATOR);
        if (!str_starts_with(strtolower($sourceRealPath), $rootPrefix)) {
            return null;
        }

        return $sourceRealPath;
    }
}
