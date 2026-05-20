$ErrorActionPreference = "Stop"

$here = Split-Path -Parent $MyInvocation.MyCommand.Path
$backend = Join-Path $here "backend"
if (-not (Test-Path -LiteralPath $backend)) {
  throw "No se encontró la carpeta: $backend"
}
Set-Location $backend
$php = Join-Path $here "tools\php\php.exe"
if (-not (Test-Path -LiteralPath $php)) {
  throw "No se encontró PHP portable en: $php"
}
$composer = Join-Path $here "tools\composer.phar"
if (-not (Test-Path -LiteralPath $composer)) {
  throw "No se encontró Composer portable en: $composer"
}

# Instala dependencias si falta vendor/
if (-not (Test-Path -LiteralPath (Join-Path $backend 'vendor'))) {
  Write-Host "Ejecutando composer install..." -ForegroundColor Cyan
  & $php $composer install --no-interaction --prefer-dist
}

if (-not (Test-Path -LiteralPath (Join-Path $backend '.env'))) {
  Write-Host "Creando backend/.env desde .env.example..." -ForegroundColor Yellow
  Copy-Item (Join-Path $backend '.env.example') (Join-Path $backend '.env')
}

New-Item -ItemType Directory -Force (Join-Path $backend 'storage\framework\views') | Out-Null
New-Item -ItemType Directory -Force (Join-Path $backend 'storage\framework\sessions') | Out-Null
New-Item -ItemType Directory -Force (Join-Path $backend 'storage\framework\cache') | Out-Null

if (-not (Select-String -Path (Join-Path $backend '.env') -Pattern '^APP_KEY=base64:' -Quiet)) {
  Write-Host "Generando APP_KEY para backend..." -ForegroundColor Cyan
  & $php artisan key:generate --force
}

if (-not (Select-String -Path (Join-Path $backend '.env') -Pattern '^JWT_SECRET=.+$' -Quiet)) {
  Write-Host "Generando JWT_SECRET para backend..." -ForegroundColor Cyan
  $bytes = New-Object byte[] 48
  [Security.Cryptography.RandomNumberGenerator]::Create().GetBytes($bytes)
  $jwtSecret = [Convert]::ToBase64String($bytes)
  (Get-Content (Join-Path $backend '.env')) -replace '^JWT_SECRET=.*$', "JWT_SECRET=$jwtSecret" | Set-Content (Join-Path $backend '.env')
}

Write-Host "Backend Laravel en http://127.0.0.1:5001 (deja esta ventana abierta)" -ForegroundColor Cyan
& $php -S 127.0.0.1:5001 server.php
