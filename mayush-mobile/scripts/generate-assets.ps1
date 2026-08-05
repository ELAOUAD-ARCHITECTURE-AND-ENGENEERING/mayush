Add-Type -AssemblyName System.Drawing

$logoPath = "c:\laragon\www\mayush\mayush-mobile\assets\brand\logo.png"
$assetsDir = "c:\laragon\www\mayush\mayush-mobile\assets"

function Generate-CenteredAsset {
    param(
        [string]$outputPath,
        [int]$canvasWidth,
        [int]$canvasHeight,
        [int]$targetWidth,
        [string]$bgColorHex = "#F2E8DA",
        [bool]$transparent = $false
    )

    $logo = [System.Drawing.Image]::FromFile($logoPath)
    $canvas = New-Object System.Drawing.Bitmap($canvasWidth, $canvasHeight)
    $g = [System.Drawing.Graphics]::FromImage($canvas)

    if (-not $transparent) {
        $bgColor = [System.Drawing.ColorTranslator]::FromHtml($bgColorHex)
        $g.Clear($bgColor)
    } else {
        $g.Clear([System.Drawing.Color]::Transparent)
    }

    $scale = $targetWidth / $logo.Width
    $targetHeight = [int]($logo.Height * $scale)
    $targetX = [int](($canvasWidth - $targetWidth) / 2)
    $targetY = [int](($canvasHeight - $targetHeight) / 2)

    $g.InterpolationMode = [System.Drawing.Drawing2D.InterpolationMode]::HighQualityBicubic
    $g.SmoothingMode = [System.Drawing.Drawing2D.SmoothingMode]::HighQuality
    $g.DrawImage($logo, $targetX, $targetY, $targetWidth, $targetHeight)

    $canvas.Save($outputPath, [System.Drawing.Imaging.ImageFormat]::Png)

    $g.Dispose()
    $canvas.Dispose()
    $logo.Dispose()
    Write-Host "Generated: $outputPath ($canvasWidth x $canvasHeight)"
}

# 1. Main App Icon (1024 x 1024)
Generate-CenteredAsset -outputPath "$assetsDir\icon.png" -canvasWidth 1024 -canvasHeight 1024 -targetWidth 720 -bgColorHex "#F2E8DA"

# 2. Splash Icon (512 x 512)
Generate-CenteredAsset -outputPath "$assetsDir\splash-icon.png" -canvasWidth 512 -canvasHeight 512 -targetWidth 380 -bgColorHex "#F2E8DA"

# 3. Favicon (48 x 48)
Generate-CenteredAsset -outputPath "$assetsDir\favicon.png" -canvasWidth 48 -canvasHeight 48 -targetWidth 40 -bgColorHex "#F2E8DA"

# 4. Android Adaptive Foreground (432 x 432 - Transparent)
Generate-CenteredAsset -outputPath "$assetsDir\android-icon-foreground.png" -canvasWidth 432 -canvasHeight 432 -targetWidth 280 -transparent $true

# 5. Android Adaptive Background (432 x 432 - Solid #F2E8DA)
$bgCanvas = New-Object System.Drawing.Bitmap(432, 432)
$bgG = [System.Drawing.Graphics]::FromImage($bgCanvas)
$bgG.Clear([System.Drawing.ColorTranslator]::FromHtml('#F2E8DA'))
$bgCanvas.Save("$assetsDir\android-icon-background.png", [System.Drawing.Imaging.ImageFormat]::Png)
$bgG.Dispose()
$bgCanvas.Dispose()
Write-Host "Generated: $assetsDir\android-icon-background.png (432 x 432)"

# 6. Android Adaptive Monochrome (432 x 432 - Transparent)
Generate-CenteredAsset -outputPath "$assetsDir\android-icon-monochrome.png" -canvasWidth 432 -canvasHeight 432 -targetWidth 280 -transparent $true
