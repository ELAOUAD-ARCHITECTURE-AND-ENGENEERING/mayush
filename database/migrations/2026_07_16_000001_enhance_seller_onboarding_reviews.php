<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\File;
use Illuminate\Support\Facades\Schema;
use Illuminate\Support\Facades\Storage;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('shops', function (Blueprint $table) {
            if (!Schema::hasColumn('shops', 'admin_note')) {
                $table->text('admin_note')->nullable()->after('rejection_reason');
            }
        });

        Schema::table('seller_documents', function (Blueprint $table) {
            if (!Schema::hasColumn('seller_documents', 'status')) {
                $table->string('status')->default('pending')->after('file_size');
            }
            if (!Schema::hasColumn('seller_documents', 'rejection_reason')) {
                $table->text('rejection_reason')->nullable()->after('status');
            }
            if (!Schema::hasColumn('seller_documents', 'reviewed_at')) {
                $table->timestamp('reviewed_at')->nullable()->after('rejection_reason');
            }
            if (!Schema::hasColumn('seller_documents', 'reviewed_by')) {
                $table->integer('reviewed_by')->nullable()->after('reviewed_at');
            }
            if (!Schema::hasColumn('seller_documents', 'version')) {
                $table->unsignedInteger('version')->default(1)->after('reviewed_by');
            }
            if (!Schema::hasColumn('seller_documents', 'replaces_document_id')) {
                $table->integer('replaces_document_id')->nullable()->after('version');
            }
            if (!Schema::hasColumn('seller_documents', 'legacy_file_path')) {
                $table->string('legacy_file_path')->nullable()->after('file_path');
            }
        });

        DB::table('seller_documents')->whereNull('status')->update(['status' => 'pending']);
        DB::table('seller_documents')->whereNull('version')->update(['version' => 1]);

        // Legacy uploads were saved below public/. Copy any files that still
        // exist there to the private disk while preserving the original path.
        // Cleanup is deliberately separate and requires an explicit operator
        // confirmation after the private copy has been verified.
        foreach (DB::table('seller_documents')->get() as $document) {
            $this->migrateLegacyDocument($document);
        }
    }

    private function migrateLegacyDocument(object $document): void
    {
        $path = str_replace('\\', '/', trim((string) $document->file_path));
        $path = ltrim($path, '/');

        if ($path === '' || preg_match('/^[A-Za-z]:\//', $path) || in_array('..', explode('/', $path), true)) {
            return;
        }

        $publicRoot = realpath(public_path());
        if ($publicRoot === false) {
            throw new \RuntimeException('The public storage root could not be resolved.');
        }

        $source = public_path($path);
        $sourceRealPath = realpath($source);
        $sourceIsInsidePublicRoot = $sourceRealPath !== false
            && str_starts_with(
                strtolower($sourceRealPath),
                strtolower(rtrim($publicRoot, DIRECTORY_SEPARATOR) . DIRECTORY_SEPARATOR)
            );

        if ($sourceRealPath !== false && (!$sourceIsInsidePublicRoot || !File::isFile($sourceRealPath))) {
            return;
        }

        $filename = preg_replace('/[^A-Za-z0-9._-]/', '_', basename($path)) ?: 'document';
        $target = 'migrated/' . (int) $document->id . '-' . sha1($path) . '-' . $filename;
        $disk = Storage::disk('seller_documents');

        // If a previous attempt copied the file, finish the database update
        // without copying it again.
        if (!$disk->exists($target)) {
            if ($sourceRealPath === false) {
                return;
            }

            $disk->put($target, File::get($sourceRealPath));
        }

        if (!$disk->exists($target)) {
            throw new \RuntimeException("Private seller document copy was not verified: {$target}");
        }

        DB::table('seller_documents')
            ->where('id', $document->id)
            ->update([
                'file_path' => $target,
                'legacy_file_path' => $path,
            ]);
    }

    public function down(): void
    {
        Schema::table('seller_documents', function (Blueprint $table) {
            $columns = ['status', 'rejection_reason', 'reviewed_at', 'reviewed_by', 'version', 'replaces_document_id', 'legacy_file_path'];
            foreach ($columns as $column) {
                if (Schema::hasColumn('seller_documents', $column)) {
                    $table->dropColumn($column);
                }
            }
        });

        Schema::table('shops', function (Blueprint $table) {
            if (Schema::hasColumn('shops', 'admin_note')) {
                $table->dropColumn('admin_note');
            }
        });
    }
};
