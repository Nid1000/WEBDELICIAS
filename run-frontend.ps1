$ErrorActionPreference = "Stop"

$here = Split-Path -Parent $MyInvocation.MyCommand.Path
$frontend = Join-Path $here "frontend"
$php = Join-Path $here "tools\php\php.exe"
$composer = Join-Path $here "tools\composer.phar"

if (-not (Test-Path -LiteralPath $frontend)) {
  throw "No se encontro la carpeta: $frontend"
}
if (-not (Test-Path -LiteralPath $php)) {
  throw "No se encontro PHP portable en: $php"
}
if (-not (Test-Path -LiteralPath $composer)) {
  throw "No se encontro Composer portable en: $composer"
}

Set-Location $frontend

if (-not (Test-Path -LiteralPath ".\.env")) {
  Write-Host "Creando frontend/.env desde .env.example..." -ForegroundColor Yellow
  Copy-Item ".\.env.example" ".\.env"
}

New-Item -ItemType Directory -Force ".\storage\framework\views" | Out-Null
New-Item -ItemType Directory -Force ".\storage\framework\sessions" | Out-Null
New-Item -ItemType Directory -Force ".\storage\framework\cache" | Out-Null

if (-not (Test-Path -LiteralPath ".\vendor\autoload.php")) {
  Write-Host "Ejecutando composer install para frontend..." -ForegroundColor Cyan
  & $php $composer install --no-interaction --prefer-dist
}

if (-not (Select-String -Path ".\.env" -Pattern '^APP_KEY=base64:' -Quiet)) {
  Write-Host "Generando APP_KEY para frontend..." -ForegroundColor Cyan
  & $php artisan key:generate --force
}

Write-Host "Instalando dependencias (si es necesario)..." -ForegroundColor Cyan
if (-not (Test-Path -LiteralPath ".\node_modules")) {
  npm.cmd install
}

Write-Host "Construyendo assets del frontend Laravel..." -ForegroundColor Cyan
npm.cmd run build

Write-Host "Frontend Laravel en http://127.0.0.1:3000" -ForegroundColor Cyan
& $php -S 127.0.0.1:3000 server.php
