$ErrorActionPreference = "Continue"

$here = Split-Path -Parent $MyInvocation.MyCommand.Path
$repoRoot = Resolve-Path (Join-Path $here "..")
Set-Location $repoRoot

function Remove-IfExists([string] $path) {
  if (Test-Path -LiteralPath $path) {
    Write-Host "Borrando: $path" -ForegroundColor Yellow
    try {
      Remove-Item -LiteralPath $path -Recurse -Force -ErrorAction SilentlyContinue
    } catch {
      Write-Host "Aviso: no se pudo borrar completamente: $path" -ForegroundColor DarkYellow
    }
  }
}

Write-Host "Limpieza segura (borra caches/generados). Esto NO borra tu BD ni tu codigo." -ForegroundColor Cyan
Write-Host "Si quieres cancelar, cierra esta ventana ahora." -ForegroundColor DarkGray

Remove-IfExists (Join-Path $repoRoot ".dart_tool")
Remove-IfExists (Join-Path $repoRoot "build")

Remove-IfExists (Join-Path $here "frontend\\.next")
Remove-IfExists (Join-Path $here "frontend\\node_modules")

Remove-IfExists (Join-Path $here "node_modules")
Remove-IfExists (Join-Path $here "backend\\node_modules")

Write-Host "Listo." -ForegroundColor Green
