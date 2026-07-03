param(
    [string]$BaseUrl = "http://localhost/mayush",
    [int]$TimeoutSeconds = 20
)

$ErrorActionPreference = "Stop"

$paths = @(
    "/",
    "/admin",
    "/api/v2/blog-list",
    "/api/v2/categories"
)

$failed = $false

foreach ($path in $paths) {
    $url = $BaseUrl.TrimEnd("/") + $path
    try {
        $response = Invoke-WebRequest -UseBasicParsing -Uri $url -TimeoutSec $TimeoutSeconds -MaximumRedirection 5
        Write-Host "[OK] $url -> $([int]$response.StatusCode) $($response.StatusDescription)"
    } catch {
        $failed = $true
        if ($_.Exception.Response) {
            Write-Host "[FAIL] $url -> $([int]$_.Exception.Response.StatusCode) $($_.Exception.Response.StatusDescription)"
        } else {
            Write-Host "[FAIL] $url -> $($_.Exception.Message)"
        }
    }
}

if ($failed) {
    exit 1
}
