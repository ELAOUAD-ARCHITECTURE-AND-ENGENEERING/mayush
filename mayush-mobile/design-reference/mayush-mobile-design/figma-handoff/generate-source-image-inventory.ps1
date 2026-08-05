$ErrorActionPreference = 'Stop'
$root = (Resolve-Path (Join-Path $PSScriptRoot '..')).Path
$outCsv = Join-Path $PSScriptRoot 'source-image-inventory.csv'
$outSummary = Join-Path $PSScriptRoot 'import-summary.json'
$folders = @('00-foundation','01-entry','02-discovery','03-product','04-auth','05-cart-wishlist','06-checkout','07-orders','08-account','09-support-settings','10-system-states','11-arabic-rtl','assetsl')
$pageMap = @{ '00-foundation'='01 — Foundations & Variables'; '01-entry'='10 — Entry'; '02-discovery'='20 — Discovery'; '03-product'='30 — Product'; '04-auth'='40 — Authentication'; '05-cart-wishlist'='50 — Cart & Wishlist'; '06-checkout'='60 — Checkout'; '07-orders'='70 — Orders'; '08-account'='80 — Account'; '09-support-settings'='90 — Support & Settings'; '10-system-states'='100 — System States'; '11-arabic-rtl'='110 — Arabic RTL'; 'assetsl'='120 — Assets' }
function Get-PngSize([string]$path) {
  $stream = [System.IO.File]::OpenRead($path)
  try { $buffer = New-Object byte[] 24; [void]$stream.Read($buffer, 0, 24); $width = (([uint32]$buffer[16] -shl 24) -bor ([uint32]$buffer[17] -shl 16) -bor ([uint32]$buffer[18] -shl 8) -bor [uint32]$buffer[19]); $height = (([uint32]$buffer[20] -shl 24) -bor ([uint32]$buffer[21] -shl 16) -bor ([uint32]$buffer[22] -shl 8) -bor [uint32]$buffer[23]); return [PSCustomObject]@{ Width = $width; Height = $height } }
  finally { $stream.Dispose() }
}
$rows = [System.Collections.Generic.List[object]]::new(); $index = 0
foreach ($folder in $folders) {
  $folderPath = Join-Path $root $folder
  Get-ChildItem -LiteralPath $folderPath -File -Recurse | Where-Object { $_.Extension -match '^(?i)\.(png|jpe?g|webp|svg)$' } | Sort-Object FullName | ForEach-Object {
    $index++; $relative = $_.FullName.Substring($root.Length + 1).Replace('\','/')
    $size = if ($_.Extension -ieq '.png') { Get-PngSize $_.FullName } else { [PSCustomObject]@{ Width=''; Height='' } }
    $name = $_.Name.ToLowerInvariant(); $locale = if ($name -match '(^|[-_])ar([-_\.]|$)|arab') {'Arabic'} elseif ($name -match '(^|[-_])fr([-_\.]|$)|french') {'French'} else {'Neutral'}
    $state = if ($name -match 'loading') {'Loading'} elseif ($name -match 'success') {'Success'} elseif ($name -match 'error') {'Error'} elseif ($name -match 'empty|no-results') {'Empty'} elseif ($name -match 'modal') {'Modal'} elseif ($name -match 'bottom-sheet') {'Bottom sheet'} else {'Default'}
    $variant = if ($name -match 'v2|version-2') {'V2'} elseif ($name -match 'ar|fr') {$locale} else {'Default'}
    $rows.Add([PSCustomObject]@{ source_index=$index; source_folder=$folder; source_relative_path=$relative; source_filename=$_.Name; file_extension=$_.Extension.TrimStart('.').ToLowerInvariant(); width=$size.Width; height=$size.Height; file_size_bytes=$_.Length; language_guess=$locale; feature_guess=$folder; state_guess=$state; variant_guess=$variant; figma_target_page=$pageMap[$folder]; figma_target_section=('ARCHIVE / ' + $folder); import_status='PENDING'; figma_node_id=''; error='' })
  }
}
$rows | Export-Csv -LiteralPath $outCsv -NoTypeInformation -Encoding utf8
$folderSummary = @{}; foreach ($folder in $folders) { $folderSummary[$folder] = @($rows | Where-Object source_folder -eq $folder).Count }
[PSCustomObject]@{ sourceRoot=$root; discoveredImageCount=$rows.Count; supportedImageCount=$rows.Count; importedImageCount=0; failedImageCount=0; skippedImageCount=0; duplicateSourcePathCount=0; folders=$folderSummary; lastImportedIndex=0; completed=$false } | ConvertTo-Json -Depth 4 | Set-Content -LiteralPath $outSummary -Encoding utf8
Write-Output ("inventory=" + $rows.Count)
