$files = Get-ChildItem -Path "C:\xampp\htdocs\mayush\resources\views\frontend\" -Recurse -Filter "*.blade.php"
foreach ($file in $files) {
    $content = Get-Content $file.FullName -Raw
    $changed = $false
    
    if ($content -match "\$\.post\('{{ route\('home\.(section\.)?(todays_deal|newest_products|preorder_products|home_categories)'\)") {
        $newContent = $content -replace "(\$\.post)\('{{ route\('home\.(section\.)?(todays_deal|newest_products|preorder_products|home_categories)'\)", "$.get('{{ route('home.$2$3') }}'"
        if ($newContent -ne $content) {
            Write-Host "UPDATING $($file.FullName)"
            Set-Content -Path $file.FullName -Value $newContent -NoNewline
            $changed = $true
        }
    }
}
