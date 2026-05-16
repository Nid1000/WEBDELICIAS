$ErrorActionPreference = "Stop"

$here = Split-Path -Parent $MyInvocation.MyCommand.Path
Set-Location $here

Write-Host "Levantando backend (5001) + frontend (3000)..." -ForegroundColor Cyan
npm run dev

