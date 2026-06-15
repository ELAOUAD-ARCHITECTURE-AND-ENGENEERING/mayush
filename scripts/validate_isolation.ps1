param (
    [string]$Directory = "."
)

$forbiddenKeywords = @(
    "lykora",
    "lykora.space",
    "turnkey renovation casablanca"
)

$excludePaths = @(
    "vendor",
    "node_modules",
    "storage",
    ".git",
    "scripts"
)

Write-Host "=============================================" -ForegroundColor Cyan
Write-Host " Running Mayush Project Isolation Validation " -ForegroundColor Cyan
Write-Host "=============================================" -ForegroundColor Cyan

$hasViolations = $false

# Recursively get all files excluding the specified directories
$files = Get-ChildItem -Path $Directory -Recurse -File | Where-Object {
    $path = $_.FullName
    $exclude = $false
    foreach ($ex in $excludePaths) {
        if ($path -match "\\$ex\\") {
            $exclude = $true
            break
        }
    }
    return -not $exclude
}

foreach ($file in $files) {
    # Skip binary files or non-text files to avoid errors
    if ($file.Extension -notin @(".php", ".blade.php", ".js", ".vue", ".css", ".scss", ".md", ".json", ".env", ".xml", ".html", ".txt")) {
        continue
    }

    try {
        $content = Get-Content -Path $file.FullName -Raw -ErrorAction Stop
        if ($null -eq $content) { continue }

        foreach ($keyword in $forbiddenKeywords) {
            if ($content -match "(?i)$([regex]::Escape($keyword))") {
                Write-Host "VIOLATION DETECTED: Found forbidden keyword '$keyword' in file:" -ForegroundColor Red
                Write-Host "  -> $($file.FullName)" -ForegroundColor Yellow
                $hasViolations = $true
            }
        }
    } catch {
        # Ignore files that can't be read
    }
}

Write-Host "---------------------------------------------" -ForegroundColor Cyan
if ($hasViolations) {
    Write-Host "❌ Validation FAILED! Cross-contamination detected." -ForegroundColor Red
    Write-Host "Please remove the forbidden references and run this script again." -ForegroundColor Red
    exit 1
} else {
    Write-Host "✅ Validation PASSED! No cross-contamination detected." -ForegroundColor Green
    exit 0
}
