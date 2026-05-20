$ErrorActionPreference = "Stop"

$here = Split-Path -Parent $MyInvocation.MyCommand.Path
$deployRoot = Join-Path $here "deploy"

if (Test-Path -LiteralPath $deployRoot) {
  Remove-Item -LiteralPath $deployRoot -Recurse -Force
}
New-Item -ItemType Directory -Path $deployRoot | Out-Null

function Copy-Tree {
  param(
    [string]$Source,
    [string]$Destination,
    [string[]]$ExcludeDirs = @(),
    [string[]]$ExcludeFiles = @()
  )
  New-Item -ItemType Directory -Path $Destination -Force | Out-Null
  $cmd = @(
    "robocopy",
    "`"$Source`"",
    "`"$Destination`"",
    "/MIR",
    "/R:1",
    "/W:1",
    "/NFL",
    "/NDL",
    "/NJH",
    "/NJS"
  )
  foreach ($d in $ExcludeDirs) { $cmd += "/XD"; $cmd += "`"$d`"" }
  foreach ($f in $ExcludeFiles) { $cmd += "/XF"; $cmd += "`"$f`"" }
  & cmd /c ($cmd -join " ")
}

Write-Host "Creating deploy package in $deployRoot" -ForegroundColor Cyan

# Backend (Laravel)
$backendSrc = Join-Path $here "backend"
$backendDst = Join-Path $deployRoot "backend"
Copy-Tree -Source $backendSrc -Destination $backendDst -ExcludeDirs @(
  "$backendSrc\\.github",
  "$backendSrc\\node_modules",
  "$backendSrc\\tests",
  "$backendSrc\\storage\\logs",
  "$backendSrc\\storage\\framework\\cache",
  "$backendSrc\\storage\\framework\\sessions",
  "$backendSrc\\storage\\framework\\views",
  "$backendSrc\\storage\\framework\\testing"
) -ExcludeFiles @(
  "$backendSrc\\backend-dev.log",
  "$backendSrc\\backend-dev.err.log"
)

# Frontend (Next)
$frontendSrc = Join-Path $here "frontend"
$frontendDst = Join-Path $deployRoot "frontend"
Copy-Tree -Source $frontendSrc -Destination $frontendDst -ExcludeDirs @(
  "$frontendSrc\\node_modules",
  "$frontendSrc\\.next\\cache"
)

Write-Host "Deploy package ready." -ForegroundColor Green
