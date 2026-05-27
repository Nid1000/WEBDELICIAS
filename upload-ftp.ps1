param(
  [Parameter(Mandatory = $true)]
  [string]$FtpHost,

  [Parameter(Mandatory = $true)]
  [string]$FtpUser,

  [Parameter(Mandatory = $true)]
  [string]$FtpPassword,

  [string]$RemotePath = "/",

  [string]$PackagePath = ".\webdelicias-cpanel-deploy.tar.gz",

  [switch]$UseFtps
)

$ErrorActionPreference = "Stop"

if (-not (Test-Path -LiteralPath $PackagePath)) {
  throw "No existe el paquete: $PackagePath"
}

$curl = Get-Command curl.exe -ErrorAction Stop
$package = Resolve-Path -LiteralPath $PackagePath
$fileName = Split-Path -Leaf $package
$scheme = if ($UseFtps) { "ftps" } else { "ftp" }
$remote = $RemotePath.TrimEnd("/")
if ($remote -eq "") { $remote = "/" }

$url = if ($remote -eq "/") {
  "${scheme}://${FtpHost}/${fileName}"
} else {
  "${scheme}://${FtpHost}${remote}/${fileName}"
}

Write-Host "Subiendo $fileName a $url" -ForegroundColor Cyan

& $curl.Source `
  --ftp-create-dirs `
  --user "${FtpUser}:${FtpPassword}" `
  --upload-file "$package" `
  "$url"

if ($LASTEXITCODE -ne 0) {
  throw "La subida FTP fallo con codigo $LASTEXITCODE"
}

Write-Host "Subida completada." -ForegroundColor Green
