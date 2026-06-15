<?php

namespace App\Services\Security;

use Illuminate\Http\UploadedFile;
use Illuminate\Support\Facades\Log;

/**
 * Result of a security scan on an uploaded file.
 */
class SecurityScanResult
{
    public bool $isSecure;
    public array $threats;
    public ?string $message;

    public function __construct(bool $isSecure, array $threats = [], ?string $message = null)
    {
        $this->isSecure = $isSecure;
        $this->threats = $threats;
        $this->message = $message;
    }

    /**
     * Create a successful scan result (no threats found).
     */
    public static function secure(): self
    {
        return new self(true, [], 'File passed security scan.');
    }

    /**
     * Create a failed scan result (threats detected).
     */
    public static function insecure(array $threats, ?string $message = null): self
    {
        return new self(false, $threats, $message);
    }
}

/**
 * Document Security Scanner Service.
 * 
 * Scans uploaded files for malicious content including:
 * - Double extension attacks (e.g., .pdf.exe)
 * - Embedded scripts in image files
 * - MIME type mismatches between extension and content
 * - Suspicious file headers
 */
class DocumentSecurityScanner implements DocumentSecurityScannerInterface
{
    /**
     * Allowed MIME types for seller documents.
     */
    public const ALLOWED_MIME_TYPES = [
        'application/pdf',
        'image/jpeg',
        'image/png',
        'image/webp',
    ];

    /**
     * Maximum file size in bytes (10MB).
     */
    public const MAX_FILE_SIZE = 10485760;

    /**
     * Patterns that indicate embedded scripts in files.
     */
    protected const SCRIPT_PATTERNS = [
        // PHP tags
        '/<\?php/i',
        '/<\?=/i',
        '/<\?(?!xml)/i',  // PHP short open tag, but not XML declaration
        // JavaScript in suspicious contexts
        '/<script\b[^>]*>/i',
        '/javascript\s*:/i',
        '/on\w+\s*=/i',  // Event handlers like onclick=, onload=
        // Suspicious HTML that could execute
        '/<iframe\b/i',
        '/<object\b/i',
        '/<embed\b/i',
        '/<base\b/i',
    ];

    /**
     * Known file signatures (magic bytes) for MIME type verification.
     */
    protected const FILE_SIGNATURES = [
        'application/pdf' => [
            [0x25, 0x50, 0x44, 0x46], // %PDF
        ],
        'image/jpeg' => [
            [0xFF, 0xD8, 0xFF], // JPEG SOI marker
        ],
        'image/png' => [
            [0x89, 0x50, 0x4E, 0x47, 0x0D, 0x0A, 0x1A, 0x0A], // PNG signature
        ],
        'image/webp' => [
            [0x52, 0x49, 0x46, 0x46], // RIFF (WebP container starts with RIFF)
        ],
    ];

    /**
     * Dangerous file extensions that should never be allowed.
     */
    protected const DANGEROUS_EXTENSIONS = [
        'php', 'php3', 'php4', 'php5', 'phtml', 'phar',
        'exe', 'bat', 'cmd', 'com', 'msi',
        'sh', 'bash', 'zsh', 'ksh',
        'py', 'pyc', 'pyo',
        'pl', 'pm',
        'rb',
        'js', 'jsx',
        'jar', 'war', 'ear',
        'asp', 'aspx', 'axd', 'ashx',
        'jsp', 'jspx',
        'cgi', 'dll',
    ];

    /**
     * Scan file for malicious content.
     * 
     * @param string $filePath Path to the file to scan
     * @return SecurityScanResult Result of the security scan
     */
    public function scan(string $filePath): SecurityScanResult
    {
        $threats = [];

        if (!file_exists($filePath)) {
            return SecurityScanResult::insecure(
                ['file_not_found'],
                'File does not exist.'
            );
        }

        // Check for embedded scripts
        if ($this->hasEmbeddedScripts($filePath)) {
            $threats[] = 'embedded_scripts';
        }

        // Check file content for suspicious patterns
        $suspiciousPatterns = $this->detectSuspiciousContent($filePath);
        if (!empty($suspiciousPatterns)) {
            $threats = array_merge($threats, $suspiciousPatterns);
        }

        if (!empty($threats)) {
            Log::warning('DocumentSecurityScanner: Threats detected', [
                'file' => $filePath,
                'threats' => $threats,
            ]);
            return SecurityScanResult::insecure(
                $threats,
                'File contains prohibited content.'
            );
        }

        return SecurityScanResult::secure();
    }

    /**
     * Check for embedded scripts in images.
     * 
     * @param string $filePath Path to the file to check
     * @return bool True if embedded scripts are detected
     */
    public function hasEmbeddedScripts(string $filePath): bool
    {
        if (!file_exists($filePath)) {
            return false;
        }

        $content = file_get_contents($filePath);
        if ($content === false) {
            Log::error('DocumentSecurityScanner: Could not read file', ['file' => $filePath]);
            return false;
        }

        foreach (self::SCRIPT_PATTERNS as $pattern) {
            if (preg_match($pattern, $content)) {
                Log::warning('DocumentSecurityScanner: Embedded script pattern detected', [
                    'file' => $filePath,
                    'pattern' => $pattern,
                ]);
                return true;
            }
        }

        return false;
    }

    /**
     * Validate file extension matches content.
     * 
     * @param UploadedFile $file The uploaded file to validate
     * @return bool True if extension matches content
     */
    public function extensionMatchesContent(UploadedFile $file): bool
    {
        $extension = strtolower($file->getClientOriginalExtension());
        $declaredMimeType = $file->getMimeType();
        $actualMimeType = $this->detectMimeType($file->getRealPath());

        // If we can't detect the actual MIME type, fail safe
        if ($actualMimeType === null) {
            Log::warning('DocumentSecurityScanner: Could not detect MIME type', [
                'file' => $file->getClientOriginalName(),
            ]);
            return false;
        }

        // Check if the actual MIME type matches what's expected for the extension
        $expectedMimeTypes = $this->getMimeTypesForExtension($extension);

        if (empty($expectedMimeTypes)) {
            // Unknown extension, check if declared MIME matches actual
            return $declaredMimeType === $actualMimeType;
        }

        // The actual MIME type must match one of the expected types for the extension
        $matchesExtension = in_array($actualMimeType, $expectedMimeTypes);
        $matchesDeclared = $declaredMimeType === $actualMimeType;

        if (!$matchesExtension || !$matchesDeclared) {
            Log::warning('DocumentSecurityScanner: MIME type mismatch detected', [
                'file' => $file->getClientOriginalName(),
                'extension' => $extension,
                'declared_mime' => $declaredMimeType,
                'actual_mime' => $actualMimeType,
                'expected_mimes' => $expectedMimeTypes,
            ]);
        }

        return $matchesExtension && $matchesDeclared;
    }

    /**
     * Check for double extension attacks.
     * 
     * @param string $filename The filename to check
     * @return bool True if double extension attack is detected
     */
    public function hasDoubleExtension(string $filename): bool
    {
        $filename = strtolower($filename);
        $parts = explode('.', $filename);

        // Remove empty parts (handles cases like "file..pdf")
        $parts = array_filter($parts, fn($p) => $p !== '');

        if (count($parts) < 2) {
            return false; // No extension at all
        }

        // Check for dangerous extensions anywhere in the filename
        foreach ($parts as $part) {
            if (in_array($part, self::DANGEROUS_EXTENSIONS)) {
                Log::warning('DocumentSecurityScanner: Dangerous extension detected', [
                    'filename' => $filename,
                    'dangerous_extension' => $part,
                ]);
                return true;
            }
        }

        // Check for double extension pattern: allowed.ext.dangerous
        // e.g., document.pdf.exe, image.jpg.php
        if (count($parts) >= 3) {
            // Get all extensions (everything after the base name)
            $extensions = array_slice($parts, 1);
            
            foreach ($extensions as $ext) {
                if (in_array($ext, self::DANGEROUS_EXTENSIONS)) {
                    Log::warning('DocumentSecurityScanner: Double extension attack detected', [
                        'filename' => $filename,
                        'extensions' => $extensions,
                    ]);
                    return true;
                }
            }
        }

        return false;
    }

    /**
     * Validate a file for all security concerns.
     * 
     * @param UploadedFile $file The file to validate
     * @return SecurityScanResult Complete security scan result
     */
    public function validateFile(UploadedFile $file): SecurityScanResult
    {
        $threats = [];
        $filename = $file->getClientOriginalName();

        // Check for double extension
        if ($this->hasDoubleExtension($filename)) {
            $threats[] = 'double_extension';
        }

        // Check file size
        if ($file->getSize() > self::MAX_FILE_SIZE) {
            $threats[] = 'file_too_large';
        }

        // Check MIME type
        $mimeType = $file->getMimeType();
        if (!in_array($mimeType, self::ALLOWED_MIME_TYPES)) {
            $threats[] = 'invalid_mime_type';
        }

        // Check extension matches content
        if (!$this->extensionMatchesContent($file)) {
            $threats[] = 'mime_type_mismatch';
        }

        // Check for embedded scripts
        $realPath = $file->getRealPath();
        if ($realPath && $this->hasEmbeddedScripts($realPath)) {
            $threats[] = 'embedded_scripts';
        }

        if (!empty($threats)) {
            Log::warning('DocumentSecurityScanner: File validation failed', [
                'filename' => $filename,
                'threats' => $threats,
            ]);

            return SecurityScanResult::insecure(
                $threats,
                $this->getThreatMessage($threats)
            );
        }

        return SecurityScanResult::secure();
    }

    /**
     * Detect the actual MIME type of a file based on its content (magic bytes).
     * 
     * @param string $filePath Path to the file
     * @return string|null The detected MIME type or null if unknown
     */
    protected function detectMimeType(string $filePath): ?string
    {
        if (!file_exists($filePath)) {
            return null;
        }

        $handle = fopen($filePath, 'rb');
        if ($handle === false) {
            return null;
        }

        $header = fread($handle, 16);
        fclose($handle);

        if ($header === false || strlen($header) < 4) {
            return null;
        }

        // Check PDF signature
        if (substr($header, 0, 4) === '%PDF') {
            return 'application/pdf';
        }

        // Check JPEG signature
        if (strlen($header) >= 3 && ord($header[0]) === 0xFF && ord($header[1]) === 0xD8 && ord($header[2]) === 0xFF) {
            return 'image/jpeg';
        }

        // Check PNG signature
        if (strlen($header) >= 8 &&
            ord($header[0]) === 0x89 && $header[1] === 'P' && $header[2] === 'N' && $header[3] === 'G') {
            return 'image/png';
        }

        // Check WebP (RIFF....WEBP)
        if (strlen($header) >= 12 &&
            substr($header, 0, 4) === 'RIFF' &&
            substr($header, 8, 4) === 'WEBP') {
            return 'image/webp';
        }

        return null;
    }

    /**
     * Get expected MIME types for a given file extension.
     * 
     * @param string $extension The file extension
     * @return array Array of expected MIME types
     */
    protected function getMimeTypesForExtension(string $extension): array
    {
        $map = [
            'pdf' => ['application/pdf'],
            'jpg' => ['image/jpeg'],
            'jpeg' => ['image/jpeg'],
            'png' => ['image/png'],
            'webp' => ['image/webp'],
        ];

        return $map[$extension] ?? [];
    }

    /**
     * Detect suspicious content patterns in a file.
     * 
     * @param string $filePath Path to the file
     * @return array Array of detected threat types
     */
    protected function detectSuspiciousContent(string $filePath): array
    {
        $threats = [];

        if (!file_exists($filePath)) {
            return $threats;
        }

        $content = file_get_contents($filePath);
        if ($content === false) {
            return $threats;
        }

        // Check for null bytes (often used to bypass extension checks)
        if (strpos($content, "\x00") !== false) {
            $threats[] = 'null_byte_injection';
        }

        // Check for polyglot file indicators (files valid as multiple formats)
        // A file that starts with both PDF and has PHP markers is suspicious
        $hasPdfHeader = substr($content, 0, 4) === '%PDF';
        $hasExecutableCode = preg_match('/<\?php|<\?=|<script/i', $content);

        if ($hasPdfHeader && $hasExecutableCode) {
            $threats[] = 'polyglot_file';
        }

        return $threats;
    }

    /**
     * Get a user-friendly message for detected threats.
     * 
     * @param array $threats Array of threat identifiers
     * @return string User-friendly message
     */
    protected function getThreatMessage(array $threats): string
    {
        $messages = [
            'double_extension' => 'Invalid file extension detected.',
            'file_too_large' => 'File size cannot exceed 10MB.',
            'invalid_mime_type' => 'File type is not allowed. Only PDF, JPEG, PNG, and WEBP files are permitted.',
            'mime_type_mismatch' => 'File content does not match its extension.',
            'embedded_scripts' => 'File contains prohibited content.',
            'null_byte_injection' => 'File contains suspicious content.',
            'polyglot_file' => 'File contains prohibited content.',
        ];

        foreach ($threats as $threat) {
            if (isset($messages[$threat])) {
                return $messages[$threat];
            }
        }

        return 'File failed security validation.';
    }
}
