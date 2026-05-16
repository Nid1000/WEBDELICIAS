$ErrorActionPreference = "Stop"

function Resolve-Php() {
  $cmd = Get-Command php -ErrorAction SilentlyContinue
  if ($cmd) { return $cmd.Source }

  $local = Join-Path $PSScriptRoot "tools\\php\\php.exe"
  if (Test-Path $local) { return $local }

  throw "No se encontró 'php' en PATH ni en '$local'. Ejecuta primero .\\setup-php-composer.ps1 o instala PHP."
}

function Resolve-ComposerPhar() {
  $local = Join-Path $PSScriptRoot "tools\\composer.phar"
  if (Test-Path $local) { return $local }

  $cmd = Get-Command composer -ErrorAction SilentlyContinue
  if ($cmd) { return $cmd.Source } # fallback si el usuario lo instaló global

  throw "No se encontró 'composer' ni '$local'. Ejecuta primero .\\setup-php-composer.ps1 o instala Composer."
}

$here = Split-Path -Parent $MyInvocation.MyCommand.Path
Set-Location $here

$env:COMPOSER_HOME = Join-Path $here "tools\\composer-home"
$env:COMPOSER_CACHE_DIR = Join-Path $here "tools\\composer-cache"
if (-not (Test-Path $env:COMPOSER_HOME)) { New-Item -ItemType Directory -Path $env:COMPOSER_HOME | Out-Null }
if (-not (Test-Path $env:COMPOSER_CACHE_DIR)) { New-Item -ItemType Directory -Path $env:COMPOSER_CACHE_DIR | Out-Null }

$phpExe = Resolve-Php
$composerResolved = Resolve-ComposerPhar

function Invoke-Composer([string[]]$composerArgs) {
  if ($composerResolved.ToLowerInvariant().EndsWith("composer.phar")) {
    & $phpExe $composerResolved @composerArgs
    return
  }
  & $composerResolved @composerArgs
}

$utf8NoBom = New-Object System.Text.UTF8Encoding($false)
function Write-Utf8NoBom([string]$path, [string]$content) {
  [System.IO.File]::WriteAllText($path, $content, $utf8NoBom)
}

$target = Join-Path $here "backend"
$stubs = Join-Path $here "backend-laravel-stubs"

if (-not (Test-Path $stubs)) {
  throw "No existe la carpeta de stubs: $stubs"
}

if (-not (Test-Path $target)) {
  New-Item -ItemType Directory -Path $target | Out-Null
}

$artisan = Join-Path $target "artisan"
if (-not (Test-Path $artisan)) {
  Write-Host "Creando proyecto Laravel en $target ..."
  Invoke-Composer @("create-project", "laravel/laravel", $target)
}

Set-Location $target

Write-Host "Instalando dependencia JWT (firebase/php-jwt) ..."
Invoke-Composer @("require", "firebase/php-jwt")

Write-Host "Copiando stubs de API (rutas/controladores/middlewares) ..."
Copy-Item -Path (Join-Path $stubs "routes\\api.php") -Destination (Join-Path $target "routes\\api.php") -Force

if (-not (Test-Path (Join-Path $target "app\\Http\\Middleware"))) {
  New-Item -ItemType Directory -Path (Join-Path $target "app\\Http\\Middleware") | Out-Null
}
Copy-Item -Path (Join-Path $stubs "app\\Http\\Middleware\\*.php") -Destination (Join-Path $target "app\\Http\\Middleware") -Force

if (-not (Test-Path (Join-Path $target "app\\Services"))) {
  New-Item -ItemType Directory -Path (Join-Path $target "app\\Services") | Out-Null
}
Copy-Item -Path (Join-Path $stubs "app\\Services\\*.php") -Destination (Join-Path $target "app\\Services") -Force

if (-not (Test-Path (Join-Path $target "app\\Http\\Controllers"))) {
  New-Item -ItemType Directory -Path (Join-Path $target "app\\Http\\Controllers") | Out-Null
}
Copy-Item -Path (Join-Path $stubs "app\\Http\\Controllers\\*.php") -Destination (Join-Path $target "app\\Http\\Controllers") -Force

Write-Host "Registrando aliases de middleware (jwt, tipo)..."
$kernelPath = Join-Path $target "app\\Http\\Kernel.php"
$bootstrapPath = Join-Path $target "bootstrap\\app.php"

if (Test-Path $kernelPath) {
  $k = Get-Content $kernelPath -Raw
  if ($k -notmatch "'jwt'\\s*=>\\s*\\\\App\\\\Http\\\\Middleware\\\\JwtAuth::class") {
    $k = $k -replace "(\\$routeMiddleware\\s*=\\s*\\[)", "`$1`r`n            'jwt' => \App\Http\Middleware\JwtAuth::class,`r`n            'tipo' => \App\Http\Middleware\RequireTipo::class,`r`n"
    Write-Utf8NoBom $kernelPath $k
  }
}
elseif (Test-Path $bootstrapPath) {
  $b = Get-Content $bootstrapPath -Raw
  if ($b -notlike "*'jwt'*JwtAuth*") {
    $needle = '->withMiddleware(function (Middleware $middleware): void {'
      if ($b -like "*$needle*") {
        $b = $b.Replace(
          $needle,
          $needle + "`r`n        " + '$middleware->alias([' + "`r`n" +
            "            'jwt' => \App\Http\Middleware\JwtAuth::class,`r`n" +
            "            'tipo' => \App\Http\Middleware\RequireTipo::class,`r`n" +
            "        ]);"
        )
      Write-Utf8NoBom $bootstrapPath $b
    } else {
      Write-Warning "No pude auto-registrar middlewares en bootstrap/app.php; revisa la documentación de tu versión de Laravel."
    }
  }
}
else {
  Write-Warning "No encontré Kernel.php ni bootstrap/app.php para registrar middlewares automáticamente."
}

Write-Host "Listo. Próximo paso: configurar .env (DB + JWT_SECRET) y ejecutar migraciones/ajustes."
