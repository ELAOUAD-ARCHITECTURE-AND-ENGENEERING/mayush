param(
    [string]$BaseUrl = "https://mayushdesign.com",
    [string]$WwwUrl = "https://www.mayushdesign.com",
    [string]$ReportPath = "cloudflare-cache-performance-audit-mayush.md",
    [switch]$SkipLighthouse
)

$ErrorActionPreference = "Stop"
$UserAgent = "Mozilla/5.0 MayushCacheAudit"
$AcceptHtml = "text/html"
$RepoRoot = (Resolve-Path ".").Path
$OutputDir = Join-Path $RepoRoot "tools\audits\output"
New-Item -ItemType Directory -Force -Path $OutputDir | Out-Null

function New-TempFilePath([string]$Prefix, [string]$Extension) {
    $name = "{0}-{1}{2}" -f $Prefix, ([guid]::NewGuid().ToString("N")), $Extension
    return Join-Path $env:TEMP $name
}

function Parse-HeaderBlocks([string]$Path) {
    $lines = Get-Content -LiteralPath $Path -ErrorAction SilentlyContinue
    $blocks = @()
    $current = $null

    foreach ($line in $lines) {
        if ($line -match "^HTTP/") {
            if ($null -ne $current) {
                $blocks += $current
            }
            $parts = $line -split "\s+", 3
            $current = [ordered]@{
                statusLine = $line
                status = if ($parts.Length -ge 2) { [int]$parts[1] } else { $null }
                headers = [ordered]@{}
                setCookie = @()
            }
            continue
        }

        if ($null -eq $current -or [string]::IsNullOrWhiteSpace($line)) {
            continue
        }

        $idx = $line.IndexOf(":")
        if ($idx -lt 1) {
            continue
        }

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

    if ($null -ne $current) {
        $blocks += $current
    }

    return @($blocks | Where-Object { $_["status"] -ne 100 -and $_["statusLine"] -notmatch "Connection established" })
}

function Get-HeaderValue($Headers, [string]$Name) {
    $key = $Name.ToLowerInvariant()
    if ($Headers -and $Headers.Contains($key)) {
        return $Headers[$key]
    }
    return ""
}

function Invoke-CurlProbe {
    param(
        [string]$Url,
        [string]$Method = "GET",
        [hashtable]$ExtraHeaders = @{},
        [string]$OutputBodyPath = "",
        [int]$MaxTimeSeconds = 45,
        [bool]$FollowRedirects = $true
    )

    $headersPath = New-TempFilePath "mayush-headers" ".txt"
    $bodyPath = if ($OutputBodyPath) { $OutputBodyPath } else { New-TempFilePath "mayush-body" ".bin" }
    $writeFormat = "FINAL_URL=%{url_effective}`nSTATUS=%{http_code}`nDNS=%{time_namelookup}`nCONNECT=%{time_connect}`nTLS=%{time_appconnect}`nTTFB=%{time_starttransfer}`nTOTAL=%{time_total}`nSIZE=%{size_download}`nCONTENT_TYPE=%{content_type}`n"

    $args = @(
        "--silent",
        "--show-error",
        "--max-time", "$MaxTimeSeconds",
        "--compressed",
        "--dump-header", $headersPath,
        "--output", $bodyPath,
        "--write-out", $writeFormat,
        "-A", $UserAgent
    )

    if ($FollowRedirects) {
        $args = @("--location") + $args
    }

    if ($Method.ToUpperInvariant() -eq "HEAD") {
        $args += "--head"
    }

    $args += @("-H", "Accept: $AcceptHtml")
    foreach ($key in $ExtraHeaders.Keys) {
        $args += @("-H", "${key}: $($ExtraHeaders[$key])")
    }
    $args += $Url

    $raw = & curl.exe @args 2>&1
    $exitCode = $LASTEXITCODE
    $timing = @{}
    foreach ($line in ($raw -split "`r?`n")) {
        if ($line -match "^([^=]+)=(.*)$") {
            $timing[$matches[1]] = $matches[2]
        }
    }

    $blocks = @(Parse-HeaderBlocks $headersPath | Where-Object { $null -ne $_ })
    $finalBlock = if ($blocks.Count -gt 0) { $blocks[$blocks.Count - 1] } else { [ordered]@{ status = $null; headers = [ordered]@{}; setCookie = @(); statusLine = "" } }
    $headers = $finalBlock["headers"]
    $setCookies = @($finalBlock["setCookie"])

    $result = [ordered]@{
        url = $Url
        method = $Method.ToUpperInvariant()
        curlExitCode = $exitCode
        error = if ($exitCode -eq 0) { "" } else { ($raw -join "`n") }
        status = if ($timing.Contains("STATUS")) { [int]$timing["STATUS"] } else { $finalBlock["status"] }
        finalUrl = if ($timing.Contains("FINAL_URL")) { $timing["FINAL_URL"] } else { $Url }
        server = Get-HeaderValue -Headers $headers -Name "server"
        cfRay = Get-HeaderValue -Headers $headers -Name "cf-ray"
        cfCacheStatus = Get-HeaderValue -Headers $headers -Name "cf-cache-status"
        cacheControl = Get-HeaderValue -Headers $headers -Name "cache-control"
        age = Get-HeaderValue -Headers $headers -Name "age"
        contentType = if ($timing.Contains("CONTENT_TYPE")) { $timing["CONTENT_TYPE"] } else { Get-HeaderValue -Headers $headers -Name "content-type" }
        contentLength = Get-HeaderValue -Headers $headers -Name "content-length"
        sizeBytes = if ($timing.Contains("SIZE") -and $timing["SIZE"] -ne "") { [double]$timing["SIZE"] } else { $null }
        setCookiePresent = ($setCookies.Count -gt 0)
        setCookieCount = $setCookies.Count
        setCookieSamples = @($setCookies | Select-Object -First 3)
        dns = if ($timing.Contains("DNS")) { [double]$timing["DNS"] } else { $null }
        connect = if ($timing.Contains("CONNECT")) { [double]$timing["CONNECT"] } else { $null }
        tls = if ($timing.Contains("TLS")) { [double]$timing["TLS"] } else { $null }
        ttfb = if ($timing.Contains("TTFB")) { [double]$timing["TTFB"] } else { $null }
        total = if ($timing.Contains("TOTAL")) { [double]$timing["TOTAL"] } else { $null }
        redirectChain = @($blocks | ForEach-Object { [ordered]@{ status = $_["status"]; location = Get-HeaderValue -Headers $_["headers"] -Name "location"; cacheStatus = Get-HeaderValue -Headers $_["headers"] -Name "cf-cache-status" } })
    }

    Remove-Item -LiteralPath $headersPath -Force -ErrorAction SilentlyContinue
    if (-not $OutputBodyPath) {
        Remove-Item -LiteralPath $bodyPath -Force -ErrorAction SilentlyContinue
    }

    return [pscustomobject]$result
}

function Normalize-SameSiteUrl([string]$Href, [string]$Base) {
    if ([string]::IsNullOrWhiteSpace($Href)) { return "" }
    if ($Href.StartsWith("#") -or $Href.StartsWith("mailto:") -or $Href.StartsWith("tel:") -or $Href.StartsWith("javascript:")) { return "" }
    try {
        $uri = [System.Uri]::new([System.Uri]::new($Base), $Href)
        if ($uri.Host -notin @("mayushdesign.com", "www.mayushdesign.com")) { return "" }
        if ($uri.AbsolutePath -match "\.(css|js|png|jpe?g|gif|svg|webp|woff2?|ttf|ico|pdf|zip)$") { return "" }
        return $uri.GetLeftPart([System.UriPartial]::Path).TrimEnd("/")
    } catch {
        return ""
    }
}

function Is-PrivateOrDynamicUrl([string]$Url) {
    try {
        $path = ([System.Uri]$Url).AbsolutePath.ToLowerInvariant()
    } catch {
        return $true
    }

    return ($path -match "^/(admin|cart|checkout|login|register|users|shop-reg|seller/login|dashboard|customer|user|orders|wishlist|wishlists|compare|purchase_history|affiliate|profile|password|api|ajax|payment|cmi)(/|$)")
}

function Pick-UrlsFromHomepage([string]$Html, [string]$Base) {
    $hrefs = [regex]::Matches($Html, "href\s*=\s*[""']([^""'#]+)[""']", "IgnoreCase") |
        ForEach-Object { Normalize-SameSiteUrl $_.Groups[1].Value $Base } |
        Where-Object { $_ -and -not (Is-PrivateOrDynamicUrl $_) } |
        Select-Object -Unique

    $categories = @($hrefs | Where-Object { $_ -match "/(category|categories|shop|collections?)/" } | Select-Object -First 2)
    $products = @($hrefs | Where-Object { $_ -match "/(product|item)/" } | Select-Object -First 2)
    $blogs = @($hrefs | Where-Object { $_ -match "/(blog|article|news)/" } | Select-Object -First 1)
    $staticPages = @($hrefs | Where-Object { $_ -match "/(about|contact|privacy|terms|return|refund|faq)" } | Select-Object -First 1)

    return [ordered]@{
        category = $categories
        product = $products
        blog = $blogs
        static = $staticPages
        all = @($hrefs)
    }
}

function Pick-AssetUrls([string]$Html, [string]$Base) {
    $matches = [regex]::Matches($Html, "(?:href|src)\s*=\s*[""']([^""']+\.(?:css|js|png|jpe?g|webp|woff2?|ttf|svg|ico)(?:\?[^""']*)?)[""']", "IgnoreCase")
    $urls = $matches | ForEach-Object {
        try {
            $uri = [System.Uri]::new([System.Uri]::new($Base), $_.Groups[1].Value)
            if ($uri.Host -in @("mayushdesign.com", "www.mayushdesign.com")) { $uri.AbsoluteUri } else { "" }
        } catch { "" }
    } | Where-Object { $_ } | Select-Object -Unique

    $wanted = @()
    foreach ($pattern in @("\.css", "\.js", "\.(png|jpe?g)", "\.webp", "\.(woff2?|ttf)")) {
        $candidate = $urls | Where-Object { $_ -match $pattern } | Select-Object -First 1
        if ($candidate) { $wanted += $candidate }
    }
    return @($wanted | Select-Object -Unique)
}

function Join-Status($Runs) {
    return (($Runs | ForEach-Object { if ($_.cfCacheStatus) { $_.cfCacheStatus } else { "(none)" } }) -join " -> ")
}

function Format-Sec($Value) {
    if ($null -eq $Value -or $Value -eq "") { return "" }
    return ("{0:N3}s" -f [double]$Value)
}

function Clean-DisplayValue($Value) {
    if ($null -eq $Value) { return "" }
    return ([string]$Value).Replace([char]0x00A0, " ")
}

function Result-ForPublic($Runs) {
    $statuses = @($Runs | ForEach-Object { $_.cfCacheStatus.ToUpperInvariant() })
    if ($statuses -contains "HIT") { return "PASS" }
    if ($statuses | Where-Object { $_ -in @("DYNAMIC", "BYPASS") }) { return "FAIL" }
    return "WARNING"
}

function Result-ForBypass($Runs) {
    $statuses = @($Runs | ForEach-Object { $_.cfCacheStatus.ToUpperInvariant() })
    if ($statuses -contains "HIT") { return "FAIL" }
    return "PASS"
}

function Result-ForStatic($Run) {
    if ($Run.cfCacheStatus.ToUpperInvariant() -in @("HIT", "MISS", "REVALIDATED", "EXPIRED")) { return "PASS" }
    return "WARNING"
}

Write-Host "Starting Mayush Cloudflare cache and performance audit..."
$startedAt = (Get-Date).ToUniversalTime().ToString("yyyy-MM-dd HH:mm:ss 'UTC'")

$canonical = @()
$canonical += Invoke-CurlProbe -Url $BaseUrl -Method "HEAD"
$canonical += Invoke-CurlProbe -Url $WwwUrl -Method "HEAD"

$homepageBodyPath = Join-Path $OutputDir "homepage.html"
$homepageDiscoveryRun = Invoke-CurlProbe -Url $BaseUrl -Method "GET" -OutputBodyPath $homepageBodyPath
$homepageHtml = Get-Content -LiteralPath $homepageBodyPath -Raw -ErrorAction SilentlyContinue
$picked = Pick-UrlsFromHomepage -Html $homepageHtml -Base $BaseUrl
$assets = Pick-AssetUrls -Html $homepageHtml -Base $BaseUrl

$publicUrls = New-Object System.Collections.Generic.List[string]
$publicUrls.Add($BaseUrl.TrimEnd("/"))
foreach ($u in $picked.category) { if (-not $publicUrls.Contains($u)) { $publicUrls.Add($u) } }
foreach ($u in $picked.product) { if (-not $publicUrls.Contains($u)) { $publicUrls.Add($u) } }
foreach ($u in $picked.blog) { if (-not $publicUrls.Contains($u)) { $publicUrls.Add($u) } }
foreach ($u in $picked.static) { if (-not $publicUrls.Contains($u)) { $publicUrls.Add($u) } }

Write-Host ("Selected public HTML URLs: {0}" -f ($publicUrls -join ", "))

$publicResults = @()
foreach ($url in $publicUrls) {
    $runs = @()
    for ($i = 1; $i -le 3; $i++) {
        $runs += Invoke-CurlProbe -Url $url -Method "GET"
        Start-Sleep -Milliseconds 500
    }
    $publicResults += [pscustomobject]@{
        url = $url
        runs = $runs
        result = Result-ForPublic $runs
    }
}

$privateRoutes = @(
    "/cart", "/checkout", "/login", "/register", "/admin", "/seller",
    "/dashboard", "/customer", "/user", "/orders", "/wishlist", "/compare"
)
$privateResults = @()
foreach ($path in $privateRoutes) {
    $url = $BaseUrl.TrimEnd("/") + $path
    $runs = @()
    for ($i = 1; $i -le 2; $i++) {
        $runs += Invoke-CurlProbe -Url $url -Method "GET" -FollowRedirects $false
        Start-Sleep -Milliseconds 300
    }
    $privateResults += [pscustomobject]@{
        url = $url
        runs = $runs
        result = Result-ForBypass $runs
    }
}

$productForCookie = @($picked.product | Select-Object -First 1)
if (-not $productForCookie -or $productForCookie.Count -eq 0) {
    $productForCookie = @($publicUrls | Where-Object { $_ -ne $BaseUrl.TrimEnd("/") } | Select-Object -First 1)
}
$cookieUrls = @($BaseUrl.TrimEnd("/")) + $productForCookie
$cookieSpecs = @(
    "logged_in=1",
    "laravel_session=test; XSRF-TOKEN=test",
    "cart=test"
)
$cookieResults = @()
foreach ($url in ($cookieUrls | Select-Object -Unique)) {
    foreach ($cookie in $cookieSpecs) {
        $run = Invoke-CurlProbe -Url $url -Method "GET" -ExtraHeaders @{ "Cookie" = $cookie }
        $cookieResults += [pscustomobject]@{
            url = $url
            cookie = $cookie
            run = $run
            result = if ($run.cfCacheStatus.ToUpperInvariant() -eq "HIT") { "FAIL" } else { "PASS" }
        }
        Start-Sleep -Milliseconds 300
    }
}

$queryUrls = @($BaseUrl.TrimEnd("/") + "/?test=1")
$queryCandidate = @($picked.category + $picked.product | Select-Object -First 1)
if ($queryCandidate) {
    $queryUrls += ($queryCandidate.TrimEnd("/") + "?test=1")
}
$queryResults = @()
foreach ($url in ($queryUrls | Select-Object -Unique)) {
    $runs = @()
    for ($i = 1; $i -le 2; $i++) {
        $runs += Invoke-CurlProbe -Url $url -Method "GET"
        Start-Sleep -Milliseconds 300
    }
    $queryResults += [pscustomobject]@{
        url = $url
        runs = $runs
        result = Result-ForBypass $runs
    }
}

$assetResults = @()
foreach ($url in ($assets | Select-Object -First 6)) {
    $run = Invoke-CurlProbe -Url $url -Method "HEAD"
    $assetResults += [pscustomobject]@{
        url = $url
        run = $run
        result = Result-ForStatic $run
    }
}

$lighthouseResults = @()
$lighthouseNote = ""
if (-not $SkipLighthouse) {
    $lighthouseCmd = Join-Path $RepoRoot "node_modules\.bin\lighthouse.cmd"
    if (Test-Path -LiteralPath $lighthouseCmd) {
        $lighthouseUrls = New-Object System.Collections.Generic.List[string]
        $lighthouseUrls.Add($BaseUrl.TrimEnd("/"))
        $firstCategory = @($picked.category | Select-Object -First 1)
        $firstProduct = @($picked.product | Select-Object -First 1)
        if ($firstCategory -and -not $lighthouseUrls.Contains($firstCategory[0])) { $lighthouseUrls.Add($firstCategory[0]) }
        if ($firstProduct -and -not $lighthouseUrls.Contains($firstProduct[0])) { $lighthouseUrls.Add($firstProduct[0]) }

        foreach ($url in $lighthouseUrls) {
            $lhOut = Join-Path $OutputDir ("lighthouse-" + ([guid]::NewGuid().ToString("N")) + ".json")
            $lhArgs = @(
                $url,
                "--quiet",
                "--chrome-flags=--headless=new --no-sandbox --disable-gpu",
                "--only-categories=performance",
                "--output=json",
                "--output-path=$lhOut",
                "--max-wait-for-load=45000"
            )
            Write-Host "Running Lighthouse for $url"
            & $lighthouseCmd @lhArgs | Out-Null
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
                $lighthouseNote = "Lighthouse was available locally but failed for at least one URL."
            }
        }
    } else {
        $lighthouseNote = "Lighthouse was not available in node_modules or PATH."
    }
} else {
    $lighthouseNote = "Lighthouse was skipped by flag."
}

$raw = [ordered]@{
    startedAt = $startedAt
    userAgent = $UserAgent
    canonical = $canonical
    selectedPublicUrls = @($publicUrls)
    discovered = $picked
    publicResults = $publicResults
    privateResults = $privateResults
    cookieResults = $cookieResults
    queryResults = $queryResults
    assetResults = $assetResults
    lighthouseResults = $lighthouseResults
    lighthouseNote = $lighthouseNote
}
$rawPath = Join-Path $OutputDir "cloudflare-cache-performance-audit-mayush.raw.json"
$raw | ConvertTo-Json -Depth 10 | Set-Content -LiteralPath $rawPath -Encoding UTF8

$publicPass = @($publicResults | Where-Object { $_.result -eq "PASS" }).Count
$publicFail = @($publicResults | Where-Object { $_.result -eq "FAIL" }).Count
$privateFail = @($privateResults | Where-Object { $_.result -eq "FAIL" }).Count
$cookieFail = @($cookieResults | Where-Object { $_.result -eq "FAIL" }).Count
$queryFail = @($queryResults | Where-Object { $_.result -eq "FAIL" }).Count

$guestVerdict = if ($publicFail -gt 0) { "No" } elseif ($publicPass -eq $publicResults.Count -and $publicResults.Count -gt 0) { "Yes" } else { "Partial" }
$privateVerdict = if ($privateFail -gt 0 -or $cookieFail -gt 0) { "No" } else { "Yes" }
$finalVerdict = if ($privateVerdict -eq "No") { "RED" } elseif ($guestVerdict -eq "Yes" -and $queryFail -eq 0) { "GREEN" } else { "YELLOW" }

$homeRuns = @($publicResults | Where-Object { $_.url -eq $BaseUrl.TrimEnd("/") } | Select-Object -First 1).runs
$homeColdTtfb = if ($homeRuns.Count -gt 0) { $homeRuns[0].ttfb } else { $null }
$homeWarmTtfb = if ($homeRuns.Count -gt 1) { $homeRuns[$homeRuns.Count - 1].ttfb } else { $null }
$allPublicRuns = @($publicResults | ForEach-Object { $_.runs })
$warmRuns = @($publicResults | ForEach-Object { $_.runs | Select-Object -Skip 1 })
$bestTtfb = ($allPublicRuns | Where-Object { $null -ne $_.ttfb } | Measure-Object -Property ttfb -Minimum).Minimum
$avgWarmTtfb = ($warmRuns | Where-Object { $null -ne $_.ttfb } | Measure-Object -Property ttfb -Average).Average

$md = New-Object System.Collections.Generic.List[string]
$md.Add("# Cloudflare Cache Performance Audit - Mayush Marketplace")
$md.Add("")
$md.Add("## A. Executive Summary")
$md.Add("")
$md.Add("- Cloudflare cache working for guest HTML: **$guestVerdict**.")
$md.Add("- Private pages safe from caching: **$privateVerdict**.")
$md.Add("- Current performance situation: homepage cold TTFB was **$(Format-Sec $homeColdTtfb)** and final warm TTFB was **$(Format-Sec $homeWarmTtfb)**. Best observed public HTML TTFB was **$(Format-Sec $bestTtfb)**.")
$md.Add("- Main risks found: see section I for private cache, cookie, query string, Set-Cookie, and cache warming findings.")
$md.Add("- Final recommendation: **$finalVerdict**. Prioritize fixing any HIT responses on private/cookie/query probes before relying on guest HTML edge cache.")
$md.Add("")
$md.Add("## B. Configuration Observed")
$md.Add("")
$md.Add("- Domain tested: $BaseUrl and $WwwUrl")
$md.Add("- Date/time of test: $startedAt")
$md.Add("- Tooling used: curl.exe HEAD/GET with timing output; local Lighthouse when available")
$md.Add("- User agent: " + $UserAgent)
$cloudflareDetected = if (@($canonical | Where-Object { $_.cfRay }).Count -gt 0) { "Yes" } else { "No" }
$md.Add("- Cloudflare headers detected: " + $cloudflareDetected)
$md.Add("")
$md.Add("| Requested URL | Status | Final URL | Server | CF-Ray | CF-Cache-Status | Cache-Control | Age | Set-Cookie | Redirect Chain |")
$md.Add("|---|---:|---|---|---|---|---|---:|---|---|")
foreach ($c in $canonical) {
    $chain = (($c.redirectChain | ForEach-Object { "$($_.status) $($_.location)" }) -join " ; ").Trim()
    $md.Add("| $($c.url) | $($c.status) | $($c.finalUrl) | $($c.server) | $($c.cfRay) | $($c.cfCacheStatus) | $($c.cacheControl) | $($c.age) | $($c.setCookiePresent) | $chain |")
}
$md.Add("")
$md.Add("## C. Guest HTML Cache Results")
$md.Add("")
$md.Add("| URL | Run 1 cache | Run 2 cache | Run 3 cache | Age header | Cold TTFB | Warm TTFB | Result |")
$md.Add("|---|---|---|---|---:|---:|---:|---|")
foreach ($row in $publicResults) {
    $runs = @($row.runs)
    $age = (($runs | ForEach-Object { $_.age } | Where-Object { $_ } | Select-Object -Last 1) -join "")
    $md.Add("| $($row.url) | $($runs[0].cfCacheStatus) | $($runs[1].cfCacheStatus) | $($runs[2].cfCacheStatus) | $age | $(Format-Sec $runs[0].ttfb) | $(Format-Sec $runs[2].ttfb) | $($row.result) |")
}
$md.Add("")
$md.Add("## D. Private/Dynamic Route Safety Results")
$md.Add("")
$md.Add("| URL | Status code | Cache status progression | Set-Cookie present | TTFB | Result |")
$md.Add("|---|---:|---|---|---:|---|")
foreach ($row in $privateResults) {
    $runs = @($row.runs)
    $md.Add("| $($row.url) | $($runs[-1].status) | $(Join-Status $runs) | $($runs[-1].setCookiePresent) | $(Format-Sec $runs[-1].ttfb) | $($row.result) |")
}
$md.Add("")
$md.Add("## E. Cookie Safety Results")
$md.Add("")
$md.Add("| URL | Cookie used | Cache status | Set-Cookie present | Result |")
$md.Add("|---|---|---|---|---|")
foreach ($row in $cookieResults) {
    $md.Add("| $($row.url) | $($row.cookie) | $($row.run.cfCacheStatus) | $($row.run.setCookiePresent) | $($row.result) |")
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
$md.Add("| URL | Asset type | Cache status | Cache-Control | Age | Size | Result |")
$md.Add("|---|---|---|---|---:|---:|---|")
foreach ($row in $assetResults) {
    $assetType = if ($row.run.url -match "\.css") { "CSS" } elseif ($row.run.url -match "\.js") { "JS" } elseif ($row.run.url -match "\.webp") { "WebP" } elseif ($row.run.url -match "\.(woff2?|ttf)") { "Font" } elseif ($row.run.url -match "\.(png|jpe?g)") { "Image" } else { "Asset" }
    $assetSize = if ($row.run.contentLength) { $row.run.contentLength } elseif ($row.run.method -ne "HEAD" -and $row.run.sizeBytes -gt 0) { $row.run.sizeBytes } else { "" }
    $md.Add("| $($row.url) | $assetType | $($row.run.cfCacheStatus) | $($row.run.cacheControl) | $($row.run.age) | $assetSize | $($row.result) |")
}
$md.Add("")
$md.Add("## H. Performance Situation")
$md.Add("")
$md.Add("- Homepage cold TTFB: **$(Format-Sec $homeColdTtfb)**.")
$md.Add("- Homepage warm TTFB: **$(Format-Sec $homeWarmTtfb)**.")
$md.Add("- Best observed TTFB: **$(Format-Sec $bestTtfb)**.")
$md.Add("- Average public HTML warm TTFB after first run: **$(Format-Sec $avgWarmTtfb)**.")
$md.Add("- Cache status progressions: " + (($publicResults | ForEach-Object { "$($_.url): $(Join-Status $_.runs)" }) -join " | "))
$ttfbMovement = if ($homeWarmTtfb -and $homeWarmTtfb -lt 0.1) { "Observed on homepage warm request." } elseif ($bestTtfb -and $bestTtfb -lt 0.1) { "Observed on at least one public HTML URL, but not consistently." } else { "Not observed consistently in this run." }
$md.Add("- Expected TTFB movement from roughly 2.97s toward sub-100ms/sub-50ms edge response: " + $ttfbMovement)
if ($lighthouseResults.Count -gt 0) {
    $md.Add("")
    $md.Add("### Optional Lighthouse Results")
    $md.Add("")
    $md.Add("| URL | Performance | FCP | LCP | TBT | CLS | Speed Index | Server response |")
    $md.Add("|---|---:|---:|---:|---:|---:|---:|---|")
    foreach ($lh in $lighthouseResults) {
        $md.Add("| $($lh.url) | $($lh.score) | $($lh.fcp) | $($lh.lcp) | $($lh.tbt) | $($lh.cls) | $($lh.speedIndex) | $($lh.serverResponse) |")
    }
} else {
    $md.Add("")
    $md.Add("Lighthouse note: $lighthouseNote")
}
$md.Add("")
$md.Add("## I. Problems Found")
$md.Add("")
if ($publicFail -gt 0) { $md.Add("- Public pages still returned DYNAMIC/BYPASS instead of warming to HIT.") }
if (@($publicResults | Where-Object { $_.result -ne "PASS" }).Count -gt 0) { $md.Add("- HTML cache did not warm to HIT for every selected guest URL.") }
if ($privateFail -gt 0) { $md.Add("- Critical: at least one private/dynamic route returned HIT.") }
if ($cookieFail -gt 0) { $md.Add("- Critical/risk: at least one cookie-bearing request received cached HTML.") }
if ($queryFail -gt 0) { $md.Add("- Query-string URL returned HIT; confirm this is intentional or exclude query strings.") }
if (@($publicResults | Where-Object { @($_.runs | Where-Object { $_.setCookiePresent }).Count -gt 0 }).Count -gt 0) { $md.Add("- Set-Cookie appeared on at least one guest public HTML response.") }
if (@($canonical | Where-Object { $_.redirectChain.Count -gt 3 }).Count -gt 0) { $md.Add("- Redirect chain is longer than expected; inspect canonical behavior.") }
if ($publicFail -eq 0 -and $privateFail -eq 0 -and $cookieFail -eq 0 -and $queryFail -eq 0) { $md.Add("- No critical cache-safety failures were observed in this bounded audit.") }
$md.Add("")
$md.Add("## J. Recommended Fixes")
$md.Add("")
$md.Add('- Keep the guest HTML cache rule constrained to browser HTML requests, public paths, empty query strings, and no cookies: `http.cookie eq ""` and `http.request.uri.query eq ""`.')
$md.Add('- Ensure bypass rules cover `/cart`, `/checkout`, `/login`, `/register`, `/admin`, `/seller`, `/dashboard`, `/customer`, `/user`, `/orders`, `/wishlist`, `/compare`, `/api`, `/ajax`, `/payment`, and `/cmi`.')
$md.Add("- If any private route returns HIT, move or reprioritize the bypass rules so they win before guest HTML caching, according to the active Cloudflare ruleset order.")
$md.Add("- If cookie probes return HIT, add or fix the cookie-empty condition and purge affected HTML cache immediately.")
$md.Add('- If guest HTML sends unnecessary `Set-Cookie`, remove session initialization from public storefront rendering paths or exclude those responses from HTML cache.')
$md.Add('- If public pages do not warm to HIT, check Cloudflare cache eligibility, origin `Cache-Control`, rule expression, Cache Rules order, and whether workers/page rules are overriding cache behavior.')
$md.Add("- Add purge automation for product, category, banner, content page, and menu changes.")
$md.Add("- Keep HTML Edge Cache TTL short at first, such as 10 minutes, and keep Browser TTL respecting origin.")
$md.Add("")
$md.Add("## K. Final Verdict")
$md.Add("")
$md.Add("**$finalVerdict**")
$md.Add("")
$md.Add("Next Action Plan:")
$md.Add("")
$md.Add("1. Fix any FAIL rows in private route, cookie, or query-string safety before expanding cache coverage.")
$md.Add("2. Tune the guest HTML cache rule until selected public URLs warm to HIT with age greater than 0 and stable low TTFB.")
$md.Add("3. Re-run this audit after any Cloudflare rule changes and after adding purge automation.")
$md.Add("")
$md.Add('Raw structured results: `tools/audits/output/cloudflare-cache-performance-audit-mayush.raw.json`')

$md | Set-Content -LiteralPath (Join-Path $RepoRoot $ReportPath) -Encoding UTF8

Write-Host ""
Write-Host "Audit complete."
Write-Host "Report: $ReportPath"
Write-Host "Raw data: $rawPath"
Write-Host "Guest HTML cache: $guestVerdict"
Write-Host "Private/cookie safety: $privateVerdict"
Write-Host "Final verdict: $finalVerdict"
Write-Host ("Homepage TTFB cold/warm: {0} / {1}" -f (Format-Sec $homeColdTtfb), (Format-Sec $homeWarmTtfb))
