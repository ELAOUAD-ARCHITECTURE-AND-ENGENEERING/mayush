$files = Get-ChildItem -Path "C:\xampp\htdocs\mayush\resources\views\frontend\" -Recurse -Filter "*.blade.php"
foreach ($file in $files) {
    $content = Get-Content $file.FullName -Raw
    if ($content -match "home\.section") {
        Write-Host "FOUND in $($file.FullName)"
        $lines = $content -split "`n"
        for ($i = 0; $i -lt $lines.Count; $i++) {
            if ($lines[$i] -match "home\.section") {
                Write-Host "  Line $($i+1): $($lines[$i].Trim())"
            }
        }
    }
}
