param(
  [Parameter(Mandatory = $true)]
  [int]$PendingOffset,
  [Parameter(Mandatory = $true)]
  [string[]]$SubmitUrls
)

$inventory = Import-Csv -LiteralPath (Join-Path $PSScriptRoot 'source-image-inventory.csv') |
  Where-Object { $_.import_status -eq 'PENDING' } |
  Sort-Object { [int]$_.source_index }

$batch = @($inventory | Select-Object -Skip $PendingOffset -First $SubmitUrls.Count)
if ($batch.Count -ne $SubmitUrls.Count) {
  throw "Requested $($SubmitUrls.Count) pending items at offset $PendingOffset, but found $($batch.Count)."
}

for ($i = 0; $i -lt $batch.Count; $i++) {
  $relativeFile = $batch[$i].source_relative_path
  & curl.exe -sS --connect-timeout 10 --max-time 40 -X POST -F "file=@$relativeFile;type=image/png" $SubmitUrls[$i]
}
