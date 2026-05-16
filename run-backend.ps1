$ErrorActionPreference = "Stop"

$here = Split-Path -Parent $MyInvocation.MyCommand.Path
$backend = Join-Path $here "backend"
if (-not (Test-Path -LiteralPath $backend)) {
  throw "No se encontró la carpeta: $backend"
}
Set-Location $backend

function Resolve-Cmd([string]$name) {
  $cmd = Get-Command $name -ErrorAction SilentlyContinue
  if ($cmd) { return $cmd.Source }
  return $null
}

$php = Resolve-Cmd 'php'
if (-not $php) {
  throw "No se encontró 'php' en PATH. Instala PHP 8.2+ y vuelve a ejecutar."
}

# Instala dependencias si falta vendor/
if (-not (Test-Path -LiteralPath (Join-Path $backend 'vendor'))) {
  $composer = Resolve-Cmd 'composer'
  if (-not $composer) {
    throw "Falta vendor/ y no se encontró 'composer' en PATH. Instala Composer y ejecuta: composer install"
  }
  Write-Host "Ejecutando composer install..." -ForegroundColor Cyan
  & $composer install --no-interaction
}

if (-not (Test-Path -LiteralPath (Join-Path $backend '.env'))) {
  Write-Warning "No existe .env. Copia .env.example a .env y edita DB/JWT_SECRET."
}

Write-Host "Backend Laravel en http://127.0.0.1:5001 (deja esta ventana abierta)" -ForegroundColor Cyan
& $php artisan serve --host 127.0.0.1 --port 5001
