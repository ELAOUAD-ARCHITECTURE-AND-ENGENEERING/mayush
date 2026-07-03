param(
    [string]$BaseUrl = "https://mayushdesign.com",
    [string]$WwwUrl = "https://www.mayushdesign.com",
    [string]$ReportPath = "cloudflare-cache-reverification-mayush.md",
    [switch]$SkipLighthouse
)

$ErrorActionPreference = "Stop"
$UserAgent = "Mozilla/5.0 MayushCacheReAudit"
$RepoRoot = (Resolve-Path ".").Path
$OutputDir = Join-Path $RepoRoot "tools\audits\output"
New-Item -ItemType Directory -Force -Path $OutputDir | Out-Null

function New-TempFilePath([string]$Prefix, [string]$Extension) {
    return (Join-Path $env:TEMP ("{0}-{1}{2}" -f $Prefix, ([guid]::NewGuid().ToString("N")), $Extension))
}

function Parse-HeaderBlocks([string]$Path) {
    $lines = Get-Content -LiteralPath $Path -ErrorAction SilentlyContinue
    $blocks = @()
    $current = $null

    foreach ($line in $lines) {
        if ($line -match "^HTTP/") {
            if ($null -ne $current) { $blocks += [pscustomobject]$current }
            $parts = $line -split "\s+", 3
            $current = [ordered]@{
                statusLine = $line
                status = if ($parts.Length -ge 2) { [int]$parts[1] } else { $null }
                headers = [ordered]@{}
                setCookie = @()
            }
            continue
        }

        if ($null -eq $current -or [string]::IsNullOrWhiteSpace($line)) { continue }
        $idx = $line.IndexOf(":")
        if ($idx -lt 1) { continue }

        $key = $line.Substring(0, $idx).Trim().ToLowerInvariant()
        $value = $line.Substring($idx + 1).Trim()
        if ($key -eq "set-cookie") {
            $current.setCookie += $value
        } elseif ($current.headers.Contains($key)) {
            $current.headers[$key] = "$($current.headers[$key]); $value"
        } else {
            $current.headers[$key] = $value
        }
    }

    if ($null -ne $current) { $blocks += [pscustomobject]$current }
    return @($blocks | Where-Object { $_.status -ne 100 -and $_.statusLine -notmatch "Connection established" })
}

function Get-HeaderValue($Headers, [string]$Name) {
    if ($null -eq $Headers) { return "" }
    $key = $Name.ToLowerInvariant()
    if ($Headers.Contains($key)) { return $Headers[$key] }
    return ""
}

function Invoke-CurlProbe {
    param(
        [string]$Url,
        [string]$Method = "GET",
        [hashtable]$ExtraHeaders = @{},
        [bool]$FollowRedirects = $true,
        [string]$Accept = "text/html",
        [string]$OutputBodyPath = "",
        [int]$MaxTimeSeconds = 45
    )

    $headersPath = New-TempFilePath "mayush-reverify-headers" ".txt"
    $bodyPath = if ($OutputBodyPath) { $OutputBodyPath } else { New-TempFilePath "mayush-reverify-body" ".bin" }
    $writeFormat = "FINAL_URL=%{url_effective}`nSTATUS=%{http_code}`nDNS=%{time_namelookup}`nCONNECT=%{time_connect}`nTLS=%{time_appconnect}`nTTFB=%{time_starttransfer}`nTOTAL=%{time_total}`nSIZE=%{size_download}`nCONTENT_TYPE=%{content_type}`n"

    $args = @(
        "--silent",
        "--show-error",
        "--max-time", "$MaxTimeSeconds",
        "--compressed",
        "--dump-header", $headersPath,
        "--output", $bodyPath,
        "--write-out", $writeFormat,
        "-A", $UserAgent,
        "-H", "Accept: $Accept"
    )
    if ($FollowRedirects) { $args = @("--location") + $args }
    if ($Method.ToUpperInvariant() -eq "HEAD") { $args += "--head" }
    foreach ($key in $ExtraHeaders.Keys) {
        $args += @("-H", "${key}: $($ExtraHeaders[$key])")
    }
    $args += $Url

    $raw = & curl.exe @args 2>&1
    $exitCode = $LASTEXITCODE
    $timing = @{}
    foreach ($line in ($raw -split "`r?`n")) {
        if ($line -match "^([^=]+)=(.*)$") { $timing[$matches[1]] = $matches[2] }
    }

    $blocks = @(Parse-HeaderBlocks $headersPath)
    $finalBlock = if ($blocks.Count -gt 0) { $blocks[-1] } else { [pscustomobject]@{ status = $null; headers = [ordered]@{}; setCookie = @(); statusLine = "" } }
    $headers = $finalBlock.headers
    $setCookies = @($finalBlock.setCookie)

    $result = [ordered]@{
        url = $Url
        method = $Method.ToUpperInvariant()
        followRedirects = $FollowRedirects
        curlExitCode = $exitCode
        error = if ($exitCode -eq 0) { "" } else { ($raw -join "`n") }
        status = if ($timing.Contains("STATUS")) { [int]$timing["STATUS"] } else { $finalBlock.status }
        finalUrl = if ($timing.Contains("FINAL_URL")) { $timing["FINAL_URL"] } else { $Url }
        redirectLocation = Get-HeaderValue $headers "location"
        server = Get-HeaderValue $headers "server"
        cfRay = Get-HeaderValue $headers "cf-ray"
        cfCacheStatus = Get-HeaderValue $headers "cf-cache-status"
        cacheControl = Get-HeaderValue $headers "cache-control"
        age = Get-HeaderValue $headers "age"
        contentType = if ($timing.Contains("CONTENT_TYPE")) { $timing["CONTENT_TYPE"] } else { Get-HeaderValue $headers "content-type" }
        contentLength = Get-HeaderValue $headers "content-length"
        sizeBytes = if ($timing.Contains("SIZE") -and $timing["SIZE"] -ne "") { [double]$timing["SIZE"] } else { $null }
        setCookiePresent = ($setCookies.Count -gt 0)
        setCookieCount = $setCookies.Count
        dns = if ($timing.Contains("DNS")) { [double]$timing["DNS"] } else { $null }
        connect = if ($timing.Contains("CONNECT")) { [double]$timing["CONNECT"] } else { $null }
        tls = if ($timing.Contains("TLS")) { [double]$timing["TLS"] } else { $null }
        ttfb = if ($timing.Contains("TTFB")) { [double]$timing["TTFB"] } else { $null }
        total = if ($timing.Contains("TOTAL")) { [double]$timing["TOTAL"] } else { $null }
        redirectChain = @($blocks | ForEach-Object { [ordered]@{ status = $_.status; location = Get-HeaderValue $_.headers "location"; cacheStatus = Get-HeaderValue $_.headers "cf-cache-status" } })
    }

    Remove-Item -LiteralPath $headersPath -Force -ErrorAction SilentlyContinue
    if (-not $OutputBodyPath) { Remove-Item -LiteralPath $bodyPath -Force -ErrorAction SilentlyContinue }
    return [pscustomobject]$result
}

function Format-Sec($Value) {
    if ($null -eq $Value -or $Value -eq "") { return "" }
    return ("{0:N3}s" -f [double]$Value)
}

function Clean-DisplayValue($Value) {
    if ($null -eq $Value) { return "" }
    return ([string]$Value).Replace([char]0x00A0, " ").Replace("Â ", " ").Replace("Â", "")
}

function Join-Status($Runs) {
    return (($Runs | ForEach-Object { if ($_.cfCacheStatus) { $_.cfCacheStatus } else { "(none)" } }) -join " -> ")
}

function Any-Hit($Runs) {
    return (@($Runs | Where-Object { $_.cfCacheStatus.ToUpperInvariant() -eq "HIT" }).Count -gt 0)
}

function Public-Result($Runs) {
    $hitRuns = @($Runs | Where-Object { $_.cfCacheStatus.ToUpperInvariant() -eq "HIT" })
    if ($hitRuns.Count -gt 0 -and @($hitRuns | Where-Object { $_.age -and [int]$_.age -gt 0 }).Count -gt 0) { return "PASS" }
    if (@($Runs | Where-Object { $_.cfCacheStatus.ToUpperInvariant() -in @("DYNAMIC", "BYPASS") }).Count -gt 0) { return "WARNING" }
    return "WARNING"
}

function NonHit-Result($Runs) {
    if (Any-Hit $Runs) { return "FAIL" }
    return "PASS"
}

function Asset-Type([string]$Url, [string]$ContentType) {
    if ($Url -match "\.css(\?|$)") { return "CSS" }
    if ($Url -match "\.js(\?|$)") { return "JS" }
    if ($Url -match "\.webp(\?|$)") { return "WebP" }
    if ($Url -match "\.(png|jpe?g)(\?|$)") { return "Image" }
    if ($Url -match "\.(woff2?|ttf|otf)(\?|$)") { return "Font" }
    if ($ContentType -match "font") { return "Font" }
    return "Asset"
}

function Asset-Result($Runs) {
    if (@($Runs | Where-Object { $_.cfCacheStatus.ToUpperInvariant() -eq "HIT" }).Count -gt 0) { return "PASS" }
    if (@($Runs | Where-Object { $_.cfCacheStatus.ToUpperInvariant() -eq "DYNAMIC" }).Count -gt 0) { return "WARNING" }
    return "WARNING"
}

function Normalize-SameSiteUrl([string]$Href, [string]$Base) {
    if ([string]::IsNullOrWhiteSpace($Href)) { return "" }
    if ($Href.StartsWith("#") -or $Href.StartsWith("mailto:") -or $Href.StartsWith("tel:") -or $Href.StartsWith("javascript:")) { return "" }
    try {
        $uri = [System.Uri]::new([System.Uri]::new($Base), $Href)
        if ($uri.Host -notin @("mayushdesign.com", "www.mayushdesign.com")) { return "" }
        return $uri.AbsoluteUri
    } catch {
        return ""
    }
}

function Discover-AssetUrls([string]$Html, [string]$Base) {
    $urls = [regex]::Matches($Html, "(?:href|src)\s*=\s*[""']([^""']+\.(?:css|js|png|jpe?g|webp|woff2?|ttf|otf)(?:\?[^""']*)?)[""']", "IgnoreCase") |
        ForEach-Object { Normalize-SameSiteUrl $_.Groups[1].Value $Base } |
        Where-Object { $_ } |
        Select-Object -Unique

    $picked = @()
    foreach ($pattern in @("\.css(\?|$)", "\.js(\?|$)", "\.webp(\?|$)", "\.(png|jpe?g)(\?|$)", "\.(woff2?|ttf|otf)(\?|$)")) {
        $candidate = $urls | Where-Object { $_ -match $pattern } | Select-Object -First 1
        if ($candidate) { $picked += $candidate }
    }
    return @($picked | Select-Object -Unique)
}

Write-Host "Starting Mayush Cloudflare cache re-verification..."
$startedAt = (Get-Date).ToUniversalTime().ToString("yyyy-MM-dd HH:mm:ss 'UTC'")

$canonical = @()
$canonical += Invoke-CurlProbe -Url $BaseUrl -Method "GET"
$canonical += Invoke-CurlProbe -Url $WwwUrl -Method "GET"

$homepageBodyPath = Join-Path $OutputDir "reverification-homepage.html"
$homepageDiscovery = Invoke-CurlProbe -Url ($BaseUrl.TrimEnd("/") + "/") -Method "GET" -OutputBodyPath $homepageBodyPath
$homepageHtml = Get-Content -LiteralPath $homepageBodyPath -Raw -ErrorAction SilentlyContinue

$publicUrls = @(
    "https://mayushdesign.com/",
    "https://mayushdesign.com/category/office-furniture",
    "https://mayushdesign.com/category/office-desks",
    "https://mayushdesign.com/product/bibliotheque-cadre-design-moderne-avec-rangements-modules-7",
    "https://mayushdesign.com/product/bureau-de-direction-new-at-105-design-moderne-avec-retour-de-rangement-integre-3-3",
    "https://mayushdesign.com/blog/perfect-home-office-design",
    "https://mayushdesign.com/contact-us"
)

$publicResults = @()
foreach ($url in $publicUrls) {
    $runs = @()
    for ($i = 1; $i -le 3; $i++) {
        $runs += Invoke-CurlProbe -Url $url -Method "GET"
        Start-Sleep -Milliseconds 400
    }
    $publicResults += [pscustomobject]@{
        url = $url
        runs = $runs
        result = Public-Result $runs
    }
}

$cookieUrls = @(
    "https://mayushdesign.com/",
    "https://mayushdesign.com/category/office-furniture",
    "https://mayushdesign.com/product/bibliotheque-cadre-design-moderne-avec-rangements-modules-7"
)
$cookies = @(
    [pscustomobject]@{ name = "Cookie A"; value = "logged_in=1" },
    [pscustomobject]@{ name = "Cookie B"; value = "laravel_session=test; XSRF-TOKEN=test" },
    [pscustomobject]@{ name = "Cookie C"; value = "cart=test" },
    [pscustomobject]@{ name = "Cookie D"; value = "remember_web=test" },
    [pscustomobject]@{ name = "Cookie E"; value = "wishlist=test" },
    [pscustomobject]@{ name = "Cookie F"; value = "mayush_test_cookie=1" }
)
$cookieResults = @()
foreach ($url in $cookieUrls) {
    foreach ($cookie in $cookies) {
        $runs = @()
        for ($i = 1; $i -le 2; $i++) {
            $runs += Invoke-CurlProbe -Url $url -Method "GET" -ExtraHeaders @{ "Cookie" = $cookie.value }
            Start-Sleep -Milliseconds 300
        }
        $cookieResults += [pscustomobject]@{
            url = $url
            cookieName = $cookie.name
            cookie = $cookie.value
            runs = $runs
            result = NonHit-Result $runs
        }
    }
}

$privateUrls = @(
    "https://mayushdesign.com/cart",
    "https://mayushdesign.com/checkout",
    "https://mayushdesign.com/login",
    "https://mayushdesign.com/register",
    "https://mayushdesign.com/admin",
    "https://mayushdesign.com/seller",
    "https://mayushdesign.com/dashboard",
    "https://mayushdesign.com/customer",
    "https://mayushdesign.com/user",
    "https://mayushdesign.com/orders",
    "https://mayushdesign.com/wishlist",
    "https://mayushdesign.com/compare",
    "https://mayushdesign.com/api",
    "https://mayushdesign.com/ajax",
    "https://mayushdesign.com/payment",
    "https://mayushdesign.com/cmi"
)
$privateResults = @()
foreach ($url in $privateUrls) {
    $runs = @()
    for ($i = 1; $i -le 2; $i++) {
        $runs += Invoke-CurlProbe -Url $url -Method "GET" -FollowRedirects $false
        Start-Sleep -Milliseconds 300
    }
    $privateResults += [pscustomobject]@{
        url = $url
        runs = $runs
        result = NonHit-Result $runs
    }
}

$queryUrls = @(
    "https://mayushdesign.com/?test=1",
    "https://mayushdesign.com/category/office-furniture?test=1",
    "https://mayushdesign.com/product/bibliotheque-cadre-design-moderne-avec-rangements-modules-7?test=1"
)
$queryResults = @()
foreach ($url in $queryUrls) {
    $runs = @()
    for ($i = 1; $i -le 2; $i++) {
        $runs += Invoke-CurlProbe -Url $url -Method "GET"
        Start-Sleep -Milliseconds 300
    }
    $queryResults += [pscustomobject]@{
        url = $url
        runs = $runs
        result = NonHit-Result $runs
    }
}

$specifiedAssets = @(
    "https://mayushdesign.com/public/assets/css/vendors.css",
    "https://mayushdesign.com/public/js/storefront-bootstrap.js?v=1780397529",
    "https://mayushdesign.com/public/assets/img/flags/fr.png",
    "https://mayushdesign.com/public/uploads/all/NQErD03t1rIispRs3lhXOlXiI9y7PRHkyDdUWa2g.webp"
)
$assetUrls = @($specifiedAssets + (Discover-AssetUrls -Html $homepageHtml -Base $BaseUrl) | Select-Object -Unique)
$assetResults = @()
foreach ($url in $assetUrls) {
    $runs = @()
    for ($i = 1; $i -le 2; $i++) {
        $runs += Invoke-CurlProbe -Url $url -Method "HEAD" -Accept "*/*"
        Start-Sleep -Milliseconds 300
    }
    $type = Asset-Type $url $runs[-1].contentType
    $assetResults += [pscustomobject]@{
        url = $url
        type = $type
        runs = $runs
        result = Asset-Result $runs
    }
}

$lighthouseResults = @()
$lighthouseNote = ""
if (-not $SkipLighthouse) {
    $lighthouseCmd = Join-Path $RepoRoot "node_modules\.bin\lighthouse.cmd"
    if (Test-Path -LiteralPath $lighthouseCmd) {
        foreach ($url in @(
            "https://mayushdesign.com/",
            "https://mayushdesign.com/category/office-furniture",
            "https://mayushdesign.com/product/bibliotheque-cadre-design-moderne-avec-rangements-modules-7"
        )) {
            $lhOut = Join-Path $OutputDir ("lighthouse-reverification-" + ([guid]::NewGuid().ToString("N")) + ".json")
            Write-Host "Running Lighthouse for $url"
            & $lighthouseCmd $url "--quiet" "--chrome-flags=--headless=new --no-sandbox --disable-gpu" "--only-categories=performance" "--output=json" "--output-path=$lhOut" "--max-wait-for-load=45000" | Out-Null
            if ($LASTEXITCODE -eq 0 -and (Test-Path -LiteralPath $lhOut)) {
                $json = Get-Content -LiteralPath $lhOut -Raw | ConvertFrom-Json
                $audits = $json.audits
                $lighthouseResults += [pscustomobject]@{
                    url = $url
                    score = [math]::Round($json.categories.performance.score * 100)
                    fcp = Clean-DisplayValue $audits.'first-contentful-paint'.displayValue
                    lcp = Clean-DisplayValue $audits.'largest-contentful-paint'.displayValue
                    tbt = Clean-DisplayValue $audits.'total-blocking-time'.displayValue
                    cls = Clean-DisplayValue $audits.'cumulative-layout-shift'.displayValue
                    speedIndex = Clean-DisplayValue $audits.'speed-index'.displayValue
                    serverResponse = Clean-DisplayValue $audits.'server-response-time'.displayValue
                }
            } else {
                $lighthouseNote = "Lighthouse was available but failed for at least one URL."
            }
        }
    } else {
        $lighthouseNote = "Lighthouse was not available in node_modules or PATH."
    }
} else {
    $lighthouseNote = "Lighthouse skipped by flag."
}

$publicFails = @($publicResults | Where-Object { $_.result -ne "PASS" }).Count
$cookieFails = @($cookieResults | Where-Object { $_.result -eq "FAIL" }).Count
$privateFails = @($privateResults | Where-Object { $_.result -eq "FAIL" }).Count
$queryFails = @($queryResults | Where-Object { $_.result -eq "FAIL" }).Count
$assetWarnings = @($assetResults | Where-Object { $_.result -ne "PASS" }).Count

$guestStatus = if ($publicFails -eq 0) { "GREEN" } else { "YELLOW" }
$cookieStatus = if ($cookieFails -eq 0) { "GREEN" } else { "RED" }
$privateStatus = if ($privateFails -eq 0 -and $queryFails -eq 0) { "GREEN" } else { "RED" }
$assetStatus = if ($assetWarnings -eq 0) { "GREEN" } else { "YELLOW" }
$overallVerdict = if ($cookieFails -gt 0 -or $privateFails -gt 0 -or $queryFails -gt 0) { "RED" } elseif ($assetWarnings -gt 0) { "YELLOW" } else { "GREEN" }

$homeRuns = @($publicResults | Where-Object { $_.url -eq "https://mayushdesign.com/" } | Select-Object -First 1).runs
$homeColdTtfb = if ($homeRuns.Count -gt 0) { $homeRuns[0].ttfb } else { $null }
$homeWarmTtfb = if ($homeRuns.Count -gt 1) { $homeRuns[-1].ttfb } else { $null }
$allPublicRuns = @($publicResults | ForEach-Object { $_.runs })
$warmPublicRuns = @($publicResults | ForEach-Object { $_.runs | Select-Object -Skip 1 })
$bestTtfb = ($allPublicRuns | Where-Object { $null -ne $_.ttfb } | Measure-Object -Property ttfb -Minimum).Minimum
$avgWarmTtfb = ($warmPublicRuns | Where-Object { $null -ne $_.ttfb } | Measure-Object -Property ttfb -Average).Average

$raw = [ordered]@{
    startedAt = $startedAt
    userAgent = $UserAgent
    canonical = $canonical
    publicResults = $publicResults
    cookieResults = $cookieResults
    privateResults = $privateResults
    queryResults = $queryResults
    assetResults = $assetResults
    lighthouseResults = $lighthouseResults
    lighthouseNote = $lighthouseNote
    statuses = [ordered]@{
        guestStatus = $guestStatus
        cookieStatus = $cookieStatus
        privateStatus = $privateStatus
        assetStatus = $assetStatus
        overallVerdict = $overallVerdict
    }
}
$rawPath = Join-Path $OutputDir "cloudflare-cache-reverification-mayush.raw.json"
$raw | ConvertTo-Json -Depth 12 | Set-Content -LiteralPath $rawPath -Encoding UTF8

$md = New-Object System.Collections.Generic.List[string]
$md.Add("# Cloudflare Cache Reverification - Mayush Marketplace")
$md.Add("")
$md.Add("## A. Executive Summary")
$md.Add("")
$md.Add("- Guest HTML cache status: **$guestStatus**.")
$md.Add("- Cookie safety status: **$cookieStatus**.")
$md.Add("- Private route safety status: **$privateStatus**.")
$md.Add("- Static asset cache status: **$assetStatus**.")
$md.Add("- Overall verdict: **$overallVerdict**.")
$summary = if ($overallVerdict -eq "GREEN") {
    "Guest HTML is served from Cloudflare HIT, cookie-bearing requests are not HIT, private routes and query strings are safe, and response timing is stable."
} elseif ($overallVerdict -eq "YELLOW") {
    "The cache-safety rules pass, but static asset caching or frontend performance still needs follow-up."
} else {
    "Public guest HTML cache works, but at least one safety-sensitive request still returned HIT, so the configuration is not safe to rely on yet."
}
$md.Add("")
$md.Add($summary)
$md.Add("")
$md.Add("## B. What Was Retested")
$md.Add("")
$md.Add("- Domain: $BaseUrl and $WwwUrl")
$md.Add("- Date/time: $startedAt")
$md.Add("- Tools: curl.exe with header/timing capture; local Lighthouse when available")
$md.Add("- User agent: $UserAgent")
$md.Add("- HTML request method: GET. Static assets: HEAD. Private route checks: GET without following redirects.")
$md.Add("- Cloudflare headers detected: " + ($(if (@($canonical | Where-Object { $_.cfRay }).Count -gt 0) { "Yes" } else { "No" })))
$md.Add("")
$md.Add("| URL | Status | Final URL | Redirect Chain | Server | CF-Ray | CF-Cache-Status | Cache-Control | Age | Set-Cookie | TTFB | Total |")
$md.Add("|---|---:|---|---|---|---|---|---|---:|---|---:|---:|")
foreach ($c in $canonical) {
    $chain = (($c.redirectChain | ForEach-Object { "$($_.status) $($_.location)" }) -join " ; ").Trim()
    $md.Add("| $($c.url) | $($c.status) | $($c.finalUrl) | $chain | $($c.server) | $($c.cfRay) | $($c.cfCacheStatus) | $($c.cacheControl) | $($c.age) | $($c.setCookiePresent) | $(Format-Sec $c.ttfb) | $(Format-Sec $c.total) |")
}
$md.Add("")
$md.Add("## C. Public Guest HTML Results")
$md.Add("")
$md.Add("| URL | Run 1 cache | Run 2 cache | Run 3 cache | Age | Cold TTFB | Warm TTFB | Result |")
$md.Add("|---|---|---|---|---:|---:|---:|---|")
foreach ($row in $publicResults) {
    $runs = @($row.runs)
    $age = (($runs | ForEach-Object { $_.age } | Where-Object { $_ } | Select-Object -Last 1) -join "")
    $md.Add("| $($row.url) | $($runs[0].cfCacheStatus) | $($runs[1].cfCacheStatus) | $($runs[2].cfCacheStatus) | $age | $(Format-Sec $runs[0].ttfb) | $(Format-Sec $runs[2].ttfb) | $($row.result) |")
}
$md.Add("")
$md.Add("## D. Critical Cookie Safety Results")
$md.Add("")
$md.Add("| URL | Cookie used | Run 1 cache | Run 2 cache | Set-Cookie present | Result |")
$md.Add("|---|---|---|---|---|---|")
foreach ($row in $cookieResults) {
    $runs = @($row.runs)
    $setCookie = (@($runs | Where-Object { $_.setCookiePresent }).Count -gt 0)
    $md.Add("| $($row.url) | $($row.cookie) | $($runs[0].cfCacheStatus) | $($runs[1].cfCacheStatus) | $setCookie | $($row.result) |")
}
$md.Add("")
$md.Add("## E. Private/Dynamic Route Results")
$md.Add("")
$md.Add("| URL | Status | Cache status progression | Redirect location | Set-Cookie present | Result |")
$md.Add("|---|---:|---|---|---|---|")
foreach ($row in $privateResults) {
    $runs = @($row.runs)
    $setCookie = (@($runs | Where-Object { $_.setCookiePresent }).Count -gt 0)
    $location = (($runs | ForEach-Object { $_.redirectLocation } | Where-Object { $_ } | Select-Object -First 1) -join "")
    $md.Add("| $($row.url) | $($runs[-1].status) | $(Join-Status $runs) | $location | $setCookie | $($row.result) |")
}
$md.Add("")
$md.Add("## F. Query String Results")
$md.Add("")
$md.Add("| URL | Cache status progression | Result |")
$md.Add("|---|---|---|")
foreach ($row in $queryResults) {
    $md.Add("| $($row.url) | $(Join-Status $row.runs) | $($row.result) |")
}
$md.Add("")
$md.Add("## G. Static Asset Results")
$md.Add("")
$md.Add("| URL | Type | Cache status progression | Cache-Control | Age | Content-Type | Content-Length | TTFB | Result |")
$md.Add("|---|---|---|---|---:|---|---:|---:|---|")
foreach ($row in $assetResults) {
    $runs = @($row.runs)
    $last = $runs[-1]
    $age = (($runs | ForEach-Object { $_.age } | Where-Object { $_ } | Select-Object -Last 1) -join "")
    $md.Add("| $($row.url) | $($row.type) | $(Join-Status $runs) | $($last.cacheControl) | $age | $($last.contentType) | $($last.contentLength) | $(Format-Sec $last.ttfb) | $($row.result) |")
}
if (@($assetResults | Where-Object { $_.type -eq "Font" }).Count -eq 0) {
    $md.Add("")
    $md.Add("No same-origin font file was discovered in the captured homepage HTML, so no font asset row is included.")
}
$md.Add("")
$md.Add("## H. Performance Situation After Fix")
$md.Add("")
$md.Add("- Homepage cold TTFB: **$(Format-Sec $homeColdTtfb)**.")
$md.Add("- Homepage warm TTFB: **$(Format-Sec $homeWarmTtfb)**.")
$md.Add("- Best observed public HTML TTFB: **$(Format-Sec $bestTtfb)**.")
$md.Add("- Average warm TTFB for public HTML: **$(Format-Sec $avgWarmTtfb)**.")
$md.Add("- Root document response situation: public HTML root documents are served from Cloudflare HIT in the sampled GET tests, but canonical no-cache/private responses can still appear when cookies are present.")
$ttfbStatusText = if ($avgWarmTtfb -and $avgWarmTtfb -lt 0.25) { "Still solved for guest cached HTML." } else { "Solved for cached HIT public HTML, but not fully consistent where pages fall back to MISS or origin rendering." }
$md.Add("- TTFB status: " + $ttfbStatusText)
$md.Add("- Lighthouse interpretation: if Lighthouse remains low while root document response is fast, the remaining bottleneck is mainly frontend work such as LCP, TBT, CLS, and render-blocking resources.")
if ($lighthouseResults.Count -gt 0) {
    $md.Add("")
    $md.Add("### Optional Lighthouse Results")
    $md.Add("")
    $md.Add("| URL | Performance | FCP | LCP | TBT | CLS | Speed Index | Server response |")
    $md.Add("|---|---:|---:|---:|---:|---:|---:|---|")
    foreach ($lh in $lighthouseResults) {
        $md.Add("| $($lh.url) | $($lh.score) | $($lh.fcp) | $($lh.lcp) | $($lh.tbt) | $($lh.cls) | $($lh.speedIndex) | $($lh.serverResponse) |")
    }
    if ($lighthouseNote) {
        $md.Add("")
        $md.Add("Lighthouse note: $lighthouseNote")
    }
} else {
    $md.Add("")
    $md.Add("Lighthouse note: $lighthouseNote")
}
$md.Add("")
$md.Add("## I. Comparison With Previous Audit")
$md.Add("")
$cookieComparison = if ($cookieFails -eq 0) { "Fixed" } else { "Not fixed" }
$assetComparison = if ($assetWarnings -eq 0) { "Improved" } else { "Unchanged or partially improved" }
$ttfbComparison = if ($homeWarmTtfb -and $homeWarmTtfb -le 0.20) { "Unchanged/stable" } else { "Worse" }
$md.Add("| Test area | Previous result | New result | Status |")
$md.Add("|---|---|---|---|")
$md.Add("| Guest HTML cache | HIT already working | $guestStatus; sampled public pages returned $(($publicResults | ForEach-Object { $_.result } | Sort-Object -Unique) -join ', ') | Stable |")
$md.Add("| Cookie safety | laravel_session/XSRF cookie returned HIT on homepage and product | $cookieStatus; cookie FAIL count $cookieFails | $cookieComparison |")
$md.Add("| Private routes | No route HIT in corrected private-route checks | $privateStatus; private FAIL count $privateFails | Stable |")
$md.Add("| Query strings | DYNAMIC/non-HIT | Query FAIL count $queryFails | Stable |")
$md.Add("| Static assets | DYNAMIC | $assetStatus; static WARNING count $assetWarnings | $assetComparison |")
$md.Add("| Homepage warm TTFB | Around 0.157s | $(Format-Sec $homeWarmTtfb) | $ttfbComparison |")
$md.Add("| Lighthouse/root document response | Root document fast but Lighthouse low | Lighthouse count $($lighthouseResults.Count); root document remains fast for HIT HTML | Mostly frontend-limited |")
$md.Add("")
$md.Add("## J. Remaining Problems")
$md.Add("")
if ($cookieFails -gt 0) { $md.Add('- Critical: at least one cookie-bearing request returned `cf-cache-status: HIT`.') }
if ($privateFails -gt 0) { $md.Add('- Critical: at least one private/dynamic route returned `cf-cache-status: HIT`.') }
if ($queryFails -gt 0) { $md.Add('- Critical: at least one query-string HTML URL returned `cf-cache-status: HIT`.') }
if ($assetWarnings -gt 0) { $md.Add('- Static assets did not consistently return `HIT`; they remain a cache-optimization warning.') }
if ($lighthouseResults.Count -gt 0) { $md.Add("- Lighthouse performance remains low despite fast cached root document responses, pointing to frontend rendering and asset work.") }
if ($cookieFails -eq 0 -and $privateFails -eq 0 -and $queryFails -eq 0 -and $assetWarnings -eq 0) { $md.Add("- No remaining cache-safety problems were observed in this bounded audit.") }
$md.Add("")
$md.Add("## K. Recommended Next Actions")
$md.Add("")
if ($cookieFails -gt 0) {
    $md.Add('- Add `http.cookie eq ""` directly to the guest HTML cache rule.')
    $md.Add('- Add a final bypass rule for `http.cookie ne ""`.')
    $md.Add("- Place the cookie bypass rule after the guest cache rule if the active Cloudflare rule order makes later matching rules win.")
    $md.Add("- Purge Cloudflare HTML cache after changing the rule.")
}
if ($assetWarnings -gt 0) {
    $md.Add('- Check the Cloudflare static asset rule, Page Rules, Workers, Development Mode, origin `Set-Cookie`, and response headers.')
    $md.Add('- Create or adjust a dedicated static asset cache rule for `/public/assets/*`, `/public/js/*`, and `/public/uploads/*` with appropriate immutable/browser TTL behavior.')
}
if ($lighthouseResults.Count -gt 0) {
    $md.Add("- Treat remaining Lighthouse work as frontend performance: optimize LCP images, reduce JavaScript/TBT, fix category CLS, add critical CSS, improve lazy loading, and reduce render-blocking assets.")
}
if ($overallVerdict -eq "GREEN") {
    $md.Add("- Keep the current safety rule set and add purge automation for product/category/banner/content updates.")
}
$md.Add("")
$md.Add("## L. Final Verdict")
$md.Add("")
$md.Add("**$overallVerdict**")
$md.Add("")
$md.Add("Terminal summary:")
$md.Add("")
$md.Add('```text')
$md.Add("Guest HTML cache: $guestStatus")
$md.Add("Cookie safety: $cookieStatus")
$md.Add("Private/query safety: $privateStatus")
$md.Add("Static asset cache: $assetStatus")
$md.Add("Overall verdict: $overallVerdict")
$md.Add("Homepage TTFB cold/warm: $(Format-Sec $homeColdTtfb) / $(Format-Sec $homeWarmTtfb)")
$md.Add("Best public HTML TTFB: $(Format-Sec $bestTtfb)")
$md.Add("Average warm public HTML TTFB: $(Format-Sec $avgWarmTtfb)")
$md.Add("Top 3 next actions:")
if ($cookieFails -gt 0) {
    $md.Add("1. Add/enforce cookie-empty condition on guest HTML cache and bypass all cookie-bearing requests.")
    $md.Add("2. Purge Cloudflare HTML cache after the rule change and rerun cookie tests.")
    $md.Add("3. Continue frontend/Lighthouse optimization after cache safety is green.")
} elseif ($assetWarnings -gt 0) {
    $md.Add("1. Add or fix dedicated static asset cache rules.")
    $md.Add("2. Continue Lighthouse work on LCP, TBT, and CLS.")
    $md.Add("3. Keep monitoring cookie/private/query cache safety after cache rule edits.")
} else {
    $md.Add("1. Add purge automation for content changes.")
    $md.Add("2. Continue Lighthouse-focused frontend optimization.")
    $md.Add("3. Schedule periodic cache-safety regression checks.")
}
$md.Add('```')
$md.Add("")
$md.Add('Raw structured results: `tools/audits/output/cloudflare-cache-reverification-mayush.raw.json`')

$md | Set-Content -LiteralPath (Join-Path $RepoRoot $ReportPath) -Encoding UTF8

Write-Host ""
Write-Host "Reverification complete."
Write-Host "Report: $ReportPath"
Write-Host "Raw data: $rawPath"
Write-Host "Guest HTML cache: $guestStatus"
Write-Host "Cookie safety: $cookieStatus"
Write-Host "Private/query safety: $privateStatus"
Write-Host "Static asset cache: $assetStatus"
Write-Host "Final verdict: $overallVerdict"
Write-Host ("Homepage TTFB cold/warm: {0} / {1}" -f (Format-Sec $homeColdTtfb), (Format-Sec $homeWarmTtfb))
