$files = Get-ChildItem -Path "C:\xampp\htdocs\mayush\resources\views\frontend\" -Recurse -Filter "*.blade.php"
foreach ($file in $files) {
    $matches = Select-String -Path $file.FullName -Pattern "home\.section\.newest_products"
    if ($matches) {
        Write-Host "FOUND in $($file.FullName)"
        foreach ($match in $matches) {
            Write-Host "  Line $($match.LineNumber): $($match.Line.Trim())"
        }
    }
}
