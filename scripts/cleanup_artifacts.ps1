# Mayush Project Cleanup Script
# This script removes non-essential files to ensure a clean production-ready environment.

$patterns = @(
    "test_*.php", "check_*.php", "debug_*.php", "fix_*.php", 
    "trigger_*.php", "final_*.php", "debug_*.log", "*.log", 
    "error*.txt", "routes_*.txt", ".phpunit.result.cache", 
    "script_dump.js", "snapshot.json", "*_backup.*", "*.bak"
)

Write-Host "Starting environment cleanup..." -ForegroundColor Cyan

foreach ($pattern in $patterns) {
    $files = Get-ChildItem -Path . -Include $pattern -Recurse -File -ErrorAction SilentlyContinue
    if ($files) {
        foreach ($file in $files) {
            Write-Host "Removing: $($file.FullName)" -ForegroundColor Yellow
            Remove-Item -Path $file.FullName -Force
        }
    }
}

Write-Host "Cleanup complete. Environment is production-ready." -ForegroundColor Green
