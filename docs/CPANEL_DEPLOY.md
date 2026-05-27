# Despliegue cPanel - Delicias

Dominio principal: `https://delicas.saborcentral.com`

## 1. Paquete local

El paquete preparado es:

```powershell
C:\Users\Usuario\Downloads\WEBDELICIAS-main\WEBDELICIAS-main\webdelicias-cpanel-deploy.tar.gz
```

Incluye:

- `frontend/`: web Laravel con assets Vite compilados.
- `backend/`: API Laravel.
- `delicias_bakery.sql`: base de datos inicial.

## 2. Estructura recomendada en cPanel

Configura dos sitios/subdominios:

```txt
delicas.saborcentral.com      -> frontend/public
api.delicas.saborcentral.com  -> backend/public
```

Si tu hosting no permite subdominio para la API, usa una carpeta como:

```txt
delicas.saborcentral.com      -> frontend/public
delicas.saborcentral.com/api  -> backend/public
```

Pero la opcion con `api.delicas.saborcentral.com` es mas limpia.

## 3. Subir por FTP desde tu PC

Edita y ejecuta:

```powershell
.\upload-ftp.ps1 `
  -FtpHost "ftp.delicas.saborcentral.com" `
  -FtpUser "TU_USUARIO_FTP" `
  -FtpPassword "TU_PASSWORD_FTP" `
  -RemotePath "/"
```

Si tu hosting usa FTPS:

```powershell
.\upload-ftp.ps1 `
  -FtpHost "ftp.delicas.saborcentral.com" `
  -FtpUser "TU_USUARIO_FTP" `
  -FtpPassword "TU_PASSWORD_FTP" `
  -RemotePath "/" `
  -UseFtps
```

## 4. Extraer paquete en cPanel Terminal

Entra al Terminal de cPanel y ejecuta en la carpeta donde subiste el archivo:

```bash
tar -xzf webdelicias-cpanel-deploy.tar.gz
```

Deberias ver:

```txt
frontend/
backend/
delicias_bakery.sql
```

## 5. Variables de entorno

Crea `frontend/.env`:

```env
APP_NAME="Delicias"
APP_ENV=production
APP_KEY=
APP_DEBUG=false
APP_URL=https://delicas.saborcentral.com
BACKEND_API_BASE_URL=https://api.delicas.saborcentral.com

APP_LOCALE=es
APP_FALLBACK_LOCALE=es
APP_FAKER_LOCALE=es_PE

LOG_CHANNEL=stack
LOG_STACK=single
LOG_LEVEL=error

DB_CONNECTION=mysql
DB_HOST=localhost
DB_PORT=3306
DB_DATABASE=TU_BASE_DATOS
DB_USERNAME=TU_USUARIO_DB
DB_PASSWORD=TU_PASSWORD_DB

JWT_SECRET=PEGA_AQUI_UN_SECRETO_LARGO_IGUAL_AL_BACKEND
DOCUMENT_PROVIDER=apiperu
DOCUMENT_VALIDATION_REQUIRED=false
APIPERU_TOKEN=
DECOLECTA_TOKEN=
YAPE_PHONE=993560096
YAPE_QR_URL=/images/payments/yape-qr.svg

SESSION_DRIVER=file
SESSION_LIFETIME=120
SESSION_ENCRYPT=false
SESSION_PATH=/
SESSION_DOMAIN=.delicas.saborcentral.com

BROADCAST_CONNECTION=log
FILESYSTEM_DISK=local
QUEUE_CONNECTION=sync
CACHE_STORE=file

MAIL_MAILER=log
MAIL_FROM_ADDRESS="no-reply@delicas.saborcentral.com"
MAIL_FROM_NAME="${APP_NAME}"

VITE_APP_NAME="${APP_NAME}"
```

Crea `backend/.env`:

```env
APP_NAME="Delicias API"
APP_ENV=production
APP_KEY=
APP_DEBUG=false
APP_URL=https://api.delicas.saborcentral.com

APP_LOCALE=es
APP_FALLBACK_LOCALE=es
APP_FAKER_LOCALE=es_PE

LOG_CHANNEL=stack
LOG_STACK=single
LOG_LEVEL=error

DB_CONNECTION=mysql
DB_HOST=localhost
DB_PORT=3306
DB_DATABASE=TU_BASE_DATOS
DB_USERNAME=TU_USUARIO_DB
DB_PASSWORD=TU_PASSWORD_DB

JWT_SECRET=PEGA_AQUI_UN_SECRETO_LARGO_IGUAL_AL_FRONTEND
DOCUMENT_PROVIDER=apiperu
DOCUMENT_VALIDATION_REQUIRED=false
APIPERU_TOKEN=
APIPERU_BASE_URL=https://dniruc.apisperu.com/api/v1
DECOLECTA_TOKEN=
DECOLECTA_API_TOKEN=
RENIEC_API_TOKEN=
SUNAT_API_TOKEN=
DECOLECTA_BASE_URL=https://api.decolecta.com/v1

UPLOAD_PATH=uploads/
MAX_FILE_SIZE=5242880

SESSION_DRIVER=file
SESSION_LIFETIME=120
SESSION_ENCRYPT=false
SESSION_PATH=/
SESSION_DOMAIN=.delicas.saborcentral.com

BROADCAST_CONNECTION=log
FILESYSTEM_DISK=local
QUEUE_CONNECTION=sync
CACHE_STORE=file

MAIL_MAILER=log
MAIL_FROM_ADDRESS="no-reply@delicas.saborcentral.com"
MAIL_FROM_NAME="${APP_NAME}"
```

## 6. Comandos Laravel en cPanel Terminal

En `frontend`:

```bash
php artisan key:generate --force
php artisan config:clear
php artisan route:clear
php artisan view:clear
php artisan config:cache
```

En `backend`:

```bash
php artisan key:generate --force
php artisan config:clear
php artisan route:clear
php artisan view:clear
php artisan config:cache
```

## 7. Importar base de datos

Desde phpMyAdmin:

1. Crea la base de datos.
2. Crea/asigna usuario con todos los permisos.
3. Importa `delicias_bakery.sql`.
4. Copia esos datos en `frontend/.env` y `backend/.env`.

Si tienes Terminal y `mysql`:

```bash
mysql -u TU_USUARIO_DB -p TU_BASE_DATOS < delicias_bakery.sql
```

## 8. Verificacion

Abre:

```txt
https://api.delicas.saborcentral.com/api/health
https://delicas.saborcentral.com
https://delicas.saborcentral.com/admin/login
```

Si `/api/health` responde `ok: true`, la API y la base de datos estan conectadas.

