$ErrorActionPreference = "Stop"

function Ensure-Dir([string]$path) {
  if (-not (Test-Path $path)) {
    New-Item -ItemType Directory -Path $path | Out-Null
  }
}

$here = Split-Path -Parent $MyInvocation.MyCommand.Path
Set-Location $here

$tools = Join-Path $here "tools"
$phpDir = Join-Path $tools "php"
$phpExe = Join-Path $phpDir "php.exe"
$phpIni = Join-Path $phpDir "php.ini"
$caCert = Join-Path $tools "cacert.pem"

Ensure-Dir $tools
Ensure-Dir $phpDir

# PHP (portable ZIP) - recomendado NTS x64 para CLI
$phpVersion = "8.3.30"
$phpZipUrl = "https://downloads.php.net/~windows/releases/archives/php-$phpVersion-nts-Win32-vs16-x64.zip"
$phpZipSha256 = "42637b42b38b9c0d731e59c5cb8b755693a01b110cd2f31951f67de5cb4cd129"
$phpZipPath = Join-Path $tools "php-$phpVersion-nts-x64.zip"

if (-not (Test-Path $phpExe)) {
  Write-Host "Descargando PHP $phpVersion (NTS x64) ..."
  Invoke-WebRequest -UseBasicParsing $phpZipUrl -OutFile $phpZipPath

  Write-Host "Verificando SHA256 de PHP ..."
  $hash = (Get-FileHash -Algorithm SHA256 -Path $phpZipPath).Hash.ToLowerInvariant()
  if ($hash -ne $phpZipSha256) {
    throw "SHA256 no coincide para PHP ZIP. Esperado: $phpZipSha256, Actual: $hash"
  }

  Write-Host "Extrayendo PHP a $phpDir ..."
  Expand-Archive -Path $phpZipPath -DestinationPath $phpDir -Force
}

if (-not (Test-Path $phpExe)) {
  throw "PHP no quedó instalado en $phpExe. Revisa permisos/antivirus y vuelve a ejecutar."
}

# Config mínima para que PHP pueda hacer HTTPS (Composer) y usar extensiones comunes
if (-not (Test-Path $phpIni)) {
  Write-Host "Creando php.ini mínimo (openssl/curl/pdo_mysql)..."
  @"
[PHP]
extension_dir = "ext"
extension = openssl
extension = curl
extension = mbstring
extension = fileinfo
extension = pdo_mysql
date.timezone = UTC
allow_url_fopen = On

[curl]
curl.cainfo = "$caCert"

[openssl]
openssl.cafile = "$caCert"
"@ | Set-Content -Path $phpIni -Encoding ASCII
}

if (-not (Test-Path $caCert)) {
  Write-Host "Descargando bundle de CA (cacert.pem) ..."
  Invoke-WebRequest -UseBasicParsing "https://curl.se/ca/cacert.pem" -OutFile $caCert
}

# Composer (installer verificado con installer.sig)
$composerPhar = Join-Path $tools "composer.phar"
$composerSetup = Join-Path $tools "composer-setup.php"
$composerSig = Join-Path $tools "composer-installer.sig"

if (-not (Test-Path $composerPhar)) {
  Write-Host "Descargando composer.phar (latest-stable) ..."
  Invoke-WebRequest -UseBasicParsing "https://getcomposer.org/download/latest-stable/composer.phar" -OutFile $composerPhar
}

Write-Host "OK. Versiones:"
& $phpExe -v
& $phpExe $composerPhar --version

Write-Host ""
Write-Host "Siguiente paso (Laravel):"
Write-Host "  powershell -ExecutionPolicy Bypass -File .\\bootstrap-laravel.ps1"
