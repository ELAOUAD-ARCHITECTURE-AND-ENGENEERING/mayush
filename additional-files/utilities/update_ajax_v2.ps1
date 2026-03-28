$files = Get-ChildItem -Path "C:\xampp\htdocs\mayush\resources\views\frontend\" -Recurse -Filter "*.blade.php"
foreach ($file in $files) {
    if ($file.Attributes -match "Directory") { continue }
    try {
        $content = Get-Content $file.FullName -Raw -ErrorAction SilentlyContinue
        if ($null -eq $content) { continue }
        $changed = $false
        
        # More flexible regex to match variations like route('home.section.newest_products')
        $newContent = $content -replace "(\$\.post)\s*\(\s*'{{ route\('home\.(section\.)?(todays_deal|newest_products|preorder_products|home_categories)'\)", "$.get('{{ route('home.$2$3')"
        if ($newContent -ne $content) {
            Write-Host "UPDATING $($file.FullName)"
            Set-Content -Path $file.FullName -Value $newContent -NoNewline
            $changed = $true
        }
    }
    catch {
        Write-Host "Could not read $($file.FullName)"
    }
}
