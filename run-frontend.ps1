$ErrorActionPreference = "Stop"

$here = Split-Path -Parent $MyInvocation.MyCommand.Path
Set-Location (Join-Path $here "frontend")

Write-Host "Instalando dependencias (si es necesario)..." -ForegroundColor Cyan
if (-not (Test-Path -LiteralPath ".\\node_modules")) {
  npm install
}

Write-Host "Construyendo assets del frontend Laravel..." -ForegroundColor Cyan
npm run build

Write-Host "Frontend Laravel en http://127.0.0.1:3000" -ForegroundColor Cyan
& ..\tools\php\php.exe -S 127.0.0.1:3000 server.php
