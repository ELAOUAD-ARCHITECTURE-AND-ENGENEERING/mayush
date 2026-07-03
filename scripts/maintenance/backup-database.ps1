param(
    [string]$OutputDirectory = "storage/app/backups"
)

$ErrorActionPreference = "Stop"

function Read-EnvFile {
    param([string]$Path)

    $values = @{}
    if (!(Test-Path $Path)) {
        throw ".env file not found at $Path"
    }

    Get-Content $Path | ForEach-Object {
        $line = $_.Trim()
        if ($line -eq "" -or $line.StartsWith("#") -or !$line.Contains("=")) {
            return
        }

        $parts = $line.Split("=", 2)
        $key = $parts[0].Trim()
        $value = $parts[1].Trim().Trim('"').Trim("'")
        $values[$key] = $value
    }

    return $values
}

$root = Resolve-Path (Join-Path $PSScriptRoot "../..")
Set-Location $root

$envValues = Read-EnvFile ".env"
$dbConnection = $envValues["DB_CONNECTION"]
if ($dbConnection -ne "mysql") {
    throw "Only mysql backups are supported by this script. Current DB_CONNECTION=$dbConnection"
}

$dbHost = $envValues["DB_HOST"]
if ([string]::IsNullOrWhiteSpace($dbHost)) {
    $dbHost = "127.0.0.1"
}

$dbPort = $envValues["DB_PORT"]
if ([string]::IsNullOrWhiteSpace($dbPort)) {
    $dbPort = "3306"
}
$dbName = $envValues["DB_DATABASE"]
$dbUser = $envValues["DB_USERNAME"]
$dbPass = $envValues["DB_PASSWORD"]

if ([string]::IsNullOrWhiteSpace($dbName) -or [string]::IsNullOrWhiteSpace($dbUser)) {
    throw "DB_DATABASE and DB_USERNAME must be set in .env"
}

$mysqldump = Get-Command mysqldump -ErrorAction SilentlyContinue
if (!$mysqldump -and (Test-Path "C:/xampp/mysql/bin/mysqldump.exe")) {
    $mysqldump = Get-Item "C:/xampp/mysql/bin/mysqldump.exe"
}
if (!$mysqldump) {
    throw "mysqldump was not found in PATH or C:/xampp/mysql/bin"
}
$mysqldumpPath = if ($mysqldump.Source) { $mysqldump.Source } else { $mysqldump.FullName }

New-Item -ItemType Directory -Force -Path $OutputDirectory | Out-Null
$stamp = Get-Date -Format "yyyyMMdd-HHmmss"
$backup = Join-Path $OutputDirectory "$dbName-before-change-$stamp.sql"

$args = @(
    "--single-transaction",
    "--quick",
    "--routines",
    "--triggers",
    "-h", $dbHost,
    "-P", $dbPort,
    "-u", $dbUser,
    "--result-file=$backup",
    $dbName
)

$previousMysqlPwd = $env:MYSQL_PWD
try {
    if (![string]::IsNullOrEmpty($dbPass)) {
        $env:MYSQL_PWD = $dbPass
    }

    & $mysqldumpPath @args
    if ($LASTEXITCODE -ne 0) {
        throw "mysqldump failed with exit code $LASTEXITCODE"
    }
} finally {
    $env:MYSQL_PWD = $previousMysqlPwd
}

Get-Item $backup | Select-Object FullName, Length, LastWriteTime
