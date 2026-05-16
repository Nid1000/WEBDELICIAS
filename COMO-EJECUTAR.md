# Como ejecutar (Frontend Laravel + Backend API)

## 1) Base de datos (MySQL)

- La BD se llama `delicias_bakery`.
- Importa `WEBNIDA/delicias_bakery.sql` en tu MySQL.
- Verifica el `.env` del backend: `WEBNIDA/backend/.env`
  - `DB_HOST=127.0.0.1`
  - `DB_PORT=3306`
  - `DB_DATABASE=delicias_bakery`
  - `DB_USERNAME=root`
  - `DB_PASSWORD=` si tu root no tiene clave

## 2) Backend API (Laravel) - puerto 5001

En PowerShell:

```ps1
cd C:\Users\usuario\Downloads\APPNID\WEBNIDA\backend
npm run backend
```

Pruebas rapidas:

- `http://127.0.0.1:5001/up`
- `http://127.0.0.1:5001/api/categorias`
- `http://127.0.0.1:5001/uploads/`

## 3) Frontend web (Laravel Blade) - puerto 3000

En otra terminal:

```ps1
cd C:\Users\usuario\Downloads\APPNID\WEBNIDA\frontend
composer install
npm install
npm run build
..\tools\php\php.exe artisan serve --host=0.0.0.0 --port=3000
```

Configura `frontend/.env`:

```env
APP_URL=http://127.0.0.1:3000
BACKEND_API_BASE_URL=http://127.0.0.1:5001
```

## 4) Flutter (Android)

### Opcion A: Wi-Fi

- Configura en la app: `192.168.152.1:5001`

### Opcion B: USB

En tu PC:

```ps1
adb devices
adb reverse tcp:5001 tcp:5001
```

En la app configura: `127.0.0.1:5001`

## Nota importante

- Si el backend no esta encendido, el frontend Laravel no podra cargar datos.
- Si el frontend no conecta, revisa `BACKEND_API_BASE_URL` en `frontend/.env`.
