<?php

namespace App\Console\Commands;

use App\Models\SellerDocument;
use App\Models\Shop;
use Illuminate\Console\Command;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\File;
use Illuminate\Support\Facades\Storage;
use Illuminate\Support\Str;
use JsonException;
use RuntimeException;

class MigrateLegacySellerVerification extends Command
{
    protected $signature = 'seller-onboarding:migrate-legacy-verification
        {--dry-run : Inspect and report migrations without writing files or database records}
        {--commit : Copy verified legacy files and create pending SellerDocument records}';

    protected $description = 'Migrate legacy seller verification files to private onboarding storage';

    private const ALLOWED_MIME_TYPES = [
        'application/pdf' => 'pdf',
        'image/jpeg' => 'jpg',
        'image/png' => 'png',
    ];

    public function handle(): int
    {
        $dryRun = (bool) $this->option('dry-run');
        $commit = (bool) $this->option('commit');

        if ($dryRun && $commit) {
            $this->error('Choose either --dry-run or --commit, not both.');
            return self::INVALID;
        }

        if (!$dryRun && !$commit) {
            $dryRun = true;
            $this->warn('No mode selected; running a dry-run. Use --commit only after reviewing the report.');
        }

        $stats = [
            'shops' => 0,
            'files' => 0,
            'migrated' => 0,
            'already_migrated' => 0,
            'unresolved' => 0,
        ];

        Shop::query()
            ->where(function ($query) {
                $query->whereNotNull('verification_info')
                    ->orWhereNotNull('business_info');
            })
            ->chunkById(50, function ($shops) use (&$stats, $commit) {
                foreach ($shops as $shop) {
                    $stats['shops']++;
                    $this->processShop($shop, $stats, $commit);
                }
            });

        $this->newLine();
        $this->line('Legacy seller verification migration report');
        foreach ($stats as $label => $value) {
            $this->line(str_pad(str_replace('_', ' ', ucfirst($label)), 20) . $value);
        }

        if ($stats['unresolved'] > 0) {
            $this->error('Migration completed with unresolved records. Resolve them before cleanup.');
            return self::FAILURE;
        }

        $this->info($commit ? 'Private migration completed successfully.' : 'Dry-run completed successfully. No files or records were changed.');
        return self::SUCCESS;
    }

    private function processShop(Shop $shop, array &$stats, bool $commit): void
    {
        try {
            $entries = $this->legacyFileEntries($shop);
        } catch (RuntimeException $e) {
            $stats['unresolved']++;
            $this->error("Shop {$shop->id}: {$e->getMessage()}");
            return;
        }

        foreach ($entries as $index => $entry) {
            $stats['files']++;
            $legacyPath = $this->normalizeRelativePath((string) ($entry['value'] ?? ''));

            if (SellerDocument::query()
                ->where('shop_id', $shop->id)
                ->where('legacy_file_path', $legacyPath)
                ->exists()) {
                $stats['already_migrated']++;
                $this->line("Shop {$shop->id}: already migrated {$legacyPath}");
                continue;
            }

            $source = $this->safePublicFilePath($legacyPath);
            if ($source === null) {
                $stats['unresolved']++;
                $this->error("Shop {$shop->id}: unsafe or missing legacy file {$legacyPath}");
                continue;
            }

            $metadata = $this->fileMetadata($source);
            if ($metadata === null) {
                $stats['unresolved']++;
                $this->error("Shop {$shop->id}: unsupported or invalid file {$legacyPath}");
                continue;
            }

            $documentType = $this->documentType($shop, $entry, $index);
            $target = $this->privateTarget($shop, $legacyPath, $metadata['extension']);
            $uploadedAt = date('Y-m-d H:i:s', (int) (@filemtime($source) ?: time()));

            if (!$commit) {
                $this->line("Would migrate shop {$shop->id}: {$legacyPath} => {$documentType}");
                $stats['migrated']++;
                continue;
            }

            try {
                $this->copyAndVerify($source, $target, $metadata);

                DB::transaction(function () use ($shop, $entry, $legacyPath, $target, $metadata, $documentType, $uploadedAt) {
                    if (SellerDocument::query()
                        ->where('shop_id', $shop->id)
                        ->where('legacy_file_path', $legacyPath)
                        ->exists()) {
                        return;
                    }

                    SellerDocument::create([
                        'shop_id' => $shop->id,
                        'document_type' => $documentType,
                        'file_path' => $target,
                        'legacy_file_path' => $legacyPath,
                        'original_name' => $this->safeOriginalName($entry['value'] ?? 'legacy-document.' . $metadata['extension']),
                        'mime_type' => $metadata['mime_type'],
                        'file_size' => $metadata['size'],
                        'uploaded_at' => $uploadedAt,
                        'status' => 'pending',
                        'version' => 1,
                    ]);
                });

                $stats['migrated']++;
                $this->info("Migrated shop {$shop->id}: {$legacyPath} => {$documentType}");
            } catch (\Throwable $e) {
                $stats['unresolved']++;
                $this->error("Shop {$shop->id}: failed to migrate {$legacyPath}: {$e->getMessage()}");
            }
        }
    }

    private function legacyFileEntries(Shop $shop): array
    {
        $entries = [];
        $raw = $shop->getRawOriginal('verification_info');
        if (filled($raw)) {
            $payload = $shop->verification_info;
            if ($payload === null) {
                // Older deployments may contain plain JSON instead of encrypted JSON.
                $payload = $raw;
            }

            $decoded = $this->decodePayload($payload, 'verification_info');
            foreach ($decoded as $entry) {
                if (is_array($entry) && ($entry['type'] ?? null) === 'file' && filled($entry['value'] ?? null)) {
                    $entries[] = $entry + ['source' => 'verification_info'];
                }
            }
        }

        // Older seller profile forms stored additional verification files in
        // encrypted business_info. Treat them as legacy artifacts as well so
        // they do not remain publicly addressable after the old form is retired.
        $businessRaw = $shop->getRawOriginal('business_info');
        if (filled($businessRaw)) {
            $businessPayload = $shop->business_info;
            if ($businessPayload === null) {
                $businessPayload = $businessRaw;
            }

            $businessInfo = $this->decodePayload($businessPayload, 'business_info');
            foreach (['certificate', 'gstin_certificate', 'id_card', 'seller_photo', 'seller_selfie'] as $field) {
                if (filled($businessInfo[$field] ?? null)) {
                    $entries[] = [
                        'type' => 'file',
                        'label' => 'business_info ' . $field,
                        'value' => $businessInfo[$field],
                        'source' => 'business_info',
                    ];
                }
            }
        }

        return $entries;
    }

    private function decodePayload(mixed $payload, string $field): array
    {
        if (is_array($payload)) {
            $decoded = $payload;
        } else {
            try {
                $decoded = json_decode((string) $payload, true, 512, JSON_THROW_ON_ERROR);
            } catch (JsonException $e) {
                throw new RuntimeException($field . ' is not readable JSON.');
            }
        }

        if (!is_array($decoded)) {
            throw new RuntimeException($field . ' does not contain a document list.');
        }

        return $decoded;
    }

    private function documentType(Shop $shop, array $entry, int $index): string
    {
        $label = strtolower(trim((string) (($entry['label'] ?? '') . ' ' . ($entry['value'] ?? ''))));
        $mapped = match (true) {
            str_contains($label, 'contract') => 'contract',
            str_contains($label, 'government'), str_contains($label, 'national id'), str_contains($label, 'passport'), str_contains($label, 'driver') => 'government_id',
            str_contains($label, 'business'), str_contains($label, 'registration'), str_contains($label, 'tax') => 'business_registration',
            str_contains($label, 'certification'), str_contains($label, 'professional'), str_contains($label, 'license') => 'certification',
            default => 'legacy_supporting_document_' . ($index + 1),
        };

        if (array_key_exists($mapped, SellerDocument::$types)
            && $shop->documents()->where('document_type', $mapped)->exists()) {
            return 'legacy_archived_' . $mapped . '_' . ($index + 1);
        }

        return $mapped;
    }

    private function privateTarget(Shop $shop, string $legacyPath, string $extension): string
    {
        return 'legacy/' . $shop->id . '/' . sha1($legacyPath) . '.' . $extension;
    }

    private function copyAndVerify(string $source, string $target, array $metadata): void
    {
        $disk = Storage::disk('seller_documents');

        if (!$disk->exists($target)) {
            $stream = fopen($source, 'rb');
            if ($stream === false || !$disk->put($target, $stream)) {
                if (is_resource($stream)) {
                    fclose($stream);
                }
                throw new RuntimeException('Private copy could not be written.');
            }
            fclose($stream);
        }

        $privatePath = $disk->path($target);
        if (!is_file($privatePath)
            || (int) filesize($privatePath) !== $metadata['size']
            || hash_file('sha256', $privatePath) !== $metadata['sha256']) {
            throw new RuntimeException('Private copy failed size or SHA-256 verification.');
        }
    }

    private function fileMetadata(string $source): ?array
    {
        $mimeType = (new \finfo(FILEINFO_MIME_TYPE))->file($source);
        $extension = self::ALLOWED_MIME_TYPES[$mimeType] ?? null;
        $actualExtension = strtolower(pathinfo($source, PATHINFO_EXTENSION));

        if ($extension === null || !in_array($actualExtension, $extension === 'jpg' ? ['jpg', 'jpeg'] : [$extension], true)) {
            return null;
        }

        return [
            'mime_type' => $mimeType,
            'extension' => $extension,
            'size' => (int) filesize($source),
            'sha256' => hash_file('sha256', $source),
        ];
    }

    private function safePublicFilePath(string $relativePath): ?string
    {
        $path = $this->normalizeRelativePath($relativePath);
        if ($path === '' || preg_match('/^[A-Za-z]:\//', $path) || in_array('..', explode('/', $path), true)) {
            return null;
        }

        $publicRoot = realpath(public_path());
        $sourceRealPath = realpath(public_path($path));

        if ($publicRoot === false || $sourceRealPath === false || !File::isFile($sourceRealPath)) {
            return null;
        }

        $rootPrefix = strtolower(rtrim($publicRoot, DIRECTORY_SEPARATOR) . DIRECTORY_SEPARATOR);
        return str_starts_with(strtolower($sourceRealPath), $rootPrefix) ? $sourceRealPath : null;
    }

    private function normalizeRelativePath(string $path): string
    {
        $path = str_replace('\\', '/', trim($path));

        // Preserve the leading separator long enough for callers to reject
        // absolute and UNC paths instead of silently converting them into a
        // public-relative path.
        if ($path === '' || str_starts_with($path, '/') || preg_match('/^[A-Za-z]:\//', $path)) {
            return $path;
        }

        return ltrim($path, '/');
    }

    private function safeOriginalName(string $path): string
    {
        return (string) Str::of(basename($path ?: 'legacy-document'))
            ->replaceMatches('/[^A-Za-z0-9._-]/', '_')
            ->limit(180, '');
    }
}
