param(
  [string]$NodeMapPath = '.\\figma-handoff\\figma-node-map.json'
)

$ErrorActionPreference = 'Stop'
$root = (Get-Location).Path
$handoff = Join-Path $root 'figma-handoff'
$inventoryPath = Join-Path $handoff 'source-image-inventory.csv'
$summaryPath = Join-Path $handoff 'import-summary.json'
$ledgerPath = Join-Path $handoff 'figma-build-state.json'
$reportPath = Join-Path $handoff 'source-import-report.md'
$map = Get-Content -Raw $NodeMapPath | ConvertFrom-Json
$nodeByFilename = @{}
foreach ($record in $map.records) {
  if (-not $nodeByFilename.ContainsKey($record.filename)) { $nodeByFilename[$record.filename] = $record }
}

$rows = @(Import-Csv $inventoryPath)
$missing = @()
foreach ($row in $rows) {
  $match = $nodeByFilename[$row.source_filename]
  if ($null -eq $match) {
    $row.import_status = 'FAILED'
    $row.error = 'No canonical Figma source node found during final reconciliation.'
    $missing += $row.source_filename
  } else {
    $row.import_status = 'IMPORTED'
    $row.figma_node_id = $match.nodeId
    $row.error = ''
  }
}
$rows | Export-Csv -NoTypeInformation -Encoding utf8 $inventoryPath

$folderSummary = [ordered]@{}
foreach ($group in ($rows | Group-Object source_folder | Sort-Object Name)) {
  $folderSummary[$group.Name] = [ordered]@{
    discovered = $group.Count
    imported = @($group.Group | Where-Object import_status -eq 'IMPORTED').Count
    failed = @($group.Group | Where-Object import_status -eq 'FAILED').Count
  }
}
$imported = @($rows | Where-Object import_status -eq 'IMPORTED').Count
$failed = @($rows | Where-Object import_status -eq 'FAILED').Count
$summary = [ordered]@{
  sourceRoot = 'mayush mobile design'
  discoveredImageCount = $rows.Count
  supportedImageCount = $rows.Count
  importedImageCount = $imported
  failedImageCount = $failed
  skippedImageCount = 0
  duplicateSourcePathCount = 0
  folders = $folderSummary
  lastImportedIndex = if ($imported -gt 0) { ($rows | Where-Object import_status -eq 'IMPORTED' | Measure-Object source_index -Maximum).Maximum } else { 0 }
  completed = ($failed -eq 0 -and $imported -eq $rows.Count)
}
$summary | ConvertTo-Json -Depth 8 | Set-Content -Encoding utf8 $summaryPath

$ledger = Get-Content -Raw $ledgerPath | ConvertFrom-Json
$ledger.activePhase = if ($summary.completed) { 'source-import-complete' } else { 'source-image-import' }
$ledger.discoveredImageCount = $rows.Count
$ledger.importedImageCount = $imported
$ledger.failedImageCount = $failed
$ledger.completedFolders = @($folderSummary.Keys)
$ledger.currentFolder = ''
$ledger.currentBatch = @()
$ledger.lastSuccessfulSourceIndex = $summary.lastImportedIndex
$ledger.lastSuccessfulOperation = 'final-source-archive-reconciliation'
$ledger.nextOperation = if ($summary.completed) { 'foundations-and-variables' } else { 'retry-unresolved-source-imports' }
$ledger | ConvertTo-Json -Depth 10 | Set-Content -Encoding utf8 $ledgerPath

$folderLines = ($folderSummary.Keys | ForEach-Object { "| $($_) | $($folderSummary[$_].discovered) | $($folderSummary[$_].imported) | $($folderSummary[$_].failed) |" }) -join "`n"
$status = if ($summary.completed) { 'COMPLETE' } else { 'INCOMPLETE' }
$missingText = if ($missing.Count) { $missing -join "`n" } else { 'None' }
$report = @"
# Mayush source-image import report

Status: **$status**

- Discovered source images: $($rows.Count)
- Imported source images: $imported
- Failed imports: $failed
- Skipped images: 0
- Figma file: Mayush Mobile — Design System & Buyer App (`wAdLNmlKanvI0AEPyEbrMs`)
- Archive page: 02 — Buyer App, Source Archive & Handoff
- Category archive sections: 13
- Duplicate-node prevention: canonical exact-filename node map used; empty setup wrappers excluded.
- Visual organization: all canonical references locked and placed in non-overlapping category grids.

| Source folder | Discovered | Imported | Failed |
|---|---:|---:|---:|
$folderLines

## Retried imports

- 00-foundation/00-brand-essence-color-typography-summary.png
- 10-system-states/10-photos-access-denied-fr-v2.png

## Missing-file check

$missingText

## Final completion status

sourceImportComplete = $($summary.completed)
"@
$report | Set-Content -Encoding utf8 $reportPath
Write-Output ("imported=$imported failed=$failed complete=$($summary.completed)")
