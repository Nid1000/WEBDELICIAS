$ErrorActionPreference = "Stop"

$here = Split-Path -Parent $MyInvocation.MyCommand.Path

Write-Host "Abriendo 2 ventanas: Backend (5001) y Frontend (3000)..." -ForegroundColor Cyan

Start-Process -FilePath "$env:SystemRoot\System32\WindowsPowerShell\v1.0\powershell.exe" -ArgumentList @(
  "-NoExit",
  "-ExecutionPolicy", "Bypass",
  "-File", (Join-Path $here "run-backend.ps1")
)

Start-Process -FilePath "$env:SystemRoot\System32\WindowsPowerShell\v1.0\powershell.exe" -ArgumentList @(
  "-NoExit",
  "-ExecutionPolicy", "Bypass",
  "-File", (Join-Path $here "run-frontend.ps1")
)

Write-Host "Listo. Abre: http://127.0.0.1:3000 y prueba http://127.0.0.1:5001/up" -ForegroundColor Green
