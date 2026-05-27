# Sincronizar GitHub con FTP cPanel

Este proyecto ya incluye el workflow:

```txt
.github/workflows/deploy-ftp.yml
```

Cuando hagas `push` a `main`, GitHub Actions:

1. Descarga el repo.
2. Instala dependencias PHP de `frontend` y `backend`.
3. Instala dependencias Node del `frontend`.
4. Ejecuta `npm run build`.
5. Sube por FTP:
   - `frontend/` hacia `/public_html/frontend/`
   - `backend/` hacia `/public_html/backend/`

## 1. Secrets en GitHub

En GitHub entra a:

```txt
Repositorio -> Settings -> Secrets and variables -> Actions -> New repository secret
```

Crea estos secrets:

```txt
FTP_SERVER=ftp.saborcentral.com
FTP_USERNAME=deploypanaderia@saborcentral.com
FTP_PASSWORD=TU_PASSWORD_FTP
FTP_PORT=21
```

No subas la contrasena al repositorio.

## 2. Rutas en cPanel

Configura los document root asi:

```txt
delicas.saborcentral.com      -> /home/TU_USUARIO/public_html/frontend/public
api.delicas.saborcentral.com  -> /home/TU_USUARIO/public_html/backend/public
```

Si el dominio principal no permite cambiar document root, crea un subdominio o dominio adicional que apunte a:

```txt
/public_html/frontend/public
```

## 3. Archivos .env en cPanel

GitHub Actions no sube `.env`, por seguridad. Debes crearlos una vez en cPanel.

`/public_html/frontend/.env`:

```env
APP_NAME="Delicias"
APP_ENV=production
APP_KEY=
APP_DEBUG=false
APP_URL=https://delicas.saborcentral.com
BACKEND_API_BASE_URL=https://api.delicas.saborcentral.com

DB_CONNECTION=mysql
DB_HOST=localhost
DB_PORT=3306
DB_DATABASE=TU_BASE_DATOS
DB_USERNAME=TU_USUARIO_DB
DB_PASSWORD=TU_PASSWORD_DB

JWT_SECRET=USA_EL_MISMO_SECRETO_EN_FRONTEND_Y_BACKEND
DOCUMENT_VALIDATION_REQUIRED=false
SESSION_DRIVER=file
SESSION_DOMAIN=.delicas.saborcentral.com
QUEUE_CONNECTION=sync
CACHE_STORE=file
MAIL_MAILER=log
```

`/public_html/backend/.env`:

```env
APP_NAME="Delicias API"
APP_ENV=production
APP_KEY=
APP_DEBUG=false
APP_URL=https://api.delicas.saborcentral.com

DB_CONNECTION=mysql
DB_HOST=localhost
DB_PORT=3306
DB_DATABASE=TU_BASE_DATOS
DB_USERNAME=TU_USUARIO_DB
DB_PASSWORD=TU_PASSWORD_DB

JWT_SECRET=USA_EL_MISMO_SECRETO_EN_FRONTEND_Y_BACKEND
DOCUMENT_VALIDATION_REQUIRED=false
SESSION_DRIVER=file
SESSION_DOMAIN=.delicas.saborcentral.com
QUEUE_CONNECTION=sync
CACHE_STORE=file
MAIL_MAILER=log
```

Despues entra al Terminal de cPanel.

En `/public_html/frontend`:

```bash
php artisan key:generate --force
php artisan config:clear
php artisan route:clear
php artisan view:clear
php artisan config:cache
```

En `/public_html/backend`:

```bash
php artisan key:generate --force
php artisan config:clear
php artisan route:clear
php artisan view:clear
php artisan config:cache
```

## 4. Probar

Abre:

```txt
https://api.delicas.saborcentral.com/api/health
https://delicas.saborcentral.com
https://delicas.saborcentral.com/admin/login
```

## 5. Ejecutar manualmente

En GitHub:

```txt
Actions -> Deploy cPanel FTP -> Run workflow
```

