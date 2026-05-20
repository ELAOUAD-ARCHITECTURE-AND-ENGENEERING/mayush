<?php

namespace App\Services\Security;

use Illuminate\Http\UploadedFile;

/**
 * Interface for document security scanning.
 * 
 * Implementations should detect malicious content in uploaded files including:
 * - Double extension attacks
 * - Embedded scripts in images
 * - MIME type mismatches
 * - Suspicious file headers
 */
interface DocumentSecurityScannerInterface
{
    /**
     * Scan file for malicious content.
     * 
     * @param string $filePath Path to the file to scan
     * @return SecurityScanResult Result of the security scan
     */
    public function scan(string $filePath): SecurityScanResult;

    /**
     * Check for embedded scripts in images.
     * 
     * @param string $filePath Path to the file to check
     * @return bool True if embedded scripts are detected
     */
    public function hasEmbeddedScripts(string $filePath): bool;

    /**
     * Validate file extension matches content.
     * 
     * @param UploadedFile $file The uploaded file to validate
     * @return bool True if extension matches content
     */
    public function extensionMatchesContent(UploadedFile $file): bool;
}
